<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExamAttemptException;
use App\Http\Controllers\Controller;
use App\Mail\ExamInviteMail;
use App\Models\ExamRegistration;
use App\Services\AuthorizationService;
use App\Services\ExamAttemptService;
use App\Services\ExamTypeAccessService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ExamRegistrationController extends Controller
{
    public function __construct(
        protected ExamAttemptService $examAttemptService,
        protected TelegramService $telegramService,
        protected ExamTypeAccessService $examTypeAccess,
        protected AuthorizationService $authorization,
    ) {}

    public function review(Request $request, ExamRegistration $examRegistration)
    {
        $this->authorization->ensureCanAccessRegistration(auth()->user(), 'exam-registrations.view', $examRegistration);

        $examRegistration->load([
            'applicant',
            'exam.examType',
            'approvedByUser:id,name',
        ]);

        $user = auth()->user();
        $canUnapprove = $this->authorization->can($user, 'exam-registrations.unapprove', $examRegistration->exam->examType);

        return Inertia::render('Admin/ExamRegistrations/Review', [
            'registration' => [
                'id' => $examRegistration->id,
                'approved' => $examRegistration->approved,
                'approved_at' => $examRegistration->approved_at?->toIso8601String(),
                'approved_by_user' => $examRegistration->approvedByUser
                    ? [
                        'id' => $examRegistration->approvedByUser->id,
                        'name' => $examRegistration->approvedByUser->name,
                    ]
                    : null,
            ],
            'applicant' => $examRegistration->applicant,
            'exam' => $examRegistration->exam->only(['id', 'name_ru', 'name_kk', 'name_en', 'language']),
            'examType' => $examRegistration->exam->examType->only(['id', 'name_ru', 'name_kk', 'name_en']),
            'canApprove' => ! $examRegistration->approved
                && $this->authorization->can($user, 'exam-registrations.approve', $examRegistration->exam->examType),
            'canUnapprove' => $canUnapprove && $examRegistration->approved,
            'canDelete' => $this->authorization->can($user, 'exam-registrations.delete', $examRegistration->exam->examType),
            'backUrl' => $this->resolveReviewBackUrl($request, $examRegistration),
        ]);
    }

    public function approve(ExamRegistration $examRegistration)
    {
        $this->authorization->ensureCanAccessRegistration(auth()->user(), 'exam-registrations.approve', $examRegistration);

        try {
            $this->processApproval($examRegistration);
        } catch (ExamAttemptException $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'approve' => 'Не удалось одобрить запись. Проверьте настройки очереди.',
            ]);
        }

        $examRegistration->loadMissing('exam');
        $channel = $examRegistration->exam->require_telegram_verification ? 'Telegram' : 'email';
        $deliveryMessage = $examRegistration->exam->require_telegram_verification
            ? "Ссылка отправлена в {$channel}."
            : 'Ссылка будет отправлена на email.';

        return back()->with('success', "Запись на экзамен одобрена. {$deliveryMessage}");
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', 'exists:exam_registrations,id'],
        ]);

        $user = auth()->user();

        $registrations = ExamRegistration::query()
            ->whereIn('id', $validated['registration_ids'])
            ->where('approved', false)
            ->with(['applicant', 'exam.examType'])
            ->get();

        $approvedCount = 0;
        $errors = [];

        foreach ($registrations as $registration) {
            $registration->loadMissing('exam.examType');

            if (! $this->authorization->can($user, 'exam-registrations.approve', $registration->exam->examType)) {
                $errors[] = "Запись #{$registration->id}: нет доступа.";

                continue;
            }

            try {
                $this->processApproval($registration);
                $approvedCount++;
            } catch (ExamAttemptException $e) {
                $errors[] = "Запись #{$registration->id}: {$e->getMessage()}";
            } catch (\Throwable $e) {
                $errors[] = "Запись #{$registration->id}: не удалось поставить приглашение в очередь.";
            }
        }

        if ($approvedCount === 0 && $errors !== []) {
            return back()->withErrors([
                'approve' => $errors[0],
            ])->with('bulk_approve_errors', $errors);
        }

        $message = "Одобрено записей: {$approvedCount}.";

        if ($errors !== []) {
            $message .= ' Не удалось одобрить: '.count($errors).'.';
        }

        return back()
            ->with('success', $message)
            ->with('bulk_approve_errors', $errors);
    }

    public function unapprove(ExamRegistration $examRegistration)
    {
        $this->authorization->ensureCanAccessRegistration(auth()->user(), 'exam-registrations.unapprove', $examRegistration);

        $examRegistration->update([
            'approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Одобрение записи на экзамен отменено');
    }

    public function destroy(ExamRegistration $examRegistration)
    {
        $this->authorization->ensureCanAccessRegistration(auth()->user(), 'exam-registrations.delete', $examRegistration);

        DB::transaction(function () use ($examRegistration) {
            $examRegistration->examAttempts()->each(fn ($attempt) => $attempt->delete());
            $examRegistration->delete();
        });

        return back()->with('success', 'Заявка на экзамен удалена.');
    }

    public function updateDate(Request $request, ExamRegistration $examRegistration)
    {
        $this->authorization->ensureCanAccessRegistration(auth()->user(), 'exam-registrations.edit-date', $examRegistration);

        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $this->applyRegistrationDate($examRegistration, $validated['date']);

        return back()->with('success', 'Дата регистрации обновлена.');
    }

    public function bulkUpdateDate(Request $request)
    {
        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', 'exists:exam_registrations,id'],
            'date' => ['required', 'date'],
        ]);

        $user = auth()->user();

        $registrations = ExamRegistration::query()
            ->whereIn('id', $validated['registration_ids'])
            ->with(['exam.examType'])
            ->get();

        $updatedCount = 0;
        $errors = [];

        foreach ($registrations as $registration) {
            $registration->loadMissing('exam.examType');

            if (! $this->authorization->can($user, 'exam-registrations.edit-date', $registration->exam->examType)) {
                $errors[] = "Запись #{$registration->id}: нет доступа.";

                continue;
            }

            $this->applyRegistrationDate($registration, $validated['date']);
            $updatedCount++;
        }

        if ($updatedCount === 0 && $errors !== []) {
            return back()->withErrors([
                'date' => $errors[0],
            ])->with('bulk_date_errors', $errors);
        }

        $message = "Обновлено дат: {$updatedCount}.";

        if ($errors !== []) {
            $message .= ' Не удалось обновить: '.count($errors).'.';
        }

        return back()
            ->with('success', $message)
            ->with('bulk_date_errors', $errors);
    }

    private function applyRegistrationDate(ExamRegistration $examRegistration, string $date): void
    {
        DB::transaction(function () use ($examRegistration, $date) {
            $examRegistration->update(['date' => $date]);

            $examRegistration->examAttempts()->update(['date' => $date]);
        });
    }

    private function resolveReviewBackUrl(Request $request, ExamRegistration $examRegistration): string
    {
        $source = $request->query('source');

        if ($source === 'exam') {
            return route('admin.exams.applicants', $examRegistration->exam_id);
        }

        return route('admin.exam-types.applicants', $examRegistration->exam->examType);
    }

    private function processApproval(ExamRegistration $examRegistration): void
    {
        DB::transaction(function () use ($examRegistration) {
            $examRegistration->update([
                'approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            $attempt = $this->examAttemptService->createPendingAttempt(
                $examRegistration->fresh(['applicant', 'exam'])
            );

            $applicant = $attempt->applicant;
            $exam = $attempt->exam;
            $examUrl = route('public.exam.show', $attempt->token, absolute: true);

            if ($exam->require_telegram_verification) {
                $sent = $this->telegramService->sendExamInvite(
                    $applicant->telegram_chat_id,
                    $exam->localizedName($exam->language),
                    $examUrl,
                    $exam->duration_minutes
                );

                if (! $sent) {
                    throw new ExamAttemptException(
                        'Не удалось отправить ссылку в Telegram. Проверьте настройки бота.'
                    );
                }
            } else {
                Mail::to($applicant->email)->queue(new ExamInviteMail(
                    $exam->localizedName($exam->language),
                    $examUrl,
                    $exam->duration_minutes
                ));
            }
        });
    }
}

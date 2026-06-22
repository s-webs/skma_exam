<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExamAttemptException;
use App\Http\Controllers\Controller;
use App\Mail\ExamInviteMail;
use App\Models\ExamRegistration;
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
    ) {}

    public function review(Request $request, ExamRegistration $examRegistration)
    {
        $this->examTypeAccess->ensureCanAccessRegistration(auth()->user(), $examRegistration);

        $examRegistration->load([
            'applicant',
            'exam.examType',
            'approvedByUser:id,name',
        ]);

        $user = auth()->user();
        $isRegistrator = $user->hasRole('registrator');

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
            'exam' => $examRegistration->exam->only(['id', 'name', 'language']),
            'examType' => $examRegistration->exam->examType->only(['id', 'name']),
            'canApprove' => ! $examRegistration->approved,
            'canUnapprove' => ! $isRegistrator && $examRegistration->approved,
            'backUrl' => $this->resolveReviewBackUrl($request, $examRegistration),
        ]);
    }

    public function approve(ExamRegistration $examRegistration)
    {
        $this->examTypeAccess->ensureCanAccessRegistration(auth()->user(), $examRegistration);

        try {
            $this->processApproval($examRegistration);
        } catch (ExamAttemptException $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'approve' => 'Не удалось отправить ссылку на email. Проверьте настройки почты.',
            ]);
        }

        $examRegistration->loadMissing('exam');
        $channel = $examRegistration->exam->require_telegram_verification ? 'Telegram' : 'email';

        return back()->with('success', "Запись на экзамен одобрена. Ссылка отправлена в {$channel}.");
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

            if (! $this->examTypeAccess->canAccess($user, $registration->exam->examType)) {
                $errors[] = "Запись #{$registration->id}: нет доступа.";

                continue;
            }

            try {
                $this->processApproval($registration);
                $approvedCount++;
            } catch (ExamAttemptException $e) {
                $errors[] = "Запись #{$registration->id}: {$e->getMessage()}";
            } catch (\Throwable $e) {
                $errors[] = "Запись #{$registration->id}: не удалось отправить приглашение.";
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
        if (auth()->user()->hasRole('registrator')) {
            abort(403, 'Регистратор не может отменять одобрение.');
        }

        $this->examTypeAccess->ensureCanAccessRegistration(auth()->user(), $examRegistration);

        $examRegistration->update([
            'approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Одобрение записи на экзамен отменено');
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
                    $exam->name,
                    $examUrl,
                    $exam->duration_minutes
                );

                if (! $sent) {
                    throw new ExamAttemptException(
                        'Не удалось отправить ссылку в Telegram. Проверьте настройки бота.'
                    );
                }
            } else {
                Mail::to($applicant->email)->send(new ExamInviteMail(
                    $exam->name,
                    $examUrl,
                    $exam->duration_minutes
                ));
            }
        });
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExamAttemptException;
use App\Http\Controllers\Controller;
use App\Models\ExamRegistration;
use App\Services\ExamAttemptService;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;

class ExamRegistrationController extends Controller
{
    public function __construct(
        protected ExamAttemptService $examAttemptService,
        protected TelegramService $telegramService,
    ) {}

    public function approve(ExamRegistration $examRegistration)
    {
        try {
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
            });
        } catch (ExamAttemptException $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }

        return back()->with('success', 'Запись на экзамен одобрена. Ссылка отправлена в Telegram.');
    }

    public function unapprove(ExamRegistration $examRegistration)
    {
        $examRegistration->update([
            'approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Одобрение записи на экзамен отменено');
    }
}

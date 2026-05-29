<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\ExamAttemptException;
use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamAttemptController extends Controller
{
    public function __construct(
        protected ExamAttemptService $examAttemptService,
        protected TelegramService $telegramService,
    ) {}

    public function show(string $token)
    {
        $attempt = $this->examAttemptService->findByToken($token);
        $attempt = $this->examAttemptService->expireIfNeeded($attempt);

        if ($attempt->status === 'in_progress') {
            return redirect()->route('public.exam.take', $token);
        }

        if ($attempt->status === 'completed') {
            return redirect()->route('public.exam.complete', $token);
        }

        if ($attempt->status === 'expired') {
            return Inertia::render('Public/Exam/Expired', $this->expiredPayload($attempt));
        }

        return Inertia::render('Public/Exam/Intro', $this->introPayload($attempt));
    }

    public function start(Request $request, string $token)
    {
        try {
            $attempt = $this->examAttemptService->findByToken($token);
            $attempt = $this->examAttemptService->startAttempt($attempt, $request);
        } catch (ExamAttemptException $e) {
            return back()->withErrors(['exam' => $e->getMessage()]);
        }

        return redirect()->route('public.exam.take', $token);
    }

    public function take(string $token)
    {
        $attempt = $this->examAttemptService->findByToken($token);
        $attempt = $this->examAttemptService->expireIfNeeded($attempt);

        if ($attempt->status === 'pending') {
            return redirect()->route('public.exam.show', $token);
        }

        if ($attempt->status === 'completed') {
            return redirect()->route('public.exam.complete', $token);
        }

        if ($attempt->status === 'expired') {
            return Inertia::render('Public/Exam/Expired', $this->expiredPayload($attempt));
        }

        return Inertia::render('Public/Exam/Take', [
            'attempt' => [
                'token' => $attempt->token,
                'expires_at' => $attempt->expires_at?->toIso8601String(),
                'started_at' => $attempt->started_at?->toIso8601String(),
            ],
            'exam' => [
                'name' => $attempt->exam->name,
                'duration_minutes' => $attempt->exam->duration_minutes,
            ],
            'questions' => $this->examAttemptService->buildQuestionsPayload($attempt),
            'savedAnswers' => $this->examAttemptService->buildSavedAnswersMap($attempt),
        ]);
    }

    public function saveAnswer(Request $request, string $token)
    {
        $validated = $request->validate([
            'question_id' => 'required|integer',
            'answer_id' => 'required|integer',
        ]);

        try {
            $attempt = $this->examAttemptService->findByToken($token);
            $this->examAttemptService->saveAnswer(
                $attempt,
                (int) $validated['question_id'],
                (int) $validated['answer_id']
            );
        } catch (ExamAttemptException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function finish(Request $request, string $token)
    {
        try {
            $attempt = $this->examAttemptService->findByToken($token);
            $result = $this->examAttemptService->finishAttempt($attempt);

            $attempt->load(['applicant', 'exam']);
            if ($attempt->applicant->telegram_chat_id) {
                $this->telegramService->sendExamResults(
                    $attempt->applicant->telegram_chat_id,
                    $attempt->exam->name,
                    $result->total_score,
                    $result->passed
                );
            }
        } catch (ExamAttemptException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['exam' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('public.exam.complete', $token)]);
        }

        return redirect()->route('public.exam.complete', $token);
    }

    public function complete(string $token)
    {
        $attempt = $this->examAttemptService->findByToken($token);
        $attempt->load(['exam', 'result']);

        if ($attempt->status === 'expired' && ! $attempt->result) {
            return Inertia::render('Public/Exam/Expired', $this->expiredPayload($attempt));
        }

        if ($attempt->status !== 'completed' || ! $attempt->result) {
            return redirect()->route('public.exam.show', $token);
        }

        $result = $attempt->result;

        return Inertia::render('Public/Exam/Complete', [
            'exam' => [
                'name' => $attempt->exam->name,
            ],
            'result' => [
                'passed' => $result->passed,
                'total_score' => $result->total_score,
                'correct_answers' => $result->correct_answers,
                'total_questions' => $result->total_questions,
                'passing_score' => $result->passing_score,
                'time_spent_seconds' => $result->time_spent_seconds,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function introPayload(ExamAttempt $attempt): array
    {
        return [
            'attempt' => [
                'token' => $attempt->token,
                'status' => $attempt->status,
            ],
            'exam' => [
                'name' => $attempt->exam->name,
                'description' => $attempt->exam->description,
                'duration_minutes' => $attempt->exam->duration_minutes,
                'questions_count' => $attempt->exam->questions_count,
            ],
            'applicant' => [
                'name' => $attempt->applicant->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function expiredPayload(ExamAttempt $attempt): array
    {
        return [
            'exam' => [
                'name' => $attempt->exam->name,
            ],
            'attempt' => [
                'token' => $attempt->token,
            ],
        ];
    }
}

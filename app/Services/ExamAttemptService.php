<?php

namespace App\Services;

use App\Exceptions\ExamAttemptException;
use App\Models\Answer;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamRegistration;
use App\Models\ExamResult;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamAttemptService
{
    public function findByToken(string $token): ExamAttempt
    {
        return ExamAttempt::query()
            ->where('token', $token)
            ->with(['exam.examType', 'applicant', 'examRegistration'])
            ->firstOrFail();
    }

    public function createPendingAttempt(ExamRegistration $registration): ExamAttempt
    {
        $registration->loadMissing(['applicant', 'exam']);

        $applicant = $registration->applicant;
        $exam = $registration->exam;

        if ($exam->require_telegram_verification) {
            if (empty($applicant->telegram_chat_id)) {
                throw ExamAttemptException::noTelegram();
            }
        } elseif (empty($applicant->email)) {
            throw new ExamAttemptException('У абитуриента не указан email. Одобрение невозможно.');
        }

        if (! $exam->is_active) {
            throw ExamAttemptException::examInactive();
        }

        $activeQuestionsCount = $exam->questions()->where('is_active', true)->count();
        if ($activeQuestionsCount < $exam->questions_count) {
            throw ExamAttemptException::notEnoughQuestions($exam->questions_count, $activeQuestionsCount);
        }

        if ($this->hasActiveAttempt($applicant->id, $exam->id)) {
            throw ExamAttemptException::activeAttemptExists();
        }

        if ($this->maxAttemptsExceeded($applicant->id, $exam)) {
            throw ExamAttemptException::maxAttemptsReached();
        }

        $existingPending = ExamAttempt::query()
            ->where('exam_registration_id', $registration->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        return DB::transaction(function () use ($registration, $applicant, $exam) {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'applicant_id' => $applicant->id,
                'exam_registration_id' => $registration->id,
                'token' => Str::random(64),
                'date' => $registration->date?->toDateString() ?? now()->toDateString(),
                'status' => 'pending',
            ]);

            $questionIds = $exam->questions()
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($exam->questions_count)
                ->pluck('id');

            foreach ($questionIds->values() as $order => $questionId) {
                $attempt->questions()->create([
                    'question_id' => $questionId,
                    'question_order' => $order + 1,
                ]);
            }

            return $attempt->fresh(['exam', 'applicant']);
        });
    }

    public function startAttempt(ExamAttempt $attempt, Request $request): ExamAttempt
    {
        $attempt->loadMissing(['exam', 'examRegistration']);

        if (! $attempt->examRegistration?->approved) {
            throw ExamAttemptException::registrationNotApproved();
        }

        $this->expireIfNeeded($attempt);

        if ($attempt->status === 'in_progress') {
            return $attempt;
        }

        if ($attempt->status !== 'pending') {
            throw ExamAttemptException::invalidStatus($attempt->status);
        }

        $exam = $attempt->exam;

        $attempt->update([
            'started_at' => now(),
            'expires_at' => now()->addMinutes($exam->duration_minutes),
            'status' => 'in_progress',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ]);

        return $attempt->fresh();
    }

    public function saveAnswer(ExamAttempt $attempt, int $questionId, int $answerId): ExamAttemptAnswer
    {
        $this->expireIfNeeded($attempt);

        if ($attempt->status !== 'in_progress') {
            throw ExamAttemptException::invalidStatus($attempt->status);
        }

        $belongsToAttempt = $attempt->questions()
            ->where('question_id', $questionId)
            ->exists();

        if (! $belongsToAttempt) {
            throw new ExamAttemptException('Вопрос не входит в эту попытку.');
        }

        $answer = Answer::query()
            ->where('question_id', $questionId)
            ->where('id', $answerId)
            ->firstOrFail();

        return ExamAttemptAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'answer_id' => $answer->id,
                'is_correct' => $answer->is_correct,
                'answered_at' => now(),
            ]
        );
    }

    public function finishAttempt(ExamAttempt $attempt): ExamResult
    {
        $this->expireIfNeeded($attempt);

        if ($attempt->status === 'completed' && $attempt->result) {
            return $attempt->result;
        }

        if (! in_array($attempt->status, ['in_progress', 'expired'], true)) {
            throw ExamAttemptException::invalidStatus($attempt->status);
        }

        return DB::transaction(function () use ($attempt) {
            $attempt->loadMissing(['exam', 'applicant', 'answers']);

            $exam = $attempt->exam;
            $totalQuestions = $attempt->questions()->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $passed = $correctAnswers >= $exam->passing_score;
            $totalScore = $totalQuestions > 0
                ? (int) round($correctAnswers / $totalQuestions * 100)
                : 0;

            $timeSpent = 0;
            if ($attempt->started_at) {
                $timeSpent = (int) $attempt->started_at->diffInSeconds(now());
            }

            $attempt->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $result = ExamResult::updateOrCreate(
                ['exam_attempt_id' => $attempt->id],
                [
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'total_score' => $totalScore,
                    'passing_score' => $exam->passing_score,
                    'passed' => $passed,
                    'time_spent_seconds' => $timeSpent,
                ]
            );

            return $result;
        });
    }

    public function expireIfNeeded(ExamAttempt $attempt): ExamAttempt
    {
        if (
            $attempt->status === 'in_progress'
            && $attempt->expires_at
            && now()->greaterThan($attempt->expires_at)
        ) {
            $attempt->update(['status' => 'expired']);

            return $attempt->fresh();
        }

        return $attempt;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildQuestionsPayload(ExamAttempt $attempt): array
    {
        $attemptQuestions = $attempt->questions()
            ->with(['question.answers'])
            ->orderBy('question_order')
            ->get();

        return $attemptQuestions->map(function ($attemptQuestion) use ($attempt) {
            $question = $attemptQuestion->question;

            return [
                'id' => $question->id,
                'order' => $attemptQuestion->question_order,
                'content' => $question->content,
                'image_url' => $question->imageUrl(),
                'answers' => $this->shuffledAnswersPayload($attempt, $question),
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function shuffledAnswersPayload(ExamAttempt $attempt, Question $question): array
    {
        return $question->answers
            ->sortBy(fn (Answer $answer) => $this->answerShuffleKey($attempt->id, $question->id, $answer->id))
            ->values()
            ->map(fn (Answer $answer) => [
                'id' => $answer->id,
                'content' => $answer->content,
                'image_url' => $answer->imageUrl(),
            ])
            ->all();
    }

    private function answerShuffleKey(int $attemptId, int $questionId, int $answerId): string
    {
        return hash('xxh128', "{$attemptId}:{$questionId}:{$answerId}");
    }

    /**
     * @return array<int, int|null>
     */
    public function buildSavedAnswersMap(ExamAttempt $attempt): array
    {
        return $attempt->answers()
            ->get()
            ->mapWithKeys(fn (ExamAttemptAnswer $a) => [$a->question_id => $a->answer_id])
            ->all();
    }

    private function hasActiveAttempt(int $applicantId, int $examId): bool
    {
        return ExamAttempt::query()
            ->where('applicant_id', $applicantId)
            ->where('exam_id', $examId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();
    }

    private function maxAttemptsExceeded(int $applicantId, $exam): bool
    {
        if ($exam->max_attempts === null) {
            return false;
        }

        $completedCount = ExamAttempt::query()
            ->where('applicant_id', $applicantId)
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['completed', 'expired'])
            ->count();

        return $completedCount >= $exam->max_attempts;
    }
}

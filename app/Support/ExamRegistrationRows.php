<?php

namespace App\Support;

use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use Illuminate\Support\Collection;

class ExamRegistrationRows
{
    /**
     * @param  iterable<ExamRegistration>  $registrations
     * @return array<int, array<string, mixed>>
     */
    public static function flatten(iterable $registrations): array
    {
        $collection = $registrations instanceof Collection
            ? $registrations
            : collect($registrations);

        $repeatRegistrationIds = self::repeatRegistrationIds($collection);

        $rows = [];

        foreach ($collection as $registration) {
            $attempts = $registration->relationLoaded('examAttempts')
                ? $registration->examAttempts
                : collect();

            if ($attempts->isEmpty()) {
                $rows[] = self::row($registration, null, $repeatRegistrationIds);

                continue;
            }

            foreach ($attempts as $attempt) {
                $rows[] = self::row($registration, $attempt, $repeatRegistrationIds);
            }
        }

        return $rows;
    }

    /**
     * @param  Collection<int, ExamRegistration>  $registrations
     * @return array<int, true>
     */
    private static function repeatRegistrationIds(Collection $registrations): array
    {
        if ($registrations->isEmpty()) {
            return [];
        }

        $firstRegistrationIds = [];

        foreach ($registrations->unique(fn (ExamRegistration $registration) => $registration->applicant_id.':'.$registration->exam_id) as $registration) {
            $pairKey = $registration->applicant_id.':'.$registration->exam_id;

            $firstRegistrationIds[$pairKey] = ExamRegistration::query()
                ->where('applicant_id', $registration->applicant_id)
                ->where('exam_id', $registration->exam_id)
                ->min('id');
        }

        $repeatIds = [];

        foreach ($registrations as $registration) {
            $pairKey = $registration->applicant_id.':'.$registration->exam_id;
            $firstId = $firstRegistrationIds[$pairKey] ?? $registration->id;

            if ($registration->id > $firstId) {
                $repeatIds[$registration->id] = true;
            }
        }

        return $repeatIds;
    }

    /**
     * @param  array<int, true>  $repeatRegistrationIds
     * @return array<string, mixed>
     */
    private static function row(
        ExamRegistration $registration,
        ?ExamAttempt $attempt,
        array $repeatRegistrationIds,
    ): array {
        $applicant = $registration->applicant;
        $result = self::attemptResult($attempt);
        $reportUrl = self::attemptReportUrl($attempt);

        return [
            'attempt_id' => $attempt?->id,
            'registration_id' => $registration->id,
            'date' => $registration->date?->toDateString(),
            'status' => $attempt?->status,
            'approved' => $registration->approved,
            'approved_at' => $registration->approved_at?->toIso8601String(),
            'approved_by_user' => $registration->approvedByUser
                ? [
                    'id' => $registration->approvedByUser->id,
                    'name' => $registration->approvedByUser->name,
                ]
                : null,
            'is_repeat_registration' => isset($repeatRegistrationIds[$registration->id]),
            'result' => $result,
            'report_url' => $reportUrl,
            'applicant' => $applicant
                ? [
                    'id' => $applicant->id,
                    'name' => $applicant->name,
                    'identifier' => $applicant->identifier,
                ]
                : null,
            'exam' => $registration->relationLoaded('exam') && $registration->exam
                ? [
                    'id' => $registration->exam->id,
                    'name' => $registration->exam->localizedName($registration->exam->language),
                ]
                : null,
        ];
    }

    /**
     * @return array{passed: bool, total_score: int, correct_answers: int, total_questions: int}|null
     */
    private static function attemptResult(?ExamAttempt $attempt): ?array
    {
        if ($attempt?->status !== 'completed' || ! $attempt->result) {
            return null;
        }

        return [
            'passed' => $attempt->result->passed,
            'total_score' => $attempt->result->total_score,
            'correct_answers' => $attempt->result->correct_answers,
            'total_questions' => $attempt->result->total_questions,
        ];
    }

    private static function attemptReportUrl(?ExamAttempt $attempt): ?string
    {
        if ($attempt?->status !== 'completed' || ! $attempt->result) {
            return null;
        }

        return route('public.exam.report', $attempt->token);
    }
}

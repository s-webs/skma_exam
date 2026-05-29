<?php

namespace App\Support;

use App\Models\ExamAttempt;
use App\Models\ExamRegistration;

class ExamRegistrationRows
{
    /**
     * @param  iterable<ExamRegistration>  $registrations
     * @return array<int, array<string, mixed>>
     */
    public static function flatten(iterable $registrations): array
    {
        $rows = [];

        foreach ($registrations as $registration) {
            $attempts = $registration->relationLoaded('examAttempts')
                ? $registration->examAttempts
                : collect();

            if ($attempts->isEmpty()) {
                $rows[] = self::row($registration, null);

                continue;
            }

            foreach ($attempts as $attempt) {
                $rows[] = self::row($registration, $attempt);
            }
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(ExamRegistration $registration, ?ExamAttempt $attempt): array
    {
        $applicant = $registration->applicant;

        return [
            'attempt_id' => $attempt?->id,
            'registration_id' => $registration->id,
            'status' => $attempt?->status,
            'approved' => $registration->approved,
            'approved_at' => $registration->approved_at?->toIso8601String(),
            'approved_by_user' => $registration->approvedByUser
                ? [
                    'id' => $registration->approvedByUser->id,
                    'name' => $registration->approvedByUser->name,
                ]
                : null,
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
                    'name' => $registration->exam->name,
                ]
                : null,
        ];
    }
}

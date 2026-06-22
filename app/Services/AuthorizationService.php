<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;

class AuthorizationService
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess
    ) {}

    public function isDeveloper(?User $user): bool
    {
        return $this->examTypeAccess->isDeveloper($user);
    }

    public function can(User $user, string $permission, ExamType|int|null $examType = null): bool
    {
        if ($this->isDeveloper($user)) {
            return true;
        }

        if (! $user->can($permission)) {
            return false;
        }

        if ($examType === null) {
            return true;
        }

        return $this->examTypeAccess->canAccess($user, $examType);
    }

    public function ensureCan(User $user, string $permission, ExamType|int|null $examType = null): void
    {
        if (! $this->can($user, $permission, $examType)) {
            abort(403, 'Недостаточно прав для выполнения действия.');
        }
    }

    public function ensureCanAccessExam(User $user, string $permission, Exam $exam): void
    {
        $exam->loadMissing('examType');
        $this->ensureCan($user, $permission, $exam->examType);
    }

    public function ensureCanAccessRegistration(User $user, string $permission, ExamRegistration $registration): void
    {
        $registration->loadMissing('exam.examType');
        $this->ensureCanAccessExam($user, $permission, $registration->exam);
    }
}

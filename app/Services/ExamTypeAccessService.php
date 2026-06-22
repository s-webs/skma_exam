<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ExamTypeAccessService
{
    public function isDeveloper(?User $user): bool
    {
        return $user !== null && $user->hasRole('developer');
    }

    public function canAccess(?User $user, ExamType|int $examType): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isDeveloper($user)) {
            return true;
        }

        $examTypeId = $examType instanceof ExamType ? $examType->id : $examType;

        if ($this->hasDirectUserAccess($user, $examTypeId)) {
            return true;
        }

        return $this->hasRoleAccess($user, $examTypeId);
    }

    /**
     * @return array<int>
     */
    public function accessibleExamTypeIds(User $user): array
    {
        if ($this->isDeveloper($user)) {
            return ExamType::query()->pluck('id')->all();
        }

        $directIds = DB::table('exam_type_user')
            ->where('user_id', $user->id)
            ->pluck('exam_type_id');

        $roleIds = $user->roles()->pluck('id');
        $roleGrantIds = $roleIds->isEmpty()
            ? collect()
            : DB::table('exam_type_role')
                ->whereIn('role_id', $roleIds)
                ->pluck('exam_type_id');

        return $directIds->merge($roleGrantIds)->unique()->values()->all();
    }

    /**
     * @param  Builder<ExamType>  $query
     * @return Builder<ExamType>
     */
    public function scopeAccessible(Builder $query, User $user): Builder
    {
        if ($this->isDeveloper($user)) {
            return $query;
        }

        $ids = $this->accessibleExamTypeIds($user);

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $ids);
    }

    public function ensureCanAccess(User $user, ExamType|int $examType): void
    {
        if (! $this->canAccess($user, $examType)) {
            abort(403, 'Нет доступа к этому типу экзамена.');
        }
    }

    public function ensureCanAccessExam(User $user, Exam $exam): void
    {
        $exam->loadMissing('examType');
        $this->ensureCanAccess($user, $exam->examType);
    }

    public function ensureCanAccessRegistration(User $user, ExamRegistration $registration): void
    {
        $registration->loadMissing('exam.examType');
        $this->ensureCanAccessExam($user, $registration->exam);
    }

    /**
     * @param  array<int>  $userIds
     * @param  array<int>  $roleIds
     */
    public function syncAccess(ExamType $examType, array $userIds, array $roleIds): void
    {
        $examType->users()->sync($userIds);
        $examType->roles()->sync($roleIds);
    }

    private function hasDirectUserAccess(User $user, int $examTypeId): bool
    {
        return DB::table('exam_type_user')
            ->where('exam_type_id', $examTypeId)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function hasRoleAccess(User $user, int $examTypeId): bool
    {
        $roleIds = $user->roles()->pluck('id');

        if ($roleIds->isEmpty()) {
            return false;
        }

        return DB::table('exam_type_role')
            ->where('exam_type_id', $examTypeId)
            ->whereIn('role_id', $roleIds)
            ->exists();
    }
}

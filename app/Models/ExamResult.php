<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'total_questions',
        'correct_answers',
        'total_score',
        'passing_score',
        'passed',
        'time_spent_seconds',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'total_score' => 'integer',
        'passing_score' => 'integer',
        'passed' => 'boolean',
        'time_spent_seconds' => 'integer',
    ];

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }
}

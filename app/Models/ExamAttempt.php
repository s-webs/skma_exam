<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'applicant_id',
        'exam_registration_id',
        'token',
        'date',
        'started_at',
        'completed_at',
        'expires_at',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function examRegistration(): BelongsTo
    {
        return $this->belongsTo(ExamRegistration::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamAttemptQuestion::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class);
    }
}

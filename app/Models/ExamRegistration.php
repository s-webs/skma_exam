<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamRegistration extends Model
{
    protected $fillable = [
        'applicant_id',
        'exam_id',
        'approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function examAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

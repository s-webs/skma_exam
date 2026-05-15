<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    protected $fillable = [
        'name',
        'email',
        'identifier',
        'address',
        'phone',
        'graduate_organization',
        'graduate_year',
        'speciality',
        'language',
        'verified',
        'approved',
        'approved_at',
        'approved_by',
        'telegram_token',
        'document_front',
        'document_back',
        'diplom',
        'certificate',
        'photo',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

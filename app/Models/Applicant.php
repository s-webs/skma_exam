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
        'telegram_token',
        'telegram_chat_id',
        'document_front',
        'document_back',
        'diplom',
        'certificate',
        'photo',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function examRegistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

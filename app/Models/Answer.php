<?php

namespace App\Models;

use App\Models\Concerns\HasStorageImageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasStorageImageUrl;

    protected $fillable = [
        'question_id',
        'content',
        'image_path',
        'is_correct',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function imageUrl(): ?string
    {
        return $this->storageImageUrl($this->image_path, 'answers');
    }
}

<?php

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\User;

test('exam type localized name uses ui locale with russian fallback', function () {
    $examType = ExamType::create([
        'name_ru' => 'Психотест',
        'name_kk' => 'Психотест KK',
        'name_en' => 'Psychotest',
        'slug' => 'psychotest',
        'is_active' => true,
    ]);

    expect($examType->localizedName('ru'))->toBe('Психотест');
    expect($examType->localizedName('kk'))->toBe('Психотест KK');
    expect($examType->localizedName('en'))->toBe('Psychotest');
    expect($examType->localizedName('kk'))->toBe('Психотест KK');
});

test('exam type localized name falls back to russian when translation missing', function () {
    $examType = ExamType::create([
        'name_ru' => 'Психотест',
        'slug' => 'psychotest-fallback',
        'is_active' => true,
    ]);

    expect($examType->localizedName('en'))->toBe('Психотест');
});

test('exam localized name uses exam language', function () {
    $examType = ExamType::create([
        'name_ru' => 'Тип',
        'slug' => 'type-lang',
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    $exam = Exam::create([
        'exam_type_id' => $examType->id,
        'name_ru' => 'Русский',
        'name_kk' => 'Орысша',
        'language' => 'kz',
        'duration_minutes' => 50,
        'questions_count' => 10,
        'passing_score' => 5,
        'is_active' => true,
        'created_by_user_id' => $user->id,
    ]);

    expect($exam->localizedName($exam->language))->toBe('Орысша');
    expect($exam->localizedName('en'))->toBe('Русский');
});

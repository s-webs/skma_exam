<?php

use App\Models\Answer;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamResultPdfService;
use App\Services\TelegramService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();

    $this->examType = ExamType::create([
        'name_ru' => 'Test Type',
        'slug' => 'test-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name_ru' => 'Test Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 2,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'created_by_user_id' => $this->admin->id,
    ]);

    foreach (range(1, 2) as $i) {
        $question = Question::create([
            'exam_id' => $this->exam->id,
            'content' => "Question {$i}",
            'is_active' => true,
            'created_by_user_id' => $this->admin->id,
        ]);

        Answer::create([
            'question_id' => $question->id,
            'content' => 'Wrong',
            'is_correct' => false,
            'created_by_user_id' => $this->admin->id,
        ]);

        Answer::create([
            'question_id' => $question->id,
            'content' => 'Correct',
            'is_correct' => true,
            'created_by_user_id' => $this->admin->id,
        ]);
    }

    $this->applicant = Applicant::create([
        'name' => 'Test Applicant',
        'email' => 'test@example.com',
        'identifier' => '123456789012',
        'address' => 'Address',
        'phone' => '+70000000000',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
        'telegram_chat_id' => '99999',
    ]);

    $registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'approved' => true,
    ]);

    $this->attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $this->applicant->id,
        'exam_registration_id' => $registration->id,
        'token' => str_repeat('a', 64),
        'date' => now()->toDateString(),
        'status' => 'completed',
        'started_at' => now()->subMinutes(30),
        'completed_at' => now(),
    ]);

    $this->attempt->questions()->create(['question_id' => Question::first()->id, 'question_order' => 1]);
    $this->attempt->questions()->create(['question_id' => Question::skip(1)->first()->id, 'question_order' => 2]);

    ExamResult::create([
        'exam_attempt_id' => $this->attempt->id,
        'total_questions' => 2,
        'correct_answers' => 2,
        'total_score' => 100,
        'passing_score' => 1,
        'passed' => true,
        'time_spent_seconds' => 1800,
    ]);
});

test('report pdf route returns pdf for completed attempt', function () {
    $response = $this->get(route('public.exam.report', $this->attempt->token));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(str_starts_with($response->getContent(), '%PDF'))->toBeTrue();
});

test('report pdf route returns 404 for pending attempt', function () {
    $pending = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $this->applicant->id,
        'token' => str_repeat('b', 64),
        'date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->get(route('public.exam.report', $pending->token))->assertNotFound();
});

test('pdf service renders non empty binary', function () {
    $pdf = app(ExamResultPdfService::class)->render($this->attempt);

    expect(strlen($pdf))->toBeGreaterThan(1000);
    expect(str_starts_with($pdf, '%PDF'))->toBeTrue();
});

test('pdf view data includes logo and single qr payload', function () {
    $data = app(ExamResultPdfService::class)->buildViewData($this->attempt);

    expect($data['logoDataUri'])->toStartWith('data:image/jpeg;base64,');
    expect($data['qrDataUri'])->toStartWith('data:image/svg+xml;base64,');
});

test('pdf html includes trilingual header and exam type row without entrance wording', function () {
    $html = view('pdf.exam-result-sheet', app(ExamResultPdfService::class)->buildViewData($this->attempt))->render();

    expect($html)->toContain('Лист результатов экзамена')
        ->and($html)->toContain('Емтихан нәтижелері парағы')
        ->and($html)->toContain('Exam results sheet')
        ->and($html)->not->toContain('вступительн')
        ->and($html)->not->toContain('Entrance EXAM')
        ->and($html)->not->toContain('Сканируйте QR')
        ->and($html)->toContain('Test Type / Test Exam')
        ->and($html)->toContain('Тип экзамена / Экзамен')
        ->and($html)->toContain('Емтихан түрі / Емтихан')
        ->and($html)->toContain('Exam type / Exam');

    expect(substr_count($html, 'data:image/svg+xml;base64,'))->toBe(1);
});

test('pdf html shows localized exam type and exam names per locale block', function () {
    $this->examType->update([
        'name_ru' => 'Психотест RU',
        'name_kk' => 'Психотест KK',
        'name_en' => 'Psychotest EN',
    ]);

    $this->exam->update([
        'name_ru' => 'Русский',
        'name_kk' => 'Орысша',
        'name_en' => 'Russian',
    ]);

    $html = view('pdf.exam-result-sheet', app(ExamResultPdfService::class)->buildViewData($this->attempt))->render();

    expect($html)->toContain('Психотест RU / Русский')
        ->and($html)->toContain('Психотест KK / Орысша')
        ->and($html)->toContain('Psychotest EN / Russian');
});

test('finish sends telegram report with pdf', function () {
    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamResultsWithReport')
            ->once()
            ->andReturn(true);
    });

    $this->exam->update(['require_telegram_verification' => true]);
    $this->attempt->update(['status' => 'in_progress', 'started_at' => now()]);
    $this->attempt->result()->delete();

    $this->postJson(route('public.exam.finish', $this->attempt->token))
        ->assertOk();
});

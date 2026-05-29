<?php

use App\Models\Answer;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamAttemptService;
use App\Services\TelegramService;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('ktbo');

    $this->examType = ExamType::create([
        'name' => 'Test Type',
        'slug' => 'test-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name' => 'Test Exam',
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
        'telegram_chat_id' => null,
    ]);

    $this->registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'approved' => false,
    ]);
});

test('approve is blocked without telegram chat id', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration))
        ->assertSessionHasErrors('approve');

    expect($this->registration->fresh()->approved)->toBeFalse();
    expect(ExamAttempt::count())->toBe(0);
});

test('approve creates attempt and sends telegram invite', function () {
    $this->applicant->update(['telegram_chat_id' => '12345']);

    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamInvite')
            ->once()
            ->withArgs(function ($chatId, $examName, $url, $duration) {
                return $chatId === '12345'
                    && $examName === 'Test Exam'
                    && str_contains($url, '/exam/')
                    && $duration === 45;
            })
            ->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->approved)->toBeTrue();
    expect(ExamAttempt::count())->toBe(1);

    $attempt = ExamAttempt::first();
    expect($attempt->status)->toBe('pending');
    expect($attempt->questions()->count())->toBe(2);
});

test('start sets expires_at from exam duration', function () {
    $this->applicant->update(['telegram_chat_id' => '12345']);

    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamInvite')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration));

    $attempt = ExamAttempt::first();

    $this->post(route('public.exam.start', $attempt->token))
        ->assertRedirect(route('public.exam.take', $attempt->token));

    $attempt->refresh();
    expect($attempt->status)->toBe('in_progress');
    expect($attempt->started_at)->not->toBeNull();
    expect($attempt->expires_at->equalTo($attempt->started_at->copy()->addMinutes(45)))->toBeTrue();
});

test('exam payload shuffles answers deterministically per attempt', function () {
    $question = Question::first();
    Answer::create([
        'question_id' => $question->id,
        'content' => 'Extra A',
        'is_correct' => false,
        'created_by_user_id' => $this->admin->id,
    ]);
    Answer::create([
        'question_id' => $question->id,
        'content' => 'Extra B',
        'is_correct' => false,
        'created_by_user_id' => $this->admin->id,
    ]);

    $this->applicant->update(['telegram_chat_id' => '12345']);

    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamInvite')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration));

    $attempt = ExamAttempt::first();
    $service = app(ExamAttemptService::class);

    $payload = $service->buildQuestionsPayload($attempt);
    $questionPayload = collect($payload)->firstWhere('id', $question->id);
    $dbOrder = $question->answers()->orderBy('id')->pluck('id')->all();
    $payloadOrder = collect($questionPayload['answers'])->pluck('id')->all();

    expect($payloadOrder)->not->toEqual($dbOrder);

    $payloadAgain = $service->buildQuestionsPayload($attempt->fresh());
    $payloadAgainOrder = collect($payloadAgain)->firstWhere('id', $question->id)['answers'];
    $payloadAgainOrder = collect($payloadAgainOrder)->pluck('id')->all();

    expect($payloadAgainOrder)->toEqual($payloadOrder);
});

test('finish calculates passed result', function () {
    $this->applicant->update(['telegram_chat_id' => '12345']);

    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamInvite')->once()->andReturn(true);
        $mock->shouldReceive('sendExamResultsWithReport')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration));

    $attempt = ExamAttempt::first();
    $this->post(route('public.exam.start', $attempt->token));

    $attempt->refresh();
    $questions = $attempt->questions()->with('question.answers')->orderBy('question_order')->get();

    foreach ($questions as $attemptQuestion) {
        $correctAnswer = $attemptQuestion->question->answers->firstWhere('is_correct', true);
        $this->postJson(route('public.exam.answers', $attempt->token), [
            'question_id' => $attemptQuestion->question_id,
            'answer_id' => $correctAnswer->id,
        ])->assertOk();
    }

    $this->postJson(route('public.exam.finish', $attempt->token))
        ->assertOk()
        ->assertJsonStructure(['redirect']);

    $attempt->refresh();
    expect($attempt->status)->toBe('completed');
    expect($attempt->result)->not->toBeNull();
    expect($attempt->result->correct_answers)->toBe(2);
    expect($attempt->result->passed)->toBeTrue();
});

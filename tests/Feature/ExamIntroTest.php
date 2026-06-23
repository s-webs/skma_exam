<?php

use App\Models\Applicant;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\User;
use App\Services\TelegramService;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('ktbo');

    $this->examType = ExamType::create([
        'name_ru' => 'Психотест (50 минут)',
        'name_kk' => 'Психотест (50 минут)',
        'name_en' => 'Psychotest (50 minutes)',
        'slug' => 'intro-exam-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->examType->roles()->attach(Role::where('name', 'ktbo')->first()->id);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name_ru' => 'Русский',
        'description' => 'Психотест на русском языке',
        'language' => 'ru',
        'duration_minutes' => 50,
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
        'email' => 'intro@example.com',
        'identifier' => '123456789012',
        'address' => 'Address',
        'phone' => '+70000000000',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
        'telegram_chat_id' => '12345',
    ]);

    $this->registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'approved' => false,
    ]);
});

test('exam intro shows localized exam type name for selected exam language', function () {
    $this->mock(TelegramService::class, function ($mock) {
        $mock->shouldReceive('sendExamInvite')->once()->andReturn(true);
    });

    $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $this->registration));

    $attempt = ExamAttempt::firstOrFail();

    $this->get(route('public.exam.show', $attempt->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Exam/Intro')
            ->where('exam_type_name', $this->examType->localizedName($this->exam->language))
            ->where('exam.name', $this->exam->localizedName($this->exam->language))
            ->missing('exam.description')
        );
});

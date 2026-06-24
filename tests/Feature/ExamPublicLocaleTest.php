<?php

use App\Models\Answer;
use App\Models\Applicant;
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
        'name_ru' => 'Locale Type',
        'slug' => 'locale-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->examType->roles()->attach(Role::where('name', 'ktbo')->first()->id);
});

function createLocaleExam(object $context, string $language): Exam
{
    $exam = Exam::create([
        'exam_type_id' => $context->examType->id,
        'name_ru' => 'Exam RU',
        'name_en' => 'Exam EN',
        'name_kk' => 'Exam KK',
        'description' => null,
        'language' => $language,
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $context->admin->id,
    ]);

    $question = Question::create([
        'exam_id' => $exam->id,
        'content' => 'Question',
        'is_active' => true,
        'created_by_user_id' => $context->admin->id,
    ]);

    Answer::create([
        'question_id' => $question->id,
        'content' => 'Wrong',
        'is_correct' => false,
        'created_by_user_id' => $context->admin->id,
    ]);

    Answer::create([
        'question_id' => $question->id,
        'content' => 'Correct',
        'is_correct' => true,
        'created_by_user_id' => $context->admin->id,
    ]);

    return $exam;
}

function approveAndStartAttempt(object $context, Exam $exam): ExamAttempt
{
    $applicant = Applicant::create([
        'name' => 'Locale Applicant',
        'email' => "locale-{$exam->language}@example.com",
        'identifier' => str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
        'address' => 'Address',
        'phone' => '+70000000000',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => $exam->language,
        'verified' => true,
        'telegram_chat_id' => null,
    ]);

    $registration = ExamRegistration::create([
        'applicant_id' => $applicant->id,
        'exam_id' => $exam->id,
        'approved' => false,
    ]);

    test()->actingAs($context->admin)
        ->post(route('admin.exam-registrations.approve', $registration));

    $attempt = ExamAttempt::firstOrFail();
    test()->post(route('public.exam.start', $attempt->token));

    return $attempt->fresh();
}

test('exam take page passes locale from exam language', function () {
    $exam = createLocaleExam($this, 'en');
    $attempt = approveAndStartAttempt($this, $exam);

    $this->get(route('public.exam.take', $attempt->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Exam/Take')
            ->where('locale', 'en')
        );
});

test('exam complete page passes normalized locale for kazakh exam', function () {
    $exam = createLocaleExam($this, 'kz');
    $attempt = approveAndStartAttempt($this, $exam);

    $this->postJson(route('public.exam.finish', $attempt->token))->assertOk();

    $this->get(route('public.exam.complete', $attempt->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Exam/Complete')
            ->where('locale', 'kk')
        );
});

test('exam expired page passes locale from exam language', function () {
    $exam = createLocaleExam($this, 'en');
    $attempt = approveAndStartAttempt($this, $exam);

    $attempt->update([
        'expires_at' => now()->subMinute(),
        'status' => 'expired',
    ]);

    $this->get(route('public.exam.take', $attempt->token))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Exam/Expired')
            ->where('locale', 'en')
        );
});

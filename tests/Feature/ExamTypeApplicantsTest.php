<?php

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('developer');

    $this->examType = ExamType::create([
        'name_ru' => 'Applicants List Type',
        'slug' => 'applicants-list-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name_ru' => 'Applicants Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'created_by_user_id' => $this->admin->id,
    ]);
});

function createApplicantRegistration(Exam $exam, string $identifier, string $name): ExamRegistration
{
    $applicant = Applicant::create([
        'name' => $name,
        'email' => $identifier.'@example.com',
        'identifier' => $identifier,
        'address' => 'Address',
        'phone' => '77001112233',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
    ]);

    return ExamRegistration::create([
        'applicant_id' => $applicant->id,
        'exam_id' => $exam->id,
        'date' => now()->toDateString(),
        'approved' => true,
    ]);
}

test('exam type applicants page filters by identifier', function () {
    createApplicantRegistration($this->exam, '111111111111', 'First Applicant');
    createApplicantRegistration($this->exam, '222222222222', 'Second Applicant');

    $this->actingAs($this->admin)
        ->get(route('admin.exam-types.applicants', [
            'examType' => $this->examType,
            'identifier' => '222222',
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamTypes/Applicants')
            ->where('filters.identifier', '222222')
            ->has('rows', 1)
            ->where('rows.0.applicant.identifier', '222222222222')
        );
});

test('exam type applicants rows include result and report url for completed attempts', function () {
    $registration = createApplicantRegistration($this->exam, '333333333333', 'Completed Applicant');

    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $registration->applicant_id,
        'exam_registration_id' => $registration->id,
        'token' => str_repeat('b', 64),
        'date' => now()->toDateString(),
        'status' => 'completed',
        'started_at' => now()->subMinutes(30),
        'completed_at' => now(),
    ]);

    ExamResult::create([
        'exam_attempt_id' => $attempt->id,
        'total_questions' => 2,
        'correct_answers' => 2,
        'total_score' => 100,
        'passing_score' => 1,
        'passed' => true,
        'time_spent_seconds' => 1200,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.exam-types.applicants', $this->examType))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamTypes/Applicants')
            ->where('rows.0.result.passed', true)
            ->where('rows.0.result.total_score', 100)
            ->where('rows.0.report_url', route('public.exam.report', $attempt->token))
        );
});

test('exam type applicants rows omit result for pending attempts', function () {
    $registration = createApplicantRegistration($this->exam, '444444444444', 'Pending Applicant');

    ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $registration->applicant_id,
        'exam_registration_id' => $registration->id,
        'token' => str_repeat('c', 64),
        'date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.exam-types.applicants', $this->examType))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamTypes/Applicants')
            ->where('rows.0.result', null)
            ->where('rows.0.report_url', null)
        );
});

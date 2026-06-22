<?php

use App\Models\Answer;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->developer = User::factory()->create();
    $this->developer->assignRole('developer');

    $this->ktbo = User::factory()->create();
    $this->ktbo->assignRole('ktbo');

    $this->registrator = User::factory()->create();
    $this->registrator->assignRole('registrator');

    $this->examType = ExamType::create([
        'name_ru' => 'Date Test Type',
        'slug' => 'date-test',
        'description' => null,
        'is_active' => true,
    ]);

    $this->otherExamType = ExamType::create([
        'name_ru' => 'Other Date Type',
        'slug' => 'other-date-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->examType->roles()->attach(Role::where('name', 'ktbo')->first()->id);
    $this->examType->users()->attach($this->registrator->id);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name_ru' => 'Date Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $question = Question::create([
        'exam_id' => $this->exam->id,
        'content' => 'Q1',
        'is_active' => true,
        'created_by_user_id' => $this->developer->id,
    ]);

    Answer::create([
        'question_id' => $question->id,
        'content' => 'A',
        'is_correct' => true,
        'created_by_user_id' => $this->developer->id,
    ]);

    Answer::create([
        'question_id' => $question->id,
        'content' => 'B',
        'is_correct' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $this->applicant = Applicant::create([
        'name' => 'Date Applicant',
        'email' => 'date-test@example.com',
        'identifier' => '444444444444',
        'address' => 'Address',
        'phone' => '77001112233',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
    ]);

    $this->registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'date' => '2026-01-15',
        'approved' => false,
    ]);
});

test('ktbo can update registration date', function () {
    $this->actingAs($this->ktbo)
        ->patch(route('admin.exam-registrations.update-date', $this->registration), [
            'date' => '2026-03-20',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->date?->toDateString())->toBe('2026-03-20');
});

test('ktbo update registration date syncs linked attempts', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $this->applicant->id,
        'exam_registration_id' => $this->registration->id,
        'token' => 'date-sync-token',
        'date' => '2026-01-15',
        'status' => 'pending',
    ]);

    $this->actingAs($this->ktbo)
        ->patch(route('admin.exam-registrations.update-date', $this->registration), [
            'date' => '2026-04-10',
        ])
        ->assertRedirect();

    expect($attempt->fresh()->date?->toDateString())->toBe('2026-04-10');
});

test('registrator cannot update registration date', function () {
    $this->actingAs($this->registrator)
        ->patch(route('admin.exam-registrations.update-date', $this->registration), [
            'date' => '2026-03-20',
        ])
        ->assertForbidden();

    expect($this->registration->fresh()->date?->toDateString())->toBe('2026-01-15');
});

test('ktbo cannot update registration date without exam type access', function () {
    $otherExam = Exam::create([
        'exam_type_id' => $this->otherExamType->id,
        'name_ru' => 'Foreign Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $foreignRegistration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $otherExam->id,
        'date' => '2026-01-15',
        'approved' => false,
    ]);

    $this->actingAs($this->ktbo)
        ->patch(route('admin.exam-registrations.update-date', $foreignRegistration), [
            'date' => '2026-03-20',
        ])
        ->assertForbidden();
});

test('ktbo can bulk update registration dates', function () {
    $secondApplicant = Applicant::create([
        'name' => 'Second Date Applicant',
        'email' => 'second-date@example.com',
        'identifier' => '333333333333',
        'address' => 'Address',
        'phone' => '77001112234',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
    ]);

    $secondRegistration = ExamRegistration::create([
        'applicant_id' => $secondApplicant->id,
        'exam_id' => $this->exam->id,
        'date' => '2026-01-15',
        'approved' => false,
    ]);

    $this->actingAs($this->ktbo)
        ->post(route('admin.exam-registrations.bulk-update-date'), [
            'registration_ids' => [$this->registration->id, $secondRegistration->id],
            'date' => '2026-05-01',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->date?->toDateString())->toBe('2026-05-01');
    expect($secondRegistration->fresh()->date?->toDateString())->toBe('2026-05-01');
});

test('bulk update date skips registrations without access', function () {
    $otherExam = Exam::create([
        'exam_type_id' => $this->otherExamType->id,
        'name_ru' => 'Foreign Bulk Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $foreignRegistration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $otherExam->id,
        'date' => '2026-01-15',
        'approved' => false,
    ]);

    $this->actingAs($this->ktbo)
        ->post(route('admin.exam-registrations.bulk-update-date'), [
            'registration_ids' => [$this->registration->id, $foreignRegistration->id],
            'date' => '2026-05-01',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->date?->toDateString())->toBe('2026-05-01');
    expect($foreignRegistration->fresh()->date?->toDateString())->toBe('2026-01-15');
    expect(session('bulk_date_errors'))->not->toBeEmpty();
});

test('approval copies registration date to attempt', function () {
    Mail::fake();

    $this->registration->update(['date' => '2026-02-14']);

    $this->actingAs($this->ktbo)
        ->post(route('admin.exam-registrations.approve', $this->registration))
        ->assertRedirect();

    $attempt = ExamAttempt::query()
        ->where('exam_registration_id', $this->registration->id)
        ->first();

    expect($attempt)->not->toBeNull();
    expect($attempt->date?->toDateString())->toBe('2026-02-14');
});

test('applicants list includes registration date', function () {
    $this->actingAs($this->ktbo)
        ->get(route('admin.exam-types.applicants', $this->examType))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamTypes/Applicants')
            ->where('rows.0.date', '2026-01-15'));
});

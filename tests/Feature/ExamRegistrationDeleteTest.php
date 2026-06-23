<?php

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Permission;
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
        'name_ru' => 'Delete Reg Type',
        'slug' => 'delete-reg-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->otherExamType = ExamType::create([
        'name_ru' => 'Other Type',
        'slug' => 'other-delete-reg',
        'description' => null,
        'is_active' => true,
    ]);

    $ktboRole = Role::where('name', 'ktbo')->first();
    $this->examType->roles()->attach($ktboRole->id);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name_ru' => 'Delete Reg Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 3,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $this->otherExam = Exam::create([
        'exam_type_id' => $this->otherExamType->id,
        'name_ru' => 'Other Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 3,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->developer->id,
    ]);

    $this->applicant = Applicant::create([
        'name' => 'Delete Reg Applicant',
        'email' => 'delete-reg@example.com',
        'identifier' => '222222222222',
        'address' => 'Address',
        'phone' => '77002223344',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
    ]);
});

function createRegistrationWithAttempt(Exam $exam, Applicant $applicant, bool $approved = false): array
{
    $registration = ExamRegistration::create([
        'applicant_id' => $applicant->id,
        'exam_id' => $exam->id,
        'date' => now()->toDateString(),
        'approved' => $approved,
        'approved_at' => $approved ? now() : null,
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'applicant_id' => $applicant->id,
        'exam_registration_id' => $registration->id,
        'token' => str_repeat('d', 64),
        'date' => now()->toDateString(),
        'status' => $approved ? 'completed' : 'pending',
        'completed_at' => $approved ? now() : null,
    ]);

    $result = null;
    if ($approved) {
        $result = ExamResult::create([
            'exam_attempt_id' => $attempt->id,
            'total_questions' => 1,
            'correct_answers' => 1,
            'total_score' => 1,
            'passing_score' => 1,
            'passed' => true,
            'time_spent_seconds' => 60,
        ]);
    }

    return compact('registration', 'attempt', 'result');
}

test('ktbo with exam-registrations.delete can delete registration', function () {
    $this->ktbo->givePermissionTo('exam-registrations.delete');

    ['registration' => $registration] = createRegistrationWithAttempt($this->exam, $this->applicant);

    $this->actingAs($this->ktbo)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ExamRegistration::find($registration->id))->toBeNull();
});

test('ktbo without exam-registrations.delete cannot delete registration', function () {
    ['registration' => $registration] = createRegistrationWithAttempt($this->exam, $this->applicant);

    $this->actingAs($this->ktbo)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertForbidden();

    expect(ExamRegistration::find($registration->id))->not->toBeNull();
});

test('registrator without exam-registrations.delete cannot delete registration', function () {
    $this->examType->users()->attach($this->registrator->id);

    ['registration' => $registration] = createRegistrationWithAttempt($this->exam, $this->applicant);

    $this->actingAs($this->registrator)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertForbidden();

    expect(ExamRegistration::find($registration->id))->not->toBeNull();
});

test('first non-duplicate registration can be deleted', function () {
    $registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'date' => now()->toDateString(),
        'approved' => false,
    ]);

    $this->actingAs($this->developer)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ExamRegistration::find($registration->id))->toBeNull();
});

test('deleting approved registration cascades attempts and results', function () {
    ['registration' => $registration, 'attempt' => $attempt, 'result' => $result] = createRegistrationWithAttempt(
        $this->exam,
        $this->applicant,
        approved: true,
    );

    $this->actingAs($this->developer)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ExamRegistration::find($registration->id))->toBeNull();
    expect(ExamAttempt::find($attempt->id))->toBeNull();
    expect(ExamResult::find($result->id))->toBeNull();
});

test('deleting registration does not delete applicant', function () {
    $registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'date' => now()->toDateString(),
        'approved' => false,
    ]);

    $this->actingAs($this->developer)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertRedirect();

    expect(Applicant::find($this->applicant->id))->not->toBeNull();
});

test('cannot delete registration from inaccessible exam type', function () {
    $this->ktbo->givePermissionTo('exam-registrations.delete');

    $registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->otherExam->id,
        'date' => now()->toDateString(),
        'approved' => false,
    ]);

    $this->actingAs($this->ktbo)
        ->delete(route('admin.exam-registrations.destroy', $registration))
        ->assertForbidden();

    expect(ExamRegistration::find($registration->id))->not->toBeNull();
});

test('exam-registrations.delete permission is registered', function () {
    expect(Permission::where('name', 'exam-registrations.delete')->exists())->toBeTrue();
});

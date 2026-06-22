<?php

use App\Models\Answer;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamTypeAccessService;
use Database\Seeders\RoleSeeder;
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
        'name' => 'Access Test Type',
        'slug' => 'access-test',
        'description' => null,
        'is_active' => true,
    ]);

    $this->otherExamType = ExamType::create([
        'name' => 'Other Type',
        'slug' => 'other-type',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name' => 'Access Exam',
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
        'name' => 'Test Applicant',
        'email' => 'access-test@example.com',
        'identifier' => '555555555555',
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
        'approved' => false,
    ]);
});

test('developer sees all exam types', function () {
    $response = $this->actingAs($this->developer)
        ->get(route('admin.exam-types.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ExamTypes/Index')
        ->has('examTypes', 2));
});

test('developer can sync exam type access', function () {
    $registratorRole = Role::where('name', 'registrator')->first();

    $response = $this->actingAs($this->developer)
        ->put(route('admin.exam-types.update', $this->examType), [
            'name' => $this->examType->name,
            'description' => null,
            'is_active' => true,
            'user_ids' => [$this->registrator->id],
            'role_ids' => [$registratorRole->id],
        ]);

    $response->assertRedirect(route('admin.exam-types.index'));

    expect($this->examType->fresh()->users()->pluck('users.id')->all())
        ->toContain($this->registrator->id);
    expect($this->examType->fresh()->roles()->pluck('roles.id')->all())
        ->toContain($registratorRole->id);
});

test('ktbo without grant gets forbidden on exam type show', function () {
    $this->actingAs($this->ktbo)
        ->get(route('admin.exam-types.show', $this->examType))
        ->assertForbidden();
});

test('ktbo with role grant can view exam type', function () {
    $ktboRole = Role::where('name', 'ktbo')->first();
    $this->examType->roles()->attach($ktboRole->id);

    $this->actingAs($this->ktbo)
        ->get(route('admin.exam-types.show', $this->examType))
        ->assertOk();
});

test('registrator with user grant can view applicants', function () {
    $this->examType->users()->attach($this->registrator->id);

    $this->actingAs($this->registrator)
        ->get(route('admin.exam-types.applicants', $this->examType))
        ->assertOk();
});

test('registrator cannot access global applicants index', function () {
    $this->actingAs($this->registrator)
        ->get(route('admin.applicants.index'))
        ->assertForbidden();
});

test('registrator cannot create questions', function () {
    $this->examType->users()->attach($this->registrator->id);

    $this->actingAs($this->registrator)
        ->get(route('admin.exams.questions.create', $this->exam))
        ->assertForbidden();
});

test('registrator can view questions index when granted', function () {
    $this->examType->users()->attach($this->registrator->id);

    $this->actingAs($this->registrator)
        ->get(route('admin.exams.questions.index', $this->exam))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('canManageQuestions', false));
});

test('registrator can review registration with profile and documents', function () {
    $this->examType->users()->attach($this->registrator->id);

    $this->applicant->update([
        'document_front' => 'applicants/documents/front.jpg',
        'photo' => 'applicants/photos/photo.jpg',
    ]);

    $this->actingAs($this->registrator)
        ->get(route('admin.exam-registrations.review', $this->registration))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamRegistrations/Review')
            ->where('canApprove', true)
            ->where('applicant.name', 'Test Applicant')
            ->where('applicant.document_front', 'applicants/documents/front.jpg')
        );
});

test('registrator review forbidden for ungranted exam type', function () {
    $this->actingAs($this->registrator)
        ->get(route('admin.exam-registrations.review', $this->registration))
        ->assertForbidden();
});

test('registrator approve works for granted exam type', function () {
    $this->examType->users()->attach($this->registrator->id);

    $this->actingAs($this->registrator)
        ->post(route('admin.exam-registrations.approve', $this->registration))
        ->assertRedirect();

    expect($this->registration->fresh()->approved)->toBeTrue();
});

test('registrator approve forbidden for ungranted exam type', function () {
    $otherExam = Exam::create([
        'exam_type_id' => $this->otherExamType->id,
        'name' => 'Other Exam',
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

    $registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $otherExam->id,
        'approved' => false,
    ]);

    $this->actingAs($this->registrator)
        ->post(route('admin.exam-registrations.approve', $registration))
        ->assertForbidden();
});

test('registrator cannot unapprove registration', function () {
    $this->examType->users()->attach($this->registrator->id);
    $this->registration->update(['approved' => true]);

    $this->actingAs($this->registrator)
        ->post(route('admin.exam-registrations.unapprove', $this->registration))
        ->assertForbidden();
});

test('registrator cannot delete exam attempt', function () {
    $this->examType->users()->attach($this->registrator->id);

    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $this->applicant->id,
        'exam_registration_id' => $this->registration->id,
        'token' => 'test-token',
        'date' => now()->toDateString(),
        'status' => 'pending',
    ]);

    $this->actingAs($this->registrator)
        ->delete(route('admin.exam-attempts.destroy', $attempt))
        ->assertForbidden();
});

test('ktbo cannot destroy exam type', function () {
    $this->actingAs($this->ktbo)
        ->delete(route('admin.exam-types.destroy', $this->examType))
        ->assertForbidden();
});

test('exam type access service resolves user and role grants', function () {
    $service = app(ExamTypeAccessService::class);
    $registratorRole = Role::where('name', 'registrator')->first();

    expect($service->canAccess($this->registrator, $this->examType))->toBeFalse();

    $this->examType->roles()->attach($registratorRole->id);
    expect($service->canAccess($this->registrator, $this->examType))->toBeTrue();

    $this->examType->roles()->detach();
    $this->examType->users()->attach($this->registrator->id);
    expect($service->canAccess($this->registrator, $this->examType))->toBeTrue();
});

test('bulk approve approves multiple pending registrations', function () {
    $this->examType->users()->attach($this->registrator->id);

    $secondApplicant = Applicant::create([
        'name' => 'Second Applicant',
        'email' => 'second@example.com',
        'identifier' => '666666666666',
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
        'approved' => false,
    ]);

    $this->actingAs($this->registrator)
        ->post(route('admin.exam-registrations.bulk-approve'), [
            'registration_ids' => [$this->registration->id, $secondRegistration->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->approved)->toBeTrue();
    expect($secondRegistration->fresh()->approved)->toBeTrue();
});

test('bulk approve skips registrations without access', function () {
    $this->examType->users()->attach($this->registrator->id);

    $otherExam = Exam::create([
        'exam_type_id' => $this->otherExamType->id,
        'name' => 'Other Exam',
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
        'approved' => false,
    ]);

    $this->actingAs($this->registrator)
        ->post(route('admin.exam-registrations.bulk-approve'), [
            'registration_ids' => [$this->registration->id, $foreignRegistration->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->registration->fresh()->approved)->toBeTrue();
    expect($foreignRegistration->fresh()->approved)->toBeFalse();
    expect(session('bulk_approve_errors'))->not->toBeEmpty();
});

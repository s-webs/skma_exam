<?php

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('ktbo');

    $examType = ExamType::create([
        'name_ru' => 'Test Type',
        'slug' => 'admin-attempt-test',
        'description' => null,
        'is_active' => true,
    ]);

    $examType->roles()->attach(Role::where('name', 'ktbo')->first()->id);

    $this->exam = Exam::create([
        'exam_type_id' => $examType->id,
        'name_ru' => 'Test Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 1,
        'passing_score' => 1,
        'max_attempts' => 3,
        'is_active' => true,
        'created_by_user_id' => $this->admin->id,
    ]);

    $this->applicant = Applicant::create([
        'name' => 'Test Applicant',
        'email' => 'admin-attempt@example.com',
        'identifier' => '111111111111',
        'address' => 'Address',
        'phone' => '77000000001',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
        'telegram_chat_id' => '999',
    ]);

    $this->registration = ExamRegistration::create([
        'applicant_id' => $this->applicant->id,
        'exam_id' => $this->exam->id,
        'approved' => true,
    ]);

    $this->attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'applicant_id' => $this->applicant->id,
        'exam_registration_id' => $this->registration->id,
        'token' => str_repeat('a', 64),
        'date' => now()->toDateString(),
        'status' => 'pending',
    ]);
});

test('deleting exam attempt does not delete applicant or registration', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.exam-attempts.destroy', $this->attempt))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ExamAttempt::find($this->attempt->id))->toBeNull();
    expect(Applicant::find($this->applicant->id))->not->toBeNull();
    expect(ExamRegistration::find($this->registration->id))->not->toBeNull();
});

<?php

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use App\Services\RegistrationEmailService;
use App\Support\ExamRegistrationRows;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

beforeEach(function () {
    Mail::fake();
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();

    $this->examType = ExamType::create([
        'name' => 'Repeat Reg Type',
        'slug' => 'repeat-reg',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name' => 'Repeat Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 2,
        'passing_score' => 1,
        'max_attempts' => 3,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->admin->id,
    ]);
});

function seedRepeatEmailDraft(string $slug, Exam $exam, ?int $applicantId, array $personal): string
{
    $service = app(RegistrationEmailService::class);
    $token = Str::random(32);

    Cache::put($service->cacheKey($token), [
        'slug' => $slug,
        'exam_id' => (string) $exam->id,
        'applicant_id' => $applicantId,
        'personal' => $service->normalizePersonal($personal),
        'code' => '123456',
        'code_expires_at' => now()->addMinutes(10)->timestamp,
        'verified' => true,
    ], now()->addHours(2));

    return $token;
}

function repeatRegistrationPayload(Exam $exam, array $overrides = []): array
{
    return array_merge([
        'exam_id' => $exam->id,
        'name' => 'Repeat User',
        'email' => 'repeat@example.com',
        'identifier' => '123123123123',
        'address' => 'Address',
        'phone' => '77001112233',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
    ], $overrides);
}

function submitEmailRegistration($test, ExamType $examType, Exam $exam, array $payload, ?int $existingApplicantId = null): void
{
    $personal = [
        'name' => $payload['name'],
        'email' => $payload['email'],
        'identifier' => $payload['identifier'],
        'address' => $payload['address'],
        'phone' => $payload['phone'],
    ];

    $token = seedRepeatEmailDraft($examType->slug, $exam, $existingApplicantId, $personal);

    $test->withSession([
        RegistrationEmailService::SESSION_TOKEN_KEY => $token,
        RegistrationEmailService::SESSION_VERIFIED_KEY => true,
    ])->post(route('public.registration.store', $examType->slug), $payload)->assertOk();
}

test('repeat registration creates a new exam registration row', function () {
    $payload = repeatRegistrationPayload($this->exam);

    submitEmailRegistration($this, $this->examType, $this->exam, $payload);

    $applicant = Applicant::where('identifier', $payload['identifier'])->firstOrFail();

    $firstRegistration = ExamRegistration::query()
        ->where('applicant_id', $applicant->id)
        ->where('exam_id', $this->exam->id)
        ->firstOrFail();

    $firstRegistration->update([
        'approved' => true,
        'approved_at' => now(),
        'approved_by' => $this->admin->id,
    ]);

    submitEmailRegistration($this, $this->examType, $this->exam, $payload, $applicant->id);

    $registrations = ExamRegistration::query()
        ->where('applicant_id', $applicant->id)
        ->where('exam_id', $this->exam->id)
        ->orderBy('id')
        ->get();

    expect($registrations)->toHaveCount(2);
    expect($registrations->last()->approved)->toBeFalse();
    expect($registrations->last()->id)->toBeGreaterThan($registrations->first()->id);
});

test('repeat registration is flagged in admin rows', function () {
    $payload = repeatRegistrationPayload($this->exam, [
        'email' => 'repeat-rows@example.com',
        'identifier' => '321321321321',
    ]);

    submitEmailRegistration($this, $this->examType, $this->exam, $payload);

    $applicant = Applicant::where('identifier', $payload['identifier'])->firstOrFail();

    submitEmailRegistration($this, $this->examType, $this->exam, $payload, $applicant->id);

    $registrations = ExamRegistration::query()
        ->with(['applicant', 'exam'])
        ->where('applicant_id', $applicant->id)
        ->orderBy('id')
        ->get();

    $rows = ExamRegistrationRows::flatten($registrations);

    expect(collect($rows)->where('is_repeat_registration', false))->toHaveCount(1);
    expect(collect($rows)->where('is_repeat_registration', true))->toHaveCount(1);
});

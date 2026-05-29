<?php

use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\User;
use App\Services\RegistrationTelegramService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();

    $this->examType = ExamType::create([
        'name' => 'Psixotest',
        'slug' => 'psixotest-reg',
        'description' => null,
        'is_active' => true,
    ]);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name' => 'Русский',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 50,
        'questions_count' => 2,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'created_by_user_id' => $this->admin->id,
    ]);
});

function seedVerifiedRegistrationDraft(
    string $slug,
    Exam $exam,
    Applicant $applicant,
    array $personal
): string {
    $service = app(RegistrationTelegramService::class);
    $token = Str::random(32);

    Cache::put($service->cacheKey($token), [
        'slug' => $slug,
        'exam_id' => (string) $exam->id,
        'applicant_id' => $applicant->id,
        'personal' => $personal,
        'code' => '123456',
        'code_expires_at' => now()->addMinutes(10)->timestamp,
        'chat_id' => $applicant->telegram_chat_id ?? '12345',
        'verified' => true,
    ], now()->addHours(2));

    return $token;
}

test('resume returns resumed_from_existing flag', function () {
    $applicant = Applicant::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'identifier' => '987654321098',
        'address' => 'Existing Address',
        'phone' => '77001112233',
        'graduate_organization' => 'University',
        'graduate_year' => '2019',
        'speciality' => 'IT',
        'language' => 'ru',
        'verified' => true,
        'telegram_token' => Str::random(32),
        'telegram_chat_id' => '55555',
    ]);

    $response = $this->postJson(route('public.registration.telegram.resume', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        'name' => $applicant->name,
        'email' => $applicant->email,
        'identifier' => $applicant->identifier,
        'address' => $applicant->address,
        'phone' => $applicant->phone,
    ]);

    $response->assertOk()
        ->assertJsonPath('resumed_from_existing', true)
        ->assertJsonPath('applicant.identifier', '987654321098');
});

test('existing applicant registration without files preserves documents', function () {
    $applicant = Applicant::create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'identifier' => '987654321098',
        'address' => 'Existing Address',
        'phone' => '77001112233',
        'graduate_organization' => 'University',
        'graduate_year' => '2019',
        'speciality' => 'IT',
        'language' => 'ru',
        'verified' => true,
        'telegram_token' => Str::random(32),
        'telegram_chat_id' => '55555',
        'document_front' => 'applicants/documents/existing-front.webp',
        'photo' => 'applicants/photos/existing-photo.webp',
    ]);

    $service = app(RegistrationTelegramService::class);
    $personal = $service->normalizePersonal([
        'name' => $applicant->name,
        'email' => $applicant->email,
        'identifier' => $applicant->identifier,
        'address' => $applicant->address,
        'phone' => $applicant->phone,
    ]);

    $token = seedVerifiedRegistrationDraft($this->examType->slug, $this->exam, $applicant, $personal);

    $this->withSession([
        RegistrationTelegramService::SESSION_TOKEN_KEY => $token,
        RegistrationTelegramService::SESSION_VERIFIED_KEY => true,
    ])->post(route('public.registration.store', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        'name' => $applicant->name,
        'email' => $applicant->email,
        'identifier' => $applicant->identifier,
        'address' => $applicant->address,
        'phone' => $applicant->phone,
        'graduate_organization' => 'New University',
        'graduate_year' => '2020',
        'speciality' => 'Updated Spec',
    ])->assertOk();

    $applicant->refresh();

    expect($applicant->document_front)->toBe('applicants/documents/existing-front.webp');
    expect($applicant->photo)->toBe('applicants/photos/existing-photo.webp');
    expect($applicant->graduate_organization)->toBe('New University');
});

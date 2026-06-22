<?php

use App\Mail\ExamInviteMail;
use App\Mail\RegistrationVerificationCodeMail;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use App\Services\RegistrationEmailService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Mail::fake();
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('ktbo');

    $this->examType = ExamType::create([
        'name' => 'Email Reg Type',
        'slug' => 'email-reg',
        'description' => null,
        'is_active' => true,
    ]);

    $this->examType->roles()->attach(Role::where('name', 'ktbo')->first()->id);

    $this->exam = Exam::create([
        'exam_type_id' => $this->examType->id,
        'name' => 'Email Exam',
        'description' => null,
        'language' => 'ru',
        'duration_minutes' => 45,
        'questions_count' => 2,
        'passing_score' => 1,
        'max_attempts' => 1,
        'is_active' => true,
        'require_telegram_verification' => false,
        'created_by_user_id' => $this->admin->id,
    ]);
});

function seedVerifiedEmailDraft(string $slug, Exam $exam, ?int $applicantId, array $personal): string
{
    $service = app(RegistrationEmailService::class);
    $token = Str::random(32);

    Cache::put($service->cacheKey($token), [
        'slug' => $slug,
        'exam_id' => (string) $exam->id,
        'applicant_id' => $applicantId,
        'personal' => app(RegistrationEmailService::class)->normalizePersonal($personal),
        'code' => '123456',
        'code_expires_at' => now()->addMinutes(10)->timestamp,
        'verified' => true,
    ], now()->addHours(2));

    return $token;
}

test('email init sends verification code', function () {
    $response = $this->postJson(route('public.registration.email.init', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        'name' => 'New Applicant',
        'email' => 'new@example.com',
        'identifier' => '123456789012',
        'address' => 'Address',
        'phone' => '77001112233',
    ]);

    $response->assertOk()
        ->assertJsonPath('email', 'new@example.com')
        ->assertJsonPath('verified', false);

    Mail::assertSent(RegistrationVerificationCodeMail::class, function ($mail) {
        return $mail->hasTo('new@example.com');
    });
});

test('email verify marks session verified', function () {
    $init = $this->postJson(route('public.registration.email.init', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        'name' => 'New Applicant',
        'email' => 'verify@example.com',
        'identifier' => '111111111111',
        'address' => 'Address',
        'phone' => '77001112233',
    ]);

    $init->assertOk();

    $verify = $this->postJson(route('public.registration.email.verify', $this->examType->slug), [
        'code' => '000000',
    ]);

    $verify->assertStatus(422);

    $token = session(RegistrationEmailService::SESSION_TOKEN_KEY);
    $draft = Cache::get(app(RegistrationEmailService::class)->cacheKey($token));
    expect($draft)->not->toBeNull();

    $verify = $this->postJson(route('public.registration.email.verify', $this->examType->slug), [
        'code' => $draft['code'],
    ]);

    $verify->assertOk()->assertJsonPath('verified', true);
});

test('registration store works with verified email session', function () {
    $personal = [
        'name' => 'Email User',
        'email' => 'store@example.com',
        'identifier' => '222222222222',
        'address' => 'Address',
        'phone' => '77001112233',
    ];

    $token = seedVerifiedEmailDraft($this->examType->slug, $this->exam, null, $personal);

    $response = $this->withSession([
        RegistrationEmailService::SESSION_TOKEN_KEY => $token,
        RegistrationEmailService::SESSION_VERIFIED_KEY => true,
    ])->post(route('public.registration.store', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        ...$personal,
        'graduate_organization' => 'University',
        'graduate_year' => '2020',
        'speciality' => 'IT',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('applicants', [
        'email' => 'store@example.com',
        'telegram_chat_id' => null,
    ]);

    $this->assertDatabaseHas('exam_registrations', [
        'exam_id' => $this->exam->id,
    ]);
});

test('registration store rejects telegram session when email exam selected', function () {
    $personal = [
        'name' => 'Email User',
        'email' => 'reject@example.com',
        'identifier' => '333333333333',
        'address' => 'Address',
        'phone' => '77001112233',
    ];

    $response = $this->withSession([
        'registration.telegram_token' => Str::random(32),
        'registration.telegram_verified' => true,
    ])->post(route('public.registration.store', $this->examType->slug), [
        'exam_id' => $this->exam->id,
        ...$personal,
        'graduate_organization' => 'University',
        'graduate_year' => '2020',
        'speciality' => 'IT',
    ]);

    $response->assertSessionHasErrors('email');
});

test('approve without telegram sends exam invite email', function () {
    foreach (range(1, 2) as $i) {
        $question = \App\Models\Question::create([
            'exam_id' => $this->exam->id,
            'content' => "Question {$i}",
            'is_active' => true,
            'created_by_user_id' => $this->admin->id,
        ]);

        \App\Models\Answer::create([
            'question_id' => $question->id,
            'content' => 'Wrong',
            'is_correct' => false,
            'created_by_user_id' => $this->admin->id,
        ]);

        \App\Models\Answer::create([
            'question_id' => $question->id,
            'content' => 'Correct',
            'is_correct' => true,
            'created_by_user_id' => $this->admin->id,
        ]);
    }

    $applicant = Applicant::create([
        'name' => 'Email Applicant',
        'email' => 'approve@example.com',
        'identifier' => '444444444444',
        'address' => 'Address',
        'phone' => '77001112233',
        'graduate_organization' => 'Org',
        'graduate_year' => '2020',
        'speciality' => 'Spec',
        'language' => 'ru',
        'verified' => true,
        'telegram_chat_id' => null,
    ]);

    $registration = ExamRegistration::create([
        'applicant_id' => $applicant->id,
        'exam_id' => $this->exam->id,
        'approved' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.exam-registrations.approve', $registration));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertSent(ExamInviteMail::class, function ($mail) use ($applicant) {
        return $mail->hasTo($applicant->email);
    });

    expect($registration->fresh()->approved)->toBeTrue();
});

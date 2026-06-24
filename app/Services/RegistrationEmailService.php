<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\RegistrationVerificationCodeMail;

class RegistrationEmailService
{
    public const CACHE_PREFIX = 'reg_email:';

    public const SESSION_TOKEN_KEY = 'registration.email_token';

    public const SESSION_VERIFIED_KEY = 'registration.email_verified';

    public function __construct(
        protected RegistrationTelegramService $registrationTelegram
    ) {}

    public function cacheKey(string $token): string
    {
        return self::CACHE_PREFIX.$token;
    }

    /**
     * @param  array<string, mixed>  $personal
     * @return array{name: string, email: string, identifier: string, address: string, phone: string}
     */
    public function normalizePersonal(array $personal): array
    {
        return $this->registrationTelegram->normalizePersonal($personal);
    }

    /**
     * @param  array<string, mixed>  $draftPersonal
     * @param  array<string, mixed>  $submitted
     */
    public function personalMatches(array $draftPersonal, array $submitted): bool
    {
        return $this->registrationTelegram->personalMatches($draftPersonal, $submitted);
    }

    public function invalidateVerification(Request $request): void
    {
        $request->session()->forget(self::SESSION_VERIFIED_KEY);

        $token = $request->session()->get(self::SESSION_TOKEN_KEY);
        if (! $token) {
            return;
        }

        $draft = $this->getDraft($token);
        if (! $draft) {
            return;
        }

        $draft['verified'] = false;
        Cache::put($this->cacheKey($token), $draft, now()->addHours(2));
    }

    /**
     * @param  array{name: string, email: string, identifier: string, address: string, phone: string}  $personal
     * @return array{token: string, code_sent: bool}
     */
    public function createDraft(string $slug, string $examId, array $personal): array
    {
        $token = Str::random(32);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $personal = $this->normalizePersonal($personal);

        Cache::put($this->cacheKey($token), [
            'slug' => $slug,
            'exam_id' => $examId,
            'applicant_id' => null,
            'personal' => $personal,
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10)->timestamp,
            'verified' => false,
        ], now()->addHours(2));

        $codeSent = $this->sendCode($personal['email'], $code, $this->examLanguage($examId));

        return ['token' => $token, 'code_sent' => $codeSent];
    }

    /**
     * @param  array{name: string, email: string, identifier: string, address: string, phone: string}|null  $personalOverride
     * @return array{token: string, code_sent: bool}
     */
    public function createDraftFromApplicant(
        string $slug,
        string $examId,
        Applicant $applicant,
        ?array $personalOverride = null
    ): array {
        $token = Str::random(32);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $personal = $personalOverride ?? $this->normalizePersonal([
            'name' => $applicant->name,
            'email' => $applicant->email,
            'identifier' => $applicant->identifier,
            'address' => $applicant->address,
            'phone' => $applicant->phone,
        ]);

        Cache::put($this->cacheKey($token), [
            'slug' => $slug,
            'exam_id' => $examId,
            'applicant_id' => $applicant->id,
            'personal' => $personal,
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10)->timestamp,
            'verified' => false,
        ], now()->addHours(2));

        $codeSent = $this->sendCode($personal['email'], $code, $this->examLanguage($examId));

        return ['token' => $token, 'code_sent' => $codeSent];
    }

    public function getDraft(string $token): ?array
    {
        $draft = Cache::get($this->cacheKey($token));

        return is_array($draft) ? $draft : null;
    }

    public function verifyCode(string $token, string $code): bool
    {
        $draft = $this->getDraft($token);
        if (! $draft) {
            return false;
        }

        if (($draft['verified'] ?? false) === true) {
            return true;
        }

        if (now()->timestamp > ($draft['code_expires_at'] ?? 0)) {
            return false;
        }

        if (! hash_equals($draft['code'], $code)) {
            return false;
        }

        $draft['verified'] = true;
        Cache::put($this->cacheKey($token), $draft, now()->addHours(2));

        return true;
    }

    public function markSessionVerified(Request $request, string $token): void
    {
        $request->session()->put(self::SESSION_TOKEN_KEY, $token);
        $request->session()->put(self::SESSION_VERIFIED_KEY, true);
    }

    public function isSessionVerified(Request $request): bool
    {
        if (! $request->session()->get(self::SESSION_VERIFIED_KEY)) {
            return false;
        }

        $token = $request->session()->get(self::SESSION_TOKEN_KEY);
        if (! $token) {
            return false;
        }

        $draft = $this->getDraft($token);

        return $draft && ($draft['verified'] ?? false) === true;
    }

    public function getVerifiedDraft(Request $request): ?array
    {
        if (! $this->isSessionVerified($request)) {
            return null;
        }

        $token = $request->session()->get(self::SESSION_TOKEN_KEY);

        return $token ? $this->getDraft($token) : null;
    }

    public function clearSession(Request $request): void
    {
        $token = $request->session()->get(self::SESSION_TOKEN_KEY);
        if ($token) {
            Cache::forget($this->cacheKey($token));
        }

        $request->session()->forget([
            self::SESSION_TOKEN_KEY,
            self::SESSION_VERIFIED_KEY,
        ]);
    }

    public function resendCode(string $token): bool
    {
        $draft = $this->getDraft($token);
        if (! $draft) {
            return false;
        }

        $email = $draft['personal']['email'] ?? null;
        if (! $email) {
            return false;
        }

        $draft['code'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $draft['code_expires_at'] = now()->addMinutes(10)->timestamp;
        $draft['verified'] = false;
        Cache::put($this->cacheKey($token), $draft, now()->addHours(2));

        return $this->sendCode($email, $draft['code'], $this->examLanguage((string) $draft['exam_id']));
    }

    public function sendCode(string $email, string $code, string $examLanguage): bool
    {
        try {
            Mail::to($email)
                ->locale($this->normalizeExamLocale($examLanguage))
                ->queue(new RegistrationVerificationCodeMail($code));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function examLanguage(string $examId): string
    {
        return Exam::query()->findOrFail($examId)->language;
    }

    private function normalizeExamLocale(string $locale): string
    {
        return match ($locale) {
            'kz' => 'kk',
            default => $locale,
        };
    }
}

<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RegistrationTelegramService
{
    public const CACHE_PREFIX = 'reg_telegram:';

    public const SESSION_TOKEN_KEY = 'registration.telegram_token';

    public const SESSION_VERIFIED_KEY = 'registration.telegram_verified';

    public function cacheKey(string $token): string
    {
        return self::CACHE_PREFIX.$token;
    }

    /**
     * @param  array{name: string, email: string, identifier: string, address: string, phone: string}  $personal
     * @return array{token: string}
     */
    public function createDraft(string $slug, string $examId, array $personal): array
    {
        $token = Str::random(32);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($token), [
            'slug' => $slug,
            'exam_id' => $examId,
            'applicant_id' => null,
            'personal' => $personal,
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10)->timestamp,
            'chat_id' => null,
            'verified' => false,
        ], now()->addHours(2));

        return ['token' => $token];
    }

    /**
     * @return array{token: string, linked: bool}
     */
    public function createDraftFromApplicant(string $slug, string $examId, Applicant $applicant): array
    {
        $token = $applicant->telegram_token ?? Str::random(32);
        if (! $applicant->telegram_token) {
            $applicant->telegram_token = $token;
            $applicant->save();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $chatId = $applicant->telegram_chat_id;

        Cache::put($this->cacheKey($token), [
            'slug' => $slug,
            'exam_id' => $examId,
            'applicant_id' => $applicant->id,
            'personal' => [
                'name' => $applicant->name,
                'email' => $applicant->email,
                'identifier' => $applicant->identifier,
                'address' => $applicant->address,
                'phone' => $applicant->phone,
            ],
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10)->timestamp,
            'chat_id' => $chatId,
            'verified' => false,
        ], now()->addHours(2));

        return [
            'token' => $token,
            'linked' => ! empty($chatId),
        ];
    }

    public function getDraft(string $token): ?array
    {
        $draft = Cache::get($this->cacheKey($token));

        return is_array($draft) ? $draft : null;
    }

    public function linkChat(string $token, string $chatId, TelegramService $telegram): bool
    {
        $draft = $this->getDraft($token);
        if (! $draft) {
            return false;
        }

        $draft['chat_id'] = $chatId;
        Cache::put($this->cacheKey($token), $draft, now()->addHours(2));

        return $telegram->sendVerificationCode($chatId, $draft['code']);
    }

    public function verifyCode(string $token, string $code): bool
    {
        $draft = $this->getDraft($token);
        if (! $draft || empty($draft['chat_id'])) {
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

    /**
     * @return array{ok: bool, message: string}
     */
    public function processVerificationToken(string $token, string $chatId, TelegramService $telegram): array
    {
        $token = trim($token);
        if ($token === '' || strlen($token) < 16) {
            return [
                'ok' => false,
                'message' => '❌ Неверный формат токена. Скопируйте токен целиком со страницы регистрации.',
            ];
        }

        if ($this->getDraft($token)) {
            if ($this->linkChat($token, $chatId, $telegram)) {
                return [
                    'ok' => true,
                    'message' => "✅ Код подтверждения отправлен в этот чат.\n\nВведите его на сайте в поле «Код из Telegram».",
                ];
            }

            return [
                'ok' => false,
                'message' => '❌ Не удалось отправить код. Попробуйте позже.',
            ];
        }

        $applicant = Applicant::where('telegram_token', $token)->first();
        if ($applicant) {
            $applicant->telegram_chat_id = $chatId;
            $applicant->save();

            return [
                'ok' => true,
                'message' => "✅ Telegram привязан к вашей заявке.\n\nЕсли вы проходите регистрацию на сайте — вернитесь на шаг подтверждения и введите код.",
            ];
        }

        return [
            'ok' => false,
            'message' => "❌ Токен не найден или устарел.\n\nВернитесь на сайт, нажмите «Продолжить» на шаге с личными данными — появится новый токен.",
        ];
    }

    public function resendCode(string $token, TelegramService $telegram): bool
    {
        $draft = $this->getDraft($token);
        if (! $draft || empty($draft['chat_id'])) {
            return false;
        }

        $draft['code'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $draft['code_expires_at'] = now()->addMinutes(10)->timestamp;
        $draft['verified'] = false;
        Cache::put($this->cacheKey($token), $draft, now()->addHours(2));

        return $telegram->sendVerificationCode($draft['chat_id'], $draft['code']);
    }
}

<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

class TelegramUpdateHandler
{
    public function __construct(
        protected RegistrationTelegramService $registrationTelegram,
        protected TelegramService $telegram,
    ) {}

    public function handle(array $update): void
    {
        if (! isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');

        if ($chatId === '' || $text === '') {
            return;
        }

        Log::info('Telegram message received', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        if (! str_starts_with($text, '/start')) {
            if (in_array($text, ['/stop', '/help'], true)) {
                $this->sendMessage(
                    $chatId,
                    "Для регистрации откройте ссылку «Открыть @бот» на сайте и нажмите Start.\n\nНе вводите /start вручную — нужна ссылка со страницы регистрации."
                );
            }

            return;
        }

        $token = $this->extractStartPayload($text);

        if ($token === null) {
            $this->sendMessage(
                $chatId,
                "👋 Добро пожаловать!\n\nНажмите кнопку «Открыть бота» на странице регистрации на сайте — откроется чат с нужной ссылкой.\n\nНе отправляйте /start вручную."
            );

            return;
        }

        if ($this->registrationTelegram->getDraft($token)) {
            if ($this->registrationTelegram->linkChat($token, $chatId, $this->telegram)) {
                $this->sendMessage(
                    $chatId,
                    "✅ Telegram подключён!\n\nКод подтверждения отправлен в этот чат. Введите его на сайте, чтобы продолжить регистрацию."
                );
            } else {
                $this->sendMessage(
                    $chatId,
                    "❌ Не удалось отправить код. Проверьте TELEGRAM_BOT_TOKEN на сервере или попробуйте позже."
                );
            }

            return;
        }

        $applicant = Applicant::where('telegram_token', $token)->first();

        if ($applicant) {
            $applicant->telegram_chat_id = $chatId;
            $applicant->save();

            $this->sendMessage(
                $chatId,
                "✅ Ваш аккаунт успешно привязан!\n\nТеперь вы будете получать коды подтверждения в этот чат."
            );

            return;
        }

        $this->sendMessage(
            $chatId,
            "❌ Ссылка устарела или недействительна.\n\nВернитесь на сайт, нажмите «Продолжить» на шаге с личными данными и снова откройте бота кнопкой на странице."
        );
    }

    protected function extractStartPayload(string $text): ?string
    {
        $parts = preg_split('/\s+/', $text, 2);
        if (! is_array($parts) || count($parts) < 2) {
            return null;
        }

        $payload = trim($parts[1]);
        if ($payload === '') {
            return null;
        }

        // /start@BotName token
        if (str_contains($payload, '@')) {
            $subParts = preg_split('/\s+/', $payload, 2);

            return isset($subParts[1]) ? trim($subParts[1]) : null;
        }

        return $payload;
    }

    protected function sendMessage(string $chatId, string $message): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            Log::error('Telegram bot token not configured');

            return;
        }

        try {
            (new BotApi($token))->sendMessage($chatId, $message);
        } catch (Exception $e) {
            Log::error('Telegram sendMessage failed: '.$e->getMessage(), ['chat_id' => $chatId]);
        }
    }
}

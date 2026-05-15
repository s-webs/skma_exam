<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

class TelegramUpdateHandler
{
    public function __construct(
        protected RegistrationTelegramService $registrationTelegram,
        protected TelegramService $telegram,
        protected TelegramBotChatState $chatState,
    ) {}

    public function handle(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);

            return;
        }

        if (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $chatId = (string) ($callback['message']['chat']['id'] ?? '');
            $data = $callback['data'] ?? '';

            if ($chatId !== '' && $data === 'get_code') {
                $this->answerCallback($callback['id'] ?? '');
                $this->promptForToken($chatId);
            }
        }
    }

    protected function handleMessage(array $message): void
    {
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');

        if ($chatId === '' || $text === '') {
            return;
        }

        Log::info('Telegram message received', ['chat_id' => $chatId, 'text' => $text]);

        if ($text === '/cancel') {
            $this->chatState->clear($chatId);
            $this->telegram->sendMainMenu(
                $chatId,
                "Действие отменено.\n\nНажмите «".TelegramBotChatState::BUTTON_GET_CODE.'», когда будете готовы.'
            );

            return;
        }

        if ($this->isGetCodeAction($text)) {
            $this->promptForToken($chatId);

            return;
        }

        if ($this->chatState->isAwaitingToken($chatId)) {
            $this->processSubmittedToken($chatId, $this->normalizeTokenInput($text));
            $this->chatState->clear($chatId);

            return;
        }

        if (str_starts_with($text, '/start')) {
            $payload = $this->extractStartPayload($text);
            if ($payload !== null) {
                $this->processSubmittedToken($chatId, $payload);

                return;
            }
        }

        if (in_array($text, ['/help', '/stop'], true)) {
            $this->sendWelcome($chatId);

            return;
        }

        if (str_starts_with($text, '/')) {
            $this->telegram->sendMainMenu(
                $chatId,
                'Используйте кнопку «'.TelegramBotChatState::BUTTON_GET_CODE."» внизу экрана.\n\n/help — подсказка"
            );

            return;
        }

        $this->sendWelcome($chatId);
    }

    protected function promptForToken(string $chatId): void
    {
        $this->chatState->setAwaitingToken($chatId);

        $this->sendPlain(
            $chatId,
            "📋 <b>Получение кода верификации</b>\n\n".
            "1. На странице регистрации найдите блок «Токен верификации»\n".
            "2. Скопируйте токен\n".
            "3. Отправьте его сюда одним сообщением\n\n".
            'Для отмены отправьте /cancel',
            'HTML'
        );
    }

    protected function processSubmittedToken(string $chatId, string $token): void
    {
        $result = $this->registrationTelegram->processVerificationToken(
            $token,
            $chatId,
            $this->telegram
        );

        $this->telegram->sendMainMenu($chatId, $result['message']);
    }

    protected function sendWelcome(string $chatId): void
    {
        $this->chatState->clear($chatId);

        $this->telegram->sendMainMenu(
            $chatId,
            "👋 <b>SKMA — верификация регистрации</b>\n\n".
            "Нажмите кнопку «".TelegramBotChatState::BUTTON_GET_CODE."».\n".
            "Бот попросит токен со страницы регистрации и пришлёт код подтверждения.",
            'HTML'
        );
    }

    protected function isGetCodeAction(string $text): bool
    {
        return $text === TelegramBotChatState::BUTTON_GET_CODE
            || $text === 'Получить код верификации';
    }

    protected function normalizeTokenInput(string $text): string
    {
        return preg_replace('/\s+/', '', $text) ?? $text;
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

        if (str_contains($payload, '@')) {
            $subParts = preg_split('/\s+/', $payload, 2);

            return isset($subParts[1]) ? trim($subParts[1]) : null;
        }

        return $payload;
    }

    protected function answerCallback(string $callbackId): void
    {
        if ($callbackId === '') {
            return;
        }

        $bot = $this->telegram->bot();
        if (! $bot) {
            return;
        }

        try {
            $bot->answerCallbackQuery($callbackId);
        } catch (Exception $e) {
            Log::warning('answerCallbackQuery failed: '.$e->getMessage());
        }
    }

    protected function sendPlain(string $chatId, string $message, ?string $parseMode = null): void
    {
        $botToken = config('services.telegram.bot_token');
        if (! $botToken) {
            Log::error('Telegram bot token not configured');

            return;
        }

        try {
            (new BotApi($botToken))->sendMessage($chatId, $message, $parseMode);
        } catch (Exception $e) {
            Log::error('Telegram sendMessage failed: '.$e->getMessage(), ['chat_id' => $chatId]);
        }
    }
}

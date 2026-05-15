<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $bot;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        if ($token) {
            $this->bot = new BotApi($token);
        }
    }

    /**
     * Отправить код подтверждения пользователю
     */
    public function sendVerificationCode(string $chatId, string $code): bool
    {
        try {
            if (!$this->bot) {
                Log::error('Telegram bot token not configured');
                return false;
            }

            $message = "🔐 Ваш код подтверждения: <b>{$code}</b>\n\n";
            $message .= "Код действителен в течение 10 минут.\n";
            $message .= "Если вы не запрашивали код, проигнорируйте это сообщение.";

            $this->bot->sendMessage(
                $chatId,
                $message,
                'HTML'
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Отправить уведомление о начале экзамена
     */
    public function sendExamNotification(string $chatId, string $examName, string $startTime): bool
    {
        try {
            if (!$this->bot) {
                return false;
            }

            $message = "📝 <b>Напоминание об экзамене</b>\n\n";
            $message .= "Экзамен: {$examName}\n";
            $message .= "Время начала: {$startTime}\n\n";
            $message .= "Не забудьте подготовиться!";

            $this->bot->sendMessage(
                $chatId,
                $message,
                'HTML'
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Отправить результаты экзамена
     */
    public function sendExamResults(string $chatId, string $examName, int $score, bool $passed): bool
    {
        try {
            if (!$this->bot) {
                return false;
            }

            $emoji = $passed ? '✅' : '❌';
            $status = $passed ? 'Сдан' : 'Не сдан';

            $message = "{$emoji} <b>Результаты экзамена</b>\n\n";
            $message .= "Экзамен: {$examName}\n";
            $message .= "Балл: {$score}\n";
            $message .= "Статус: {$status}";

            $this->bot->sendMessage(
                $chatId,
                $message,
                'HTML'
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram results: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Установить webhook
     */
    public function setWebhook(string $url): bool
    {
        try {
            if (!$this->bot) {
                return false;
            }

            $this->bot->setWebhook($url);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to set webhook: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить webhook
     */
    public function deleteWebhook(): bool
    {
        try {
            if (!$this->bot) {
                return false;
            }

            $this->bot->deleteWebhook();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete webhook: ' . $e->getMessage());
            return false;
        }
    }
}

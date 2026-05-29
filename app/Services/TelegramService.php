<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ExamResult;
use CURLFile;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

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
    public function bot(): ?BotApi
    {
        return $this->bot;
    }

    public function buildBotUrl(): ?string
    {
        $username = config('services.telegram.bot_username');
        if (! $username) {
            return null;
        }

        return 'https://t.me/'.ltrim($username, '@');
    }

    /**
     * @param  array<int, array<int, array{text: string}>>>  $keyboardRows
     */
    public function sendMessageWithKeyboard(
        string $chatId,
        string $message,
        array $keyboardRows,
        ?string $parseMode = null
    ): bool {
        try {
            if (! $this->bot) {
                return false;
            }

            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'reply_markup' => json_encode([
                    'keyboard' => $keyboardRows,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]),
            ];

            if ($parseMode) {
                $payload['parse_mode'] = $parseMode;
            }

            $this->bot->call('sendMessage', $payload);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram keyboard message: '.$e->getMessage());

            return false;
        }
    }

    public function sendMainMenu(string $chatId, string $message, ?string $parseMode = null): bool
    {
        return $this->sendMessageWithKeyboard($chatId, $message, [
            [['text' => TelegramBotChatState::BUTTON_GET_CODE]],
        ], $parseMode);
    }

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
     * Отправить ссылку на прохождение экзамена после одобрения
     */
    public function sendExamInvite(
        string $chatId,
        string $examName,
        string $examUrl,
        int $durationMinutes
    ): bool {
        try {
            if (! $this->bot) {
                Log::error('Telegram bot token not configured');

                return false;
            }

            $message = "✅ <b>Запись на экзамен одобрена</b>\n\n";
            $message .= "Экзамен: {$examName}\n";
            $message .= "Время на прохождение: {$durationMinutes} мин.\n";
            $message .= "Отсчёт начнётся после нажатия кнопки «Начать экзамен».\n\n";
            $message .= "🔗 <a href=\"{$examUrl}\">Перейти к экзамену</a>";

            $this->bot->sendMessage(
                $chatId,
                $message,
                'HTML'
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram exam invite: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отправить результаты экзамена (текст + PDF-отчёт)
     */
    public function sendExamResultsWithReport(
        string $chatId,
        ExamAttempt $attempt,
        ExamResult $result,
        ExamResultPdfService $pdfService
    ): bool {
        $attempt->loadMissing(['exam']);
        $examName = $attempt->exam->name;
        $pdfUrl = $pdfService->publicUrl($attempt);

        $messageSent = $this->sendExamResultsMessage(
            $chatId,
            $examName,
            $result->total_score,
            $result->passed,
            $pdfUrl,
            $attempt->exam->language ?? 'ru'
        );

        $documentSent = $this->sendExamResultsPdf($chatId, $attempt, $result, $pdfService);

        return $messageSent || $documentSent;
    }

    public function sendExamResultsMessage(
        string $chatId,
        string $examName,
        int $score,
        bool $passed,
        ?string $pdfUrl = null,
        string $locale = 'ru'
    ): bool {
        try {
            if (! $this->bot) {
                return false;
            }

            app()->setLocale($this->normalizeLocale($locale));

            $emoji = $passed ? '✅' : '❌';
            $status = $passed ? __('exam_report.passed') : __('exam_report.failed');

            $message = "{$emoji} <b>".__('exam_report.exam_result')."</b>\n\n";
            $message .= __('exam_report.subject').": {$examName}\n";
            $message .= __('exam_report.result').": {$score}\n";
            $message .= __('exam_report.status').": {$status}";

            if ($pdfUrl) {
                $message .= "\n\n📄 <a href=\"{$pdfUrl}\">".__('exam_report.pdf_link')."</a>";
            }

            $this->bot->sendMessage($chatId, $message, 'HTML');

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram results: '.$e->getMessage());

            return false;
        }
    }

    public function sendExamResultsPdf(
        string $chatId,
        ExamAttempt $attempt,
        ExamResult $result,
        ExamResultPdfService $pdfService
    ): bool {
        if (! $this->bot) {
            return false;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'exam_report_');
        if ($tempPath === false) {
            return false;
        }

        try {
            file_put_contents($tempPath, $pdfService->render($attempt));

            app()->setLocale($this->normalizeLocale($attempt->exam->language ?? 'ru'));
            $caption = __('exam_report.exam_result').': '.$attempt->exam->name."\n";
            $caption .= __('exam_report.result').': '.$result->total_score.' — ';
            $caption .= $result->passed ? __('exam_report.passed') : __('exam_report.failed');

            $this->bot->call('sendDocument', [
                'chat_id' => $chatId,
                'document' => new CURLFile($tempPath, 'application/pdf', $pdfService->filename($attempt)),
                'caption' => $caption,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram exam PDF: '.$e->getMessage());

            return false;
        } finally {
            @unlink($tempPath);
        }
    }

    private function normalizeLocale(string $language): string
    {
        return match ($language) {
            'kz', 'kk' => 'kk',
            'en' => 'en',
            default => 'ru',
        };
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

    public function getWebhookInfo(): ?array
    {
        try {
            if (! $this->bot) {
                return null;
            }

            $info = $this->bot->getWebhookInfo();

            return [
                'url' => $info->getUrl(),
                'pending_update_count' => $info->getPendingUpdateCount(),
                'last_error_date' => $info->getLastErrorDate(),
                'last_error_message' => $info->getLastErrorMessage(),
                'max_connections' => $info->getMaxConnections(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get webhook info: '.$e->getMessage());

            return null;
        }
    }

    public function getMe(): ?array
    {
        try {
            if (! $this->bot) {
                return null;
            }

            $user = $this->bot->getMe();

            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get bot info: '.$e->getMessage());

            return null;
        }
    }
}

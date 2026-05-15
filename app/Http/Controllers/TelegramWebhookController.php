<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Services\RegistrationTelegramService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        RegistrationTelegramService $registrationTelegram,
        TelegramService $telegram
    ) {
        try {
            $update = $request->all();

            if (! isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = (string) $message['chat']['id'];
            $text = $message['text'] ?? '';

            if (strpos($text, '/start') !== 0) {
                return response()->json(['ok' => true]);
            }

            $parts = explode(' ', $text, 2);
            $bot = new BotApi(config('services.telegram.bot_token'));

            if (count($parts) !== 2 || $parts[1] === '') {
                $bot->sendMessage(
                    $chatId,
                    "👋 Добро пожаловать!\n\nДля регистрации или привязки аккаунта откройте ссылку на бота со страницы регистрации."
                );

                return response()->json(['ok' => true]);
            }

            $token = trim($parts[1]);

            if ($registrationTelegram->getDraft($token)) {
                if ($registrationTelegram->linkChat($token, $chatId, $telegram)) {
                    $bot->sendMessage(
                        $chatId,
                        "✅ Telegram подключён!\n\nКод подтверждения отправлен в этот чат. Введите его на сайте, чтобы продолжить регистрацию."
                    );
                } else {
                    $bot->sendMessage(
                        $chatId,
                        "❌ Не удалось отправить код. Попробуйте позже или обратитесь в поддержку."
                    );
                }

                return response()->json(['ok' => true]);
            }

            $applicant = Applicant::where('telegram_token', $token)->first();

            if ($applicant) {
                $applicant->telegram_chat_id = $chatId;
                $applicant->save();

                $bot->sendMessage(
                    $chatId,
                    "✅ Ваш аккаунт успешно привязан!\n\nТеперь вы будете получать коды подтверждения в этот чат."
                );
            } else {
                $bot->sendMessage(
                    $chatId,
                    "❌ Неверная ссылка. Откройте бота со страницы регистрации на сайте."
                );
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: '.$e->getMessage());

            return response()->json(['ok' => true]);
        }
    }
}

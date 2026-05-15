<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $update = $request->all();

            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';

            // Проверяем команду /start с токеном
            if (strpos($text, '/start') === 0) {
                $parts = explode(' ', $text);

                if (count($parts) === 2) {
                    $token = $parts[1];

                    // Ищем абитуриента по токену
                    $applicant = Applicant::where('telegram_token', $token)->first();

                    if ($applicant) {
                        // Сохраняем chat_id
                        $applicant->telegram_chat_id = $chatId;
                        $applicant->save();

                        $bot = new BotApi(config('services.telegram.bot_token'));
                        $bot->sendMessage(
                            $chatId,
                            "✅ Ваш аккаунт успешно привязан!\n\nТеперь вы будете получать коды подтверждения в этот чат."
                        );
                    } else {
                        $bot = new BotApi(config('services.telegram.bot_token'));
                        $bot->sendMessage(
                            $chatId,
                            "❌ Неверный токен. Пожалуйста, проверьте токен в личном кабинете."
                        );
                    }
                } else {
                    $bot = new BotApi(config('services.telegram.bot_token'));
                    $bot->sendMessage(
                        $chatId,
                        "👋 Добро пожаловать!\n\nДля привязки аккаунта отправьте команду:\n/start ВАШ_ТОКЕН\n\nТокен можно найти в личном кабинете."
                    );
                }
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            return response()->json(['ok' => true]);
        }
    }
}

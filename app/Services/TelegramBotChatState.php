<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TelegramBotChatState
{
    public const CACHE_PREFIX = 'telegram_bot_chat:';

    public const STATE_AWAITING_TOKEN = 'awaiting_token';

    public const BUTTON_GET_CODE = '🔐 Получить код верификации';

    public function setAwaitingToken(string $chatId): void
    {
        Cache::put($this->key($chatId), self::STATE_AWAITING_TOKEN, now()->addHours(2));
    }

    public function isAwaitingToken(string $chatId): bool
    {
        return Cache::get($this->key($chatId)) === self::STATE_AWAITING_TOKEN;
    }

    public function clear(string $chatId): void
    {
        Cache::forget($this->key($chatId));
    }

    protected function key(string $chatId): string
    {
        return self::CACHE_PREFIX.$chatId;
    }
}

# Быстрый старт Telegram бота

## Шаг 1: Создайте бота
1. Найдите @BotFather в Telegram
2. Отправьте `/newbot`
3. Следуйте инструкциям
4. Сохраните токен

## Шаг 2: Настройте .env
```env
TELEGRAM_BOT_TOKEN=ваш_токен_здесь
TELEGRAM_BOT_USERNAME=ваш_бот_username
```

## Шаг 3: Запустите миграцию
```bash
php artisan migrate
```

## Шаг 4: Настройте webhook
```bash
php artisan telegram:setup-webhook
```

**Для локальной разработки используйте ngrok:**
```bash
ngrok http 8000
# Обновите APP_URL в .env на ngrok URL
php artisan telegram:setup-webhook
```

## Шаг 5: Тестирование

### Проверка привязки аккаунта:
1. Найдите telegram_token абитуриента в базе данных
2. Отправьте боту: `/start TOKEN`
3. Бот должен подтвердить привязку

### Отправка тестового сообщения:
```bash
php artisan telegram:test 1
```
(где 1 - ID абитуриента)

## Использование в коде

```php
use App\Services\TelegramService;

$telegram = new TelegramService();

// Отправка кода подтверждения
$telegram->sendVerificationCode($applicant->telegram_chat_id, '123456');

// Отправка уведомления об экзамене
$telegram->sendExamNotification(
    $applicant->telegram_chat_id,
    'Магистратура 2026',
    '2026-06-01 10:00'
);

// Отправка результатов
$telegram->sendExamResults(
    $applicant->telegram_chat_id,
    'Магистратура 2026',
    85,
    true
);
```

## Проверка работы

Проверить webhook:
```
https://api.telegram.org/bot{YOUR_TOKEN}/getWebhookInfo
```

Посмотреть логи:
```bash
tail -f storage/logs/laravel.log
```

## Готово! 🎉

Полная документация: `TELEGRAM_SETUP.md`

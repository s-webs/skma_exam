# Инструкция по настройке Telegram бота

## 1. Создание бота в Telegram

1. Откройте Telegram и найдите бота **@BotFather**
2. Отправьте команду `/newbot`
3. Введите имя бота (например: "SKMA Verification Bot")
4. Введите username бота (должен заканчиваться на "bot", например: "skma_verification_bot")
5. BotFather даст вам **токен** (например: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)
6. **СОХРАНИТЕ ЭТОТ ТОКЕН!**

## 2. Настройка .env файла

Откройте файл `.env` и добавьте следующие строки:

```env
TELEGRAM_BOT_TOKEN=ваш_токен_от_botfather
TELEGRAM_BOT_USERNAME=skma_verification_bot
```

Замените `ваш_токен_от_botfather` на реальный токен, полученный от BotFather.

## 3. Запуск миграции

Выполните команду для добавления поля `telegram_chat_id` в базу данных:

```bash
php artisan migrate
```

## 4. Настройка webhook

Выполните команду для установки webhook:

```bash
php artisan telegram:setup-webhook
```

Эта команда автоматически настроит webhook на URL: `https://ваш-домен.com/telegram/webhook`

**ВАЖНО:** Webhook работает только на HTTPS! Для локальной разработки используйте ngrok или похожие сервисы.

### Для локальной разработки с ngrok:

1. Установите ngrok: https://ngrok.com/
2. Запустите ngrok:
   ```bash
   ngrok http 8000
   ```
3. Скопируйте HTTPS URL (например: `https://abc123.ngrok.io`)
4. Обновите `APP_URL` в `.env`:
   ```env
   APP_URL=https://abc123.ngrok.io
   ```
5. Запустите команду настройки webhook:
   ```bash
   php artisan telegram:setup-webhook
   ```

## 5. Как это работает

### Для абитуриента:

1. Абитуриент регистрируется в системе
2. Система автоматически генерирует уникальный `telegram_token` для него
3. В личном кабинете абитуриент видит инструкцию:
   ```
   Для получения кодов подтверждения в Telegram:
   1. Найдите бота @skma_verification_bot
   2. Отправьте команду: /start ABC123XYZ
   ```
4. После отправки команды бот сохраняет `chat_id` абитуриента
5. Теперь система может отправлять коды подтверждения в Telegram

### Отправка кода подтверждения:

```php
use App\Services\TelegramService;

$telegram = new TelegramService();
$code = '123456';
$chatId = $applicant->telegram_chat_id;

$telegram->sendVerificationCode($chatId, $code);
```

## 6. Доступные методы TelegramService

### Отправка кода подтверждения
```php
$telegram->sendVerificationCode($chatId, $code);
```

### Отправка уведомления об экзамене
```php
$telegram->sendExamNotification($chatId, 'Магистратура 2026', '2026-06-01 10:00');
```

### Отправка результатов экзамена
```php
$telegram->sendExamResults($chatId, 'Магистратура 2026', 85, true);
```

## 7. Проверка работы

1. Найдите вашего бота в Telegram по username
2. Отправьте команду `/start`
3. Бот должен ответить приветственным сообщением
4. Возьмите `telegram_token` любого абитуриента из базы данных
5. Отправьте команду `/start TOKEN`
6. Бот должен подтвердить привязку аккаунта

## 8. Отладка

### Проверка логов
```bash
tail -f storage/logs/laravel.log
```

### Проверка webhook
Отправьте GET запрос:
```
https://api.telegram.org/bot{YOUR_BOT_TOKEN}/getWebhookInfo
```

### Удаление webhook (если нужно)
```bash
php artisan tinker
```
```php
$telegram = new App\Services\TelegramService();
$telegram->deleteWebhook();
```

## 9. Безопасность

- Никогда не публикуйте токен бота в публичных репозиториях
- Добавьте `.env` в `.gitignore`
- Используйте HTTPS для webhook
- Регулярно проверяйте логи на подозрительную активность

## 10. Troubleshooting

### Webhook не работает
- Проверьте, что используется HTTPS
- Проверьте, что URL доступен извне
- Проверьте логи Laravel

### Бот не отвечает
- Проверьте токен в `.env`
- Проверьте, что webhook установлен правильно
- Проверьте логи

### Сообщения не доходят
- Проверьте, что `telegram_chat_id` сохранен в базе
- Проверьте, что абитуриент отправил `/start TOKEN`
- Проверьте логи

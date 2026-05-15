<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramDiagnoseCommand extends Command
{
    protected $signature = 'telegram:diagnose';

    protected $description = 'Check Telegram bot token, webhook URL and last errors';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Telegram diagnostics');
        $this->line('APP_URL: '.config('app.url'));
        $this->line('Expected webhook: '.rtrim(config('app.url'), '/').'/telegram/webhook');
        $this->newLine();

        if (! config('services.telegram.bot_token')) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env');

            return Command::FAILURE;
        }

        $me = $telegram->getMe();
        if ($me) {
            $this->info('Bot: @'.($me['username'] ?? '?').' ('.($me['first_name'] ?? '').')');
        } else {
            $this->error('Cannot call getMe — check TELEGRAM_BOT_TOKEN');
        }

        $info = $telegram->getWebhookInfo();
        if (! $info) {
            $this->error('Cannot read webhook info');

            return Command::FAILURE;
        }

        $this->newLine();
        $this->line('Webhook URL: '.($info['url'] ?: '(not set)'));
        $this->line('Pending updates: '.($info['pending_update_count'] ?? 0));

        if (! empty($info['last_error_message'])) {
            $this->error('Last webhook error: '.$info['last_error_message']);
            if (! empty($info['last_error_date'])) {
                $this->line('Error time: '.date('Y-m-d H:i:s', (int) $info['last_error_date']));
            }
        } else {
            $this->info('No recent webhook errors from Telegram.');
        }

        $expected = rtrim(config('app.url'), '/').'/telegram/webhook';
        if (($info['url'] ?? '') !== $expected) {
            $this->warn('Webhook URL does not match APP_URL. Run: php artisan telegram:setup-webhook');
        }

        return Command::SUCCESS;
    }
}

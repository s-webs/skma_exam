<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class SetupTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram bot webhook';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if (! str_starts_with($appUrl, 'https://')) {
            $this->error('APP_URL must be a public HTTPS URL (e.g. https://your-domain.kz)');
            $this->line('Current APP_URL: '.$appUrl);

            return Command::FAILURE;
        }

        $url = $appUrl.'/telegram/webhook';

        $this->info('Setting up Telegram webhook...');
        $this->info('Webhook URL: '.$url);

        if ($telegram->setWebhook($url)) {
            $this->info('✅ Webhook successfully set!');
            $this->call('telegram:diagnose');

            return Command::SUCCESS;
        }

        $this->error('❌ Failed to set webhook. Check storage/logs/laravel.log');
        $this->line('Run: php artisan telegram:diagnose');

        return Command::FAILURE;
    }
}

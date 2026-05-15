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
        $url = config('app.url') . '/telegram/webhook';

        $this->info('Setting up Telegram webhook...');
        $this->info('Webhook URL: ' . $url);

        if ($telegram->setWebhook($url)) {
            $this->info('✅ Webhook successfully set!');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to set webhook. Check your bot token and logs.');
            return Command::FAILURE;
        }
    }
}

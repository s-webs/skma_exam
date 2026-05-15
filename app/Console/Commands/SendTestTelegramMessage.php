<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendTestTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {applicant_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test verification code to applicant via Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        $applicantId = $this->argument('applicant_id');
        $applicant = Applicant::find($applicantId);

        if (!$applicant) {
            $this->error('Applicant not found!');
            return Command::FAILURE;
        }

        if (!$applicant->telegram_chat_id) {
            $this->error('Applicant has not linked Telegram account!');
            $this->info('Ask applicant to send: /start ' . $applicant->telegram_token);
            return Command::FAILURE;
        }

        $code = rand(100000, 999999);

        $this->info('Sending test code: ' . $code);

        if ($telegram->sendVerificationCode($applicant->telegram_chat_id, $code)) {
            $this->info('✅ Message sent successfully!');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send message. Check logs.');
            return Command::FAILURE;
        }
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $examName,
        public string $examUrl,
        public int $durationMinutes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Запись на экзамен одобрена',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.exam-invite',
        );
    }
}

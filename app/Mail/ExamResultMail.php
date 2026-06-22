<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $examName,
        public int $score,
        public bool $passed,
        public string $reportUrl,
        public string $pdfContent,
        public string $pdfFilename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('exam_report.exam_result'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.exam-result',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}

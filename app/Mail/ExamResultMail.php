<?php

namespace App\Mail;

use App\Models\ExamAttempt;
use App\Services\ExamResultPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamResultMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $attemptId,
        public string $examName,
        public int $score,
        public bool $passed,
        public string $reportUrl,
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
        $pdfService = app(ExamResultPdfService::class);
        $attempt = ExamAttempt::query()->findOrFail($this->attemptId);

        return [
            Attachment::fromData(
                fn () => $pdfService->render($attempt->fresh()),
                $pdfService->filename($attempt),
            )->withMime('application/pdf'),
        ];
    }
}

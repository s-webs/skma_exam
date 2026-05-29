<?php

namespace App\Services;

use App\Models\ExamAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamResultPdfService
{
    public function publicUrl(ExamAttempt $attempt): string
    {
        return route('public.exam.report', $attempt->token, absolute: true);
    }

    public function buildQrCodeDataUri(string $url): string
    {
        $svg = QrCode::format('svg')
            ->size(120)
            ->margin(1)
            ->generate($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function ensureReportable(ExamAttempt $attempt): ExamAttempt
    {
        $attempt->loadMissing(['applicant', 'exam', 'exam.examType', 'result']);

        if ($attempt->status !== 'completed' || ! $attempt->result) {
            throw new NotFoundHttpException;
        }

        return $attempt;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(ExamAttempt $attempt): array
    {
        $attempt = $this->ensureReportable($attempt);

        $result = $attempt->result;
        $completedAt = $attempt->completed_at ?? $attempt->updated_at;
        $reportUrl = $this->publicUrl($attempt);

        return [
            'attempt' => $attempt,
            'applicant' => $attempt->applicant,
            'exam' => $attempt->exam,
            'result' => $result,
            'reportUrl' => $reportUrl,
            'qrDataUri' => $this->buildQrCodeDataUri($reportUrl),
            'completedDate' => $completedAt?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'locales' => ['kk', 'ru', 'en'],
        ];
    }

    public function render(ExamAttempt $attempt): string
    {
        $data = $this->buildViewData($attempt);

        return Pdf::loadView('pdf.exam-result-sheet', $data)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function streamResponse(ExamAttempt $attempt): Response
    {
        $attempt = $this->ensureReportable($attempt);
        $pdf = $this->render($attempt);
        $filename = 'result-'.$attempt->id.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function filename(ExamAttempt $attempt): string
    {
        return 'result-'.$attempt->id.'.pdf';
    }
}

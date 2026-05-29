<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ExamAttemptService;
use App\Services\ExamResultPdfService;

class ExamResultReportController extends Controller
{
    public function __construct(
        protected ExamAttemptService $examAttemptService,
        protected ExamResultPdfService $examResultPdfService,
    ) {}

    public function show(string $token)
    {
        $attempt = $this->examAttemptService->findByToken($token);

        return $this->examResultPdfService->streamResponse($attempt);
    }
}

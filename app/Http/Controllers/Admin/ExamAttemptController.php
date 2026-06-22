<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\AuthorizationService;

class ExamAttemptController extends Controller
{
    public function __construct(
        protected AuthorizationService $authorization
    ) {}

    public function destroy(ExamAttempt $examAttempt)
    {
        $examAttempt->loadMissing('exam');
        $this->authorization->ensureCanAccessExam(auth()->user(), 'exam-attempts.delete', $examAttempt->exam);

        $examAttempt->delete();

        return back()->with('success', 'Попытка удалена.');
    }
}

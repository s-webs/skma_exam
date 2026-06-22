<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\ExamTypeAccessService;

class ExamAttemptController extends Controller
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess
    ) {}

    public function destroy(ExamAttempt $examAttempt)
    {
        $examAttempt->loadMissing('exam');
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $examAttempt->exam);

        $examAttempt->delete();

        return back()->with('success', 'Попытка удалена.');
    }
}

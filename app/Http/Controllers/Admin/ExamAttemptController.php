<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;

class ExamAttemptController extends Controller
{
    public function destroy(ExamAttempt $examAttempt)
    {
        $examAttempt->delete();

        return back()->with('success', 'Попытка удалена.');
    }
}

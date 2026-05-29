<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamRegistration;

class ExamRegistrationController extends Controller
{
    public function approve(ExamRegistration $examRegistration)
    {
        $examRegistration->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Запись на экзамен одобрена');
    }

    public function unapprove(ExamRegistration $examRegistration)
    {
        $examRegistration->update([
            'approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Одобрение записи на экзамен отменено');
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    public function index($slug)
    {
        $examType = ExamType::where('slug', $slug)
            ->with(['exams' => function ($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        return Inertia::render('Public/Registration/Index', [
            'examType' => $examType,
        ]);
    }

    public function store(Request $request, $slug)
    {
        $examType = ExamType::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email',
            'identifier' => 'required|string|size:12|unique:applicants,identifier',
            'address' => 'required|string',
            'phone' => 'required|string',
            'graduate_organization' => 'required|string',
            'graduate_year' => 'required|string',
            'speciality' => 'required|string',
            'document_front' => 'nullable|image|max:2048',
            'document_back' => 'nullable|image|max:2048',
            'diplom' => 'nullable|image|max:2048',
            'certificate' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Get language from selected exam
        $exam = \App\Models\Exam::findOrFail($validated['exam_id']);
        $validated['language'] = $exam->language;

        $applicant = \App\Models\Applicant::create($validated);

        // Handle file uploads
        if ($request->hasFile('document_front')) {
            $path = $request->file('document_front')->store('applicants/documents', 'public');
            $applicant->update(['document_front' => $path]);
        }

        if ($request->hasFile('document_back')) {
            $path = $request->file('document_back')->store('applicants/documents', 'public');
            $applicant->update(['document_back' => $path]);
        }

        if ($request->hasFile('diplom')) {
            $path = $request->file('diplom')->store('applicants/diploms', 'public');
            $applicant->update(['diplom' => $path]);
        }

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('applicants/certificates', 'public');
            $applicant->update(['certificate' => $path]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('applicants/photos', 'public');
            $applicant->update(['photo' => $path]);
        }

        return Inertia::render('Public/Registration/Success', [
            'applicant' => $applicant,
        ]);
    }
}

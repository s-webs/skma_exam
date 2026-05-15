<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['examType', 'createdBy'])
            ->withCount('questions')
            ->latest()
            ->get();

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $exams,
        ]);
    }

    public function create()
    {
        $examTypes = ExamType::where('is_active', true)->get();

        return Inertia::render('Admin/Exams/Create', [
            'examTypes' => $examTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|in:kz,ru,en',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'questions_count' => 'required|integer|min:1|max:200',
            'passing_score' => 'required|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['created_by_user_id'] = auth()->id();

        Exam::create($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно создан');
    }

    public function edit(Exam $exam)
    {
        $examTypes = ExamType::where('is_active', true)->get();

        return Inertia::render('Admin/Exams/Edit', [
            'exam' => $exam->load('examType'),
            'examTypes' => $examTypes,
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|in:kz,ru,en',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'questions_count' => 'required|integer|min:1|max:200',
            'passing_score' => 'required|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $exam->update($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно обновлен');
    }

    public function destroy(Exam $exam)
    {
        if ($exam->attempts()->count() > 0) {
            return back()->with('error', 'Невозможно удалить экзамен с существующими попытками');
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно удален');
    }

    public function applicants(Exam $exam)
    {
        $applicants = \App\Models\Applicant::where('language', $exam->language)
            ->withCount('examAttempts')
            ->with('approvedByUser:id,name')
            ->latest()
            ->paginate(30);

        return Inertia::render('Admin/Exams/Applicants', [
            'exam' => $exam->load('examType'),
            'applicants' => $applicants,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ExamTypeController extends Controller
{
    public function index()
    {
        $examTypes = ExamType::withCount('exams')
            ->with(['exams' => function ($query) {
                $query->select('id', 'exam_type_id', 'name', 'language');
            }])
            ->latest()
            ->get();

        return Inertia::render('Admin/ExamTypes/Index', [
            'examTypes' => $examTypes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ExamTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ExamType::create($validated);

        return redirect()->route('admin.exam-types.index')
            ->with('success', 'Тип экзамена успешно создан');
    }

    public function edit(ExamType $examType)
    {
        return Inertia::render('Admin/ExamTypes/Edit', [
            'examType' => $examType,
        ]);
    }

    public function update(Request $request, ExamType $examType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $examType->update($validated);

        return redirect()->route('admin.exam-types.index')
            ->with('success', 'Тип экзамена успешно обновлен');
    }

    public function destroy(ExamType $examType)
    {
        $examType->delete();

        return redirect()->route('admin.exam-types.index')
            ->with('success', 'Тип экзамена успешно удален');
    }

    public function applicants(ExamType $examType)
    {
        $examIds = $examType->exams()->pluck('id');

        $registrations = ExamRegistration::whereIn('exam_id', $examIds)
            ->with([
                'applicant',
                'exam:id,name',
                'approvedByUser:id,name',
            ])
            ->latest()
            ->paginate(30);

        return Inertia::render('Admin/ExamTypes/Applicants', [
            'examType' => $examType,
            'registrations' => $registrations,
        ]);
    }
}

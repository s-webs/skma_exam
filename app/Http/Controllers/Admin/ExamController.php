<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Services\ExamTypeAccessService;
use App\Support\ExamRegistrationRows;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamController extends Controller
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess
    ) {}

    public function index()
    {
        $accessibleTypeIds = $this->examTypeAccess->accessibleExamTypeIds(auth()->user());

        $exams = Exam::with(['examType', 'createdBy'])
            ->withCount('questions')
            ->when(
                ! $this->examTypeAccess->isDeveloper(auth()->user()),
                fn ($query) => $query->whereIn('exam_type_id', $accessibleTypeIds)
            )
            ->latest()
            ->get();

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $exams,
        ]);
    }

    public function create()
    {
        $examTypes = $this->examTypeAccess
            ->scopeAccessible(ExamType::where('is_active', true), auth()->user())
            ->get();

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
            'require_telegram_verification' => 'boolean',
        ]);

        $this->examTypeAccess->ensureCanAccess(auth()->user(), (int) $validated['exam_type_id']);

        $validated['created_by_user_id'] = auth()->id();

        Exam::create($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно создан');
    }

    public function edit(Exam $exam)
    {
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

        $examTypes = $this->examTypeAccess
            ->scopeAccessible(ExamType::where('is_active', true), auth()->user())
            ->get();

        return Inertia::render('Admin/Exams/Edit', [
            'exam' => $exam->load('examType'),
            'examTypes' => $examTypes,
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

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
            'require_telegram_verification' => 'boolean',
        ]);

        $this->examTypeAccess->ensureCanAccess(auth()->user(), (int) $validated['exam_type_id']);

        $exam->update($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно обновлен');
    }

    public function destroy(Exam $exam)
    {
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

        if ($exam->attempts()->count() > 0) {
            return back()->with('error', 'Невозможно удалить экзамен с существующими попытками');
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Экзамен успешно удален');
    }

    public function applicants(Exam $exam)
    {
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

        $registrations = ExamRegistration::query()
            ->where('exam_registrations.exam_id', $exam->id)
            ->join('applicants', 'exam_registrations.applicant_id', '=', 'applicants.id')
            ->select('exam_registrations.*')
            ->with([
                'applicant',
                'approvedByUser:id,name',
                'examAttempts' => fn ($query) => $query->latest('id'),
            ])
            ->orderBy('applicants.id')
            ->paginate(30);

        return Inertia::render('Admin/Exams/Applicants', [
            'exam' => $exam->load('examType'),
            'registrations' => $registrations,
            'rows' => ExamRegistrationRows::flatten($registrations->items()),
        ]);
    }
}

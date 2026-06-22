<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use App\Services\ExamTypeAccessService;
use App\Support\ExamRegistrationRows;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class ExamTypeController extends Controller
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess
    ) {}

    public function index()
    {
        $examTypes = $this->examTypeAccess
            ->scopeAccessible(ExamType::query(), auth()->user())
            ->withCount('exams')
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

    public function show(ExamType $examType)
    {
        $this->examTypeAccess->ensureCanAccess(auth()->user(), $examType);

        $examType->load(['exams' => function ($query) {
            $query->select('id', 'exam_type_id', 'name', 'language', 'is_active')
                ->withCount('questions');
        }]);

        return Inertia::render('Admin/ExamTypes/Show', [
            'examType' => $examType,
        ]);
    }

    public function edit(ExamType $examType)
    {
        $this->examTypeAccess->ensureCanAccess(auth()->user(), $examType);

        $examType->load(['users:id,name,email', 'roles:id,name']);

        $assignableUsers = User::role(['ktbo', 'registrator'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $assignableRoles = Role::whereIn('name', ['ktbo', 'registrator'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/ExamTypes/Edit', [
            'examType' => $examType,
            'assignableUsers' => $assignableUsers,
            'assignableRoles' => $assignableRoles,
            'assignedUserIds' => $examType->users->pluck('id'),
            'assignedRoleIds' => $examType->roles->pluck('id'),
        ]);
    }

    public function update(Request $request, ExamType $examType)
    {
        $this->examTypeAccess->ensureCanAccess(auth()->user(), $examType);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $examType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? false,
            'slug' => $validated['slug'],
        ]);

        if ($this->examTypeAccess->isDeveloper(auth()->user())) {
            $this->examTypeAccess->syncAccess(
                $examType,
                $validated['user_ids'] ?? [],
                $validated['role_ids'] ?? []
            );
        }

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
        $this->examTypeAccess->ensureCanAccess(auth()->user(), $examType);

        $examIds = $examType->exams()->pluck('id');

        $registrations = ExamRegistration::whereIn('exam_id', $examIds)
            ->with([
                'applicant',
                'exam:id,name',
                'approvedByUser:id,name',
                'examAttempts' => fn ($query) => $query->latest('id'),
            ])
            ->latest()
            ->paginate(30);

        $examType->load(['exams' => function ($query) {
            $query->select('id', 'exam_type_id', 'name', 'language');
        }]);

        return Inertia::render('Admin/ExamTypes/Applicants', [
            'examType' => $examType,
            'registrations' => $registrations,
            'rows' => ExamRegistrationRows::flatten($registrations->items()),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\User;
use App\Services\AuthorizationService;
use App\Services\ExamTypeAccessService;
use App\Support\ExamRegistrationRows;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class ExamTypeController extends Controller
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess,
        protected AuthorizationService $authorization
    ) {}

    public function index()
    {
        $examTypes = $this->examTypeAccess
            ->scopeAccessible(ExamType::query(), auth()->user())
            ->withCount('exams')
            ->with(['exams' => function ($query) {
                $query->select('id', 'exam_type_id', 'name_ru', 'name_kk', 'name_en', 'language');
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
            'name_ru' => 'required|string|max:255',
            'name_kk' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name_ru']);

        ExamType::create($validated);

        return redirect()->route('admin.exam-types.index')
            ->with('success', 'Тип экзамена успешно создан');
    }

    public function show(ExamType $examType)
    {
        $this->authorization->ensureCan(auth()->user(), 'exam-types.view', $examType);

        $examType->load(['exams' => function ($query) {
            $query->select('id', 'exam_type_id', 'name_ru', 'name_kk', 'name_en', 'language', 'is_active')
                ->withCount('questions');
        }]);

        return Inertia::render('Admin/ExamTypes/Show', [
            'examType' => $examType,
        ]);
    }

    public function edit(ExamType $examType)
    {
        $this->authorization->ensureCan(auth()->user(), 'exam-types.edit', $examType);

        $examType->load(['users:id,name,email', 'roles:id,name']);

        $assignableRoles = Role::query()
            ->where('name', '!=', 'developer')
            ->orderBy('name')
            ->get(['id', 'name']);

        $assignableRoleIds = $assignableRoles->pluck('id');

        $assignableUsers = $assignableRoleIds->isEmpty()
            ? collect()
            : User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $assignableRoleIds))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

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
        $this->authorization->ensureCan(auth()->user(), 'exam-types.edit', $examType);

        $validated = $request->validate([
            'name_ru' => 'required|string|max:255',
            'name_kk' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ]);

        $validated['slug'] = Str::slug($validated['name_ru']);

        $examType->update([
            'name_ru' => $validated['name_ru'],
            'name_kk' => $validated['name_kk'] ?? null,
            'name_en' => $validated['name_en'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? false,
            'slug' => $validated['slug'],
        ]);

        if ($this->authorization->can(auth()->user(), 'exam-types.manage-access', $examType)) {
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

    public function applicants(Request $request, ExamType $examType)
    {
        $this->authorization->ensureCan(auth()->user(), 'exam-types.view', $examType);

        $examIds = $examType->exams()->pluck('id');
        $identifier = preg_replace('/\D+/', '', (string) $request->query('identifier', '')) ?? '';

        $registrationsQuery = ExamRegistration::whereIn('exam_id', $examIds)
            ->with([
                'applicant',
                'exam:id,name_ru,name_kk,name_en,language',
                'approvedByUser:id,name',
                'examAttempts' => fn ($query) => $query->latest('id')->with('result'),
            ]);

        if ($identifier !== '') {
            $registrationsQuery->whereHas(
                'applicant',
                fn ($query) => $query->where('identifier', 'like', '%'.$identifier.'%'),
            );
        }

        $registrations = $registrationsQuery
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $examType->load(['exams' => function ($query) {
            $query->select('id', 'exam_type_id', 'name_ru', 'name_kk', 'name_en', 'language');
        }]);

        return Inertia::render('Admin/ExamTypes/Applicants', [
            'examType' => $examType,
            'registrations' => $registrations,
            'rows' => ExamRegistrationRows::flatten($registrations->items()),
            'filters' => [
                'identifier' => $request->string('identifier')->toString(),
            ],
        ]);
    }
}

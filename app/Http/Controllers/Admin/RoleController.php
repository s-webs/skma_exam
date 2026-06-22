<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissionGroups' => PermissionRegistry::grouped(),
            'allPermissions' => PermissionRegistry::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permission_names' => 'nullable|array',
            'permission_names.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permission_names'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно создана.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions:id,name');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissionGroups' => PermissionRegistry::grouped(),
            'assignedPermissionNames' => $role->permissions->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id),
            ],
            'permission_names' => 'nullable|array',
            'permission_names.*' => 'string|exists:permissions,name',
        ]);

        if ($role->name === 'developer' && $validated['name'] !== 'developer') {
            return back()->with('error', 'Нельзя переименовать роль developer.');
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permission_names'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно обновлена.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'developer') {
            return back()->with('error', 'Нельзя удалить роль developer.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Нельзя удалить роль, назначенную пользователям.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно удалена.');
    }
}

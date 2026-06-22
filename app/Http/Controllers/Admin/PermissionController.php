<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'permissionGroups' => PermissionRegistry::grouped(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Permissions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
            'guard_name' => 'nullable|string|max:255',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission успешно создан.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->exists()) {
            return back()->with('error', 'Нельзя удалить permission, назначенный ролям.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission успешно удалён.');
    }
}

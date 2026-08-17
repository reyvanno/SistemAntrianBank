<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class RoleController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:role.view')
            ->only('index');

        $this->middleware('permission:role.create')
            ->only(['create', 'store']);

        $this->middleware('permission:role.update')
            ->only(['edit', 'update']);

        $this->middleware('permission:role.delete')
            ->only('destroy');
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return inertia('Admin/Roles/Create', [
            'permissions' => $this->getPermissions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        $permissionModels = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissionModels);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->load('permissions')
            ->loadCount('users');

        return inertia('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $this->getPermissions(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($role->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where('guard_name', 'web'),
            ],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        $permissionModels = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissionModels);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        try {
            if ($role->users()->exists()) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Role tidak dapat dihapus karena masih digunakan oleh user.'
                    );
            }

            $role->delete();

            return redirect()
                ->back()
                ->with('success', 'Role berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Role tidak dapat dihapus karena masih digunakan.'
                );
        }
    }

    /**
     * Get all available permissions.
     */
    private function getPermissions()
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'guard_name',
            ]);
    }
}
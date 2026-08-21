<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:permission.view')
            ->only('index');

        $this->middleware('permission:permission.create')
            ->only(['create', 'store']);

        $this->middleware('permission:permission.update')
            ->only(['edit', 'update']);

        $this->middleware('permission:permission.delete')
            ->only('destroy');
    }

    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->withCount('roles')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return inertia('Admin/Permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return inertia('Admin/Permissions/Create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+\.[a-z0-9_]+$/',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', 'web'),
            ],
        ], [
            'name.regex' =>
                'Format permission harus seperti: user.view atau queue.call.',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        abort_unless(
            $permission->guard_name === 'web',
            404
        );

        return inertia('Admin/Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(
        Request $request,
        Permission $permission
    ) {
        abort_unless(
            $permission->guard_name === 'web',
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+\.[a-z0-9_]+$/',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($permission->id),
            ],
        ], [
            'name.regex' =>
                'Format permission harus seperti: user.view atau queue.call.',
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        try {
            abort_unless(
                $permission->guard_name === 'web',
                404
            );

            if ($permission->roles()->exists()) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Permission tidak dapat dihapus karena masih digunakan oleh role.'
                    );
            }

            $permission->delete();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Permission berhasil dihapus.'
                );
        } catch (QueryException $e) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Permission tidak dapat dihapus karena masih digunakan.'
                );
        }
    }
}
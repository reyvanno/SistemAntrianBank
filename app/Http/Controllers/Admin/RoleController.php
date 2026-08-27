<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Spatie\Permission\Models\Role;
use App\Services\RoleService;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Database\QueryException;

class RoleController extends BaseController
{
    public function __construct(
        protected RoleService $roleService
    ) {
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
        return inertia('Admin/Roles/Index', [
            'roles' => $this->roleService->paginate(
                request('search')
            ),

            'filters' => [
                'search' => request('search'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return inertia('Admin/Roles/Create', [
            'permissions' => $this->roleService
                ->getPermissions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(
        StoreRoleRequest $request
    ) {
        $this->roleService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.roles.index')
            ->with(
                'success',
                'Role berhasil ditambahkan.'
            );
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

            'permissions' => $this->roleService
                ->getPermissions(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role
    ) {
        $this->roleService->update(
            $role,
            $request->validated()
        );

        return redirect()
            ->route('admin.roles.index')
            ->with(
                'success',
                'Role berhasil diperbarui.'
            );
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

            $this->roleService->delete($role);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Role berhasil dihapus.'
                );

        } catch (QueryException $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Role tidak dapat dihapus karena masih digunakan.'
                );
        }
    }
}
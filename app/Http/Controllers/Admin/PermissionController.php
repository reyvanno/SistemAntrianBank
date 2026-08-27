<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Database\QueryException;

class PermissionController extends BaseController
{
    public function __construct(
        protected PermissionService $permissionService
    ) {
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
        return inertia('Admin/Permissions/Index', [
            'permissions' => $this->permissionService->paginate(
                request('search')
            ),

            'filters' => [
                'search' => request('search'),
            ],
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
    public function store(
        StorePermissionRequest $request
    ) {
        $this->permissionService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.permissions.index')
            ->with(
                'success',
                'Permission berhasil ditambahkan.'
            );
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return inertia('Admin/Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ) {
        $this->permissionService->update(
            $permission,
            $request->validated()
        );

        return redirect()
            ->route('admin.permissions.index')
            ->with(
                'success',
                'Permission berhasil diperbarui.'
            );
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        try {
            $this->permissionService->delete(
                $permission
            );

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
                    'Permission tidak dapat dihapus karena masih digunakan oleh role.'
                );
        }
    }
}
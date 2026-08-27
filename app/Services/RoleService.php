<?php

namespace App\Services;

use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    /**
     * Mengambil daftar role dengan search dan pagination.
     */
    public function paginate(
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return Role::query()
            ->withCount([
                'permissions',
                'users',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'ILIKE',
                        "%{$search}%"
                    )->orWhere(
                            'description',
                            'ILIKE',
                            "%{$search}%"
                        );
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Membuat role baru.
     */
    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'description' => $data['description'] ?? null,
        ]);

        $this->syncPermissions(
            $role,
            $data['permissions'] ?? []
        );

        return $role->refresh();
    }

    /**
     * Memperbarui role.
     */
    public function update(
        Role $role,
        array $data
    ): Role {
        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncPermissions(
            $role,
            $data['permissions'] ?? []
        );

        return $role->refresh();
    }

    /**
     * Menghapus role.
     */
    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * Sinkronisasi permission yang dimiliki role.
     */
    protected function syncPermissions(
        Role $role,
        array $permissionIds
    ): void {
        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissions);
    }

    /**
     * Mengambil seluruh permission yang tersedia.
     */
    public function getPermissions()
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
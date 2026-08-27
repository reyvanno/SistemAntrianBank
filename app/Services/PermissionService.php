<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * Mengambil daftar permission dengan search dan pagination.
     */
    public function paginate(
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return Permission::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'ILIKE',
                        "%{$search}%"
                    )->orWhere(
                            'guard_name',
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
     * Membuat permission baru.
     */
    public function create(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    /**
     * Memperbarui permission.
     */
    public function update(
        Permission $permission,
        array $data
    ): Permission {
        $permission->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        return $permission->refresh();
    }

    /**
     * Menghapus permission.
     */
    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
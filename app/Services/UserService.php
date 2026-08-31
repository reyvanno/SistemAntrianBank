<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(
        ?string $search = null
    ): LengthAwarePaginator {
        return User::query()
            ->with('roles')
            ->when(
                $search,
                function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'name',
                            'ILIKE',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'username',
                                'ILIKE',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'ILIKE',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'roles',
                                function ($roleQuery) use ($search) {
                                    $roleQuery->where(
                                        'name',
                                        'ILIKE',
                                        "%{$search}%"
                                    );
                                }
                            );
                    });
                }
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        return DB::transaction(
            function () use ($data) {
                $role = $data['role'];

                unset($data['role']);

                if (
                    !in_array(
                        $role,
                        ['teller', 'customer_service']
                    )
                ) {
                    $data['counter_id'] = null;
                }

                $data['password'] = Hash::make(
                    $data['password']
                );

                $user = User::create($data);

                $user->assignRole($role);

                return $user->refresh();
            }
        );
    }

    public function update(
        User $user,
        array $data
    ): User {
        return DB::transaction(
            function () use ($user, $data) {
                $role = $data['role'];

                unset($data['role']);

                if (
                    !in_array(
                        $role,
                        ['teller', 'customer_service']
                    )
                ) {
                    $data['counter_id'] = null;
                }

                if (!empty($data['password'])) {
                    $data['password'] = Hash::make(
                        $data['password']
                    );
                } else {
                    unset($data['password']);
                }

                $user->update($data);

                $user->syncRoles($role);

                return $user->refresh();
            }
        );
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return User::query()

            ->with('roles')

            ->when($search, function ($query) use ($search) {

                $query

                    ->where('name', 'ILIKE', "%{$search}%")

                    ->orWhere('email', 'ILIKE', "%{$search}%")

                    ->orWhereHas('roles', function ($q) use ($search) {

                        $q->where('name', 'ILIKE', "%{$search}%");

                    });

            })

            ->latest()

            ->paginate(10)

            ->withQueryString();
    }

    public function create(array $data): User
    {
        $role = $data['role'];

        unset($data['role']);
        
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $user->assignRole($role);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $role = $data['role'];

        unset($data['role']);

        if (!empty($data['password'])) {

            $data['password'] = Hash::make($data['password']);

        } else {

            unset($data['password']);

        }

        $user->update($data);

        $user->syncRoles($role);

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
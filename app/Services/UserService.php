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

            ->with('role')

            ->when($search, function ($query) use ($search) {

                $query

                    ->where('name', 'ILIKE', "%{$search}%")

                    ->orWhere('email', 'ILIKE', "%{$search}%")

                    ->orWhereHas('role', function ($q) use ($search) {

                        $q->where('description', 'ILIKE', "%{$search}%");

                    });

            })

            ->latest()

            ->paginate(10)

            ->withQueryString();
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (!empty($data['password'])) {

            $data['password'] = Hash::make($data['password']);

        } else {

            unset($data['password']);

        }

        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
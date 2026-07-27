<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function index()
    {
        return inertia('Admin/Users/Index', [
            'users' => $this->userService->paginate(
                request('search')
            ),

            'filters' => [
                'search' => request('search'),
            ],
        ]);
    }

    public function create()
    {
        return inertia('Admin/Users/Create', [
            'roles' => Role::orderBy('description')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return inertia('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::orderBy('description')->get(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        $this->userService->update(
            $user,
            $request->validated()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        try {

            $this->userService->delete($user);

            return redirect()
                ->back()
                ->with('success', 'User berhasil dihapus.');

        } catch (QueryException $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'User tidak dapat dihapus karena masih digunakan.'
                );

        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Counter;
use App\Services\UserService;
use Illuminate\Database\QueryException;

class UserController extends BaseController
{
    public function __construct(
        protected UserService $userService
    ) {
        $this->middleware('permission:user.view')
            ->only('index');

        $this->middleware('permission:user.create')
            ->only(['create', 'store']);

        $this->middleware('permission:user.update')
            ->only(['edit', 'update']);

        $this->middleware('permission:user.delete')
            ->only('destroy');
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
            'roles' => Role::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'counters' => Counter::query()
                ->with('service:id,code,name')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
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
            'user' => $user->load('roles', 'counter.service'),

            'roles' => Role::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

            'counters' => Counter::query()
                ->with('service:id,code,name')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
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
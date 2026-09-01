<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $authStart = microtime(true);

        $user = $request->user();

        $userQueryTime = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | COUNTER
        |--------------------------------------------------------------------------
        */

        $counterStart = microtime(true);

        if ($user) {
            $user->load('counter');
        }

        $counterTime = microtime(true) - $counterStart;

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */

        $roleStart = microtime(true);

        $role = $user
            ? $user->getRoleNames()->first()
            : null;

        $roleTime = microtime(true) - $roleStart;

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissionStart = microtime(true);

        $permissions = $user
            ? $user->getAllPermissions()
                ->pluck('name')
                ->values()
            : collect();

        $permissionTime = microtime(true) - $permissionStart;

        /*
        |--------------------------------------------------------------------------
        | USER TO ARRAY
        |--------------------------------------------------------------------------
        */

        $toArrayStart = microtime(true);

        $userArray = $user
            ? $user->toArray()
            : null;

        $toArrayTime = microtime(true) - $toArrayStart;

        /*
        |--------------------------------------------------------------------------
        | BUILD AUTH
        |--------------------------------------------------------------------------
        */

        $buildStart = microtime(true);

        $authUser = $user
            ? [
                ...$userArray,

                'role' => $role,

                'permissions' => $permissions,
            ]
            : null;

        $buildTime = microtime(true) - $buildStart;

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $authTime = microtime(true) - $authStart;


        return [
            ...parent::share($request),

            'auth' => [
                'user' => $authUser,
            ],

            'flash' => [
                'success' => fn() =>
                    $request->session()->get('success'),

                'error' => fn() =>
                    $request->session()->get('error'),

                'queue' => fn() =>
                    $request->session()->get('queue'),
            ],
        ];
    }
}
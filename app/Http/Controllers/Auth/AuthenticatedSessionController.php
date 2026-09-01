<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Masa hidup presence dalam Redis.
     */
    private const PRESENCE_TTL = 90;

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        /*
         * Authenticate user.
         */
        $request->authenticate();

        /*
         * Regenerate session setelah login.
         */
        $request->session()->regenerate();

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Counter Presence
        |--------------------------------------------------------------------------
        |
        | Presence langsung dibuat ketika login berhasil.
        |
        | Ini penting karena DashboardService akan membaca
        | Redis ketika dashboard pertama kali dirender.
        |
        */
        if (
            $user->counter_id &&
            $user->is_active &&
            $user->counter?->is_active
        ) {
            Redis::setex(
                $this->presenceKey($user->id),
                self::PRESENCE_TTL,
                'online'
            );
        }

        return redirect()
            ->route('admin.dashboard')
            ->with(
                'success',
                "Selamat datang, {$user->name}!"
            );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        /*
         * Hapus presence ketika logout.
         */
        if ($user) {
            Redis::del(
                $this->presenceKey($user->id)
            );
        }

        /*
         * Logout.
         */
        Auth::guard('web')->logout();

        /*
         * Hapus session.
         */
        $request->session()->invalidate();

        /*
         * Generate CSRF token baru.
         */
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redis key untuk presence user.
     */
    private function presenceKey(int $userId): string
    {
        return "counter_presence:user:{$userId}";
    }
}
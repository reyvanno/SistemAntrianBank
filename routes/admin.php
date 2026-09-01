<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\CounterPresenceController;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        Route::get('/dashboard/data', [
            DashboardController::class,
            'data',
        ])->name('dashboard.data');

        Route::get('/performance-test', function () {
            return response()->json([
                'success' => true,
                'time' => now()->toDateTimeString(),
            ]);
        })->name('performance.test');

        /*
        |--------------------------------------------------------------------------
        | Counter Presence
        |--------------------------------------------------------------------------
        */

        Route::post('/counter/heartbeat', [
            CounterPresenceController::class,
            'heartbeat',
        ])->name('counter.heartbeat');

        Route::post('/counter/offline', [
            CounterPresenceController::class,
            'offline',
        ])->name('counter.offline');

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        Route::resource('services', ServiceController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        Route::resource('counters', CounterController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class)
            ->except(['show']);

        /*
|--------------------------------------------------------------------------
| Queues
|--------------------------------------------------------------------------
*/

        Route::resource('queues', QueueController::class)
            ->only([
                'index',
                'store',
            ]);

        /*
         * Realtime queue data.
         *
         * Dipanggil oleh halaman Antrian
         * setiap 1.5 detik.
         */
        Route::get('/queues/data', [
            QueueController::class,
            'data',
        ])
            ->withoutMiddleware([
                \App\Http\Middleware\HandleInertiaRequests::class,
            ])
            ->name('queues.data');

        Route::post('/queues/call', [
            QueueController::class,
            'call',
        ])->name('queues.call');

        Route::post('/queues/recall', [
            QueueController::class,
            'recall',
        ])->name('queues.recall');

        Route::post('/queues/start', [
            QueueController::class,
            'start',
        ])->name('queues.start');

        Route::post('/queues/finish', [
            QueueController::class,
            'finish',
        ])->name('queues.finish');

        Route::post('/queues/skip', [
            QueueController::class,
            'skip',
        ])->name('queues.skip');

        Route::post('/queues/{queue}/cancel', [
            QueueController::class,
            'cancel',
        ])->name('queues.cancel');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::resource('roles', RoleController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        Route::resource('permissions', PermissionController::class)
            ->except(['show']);
    });
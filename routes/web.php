<?php

use App\Http\Controllers\Customer\QueueController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Monitor
|--------------------------------------------------------------------------
*/

Route::get('/monitor', [
    MonitorController::class,
    'index',
])->name('monitor');

Route::get('/monitor/data', [
    MonitorController::class,
    'data',
])->name('monitor.data');

/*
|--------------------------------------------------------------------------
| Customer Queue
|--------------------------------------------------------------------------
*/

Route::get('/ambil-nomor', [
    QueueController::class,
    'create',
])->name('customer.queue.create');

Route::post('/ambil-nomor', [
    QueueController::class,
    'store',
])->name('customer.queue.store');

Route::get('/ambil-nomor/{queue}/pdf', [
    QueueController::class,
    'pdf',
])->name('customer.queue.pdf');

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy',
    ])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
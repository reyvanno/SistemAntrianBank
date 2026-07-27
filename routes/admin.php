<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueueController;

Route::middleware([
    'auth',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])
            ->middleware('role:admin,teller,customer_service')
            ->name('dashboard');
            
        Route::resource('services', ServiceController::class)
            ->middleware('role:admin')
            ->except(['show']);

        Route::resource('counters', CounterController::class)
            ->middleware('role:admin')
            ->except(['show']);

        Route::resource('users', UserController::class)
            ->middleware('role:admin')
            ->except('show');

        Route::resource(
            'queues',
            QueueController::class
        )->only([
                    'index',
                    'store',
                ])
            ->middleware('role:admin,teller');
    });

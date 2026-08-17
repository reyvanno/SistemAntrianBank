<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\RoleController;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        Route::resource('services', ServiceController::class)
            ->except(['show']);

        Route::resource('counters', CounterController::class)
            ->except(['show']);

        Route::resource('users', UserController::class)
            ->except(['show']);

        Route::resource('queues', QueueController::class)
            ->only([
                'index',
                'store',
            ]);

        Route::resource('roles', RoleController::class)
            ->except(['show']);
    });
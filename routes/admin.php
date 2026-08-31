<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

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

        Route::resource('roles', RoleController::class)
            ->except(['show']);

        Route::resource('permissions', PermissionController::class)
            ->except(['show']);
    });
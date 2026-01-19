<?php

use App\Http\Controllers\Admin\GenerationController;
use App\Http\Controllers\Admin\StyleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

Route::prefix('admin')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'authenticate']);
    });

    // Protected Routes
    Route::middleware('auth')->group(function () {
        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        Route::resource('styles', StyleController::class);
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/generations', [GenerationController::class, 'index'])->name('generations.index');
    });
});
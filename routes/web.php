<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Otp
Route::middleware('guest')->group(function () {
    Route::get('/otp-login', [OtpController::class, 'showLogin'])->name('otp.login');
    Route::post('/otp-login', [OtpController::class, 'generate']);

    Route::get('/otp-verify', [OtpController::class, 'showVerify'])->name('otp.verify');
    Route::post('/otp-verify', [OtpController::class, 'verify']);
});

// Admin
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('index');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
});
require __DIR__.'/settings.php';

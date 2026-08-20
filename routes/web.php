<?php

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
require __DIR__.'/settings.php';

<?php

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\ChatController;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Responses\RegisterResponse;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::post('dashboard', [ChatController::class, 'store'])->name('chat.store');
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
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/detail', [AdminController::class, 'show'])->name('users.show');

    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{conversation}/read', [InboxController::class, 'markRead'])->name('inbox.mark-read');
    Route::post('/inbox/{conversation}', [InboxController::class, 'store'])->name('inbox.store');
});

// Register
Route::post('/register', function (RegisterRequest $request, CreateNewUser $creator) {
    event(new Registered($user = $creator->create($request->all())));
    Auth::login($user);

    return app(RegisterResponse::class);
})->middleware(['guest', HandlePrecognitiveRequests::class])->name('register.store');

require __DIR__.'/settings.php';

if (app()->environment('local') && file_exists(__DIR__.'/demo.php')) {
    require __DIR__.'/demo.php';
}

<?php

use App\Http\Controllers\Client\AuthController;
use App\Http\Middleware\VerifyEmailMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::get('/forgot-password-sent', [AuthController::class, 'forgotPasswordEmailSent'])->name('forgot-password-sent');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset-password');
});

Route::middleware(['auth'])->group(function () {
    Route::any('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email')->withoutMiddleware(VerifyEmailMiddleware::class);
    Route::get('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->name('resend-verify-email')->withoutMiddleware(VerifyEmailMiddleware::class);
    Route::get('/enable-2fa', [AuthController::class, 'enableTwoFactor'])->name('enable-2fa');
    Route::get('/disable-2fa', [AuthController::class, 'disableTwoFactor'])->name('disable-2fa');
    Route::get('/require-2fa', [AuthController::class, 'requireTwoFactor'])->name('require-2fa');
    Route::get('/lost-access-2fa/{email_token}', [AuthController::class, 'lostAccessTwoFactor'])->name('lost-access-2fa');
    Route::get('/update-address', [AuthController::class, 'updateAddress'])->name('update-address');
    Route::get('/confirm-email-address/{email:token}', [AuthController::class, 'confirmEmailAddress'])->name('confirm-email-address');
});

Route::middleware(['web'])->group(function () {
    Route::get('/verify-email/verified', [AuthController::class, 'verifiedEmail'])->name('verify-email.verified');
    Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmailWithToken'])->name('verify-email.token')->withoutMiddleware(VerifyEmailMiddleware::class);
});

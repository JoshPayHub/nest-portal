<?php
// auth.php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // This makes the root of your site the login page
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store']);

    // FORGOT PASSWORD FLOW
    Route::get('/forgot-password', [ForgotPasswordController::class, 'index']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);

    Route::get('/otp-verify', [OtpController::class, 'index']);
    Route::post('/otp-verify', [OtpController::class, 'verify']);
    Route::post('/otp-resend', [OtpController::class, 'resend']);

    Route::get('/reset-password', [ResetPasswordController::class, 'index']);
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

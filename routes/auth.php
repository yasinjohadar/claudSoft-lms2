<?php

use App\Http\Controllers\Auth\LocalDevLoginController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\PhoneChangeOtpController;
use App\Http\Controllers\Auth\PhoneLoginController;
use App\Http\Controllers\Auth\PhoneOtpController;
use App\Http\Controllers\Auth\PhonePasswordResetOtpController;
use App\Http\Controllers\Auth\RegisterOtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Support\LocalDevLoginGate;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::middleware('public.registration')->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])
            ->name('register');

        Route::post('register', [RegisteredUserController::class, 'store']);

        Route::post('register/otp/complete', [RegisterOtpController::class, 'verifyAndRegister'])->name('register.otp.complete');
    });

    // Simple login page for testing
    Route::get('simple-login', function () {
        return view('auth.simple-login');
    })->name('simple-login');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('phone-login', [PhoneLoginController::class, 'create'])->name('phone-login');
    Route::post('phone-login/send-otp', [PhoneLoginController::class, 'sendOtp'])->name('phone-login.send-otp');
    Route::post('phone-login/verify', [PhoneLoginController::class, 'verifyAndLogin'])->name('phone-login.verify');

    Route::get('phone-otp/verify', [PhoneOtpController::class, 'showVerify'])->name('phone-otp.verify');
    Route::post('phone-otp/send', [PhoneOtpController::class, 'send'])->name('phone-otp.send');
    Route::post('phone-otp/verify', [PhoneOtpController::class, 'verify'])->name('phone-otp.verify.submit');

    Route::post('password/otp/send', [PhonePasswordResetOtpController::class, 'send'])->name('password.otp.send');
    Route::post('password/otp/verify', [PhonePasswordResetOtpController::class, 'verify'])->name('password.otp.verify');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

if (app()->environment('local')) {
    Route::middleware(['guest', 'local.dev.login'])->group(function () {
        $localDevPath = LocalDevLoginGate::path();

        Route::get($localDevPath, [LocalDevLoginController::class, 'show'])
            ->name('local-dev-login.show');
        Route::post($localDevPath, [LocalDevLoginController::class, 'login'])
            ->name('local-dev-login.login');
    });
}

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('phone/change/send-otp', [PhoneChangeOtpController::class, 'send'])->name('phone.change.send-otp');
    Route::post('phone/change/apply', [PhoneChangeOtpController::class, 'applyVerifiedPhone'])->name('phone.change.apply');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Dedicated Portal Login Routes (accessible to guests & switching sessions)
// Login POST endpoints share the dual-tier limiter: Tier A (40 req/min/IP)
// + Tier B (5 failed attempts / 15 min per email+institute, via LoginRequest).
// Unified Multi-Role Portal Login Routes (Principal, Faculty, Student)
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

Route::get('principal/login', [AuthenticatedSessionController::class, 'createPrincipal'])->name('principal.login');
Route::post('principal/login', [AuthenticatedSessionController::class, 'storePrincipal'])->middleware('throttle:login')->name('principal.login.store');

Route::get('faculty/login', [AuthenticatedSessionController::class, 'createFaculty'])->name('faculty.login');
Route::get('teacher/login', [AuthenticatedSessionController::class, 'createFaculty'])->name('teacher.login');
Route::post('faculty/login', [AuthenticatedSessionController::class, 'storeFaculty'])->middleware('throttle:login')->name('faculty.login.store');

Route::get('student/login', [AuthenticatedSessionController::class, 'createStudent'])->name('student.login');
Route::post('student/login', [AuthenticatedSessionController::class, 'storeStudent'])->middleware('throttle:login')->name('student.login.store');

// Global Admin Access (Dedicated Secure URL)
Route::get('globaladmin/login', [AuthenticatedSessionController::class, 'createGlobalAdmin'])->name('globaladmin.login');
Route::post('globaladmin/login', [AuthenticatedSessionController::class, 'storeGlobalAdmin'])->middleware('throttle:login')->name('globaladmin.login.store');
Route::get('globaladmin', fn () => redirect()->route('globaladmin.login'));

Route::get('global-admin/login', [AuthenticatedSessionController::class, 'createGlobalAdmin'])->name('global-admin.login');
Route::post('global-admin/login', [AuthenticatedSessionController::class, 'storeGlobalAdmin'])->middleware('throttle:login')->name('global-admin.login.store');
Route::get('global-admin', fn () => redirect()->route('globaladmin.login'));

Route::middleware('guest')->group(function () {

    // ── Password Reset via OTP + Admin Approval (existing flow) ────────────
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // OTP Verification & One-Click Cancellation Routes
    Route::get('password/cancel/{token}', [PasswordResetLinkController::class, 'cancel'])->name('password.cancel');
    Route::get('password/verify-otp', [PasswordResetLinkController::class, 'showOtpForm'])->name('password.otp.show');
    Route::post('password/verify-otp', [PasswordResetLinkController::class, 'verifyOtp'])->name('password.otp.verify');

    // ── Standard Token-Based Password Reset (Laravel Password Broker) ──────
    // Uses a separate URI prefix ("forgot-password-token") so both flows
    // coexist. The POST endpoint sends the reset link via email; the GET
    // endpoint renders the "set new password" form when the user clicks
    // the link. Throttle: max 3 requests per minute per IP.
    Route::middleware('throttle:3,1')->group(function () {
        // Step 1: User enters their email → broker sends reset link
        Route::get('forgot-password-token', [NewPasswordController::class, 'create'])
            ->name('token.password.request');

        Route::post('forgot-password-token', [NewPasswordController::class, 'store'])
            ->name('token.password.email');

        // Step 2: User clicks link in email → shown "set new password" form
        Route::get('reset-password/{token}', [NewPasswordController::class, 'resetCreate'])
            ->name('password.reset');

        // Step 3: User submits new password → broker validates + saves
        Route::post('reset-password', [NewPasswordController::class, 'resetStore'])
            ->name('password.store');
    });
});

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

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

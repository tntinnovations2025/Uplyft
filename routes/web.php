<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ─── Auth Routes ────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Root Redirect (Auth-Aware) ──────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->dashboardRoute());
    }
    return redirect()->route('login');
});

// ─── Admin / Staff Portal (Module 4 & 5) ────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/admissions', fn () => view('admissions'))->name('admissions.index');
    Route::get('/teachers/onboarding', fn () => view('teachers.onboarding'))->name('teachers.onboarding');
    Route::get('/attendance', fn () => view('attendance'))->name('attendance.index');
});

// ─── Student Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', fn () => view('student.dashboard'))->name('dashboard');
});

// ─── Teacher Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', fn () => view('teacher.dashboard'))->name('dashboard');
});

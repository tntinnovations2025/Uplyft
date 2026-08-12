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
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin/admissions', [\App\Http\Controllers\AdminPortalController::class, 'admissions'])->name('admissions.index');
    Route::get('/admin/teachers/onboarding', [\App\Http\Controllers\AdminPortalController::class, 'onboarding'])->name('teachers.onboarding');
    
    // New Admin Directories
    Route::get('/admin/students', [\App\Http\Controllers\AdminPortalController::class, 'studentsDirectory'])->name('admin.students.directory');
    Route::get('/admin/teachers', [\App\Http\Controllers\AdminPortalController::class, 'teachersDirectory'])->name('admin.teachers.directory');
    Route::get('/admin/fees', [\App\Http\Controllers\AdminPortalController::class, 'feeManagement'])->name('admin.fees.management');
    Route::post('/admin/invoices/{invoice}/paid', [\App\Http\Controllers\AdminPortalController::class, 'markInvoicePaid'])->name('admin.invoices.mark-paid');
});

// ─── Student Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', fn () => view('student.dashboard'))->name('dashboard');
    Route::get('/fees', [\App\Http\Controllers\StudentPortalController::class, 'fees'])->name('fees');
    Route::get('/invoices', [\App\Http\Controllers\StudentPortalController::class, 'invoices'])->name('invoices');
    Route::get('/attendance', [\App\Http\Controllers\StudentPortalController::class, 'attendance'])->name('attendance');
    Route::get('/timetable', [\App\Http\Controllers\StudentPortalController::class, 'timetable'])->name('timetable');
    Route::get('/courses', [\App\Http\Controllers\StudentPortalController::class, 'courses'])->name('courses');
    Route::get('/lms', [\App\Http\Controllers\StudentPortalController::class, 'lms'])->name('lms');
});

// ─── Teacher Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\TeacherPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/schedule', [\App\Http\Controllers\TeacherPortalController::class, 'schedule'])->name('schedule');
    Route::get('/attendance', [\App\Http\Controllers\TeacherPortalController::class, 'attendance'])->name('attendance');
    Route::get('/lms', [\App\Http\Controllers\TeacherPortalController::class, 'lms'])->name('lms');
});

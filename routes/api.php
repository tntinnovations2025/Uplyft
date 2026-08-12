<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentAdmissionController;
use App\Http\Controllers\TeacherOnboardingController;
use App\Http\Controllers\AttendanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Module 4: Student Admissions & Invoicing
Route::post('/admissions', [StudentAdmissionController::class, 'store'])->name('admissions.store');
Route::get('/admissions/{student}/invoice', [StudentAdmissionController::class, 'generateInvoicePdf'])->name('admissions.invoice');

// Module 5: Teacher Onboarding & Secure Document Upload
Route::post('/teachers/onboarding', [TeacherOnboardingController::class, 'store'])->name('teachers.onboarding');

// Module 5: Class Roster & Attendance Management
Route::get('/attendance/roster', [AttendanceController::class, 'index'])->name('attendance.roster');
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

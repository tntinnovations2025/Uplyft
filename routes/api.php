<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentAdmissionController;
use App\Http\Controllers\TeacherOnboardingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Principal\AcademicTrackController;

// Global API abuse protection: throttle:api (60 req/min per IP).
Route::middleware('throttle:api')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum,web'])->get('/students', function (Request $request) {
        $user = $request->user();
        $students = \App\Models\Student::where('institute_id', $user->institute_id)->get();
        return response()->json($students);
    })->name('api.students.index');

    // Dynamic Subject Architecture: Tracks & Subjects Query
    Route::get('/classes/{class}/tracks-and-subjects', [AcademicTrackController::class, 'tracksAndSubjects'])->name('api.classes.tracks-and-subjects');

    // Module 4: Student Admissions & Invoicing
    Route::post('/admissions', [StudentAdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{student}/invoice', [StudentAdmissionController::class, 'generateInvoicePdf'])->name('admissions.invoice');

    // Module 5: Teacher Onboarding & Secure Document Upload
    Route::post('/teachers/onboarding', [TeacherOnboardingController::class, 'store'])->name('teachers.onboarding');

    // Module 5: Class Roster & Attendance Management
    Route::get('/attendance/roster', [AttendanceController::class, 'index'])->name('attendance.roster');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

});

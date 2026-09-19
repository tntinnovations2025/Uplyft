<?php

use App\Http\Controllers\StudentPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Portal Routes (Module 2 & Module 5)
|--------------------------------------------------------------------------
| Dedicated portal routes for students.
| Protected by:
|  • auth: Login required
|  • role:student: Student role restriction
|  • student.fee_paid: Blocks unpaid students from courses, LMS, and exams (BUG-ENROLL-001)
*/

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // ── Open Portal Routes (Accessible to Pending & Enrolled Students) ────
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/fees', [StudentPortalController::class, 'fees'])->name('fees');
    Route::get('/invoices', [StudentPortalController::class, 'invoices'])->name('invoices');
    Route::get('/profile', [StudentPortalController::class, 'invoices'])->name('profile');

    // ── Gated Behind Admission Fee Payment & Active Enrollment (BUG-ENROLL-001) ──
    Route::middleware(['student.fee_paid'])->group(function () {
        Route::get('/attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
        Route::get('/timetable', [StudentPortalController::class, 'timetable'])->name('timetable');
        Route::get('/timetable/download', [StudentPortalController::class, 'downloadTimetable'])->name('timetable.download');
        Route::get('/timetable/export', [StudentPortalController::class, 'downloadTimetable'])->name('timetable.export');
        Route::get('/schedule', [StudentPortalController::class, 'timetable'])->name('schedule');
        Route::get('/courses', [StudentPortalController::class, 'courses'])->name('courses');
        Route::get('/subjects', [StudentPortalController::class, 'courses'])->name('subjects');
        Route::get('/materials/{id}', [StudentPortalController::class, 'lms'])->name('materials.show');
        Route::post('/rag/chat', [\App\Http\Controllers\Lms\ChatbotController::class, 'sendMessage'])->name('rag.chat');
        Route::get('/lms', [StudentPortalController::class, 'lms'])->name('lms');
        Route::get('/datesheet', [StudentPortalController::class, 'datesheet'])->name('datesheet');
        Route::get('/exam-report', [StudentPortalController::class, 'examReport'])->name('examReport');

        // ── Student Daily Diary (Homework, Tests & Class Updates) ──
        Route::get('/diary', [\App\Http\Controllers\Student\DailyDiaryController::class, 'index'])->name('diary.index');

        // ── Cambridge / Standard Mock Examinations & Instant Grading ──
        Route::get('/mocks', [\App\Http\Controllers\Student\StudentMockController::class, 'index'])->name('mocks.index');
        Route::get('/mocks/{assessment}/take', [\App\Http\Controllers\Student\StudentMockController::class, 'take'])->name('mocks.take');
        Route::post('/mocks/{assessment}/submit', [\App\Http\Controllers\Student\StudentMockController::class, 'submit'])->name('mocks.submit');
        Route::get('/mocks/{assessment}/result/{submission}', [\App\Http\Controllers\Student\StudentMockController::class, 'result'])->name('mocks.result');
    });
});

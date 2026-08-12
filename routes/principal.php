<?php

use App\Http\Controllers\Principal\AcademicTermController;
use App\Http\Controllers\Principal\ClassSubjectController;
use App\Http\Controllers\Principal\RoomController;
use App\Http\Controllers\Principal\SectionController;
use App\Http\Controllers\Principal\StaffController;
use App\Http\Controllers\Principal\TeacherAvailabilityController;
use App\Http\Controllers\Principal\TimetableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Principal & Staff Portal Routes (Module 3)
|--------------------------------------------------------------------------
| Protected by:
|  • auth: Login required
|  • role:principal,teacher: Principal & Teachers (with delegated admin)
|  • institute.member: Scoped strictly to current institute
*/

Route::middleware(['auth', 'role:principal,teacher', 'institute.member'])->group(function () {

    // ── Principal Dashboard ───────────────────────────────────────────────
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $institute = $user->institute;
        $activeTerm = \App\Models\AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $classesCount = \App\Models\InstituteClass::where('institute_id', $user->institute_id)->count();
        $staffCount   = \App\Models\User::where('institute_id', $user->institute_id)->count();
        $slotsCount   = $activeTerm ? \App\Models\Timetable::where('academic_term_id', $activeTerm->id)->count() : 0;

        return view('principal.dashboard', compact('user', 'institute', 'activeTerm', 'classesCount', 'staffCount', 'slotsCount'));
    })->name('dashboard');

    // ── 1. Academic Terms Lifecycle (Exempt from active.term prerequisite) ──
    Route::get('/academic-terms', [AcademicTermController::class, 'index'])->name('academic-terms.index');
    Route::post('/academic-terms', [AcademicTermController::class, 'store'])->name('academic-terms.store');
    Route::post('/academic-terms/{term}/set-active', [AcademicTermController::class, 'setActive'])->name('academic-terms.set-active');
    Route::delete('/academic-terms/{term}', [AcademicTermController::class, 'destroy'])->name('academic-terms.destroy');

    // ── Operational Routes (Guarded by active.term middleware) ───────────
    Route::middleware(['active.term'])->group(function () {

        // 2. Rooms & Facilities Management
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // 3. Dynamic Classes, Sections & Subjects
        Route::get('/classes-subjects', [ClassSubjectController::class, 'index'])->name('classes-subjects.index');
        Route::post('/classes', [ClassSubjectController::class, 'storeClass'])->name('classes.store');
        Route::delete('/classes/{class}', [ClassSubjectController::class, 'destroyClass'])->name('classes.destroy');
        Route::post('/sections', [ClassSubjectController::class, 'storeSection'])->name('sections.store');
        Route::delete('/sections/{section}', [ClassSubjectController::class, 'destroySection'])->name('sections.destroy');
        Route::post('/subjects', [ClassSubjectController::class, 'storeSubject'])->name('subjects.store');
        Route::delete('/subjects/{subject}', [ClassSubjectController::class, 'destroySubject'])->name('subjects.destroy');

        // Redirect old Section Allocation route directly to Classes & Subjects
        Route::get('/sections', fn() => redirect()->route('principal.classes-subjects.index'))->name('sections.index');

        // 4. Timetable Matrix Engine & Optimistic Generator
        Route::get('/timetables', [TimetableController::class, 'index'])->name('timetables.index');
        Route::post('/timetables', [TimetableController::class, 'store'])->name('timetables.store');
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->name('timetables.destroy');

        Route::post('/timetables/generate', [TimetableController::class, 'generate'])->name('timetables.generate');
        Route::get('/timetables/grid', [TimetableController::class, 'generatedGrid'])->name('timetables.grid');
        Route::get('/timetables/export', [TimetableController::class, 'exportExcel'])->name('timetables.export');
        Route::post('/timetables/chat', [TimetableController::class, 'chat'])->name('timetables.chat');

        // 5. Teacher Availabilities
        Route::get('/teachers/availability', [TeacherAvailabilityController::class, 'index'])->name('teachers.availability.index');
        Route::post('/teachers/availability', [TeacherAvailabilityController::class, 'store'])->name('teachers.availability.store');

        // 6. Staff Lifecycle
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffController::class, 'storeStaff'])->name('staff.store');
        Route::post('/staff/{staff}/toggle-delegation', [StaffController::class, 'toggleDelegation'])->name('staff.toggle-delegation');
        Route::post('/staff/{staff}/toggle-permission', [StaffController::class, 'updatePermissionToggle'])->name('staff.toggle-permission');

        // 7. Dedicated Subject-Teacher Assignments Management
        Route::get('/assignments', [StaffController::class, 'assignmentsIndex'])->name('assignments.index');
        Route::post('/assignments', [StaffController::class, 'assignSubjectSection'])->name('assignments.store');
        Route::put('/assignments/{assignment}', [StaffController::class, 'updateAssignment'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [StaffController::class, 'removeAssignment'])->name('assignments.destroy');

        // Legacy compatibility routes
        Route::post('/staff/assign', [StaffController::class, 'assignSubjectSection'])->name('staff.assign');
        Route::delete('/staff/assignments/{assignment}', [StaffController::class, 'removeAssignment'])->name('staff.assignments.destroy');
    });
});

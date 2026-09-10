<?php

use App\Http\Controllers\Principal\AcademicTermController;
use App\Http\Controllers\Principal\ClassSubjectController;
use App\Http\Controllers\Principal\FeeInvoiceController;
use App\Http\Controllers\Principal\MasterDirectoryController;
use App\Http\Controllers\Principal\RoomController;
use App\Http\Controllers\Principal\ScholarshipPolicyController;
use App\Http\Controllers\Principal\StaffController;
use App\Http\Controllers\Principal\StudentController;
use App\Http\Controllers\Principal\TeacherAvailabilityController;
use App\Http\Controllers\Principal\TimetableController;
use App\Http\Controllers\Principal\PrincipalDashboardController;
use App\Http\Controllers\TeacherPortalController;
use App\Models\AcademicTerm;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\User;
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

    // ── Principal Dashboard & Institute Settings ─────────────────────────
    Route::get('/dashboard', [PrincipalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [\App\Http\Controllers\Principal\InstituteSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Principal\InstituteSettingController::class, 'update'])->name('settings.update');
    Route::put('/settings/staff/{staff}/payroll', [\App\Http\Controllers\Principal\InstituteSettingController::class, 'updateStaffPayroll'])->name('settings.staff-payroll.update');

    // ── 1. Academic Terms Lifecycle & Security Settings (Exempt from active.term prerequisite) ──
    Route::get('/security', function () {
        return view('principal.security.edit');
    })->name('security.edit');

    Route::get('/academic-terms', [AcademicTermController::class, 'index'])->name('academic-terms.index');
    Route::post('/academic-terms', [AcademicTermController::class, 'store'])->name('academic-terms.store');
    Route::put('/academic-terms/{term}/deadline', [AcademicTermController::class, 'updateDeadline'])->name('academic-terms.update-deadline');
    Route::post('/academic-terms/clone', [AcademicTermController::class, 'cloneData'])->name('academic-terms.clone');
    Route::post('/academic-terms/{term}/set-active', [AcademicTermController::class, 'setActive'])->name('academic-terms.set-active');
    Route::delete('/academic-terms/{term}', [AcademicTermController::class, 'destroy'])->name('academic-terms.destroy');

    // ── Operational Routes (Guarded by active.term middleware) ───────────
    Route::middleware(['active.term'])->group(function () {

        // 2. Rooms & Facilities Management
        Route::middleware(['feature:rooms_facilities'])->group(function () {
            Route::get('/rooms', [RoomController::class, 'index'])->middleware('permission:rooms,view')->name('rooms.index');
            Route::post('/rooms', [RoomController::class, 'store'])->middleware('permission:rooms,edit')->name('rooms.store');
            Route::put('/rooms/{room}', [RoomController::class, 'update'])->middleware('permission:rooms,edit')->name('rooms.update');
            Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->middleware('permission:rooms,edit')->name('rooms.destroy');
        });

        // 3. Dynamic Classes, Sections & Subjects
        Route::middleware(['feature:classes_sections'])->group(function () {
            Route::get('/classes-subjects', [ClassSubjectController::class, 'index'])->middleware('permission:classes,view')->name('classes-subjects.index');
            Route::post('/classes', [ClassSubjectController::class, 'storeClass'])->middleware('permission:classes,edit')->name('classes.store');
            Route::put('/classes/{class}', [ClassSubjectController::class, 'updateClass'])->middleware('permission:classes,edit')->name('classes.update');
            Route::post('/classes/import', [ClassSubjectController::class, 'importSystemClass'])->middleware('permission:classes,edit')->name('classes.import');
            Route::get('/classes/promote', [ClassSubjectController::class, 'promotePage'])->middleware('permission:classes,edit')->name('classes.promote.page');
            Route::post('/classes/promote/preview', [ClassSubjectController::class, 'previewPromotion'])->middleware('permission:classes,edit')->name('classes.promote.preview');
            Route::post('/classes/promote', [ClassSubjectController::class, 'promoteClassRoster'])->middleware('permission:classes,edit')->name('classes.promote');
            Route::delete('/classes/{class}', [ClassSubjectController::class, 'destroyClass'])->middleware('permission:classes,edit')->name('classes.destroy');
            Route::post('/sections', [ClassSubjectController::class, 'storeSection'])->middleware('permission:classes,edit')->name('sections.store');
            Route::put('/sections/{section}', [ClassSubjectController::class, 'updateSection'])->middleware('permission:classes,edit')->name('sections.update');
            Route::post('/sections/{section}/incharge', [ClassSubjectController::class, 'updateSectionIncharge'])->middleware('permission:classes,edit')->name('sections.incharge');
            Route::delete('/sections/{section}', [ClassSubjectController::class, 'destroySection'])->middleware('permission:classes,edit')->name('sections.destroy');
            Route::get('/sections', fn () => redirect()->route('principal.classes-subjects.index'))->name('sections.index');
        });

        Route::middleware(['feature:subjects_catalog'])->group(function () {
            Route::get('/subjects', [ClassSubjectController::class, 'subjectsIndex'])->middleware('permission:subjects,view')->name('subjects.index');
            Route::post('/subjects', [ClassSubjectController::class, 'storeSubject'])->middleware('permission:subjects,edit')->name('subjects.store');
            Route::put('/subjects/{subject}', [ClassSubjectController::class, 'updateSubject'])->middleware('permission:subjects,edit')->name('subjects.update');
            Route::delete('/subjects/{subject}', [ClassSubjectController::class, 'destroySubject'])->middleware('permission:subjects,edit')->name('subjects.destroy');
        });

        // 4. Timetable Matrix Engine & Optimistic Generator
        Route::middleware(['feature:timetable'])->group(function () {
            Route::get('/timetables', [TimetableController::class, 'index'])->middleware('permission:timetables,view')->name('timetables.index');
            Route::get('/timetables/days-and-hours', [TimetableController::class, 'daysAndHours'])->middleware('permission:timetables,view')->name('timetables.days-and-hours');
            Route::post('/timetables', [TimetableController::class, 'store'])->middleware('permission:timetables,edit')->name('timetables.store');
            Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->middleware('permission:timetables,edit')->name('timetables.destroy');
            Route::post('/timetables/generate', [TimetableController::class, 'generate'])->middleware('permission:timetables,edit')->name('timetables.generate');
            Route::put('/timetables/allocations/{allocation}', [TimetableController::class, 'updateAllocationDuration'])->middleware('permission:timetables,edit')->name('timetables.allocations.update');
            Route::get('/timetables/grid', [TimetableController::class, 'generatedGrid'])->middleware('permission:timetables,view')->name('timetables.grid');
            Route::get('/timetables/export', [TimetableController::class, 'exportExcel'])->middleware('permission:timetables,view')->name('timetables.export');
            Route::post('/timetables/chat', [TimetableController::class, 'chat'])->middleware('permission:timetables,view')->name('timetables.chat');
        });

        // 5. Teacher Availabilities & Attendance Controls
        Route::middleware(['feature:attendance_system'])->group(function () {
            Route::get('/teachers/availability', [TeacherAvailabilityController::class, 'index'])->middleware('permission:faculty_hours,view')->name('teachers.availability.index');
            Route::post('/teachers/availability', [TeacherAvailabilityController::class, 'store'])->middleware('permission:faculty_hours,edit')->name('teachers.availability.store');

            // Principal Attendance Controls & Lock Rules
            Route::get('/attendance', [TeacherPortalController::class, 'attendance'])->middleware('permission:attendance,view')->name('attendance');
            Route::post('/attendance', [TeacherPortalController::class, 'storeAttendance'])->middleware('permission:attendance,edit')->name('attendance.store');
            Route::middleware(['permission:security'])->group(function () {
                Route::get('/attendance-settings', [\App\Http\Controllers\Principal\AttendanceSettingController::class, 'index'])->name('attendance-settings.index');
                Route::put('/attendance-settings', [\App\Http\Controllers\Principal\AttendanceSettingController::class, 'update'])->name('attendance-settings.update');
            });
        });

        // 6. Staff Lifecycle
        Route::middleware(['feature:teacher_portal'])->group(function () {
            Route::get('/staff', [StaffController::class, 'index'])->middleware('permission:staff,view')->name('staff.index');
            Route::get('/staff/create', [StaffController::class, 'create'])->middleware('permission:staff_onboard,edit')->name('staff.create');
            Route::post('/staff', [StaffController::class, 'storeStaff'])->middleware('permission:staff_onboard,edit')->name('staff.store');
            Route::post('/staff/{staff}/toggle-delegation', [StaffController::class, 'toggleDelegation'])->middleware('permission:staff,edit')->name('staff.toggle-delegation');
            Route::post('/staff/{staff}/toggle-permission', [StaffController::class, 'updatePermissionToggle'])->middleware('permission:staff,edit')->name('staff.toggle-permission');
            Route::post('/staff/{staff}/update-permissions-bulk', [StaffController::class, 'updatePermissionsBulk'])->middleware('permission:staff,edit')->name('staff.update-permissions-bulk');
            Route::put('/staff/{staff}/update-role', [StaffController::class, 'updateRole'])->middleware('permission:staff,edit')->name('staff.update-role');
            Route::post('/staff/{staff}/update-role', [StaffController::class, 'updateRole'])->middleware('permission:staff,edit')->name('staff.update-role.post');
            Route::get('/staff/authorities', [StaffController::class, 'authoritiesIndex'])->middleware('permission:staff,view')->name('staff.authorities');
            Route::post('/staff/authorities', [StaffController::class, 'storeRoleAuthority'])->middleware('permission:staff,edit')->name('staff.authorities.store');
            Route::delete('/staff/authorities/{authority}', [StaffController::class, 'destroyRoleAuthority'])->middleware('permission:staff,edit')->name('staff.authorities.destroy');
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->middleware('permission:staff,edit')->name('staff.destroy');
        });

        // 6b. Password Reset Requests Queue & Direct Reset
        Route::middleware(['permission:security'])->group(function () {
            Route::get('/password-resets', [\App\Http\Controllers\AccountManagement\AdminPasswordResetController::class, 'index'])->name('password-resets.index');
            Route::get('/password-resets/{notification}', [\App\Http\Controllers\AccountManagement\AdminPasswordResetController::class, 'show'])->name('password-resets.show');
            Route::post('/password-resets/{notification}/reset', [\App\Http\Controllers\AccountManagement\AdminPasswordResetController::class, 'executeReset'])->name('password-resets.execute');
            Route::post('/password-resets/{notification}/deny', [\App\Http\Controllers\AccountManagement\AdminPasswordResetController::class, 'deny'])->name('password-resets.deny');
            Route::post('/users/{user}/direct-reset-password', [\App\Http\Controllers\AccountManagement\AdminPasswordResetController::class, 'directResetUserPassword'])->name('users.direct-reset-password');
        });

        // 7. Dedicated Subject-Teacher Assignments Management
        Route::middleware(['feature:teacher_allocations'])->group(function () {
            Route::get('/assignments', [StaffController::class, 'assignmentsIndex'])->middleware('permission:allocations,view')->name('assignments.index');
            Route::post('/assignments', [StaffController::class, 'assignSubjectSection'])->middleware('permission:allocations,edit')->name('assignments.store');
            Route::put('/assignments/{assignment}', [StaffController::class, 'updateAssignment'])->middleware('permission:allocations,edit')->name('assignments.update');
            Route::delete('/assignments/{assignment}', [StaffController::class, 'removeAssignment'])->middleware('permission:allocations,edit')->name('assignments.destroy');
            Route::post('/staff/assign', [StaffController::class, 'assignSubjectSection'])->middleware('permission:allocations,edit')->name('staff.assign');
            Route::delete('/staff/assignments/{assignment}', [StaffController::class, 'removeAssignment'])->middleware('permission:allocations,edit')->name('staff.assignments.destroy');
        });

        // 8. Student Registration & Roster Management
        Route::middleware(['feature:registration_portals'])->group(function () {
            Route::get('/students', [StudentController::class, 'index'])->middleware('permission:students,view')->name('students.index');
            Route::get('/students/create', [StudentController::class, 'create'])->middleware('permission:student_registration,edit')->name('students.create');
            Route::post('/students', [StudentController::class, 'store'])->middleware('permission:student_registration,edit')->name('students.store');
            Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:students,view')->name('students.show');
            Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->middleware('permission:profile_edit,edit')->name('students.edit');
            Route::put('/students/{student}/profile', [StudentController::class, 'updateProfile'])->middleware('permission:profile_edit,edit')->name('students.update-profile');
            Route::post('/students/{student}/result-status', [StudentController::class, 'updateResultStatus'])->middleware('permission:students,edit')->name('students.result-status');
            Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students,edit')->name('students.destroy');
        });

        // 9. Fee Assignment & Invoicing Engine
        Route::middleware(['feature:fee_invoicing'])->group(function () {
            Route::get('/invoices', [FeeInvoiceController::class, 'index'])->middleware('permission:invoices,view')->name('invoices.index');
            Route::get('/invoices/create', [FeeInvoiceController::class, 'create'])->middleware('permission:invoices,edit')->name('invoices.create');
            Route::post('/invoices', [FeeInvoiceController::class, 'store'])->middleware('permission:invoices,edit')->name('invoices.store');
            Route::post('/invoices/{invoice}/mark-paid', [FeeInvoiceController::class, 'markPaid'])->middleware('permission:invoices,edit')->name('invoices.mark-paid');
        });

        // 10. Master Directory Search & Salary Slips Engine
        Route::middleware(['feature:master_directory'])->group(function () {
            Route::get('/directory', [MasterDirectoryController::class, 'index'])->middleware('permission:directory,view')->name('directory.index');
            Route::get('/directory/student/{student}', [MasterDirectoryController::class, 'showStudent'])->middleware('permission:directory,view')->name('directory.student');
            Route::get('/directory/teacher/{teacher}', [MasterDirectoryController::class, 'showTeacher'])->middleware('permission:directory,view')->name('directory.teacher');
            Route::get('/directory/teacher/{teacher}/edit', [MasterDirectoryController::class, 'editTeacher'])->middleware('permission:profile_edit,edit')->name('directory.teacher.edit');
            Route::put('/directory/teacher/{teacher}', [MasterDirectoryController::class, 'updateTeacher'])->middleware('permission:profile_edit,edit')->name('directory.teacher.update');
            Route::post('/directory/teacher/{teacher}/salary-slips', [MasterDirectoryController::class, 'uploadSalarySlip'])->middleware('permission:directory,edit')->name('directory.teacher.salary-slips.store');
            Route::delete('/directory/salary-slips/{slip}', [MasterDirectoryController::class, 'deleteSalarySlip'])->middleware('permission:directory,edit')->name('directory.salary-slips.destroy');
        });

        // 11. Scholarship Policies & Discount Decision Engine
        Route::middleware(['feature:scholarships'])->group(function () {
            Route::get('/scholarships', [ScholarshipPolicyController::class, 'index'])->middleware('permission:scholarships,view')->name('scholarships.index');
            Route::post('/scholarships', [ScholarshipPolicyController::class, 'store'])->middleware('permission:scholarships,edit')->name('scholarships.store');
            Route::put('/scholarships/{scholarship}', [ScholarshipPolicyController::class, 'update'])->middleware('permission:scholarships,edit')->name('scholarships.update');
            Route::delete('/scholarships/{scholarship}', [ScholarshipPolicyController::class, 'destroy'])->middleware('permission:scholarships,edit')->name('scholarships.destroy');
        });

        // 12. Accounts Department & Financial AI Audit Engine
        Route::prefix('accounts')->name('accounts.')
            ->middleware(['permission:accounts'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Principal\AccountsController::class, 'index'])->name('index');
            Route::post('/heads', [\App\Http\Controllers\Principal\AccountsController::class, 'storeHead'])->name('heads.store');
            Route::put('/heads/{head}', [\App\Http\Controllers\Principal\AccountsController::class, 'updateHead'])->name('heads.update');
            Route::delete('/heads/{head}', [\App\Http\Controllers\Principal\AccountsController::class, 'destroyHead'])->name('heads.destroy');
            Route::post('/transactions', [\App\Http\Controllers\Principal\AccountsController::class, 'storeTransaction'])->name('transactions.store');
            Route::delete('/transactions/{transaction}', [\App\Http\Controllers\Principal\AccountsController::class, 'destroyTransaction'])->name('transactions.destroy');
            Route::post('/salaries/teachers/{teacher}', [\App\Http\Controllers\Principal\AccountsController::class, 'storeSalaryPayment'])->name('salaries.store');
            Route::match(['get', 'post'], '/salaries/auto-disburse', [\App\Http\Controllers\Principal\AccountsController::class, 'autoDisburseSalaries'])->name('salaries.auto-disburse');
        });

        // 13. Universal CSV/Excel Bulk Data Importer
        Route::prefix('bulk-import')->name('bulk-import.')->group(function () {
            Route::post('/students', [\App\Http\Controllers\Principal\BulkImportController::class, 'importStudents'])->middleware('permission:student_registration,edit')->name('students');
            Route::post('/staff', [\App\Http\Controllers\Principal\BulkImportController::class, 'importStaff'])->middleware('permission:staff_onboard,edit')->name('staff');
            Route::post('/finance', [\App\Http\Controllers\Principal\BulkImportController::class, 'importFinance'])->middleware('permission:accounts')->name('finance');
            Route::get('/sample/{type}', [\App\Http\Controllers\Principal\BulkImportController::class, 'downloadSample'])->middleware('permission:student_registration,view')->name('sample');
        });
    });
});

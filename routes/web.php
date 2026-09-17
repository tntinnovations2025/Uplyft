<?php

use App\Http\Controllers\AdminPortalController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\PasswordOtpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentAdmissionController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherOnboardingController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\Principal\ScholarshipPolicyController;
use App\Http\Controllers\Principal\StudentController;
use App\Http\Controllers\Principal\TimetableController;
use App\Http\Controllers\Principal\FeeInvoiceController;
use App\Http\Controllers\Principal\RoomController;
use App\Http\Controllers\Principal\ClassSubjectController;
use App\Http\Controllers\Principal\MasterDirectoryController;
use App\Http\Controllers\Principal\StaffController;
use App\Http\Controllers\AccountManagement\AdminPasswordResetController;
use App\Http\Controllers\Principal\OrganizationCampusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isGlobalAdmin()) {
            return redirect()->route('global-admin.dashboard');
        }
        if ($user->isPrincipal()) {
            return redirect()->route('principal.dashboard');
        }
        if ($user->role === 'teacher') {
            return redirect($user->dashboardRoute());
        }
        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }
        return redirect($user->dashboardRoute());
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function (Request $request) {
    $user = Auth::user();

    if ($user->isGlobalAdmin()) {
        return redirect()->route('global-admin.dashboard');
    }

    if ($user->isPrincipal()) {
        return redirect()->route('principal.dashboard');
    }

    if ($user->role === 'teacher') {
        return redirect($user->dashboardRoute());
    }

    if ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/tenant/switch-institute', [\App\Http\Controllers\TenantSwitchController::class, 'switchInstitute'])
    ->middleware('auth')
    ->name('tenant.switch-institute');

// ── 1. Student Portal Routes are registered in routes/student.php ──────

// ── 2. Teacher Portal (Includes Delegated Administrative Rights under /teacher/) ─
// ── 2. Staff / Faculty / Employee Portals (Teacher, Staff, Accountant) ────────
$staffPortalGroup = function () {
    Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/schedule', [TeacherPortalController::class, 'schedule'])->name('schedule');
    Route::get('/attendance', [TeacherPortalController::class, 'attendance'])->name('attendance');
    Route::post('/attendance', [TeacherPortalController::class, 'storeAttendance'])->name('attendance.store');
    Route::get('/lms', [TeacherPortalController::class, 'lms'])->name('lms');

    // Delegated Administrative Rights Routes (Staying 100% on Employee Portal Workspace)
    Route::middleware(['active.term'])->group(function () {
        // Scholarship Policy & Discounts
        Route::get('/scholarships', [ScholarshipPolicyController::class, 'index'])->middleware('permission:scholarships,view')->name('scholarships.index');
        Route::post('/scholarships', [ScholarshipPolicyController::class, 'store'])->middleware('permission:scholarships,edit')->name('scholarships.store');
        Route::put('/scholarships/{scholarship}', [ScholarshipPolicyController::class, 'update'])->middleware('permission:scholarships,edit')->name('scholarships.update');
        Route::delete('/scholarships/{scholarship}', [ScholarshipPolicyController::class, 'destroy'])->middleware('permission:scholarships,edit')->name('scholarships.destroy');

        // Students & Admissions
        Route::get('/students', [StudentController::class, 'index'])->middleware('permission:students,view')->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->middleware('permission:student_registration,edit')->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->middleware('permission:student_registration,edit')->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:students,view')->name('students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->middleware('permission:profile_edit,edit')->name('students.edit');
        Route::put('/students/{student}/profile', [StudentController::class, 'updateProfile'])->middleware('permission:profile_edit,edit')->name('students.update-profile');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students,edit')->name('students.destroy');

        // Timetable Matrix
        Route::get('/timetables', [TimetableController::class, 'index'])->middleware('permission:timetables,view')->name('timetables.index');
        Route::get('/timetables/days-and-hours', [TimetableController::class, 'daysAndHours'])->middleware('permission:timetables,view')->name('timetables.days-and-hours');
        Route::post('/timetables', [TimetableController::class, 'store'])->middleware('permission:timetables,edit')->name('timetables.store');
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])->middleware('permission:timetables,edit')->name('timetables.destroy');
        Route::post('/timetables/generate', [TimetableController::class, 'generate'])->middleware('permission:timetables,edit')->name('timetables.generate');
        Route::put('/timetables/allocations/{allocation}', [TimetableController::class, 'updateAllocationDuration'])->middleware('permission:timetables,edit')->name('timetables.allocations.update');
        Route::get('/timetables/grid', [TimetableController::class, 'generatedGrid'])->middleware('permission:timetables,view')->name('timetables.grid');
        Route::get('/timetables/export', [TimetableController::class, 'exportExcel'])->middleware('permission:timetables,view')->name('timetables.export');
        Route::post('/timetables/chat', [TimetableController::class, 'chat'])->middleware('permission:timetables,view')->name('timetables.chat');

        // Invoices & Fee Vouchers
        Route::get('/invoices', [FeeInvoiceController::class, 'index'])->middleware('permission:invoices,view')->name('invoices.index');
        Route::get('/invoices/create', [FeeInvoiceController::class, 'create'])->middleware('permission:invoices,edit')->name('invoices.create');
        Route::post('/invoices', [FeeInvoiceController::class, 'store'])->middleware('permission:invoices,edit')->name('invoices.store');
        Route::post('/invoices/{invoice}/mark-paid', [FeeInvoiceController::class, 'markPaid'])->middleware('permission:invoices,edit')->name('invoices.mark-paid');

        // Accounts Department & Financial Ledger
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Principal\AccountsController::class, 'index'])->middleware('permission:accounts,view')->name('index');
            Route::post('/heads', [\App\Http\Controllers\Principal\AccountsController::class, 'storeHead'])->middleware('permission:accounts,edit')->name('heads.store');
            Route::put('/heads/{head}', [\App\Http\Controllers\Principal\AccountsController::class, 'updateHead'])->middleware('permission:accounts,edit')->name('heads.update');
            Route::delete('/heads/{head}', [\App\Http\Controllers\Principal\AccountsController::class, 'destroyHead'])->middleware('permission:accounts,edit')->name('heads.destroy');
            Route::post('/transactions', [\App\Http\Controllers\Principal\AccountsController::class, 'storeTransaction'])->middleware('permission:accounts,edit')->name('transactions.store');
            Route::delete('/transactions/{transaction}', [\App\Http\Controllers\Principal\AccountsController::class, 'destroyTransaction'])->middleware('permission:accounts,edit')->name('transactions.destroy');
            Route::post('/salaries/teachers/{teacher}', [\App\Http\Controllers\Principal\AccountsController::class, 'storeSalaryPayment'])->middleware('permission:staff_salaries,edit')->name('salaries.store');
            Route::match(['get', 'post'], '/salaries/auto-disburse', [\App\Http\Controllers\Principal\AccountsController::class, 'autoDisburseSalaries'])->middleware('permission:staff_salaries,edit')->name('salaries.auto-disburse');
        });

        // Campus Rooms
        Route::get('/rooms', [RoomController::class, 'index'])->middleware('permission:rooms,view')->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->middleware('permission:rooms,edit')->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->middleware('permission:rooms,edit')->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->middleware('permission:rooms,edit')->name('rooms.destroy');

        // Classes & Subjects
        Route::get('/classes-subjects', [ClassSubjectController::class, 'index'])->middleware('permission:classes,view')->name('classes-subjects.index');
        Route::post('/classes', [ClassSubjectController::class, 'storeClass'])->middleware('permission:classes,edit')->name('classes.store');
        Route::delete('/classes/{class}', [ClassSubjectController::class, 'destroyClass'])->middleware('permission:classes,edit')->name('classes.destroy');
        Route::post('/sections', [ClassSubjectController::class, 'storeSection'])->middleware('permission:classes,edit')->name('sections.store');
        Route::post('/sections/{section}/incharge', [ClassSubjectController::class, 'updateSectionIncharge'])->middleware('permission:classes,edit')->name('sections.incharge');
        Route::delete('/sections/{section}', [ClassSubjectController::class, 'destroySection'])->middleware('permission:classes,edit')->name('sections.destroy');
        Route::get('/subjects', [ClassSubjectController::class, 'subjectsIndex'])->middleware('permission:subjects,view')->name('subjects.index');
        Route::post('/subjects', [ClassSubjectController::class, 'storeSubject'])->middleware('permission:subjects,edit')->name('subjects.store');
        Route::delete('/subjects/{subject}', [ClassSubjectController::class, 'destroySubject'])->middleware('permission:subjects,edit')->name('subjects.destroy');

        // Master Directory
        Route::get('/directory', [MasterDirectoryController::class, 'index'])->middleware('permission:directory,view')->name('directory.index');
        Route::get('/directory/student/{student}', [MasterDirectoryController::class, 'showStudent'])->middleware('permission:directory,view')->name('directory.student');
        Route::get('/directory/teacher/{teacher}', [MasterDirectoryController::class, 'showTeacher'])->middleware('permission:directory,view')->name('directory.teacher');
        Route::get('/directory/teacher/{teacher}/edit', [MasterDirectoryController::class, 'editTeacher'])->middleware('permission:profile_edit,edit')->name('directory.teacher.edit');
        Route::put('/directory/teacher/{teacher}', [MasterDirectoryController::class, 'updateTeacher'])->middleware('permission:profile_edit,edit')->name('directory.teacher.update');

        // Staff Directory
        Route::get('/staff', [StaffController::class, 'index'])->middleware('permission:staff,view')->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->middleware('permission:staff_onboard,edit')->name('staff.create');
        Route::post('/staff', [StaffController::class, 'storeStaff'])->middleware('permission:staff_onboard,edit')->name('staff.store');
        Route::put('/staff/{staff}/update-role', [StaffController::class, 'updateRole'])->middleware('permission:staff,edit')->name('staff.update-role');
        Route::post('/staff/{staff}/update-role', [StaffController::class, 'updateRole'])->middleware('permission:staff,edit')->name('staff.update-role.post');
        Route::post('/staff/{staff}/toggle-delegation', [StaffController::class, 'toggleDelegation'])->middleware('permission:staff,edit')->name('staff.toggle-delegation');
        Route::post('/staff/{staff}/toggle-permission', [StaffController::class, 'updatePermissionToggle'])->middleware('permission:staff,edit')->name('staff.toggle-permission');
        Route::post('/staff/{staff}/update-permissions-bulk', [StaffController::class, 'updatePermissionsBulk'])->middleware('permission:staff,edit')->name('staff.update-permissions-bulk');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->middleware('permission:staff,edit')->name('staff.destroy');

        // Security & Password Reset Requests
        Route::get('/password-resets', [AdminPasswordResetController::class, 'index'])->middleware('permission:security,view')->name('password-resets.index');
        Route::get('/password-resets/{notification}', [AdminPasswordResetController::class, 'show'])->middleware('permission:security,view')->name('password-resets.show');
        Route::post('/password-resets/{notification}/reset', [AdminPasswordResetController::class, 'executeReset'])->middleware('permission:security,edit')->name('password-resets.execute');
        Route::post('/password-resets/{notification}/deny', [AdminPasswordResetController::class, 'deny'])->middleware('permission:security,edit')->name('password-resets.deny');
        Route::post('/users/{user}/direct-reset-password', [AdminPasswordResetController::class, 'directResetUserPassword'])->middleware('permission:security,edit')->name('users.direct-reset-password');
    });
};

Route::middleware(['auth', 'institute.member', 'staff.prefix'])->prefix('teacher')->name('teacher.')->group($staffPortalGroup);
Route::middleware(['auth', 'institute.member', 'staff.prefix'])->prefix('staff')->name('staff.')->group($staffPortalGroup);
Route::middleware(['auth', 'institute.member', 'staff.prefix'])->prefix('accountant')->name('accountant.')->group($staffPortalGroup);

// ── 3. Administration & Helping Staff Portal ─────────────────────────────
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/admissions', [AdminPortalController::class, 'admissions'])->name('admissions');
    Route::get('/teachers/onboarding', [AdminPortalController::class, 'onboarding'])->name('teachers.onboarding');
    Route::get('/students', [AdminPortalController::class, 'studentsDirectory'])->name('students');
    Route::get('/teachers', [AdminPortalController::class, 'teachersDirectory'])->name('teachers');
    Route::get('/fees', [AdminPortalController::class, 'feeManagement'])->name('fees');
    Route::post('/fees/{invoice}/mark-paid', [AdminPortalController::class, 'markInvoicePaid'])->name('fees.mark-paid');
});

// ── Core Admissions & Attendance Actions ──────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/admissions', [AdminPortalController::class, 'admissions'])->middleware('permission:student_registration,view')->name('admissions.index');
    Route::post('/admissions', [StudentAdmissionController::class, 'store'])->middleware('permission:student_registration,edit')->name('admissions.store');
    Route::put('/admissions/{id}', [StudentAdmissionController::class, 'update'])->middleware('permission:student_registration,edit')->name('admissions.update');
    Route::post('/teachers/onboarding', [TeacherOnboardingController::class, 'store'])->name('teachers.onboarding.store');
    Route::post('/attendance/store', [AttendanceController::class, 'storeBatchAttendance'])->name('attendance.store');

    // Teacher Daily Diary Posting (Hardened against IDOR)
    Route::post('/teacher/diary', [\App\Http\Controllers\Teacher\DailyDiaryController::class, 'store'])->name('teacher.diary.store');
    Route::post('/teacher/daily-diaries', [\App\Http\Controllers\Teacher\DailyDiaryController::class, 'store'])->name('teacher.daily-diaries.store');
});

// ── Principal Multi-Campus Organization Onboarding & Management ─────────────
Route::middleware(['auth'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/organization/campuses', [OrganizationCampusController::class, 'index'])->name('organization.campuses.index');
    Route::get('/organization/campuses/create', [OrganizationCampusController::class, 'create'])->name('organization.campuses.create');
    Route::post('/organization/campuses', [OrganizationCampusController::class, 'store'])->name('organization.campuses.store');
    Route::delete('/organization/campuses/{campus}', [OrganizationCampusController::class, 'destroy'])->name('organization.campuses.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/platform-logo', [ProfileController::class, 'updatePlatformLogo'])->name('profile.platform-logo.update');
    Route::post('/profile/password/send-otp', [PasswordOtpController::class, 'send'])->name('profile.password.send-otp');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

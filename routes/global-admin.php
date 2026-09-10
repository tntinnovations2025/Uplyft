<?php

use App\Http\Controllers\AccountManagement\AdminPasswordResetController;
use App\Http\Controllers\AccountManagement\PrincipalAccountController;
use App\Http\Controllers\GlobalAdmin\DashboardController;
use App\Http\Controllers\GlobalAdmin\FeatureToggleController;
use App\Http\Controllers\GlobalAdmin\InstituteController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GlobalAdmin\OrganizationController;

/*
|--------------------------------------------------------------------------
| Global Admin Routes
|--------------------------------------------------------------------------
| All routes here are prefixed with /global-admin (set in bootstrap/app.php)
| and named with global-admin.*
|
| Protected by auth + role:global_admin middleware.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:global_admin'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Organizations Network CRUD ─────────────────────────────────────────
    Route::resource('organizations', OrganizationController::class);
    Route::post('organizations/{organization}/toggle-status', [OrganizationController::class, 'toggleStatus'])
        ->name('organizations.toggle-status');

    // ── Institutes CRUD ──────────────────────────────────────────────────
    Route::resource('institutes', InstituteController::class);
    Route::post('institutes/{institute}/toggle-status', [InstituteController::class, 'toggleStatus'])
        ->name('institutes.toggle-status');

    // Restore soft-deleted institute
    Route::post('institutes/{id}/restore', [InstituteController::class, 'restore'])
        ->name('institutes.restore');

    // Force purge soft-deleted institute
    Route::delete('institutes/{id}/purge', [InstituteController::class, 'forceDestroy'])
        ->name('institutes.purge');

    // ── Feature Toggle Panel ─────────────────────────────────────────────
    Route::get('institutes/{institute}/toggles', [FeatureToggleController::class, 'edit'])
        ->name('institutes.toggles.edit');

    Route::match(['put', 'post'], 'institutes/{institute}/toggles', [FeatureToggleController::class, 'update'])
        ->name('institutes.toggles.update');

    Route::post('institutes/{institute}/toggles/apply-tier', [FeatureToggleController::class, 'applyTierDefaults'])
        ->name('institutes.toggles.apply-tier');

    // ── Principal Account Management (Module 2) ─────────────────────────
    Route::get('accounts/principals', [PrincipalAccountController::class, 'index'])
        ->name('accounts.principals.index');

    Route::get('accounts/principals/create', [PrincipalAccountController::class, 'create'])
        ->name('accounts.principals.create');

    Route::post('accounts/principals', [PrincipalAccountController::class, 'store'])
        ->name('accounts.principals.store');

    Route::post('accounts/principals/{user}/change-password', [PrincipalAccountController::class, 'changePassword'])
        ->name('accounts.principals.change-password');

    Route::delete('accounts/principals/{user}', [PrincipalAccountController::class, 'destroy'])
        ->name('accounts.principals.destroy');

    Route::post('accounts/users/{user}/update-role', [PrincipalAccountController::class, 'updateUserRole'])
        ->name('accounts.users.update-role');

    // ── Password Reset Requests (Global Admin handles Principal resets) ──
    Route::get('password-resets', [AdminPasswordResetController::class, 'index'])
        ->name('password-resets.index');

    Route::get('password-resets/{notification}', [AdminPasswordResetController::class, 'show'])
        ->name('password-resets.show');

    Route::post('password-resets/{notification}/execute', [AdminPasswordResetController::class, 'executeReset'])
        ->name('password-resets.execute');

    Route::post('password-resets/{notification}/deny', [AdminPasswordResetController::class, 'deny'])
        ->name('password-resets.deny');

});

<?php

use App\Http\Controllers\GlobalAdmin\DashboardController;
use App\Http\Controllers\GlobalAdmin\FeatureToggleController;
use App\Http\Controllers\GlobalAdmin\InstituteController;
use App\Http\Controllers\GlobalAdmin\SystemClassController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Global Admin Routes
|--------------------------------------------------------------------------
| All routes here are prefixed with /global-admin (set in bootstrap/app.php)
| and named with global-admin.*
|
| TODO (Module 2): Replace 'auth' with 'auth,role:global_admin' middleware
| once the role-based auth system is built.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Institutes CRUD ──────────────────────────────────────────────────
    Route::resource('institutes', InstituteController::class);

    // Restore soft-deleted institute
    Route::post('institutes/{id}/restore', [InstituteController::class, 'restore'])
         ->name('institutes.restore');

    // ── Feature Toggle Panel ─────────────────────────────────────────────
    Route::get('institutes/{institute}/toggles', [FeatureToggleController::class, 'edit'])
         ->name('institutes.toggles.edit');

    Route::put('institutes/{institute}/toggles', [FeatureToggleController::class, 'update'])
         ->name('institutes.toggles.update');

    Route::post('institutes/{institute}/toggles/apply-tier', [FeatureToggleController::class, 'applyTierDefaults'])
         ->name('institutes.toggles.apply-tier');

    // ── System Classes CRUD ──────────────────────────────────────────────
    Route::resource('system-classes', SystemClassController::class);

});

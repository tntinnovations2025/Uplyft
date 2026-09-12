<?php

use App\Http\Middleware\CheckInstituteFeature;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureActiveAcademicTerm;
use App\Http\Middleware\EnsureUserBelongsToInstitute;
use App\Http\Middleware\Throttle;
use App\Providers\RateLimiterServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        RateLimiterServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('global-admin')
                ->name('global-admin.')
                ->group(base_path('routes/global-admin.php'));

            Route::middleware('web')
                ->prefix('principal')
                ->name('principal.')
                ->group(base_path('routes/principal.php'));

            // Student Portal Routes (Module 2 & 5)
            Route::middleware('web')
                ->group(base_path('routes/student.php'));

            // Module 6: LMS, Assessments & Grading (plug-and-play)
            Route::middleware('web')
                ->group(base_path('routes/lms.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\EnsureActiveInstituteAndPortal::class,
        ]);

        $middleware->alias([
            'feature' => CheckInstituteFeature::class,
            'role' => CheckRole::class,
            'institute.member' => EnsureUserBelongsToInstitute::class,
            'active.term' => EnsureActiveAcademicTerm::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'portal.active' => \App\Http\Middleware\EnsureActiveInstituteAndPortal::class,
            'staff.prefix' => \App\Http\Middleware\EnsureStaffPortalPrefix::class,
            'throttle' => Throttle::class,
            'student.fee_paid' => \App\Http\Middleware\EnsureStudentFeePaid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

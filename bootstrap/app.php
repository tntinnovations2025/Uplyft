<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'feature'            => \App\Http\Middleware\CheckInstituteFeature::class,
            'role'               => \App\Http\Middleware\CheckRole::class,
            'institute.member'   => \App\Http\Middleware\EnsureUserBelongsToInstitute::class,
            'active.term'        => \App\Http\Middleware\EnsureActiveAcademicTerm::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

<?php

namespace App\Providers;

use App\Events\PasswordResetRequested;
use App\Listeners\SendPasswordResetAlertToAdmin;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Port-Based Multi-Portal Session Cookie Isolation ───────────────
        $port = request()->getPort() ?? (int) ($_SERVER['SERVER_PORT'] ?? 8000);
        $cookieName = 'uplyft_session_port_' . $port;
        config(['session.cookie' => $cookieName]);

        // ── Register UserPolicy ──────────────────────────────────────────────
        Gate::policy(User::class, UserPolicy::class);

        // ── Register Gate shortcuts for common checks ────────────────────────
        Gate::define('create-principal', function (User $user) {
            return $user->isGlobalAdmin();
        });

        Gate::define('create-staff-or-student', function (User $user) {
            return $user->isPrincipal() || $user->hasDelegatedAdminRights();
        });

        Gate::define('manage-delegation', function (User $user) {
            return $user->isPrincipal();
        });

        // ── Register Events ─────────────────────────────────────────────────
        Event::listen(
            PasswordResetRequested::class,
            SendPasswordResetAlertToAdmin::class
        );

        // ── Global Multi-Tenant Institute Branding View Composer ────────────
        \Illuminate\Support\Facades\View::composer(
            '*',
            \App\View\Composers\InstituteBrandingComposer::class
        );
    }
}

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
    }
}

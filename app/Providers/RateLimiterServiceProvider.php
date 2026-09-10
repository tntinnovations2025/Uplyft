<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    /**
     * Register named rate limiters.
     */
    public function boot(): void
    {
        // login → Tier A: per-IP network cap (Tier B handled by Throttle middleware + LoginRequest).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(
                (int) config('rate-limiter.login.network_max_attempts', 40)
            )->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) config('rate-limiter.limiters.api', 60))
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many requests. Please slow down.',
                    'status' => 429,
                ], 429, $headers));
        });

        RateLimiter::for('pwd-reset', function (Request $request) {
            return Limit::perMinute((int) config('rate-limiter.limiters.pwd-reset', 3))
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many password reset requests. Please wait before trying again.',
                    'status' => 429,
                ], 429, $headers));
        });

        RateLimiter::for('email-verify', function (Request $request) {
            return Limit::perMinute((int) config('rate-limiter.limiters.email-verify', 6))
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many verification emails sent. Please check your inbox.',
                    'status' => 429,
                ], 429, $headers));
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinutes(15, (int) config('rate-limiter.limiters.auth', 5))
                ->by($request->ip())
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many authentication attempts. Please try again later.',
                    'status' => 429,
                ], 429, $headers));
        });
    }
}
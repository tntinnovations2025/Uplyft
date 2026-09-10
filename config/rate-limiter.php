<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login Rate Limiting (Dual-Tier)
    |--------------------------------------------------------------------------
    |
    | Tier A (network):  per-IP volumetric cap on login endpoints. Stops a
    |                    single client (or bot) hammering /login. Counts EVERY
    |                    request so successful logins also consume the budget.
    |
    | Tier B (account):  per-account failed-attempt lockout. Keyed by the
    |                    normalized email/identifier PLUS the institute the
    |                    credential maps to, so an attacker cannot lock out a
    |                    shared campus IP or burn another tenant's quota. Only
    |                    FAILED attempts increment the counter; a successful
    |                    login clears it.
    |
    | Applied via: Route::middleware('throttle:login')->post('...')
    |
    */
    'login' => [
        'network_max_attempts' => 40,
        'network_decay_minutes' => 1,
        'account_max_attempts' => 5,
        'account_decay_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Named Limiters
    |--------------------------------------------------------------------------
    | These are registered in App\Providers\RateLimiterServiceProvider via
    | RateLimiter::for(). Usage:
    |   throttle:api          → global API abuse protection
    |   throttle:pwd-reset    → password-reset email abuse
    |   throttle:email-verify → email verification spam
    |   throttle:auth         → generic auth-route protection (legacy)
    |
    | Storage driver: config('cache.default') — currently 'database'
    | (atomic counters + lock table). Swap to 'redis' later by setting
    | CACHE_STORE=redis once a Redis server + phpredis/predis is available.
    |--------------------------------------------------------------------------
    */

    'limiters' => [
        'api' => 60,
        'pwd-reset' => 3,
        'email-verify' => 6,
        'auth' => 5,
    ],

];
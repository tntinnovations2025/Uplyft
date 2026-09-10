<?php

namespace App\Http\Middleware;

use App\Http\Requests\Auth\LoginRequest;
use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced Throttle middleware with DUAL-TIER login protection.
 *
 * Applied via the standard 'throttle:' alias. Non-login limiters (api,
 * pwd-reset, email-verify, auth, and inline `60,1` / `3,1` args) fall
 * through to the stock Laravel ThrottleRequests implementation unchanged.
 *
 * For the special 'login' limiter it enforces BOTH tiers:
 *
 *   Tier A (network): 40 req/min per IP across the login endpoints.
 *                     Counts EVERY request (successful logins included),
 *                     so one client cannot hammer /login.
 *
 *   Tier B (account): 5 failed attempts per 15-min window keyed by the
 *                     normalized email/identifier + the institute the
 *                     credential maps to. The counter is incremented ONLY
 *                     on failed attempts (inside LoginRequest) and cleared
 *                     on a successful login. This middleware performs the
 *                     pre-check and short-circuits with a JSON 429 that
 *                     carries RFC-compliant headers.
 *
 * Storage driver: config('cache.default') — database (atomic + locked),
 * ready to switch to redis via CACHE_STORE=redis.
 */
class Throttle extends ThrottleRequests
{
    /**
     * Name of the dual-tier login limiter.
     */
    public const LOGIN_LIMITER = 'login';

    /**
     * Handle an incoming request.
     *
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @return Response
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (is_string($maxAttempts) && func_num_args() === 3) {
            $limiter = $this->limiter->limiter($maxAttempts);

            if ($maxAttempts === self::LOGIN_LIMITER && ! is_null($limiter)) {
                return $this->handleLoginRequest($request, $next, $limiter);
            }

            // Delegate with exactly 3 args so Laravel's own named-limiter
            // detection (func_num_args() === 3) and inline-numeric fallback
            // behave exactly as stock.
            return parent::handle($request, $next, $maxAttempts);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }

    /**
     * Enforce the dual-tier login limits.
     */
    protected function handleLoginRequest($request, Closure $next, Closure $limiter): Response
    {
        // ── Tier B: account-level lockout (check only; LoginRequest records hits) ──
        $accountKey = LoginRequest::resolveLoginThrottleKey($request);
        $request->attributes->set('uplyft_login_throttle_key', $accountKey);

        $accountMax = (int) config('rate-limiter.login.account_max_attempts', 5);

        if ($this->limiter->tooManyAttempts($accountKey, $accountMax)) {
            return $this->tooManyResponse(
                $accountKey,
                $accountMax,
                'Too many failed login attempts for this account. Please try again later.'
            );
        }

        // ── Tier A: network / per-IP volumetric cap ─────────────────────────
        $tierA = $limiter($request);

        if ($tierA instanceof Response) {
            return $tierA;
        }

        if (! $tierA instanceof Unlimited) {
            foreach (Collection::wrap($tierA) as $limit) {
                if ($this->limiter->tooManyAttempts($this->resolveLimitKey($limit, $request), $limit->maxAttempts)) {
                    return $this->tooManyResponse(
                        $this->resolveLimitKey($limit, $request),
                        $limit->maxAttempts,
                        'Too many requests. Please slow down.'
                    );
                }
            }

            foreach (Collection::wrap($tierA) as $limit) {
                if (! $limit->afterCallback) {
                    $this->limiter->hit($this->resolveLimitKey($limit, $request), $limit->decaySeconds);
                }
            }
        }

        $response = $next($request);

        if (! $tierA instanceof Unlimited) {
            foreach (Collection::wrap($tierA) as $limit) {
                if ($limit->afterCallback && ($limit->afterCallback)($response)) {
                    $this->limiter->hit($this->resolveLimitKey($limit, $request), $limit->decaySeconds);
                }

                $response = $this->addHeaders(
                    $response,
                    $limit->maxAttempts,
                    $this->calculateRemainingAttempts($this->resolveLimitKey($limit, $request), $limit->maxAttempts)
                );
            }
        }

        return $response;
    }

    /**
     * Resolve the concrete cache key for a Limit, mirroring the stock
     * middleware's md5($limiterName.$key) hashing so keys stay consistent.
     */
    protected function resolveLimitKey(Limit $limit, $request): string
    {
        $key = $limit->key;

        if ($key instanceof Closure) {
            $key = $key($request);
        }

        return md5(self::LOGIN_LIMITER.'.' . $key);
    }

    /**
     * Build a JSON 429 response carrying RFC-compliant rate-limit headers.
     */
    protected function tooManyResponse(string $key, int $maxAttempts, string $message): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $headers = [
            'X-RateLimit-Limit' => (string) $maxAttempts,
            'X-RateLimit-Remaining' => '0',
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Reset' => (string) $this->availableAt($retryAfter),
        ];

        return response()->json([
            'message' => $message,
            'status' => 429,
            'retry_after' => $retryAfter,
        ], 429, $headers);
    }
}
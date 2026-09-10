<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defensive global input normalization.
 *
 * Defense layers (defense-in-depth):
 *   - SQLi:      all queries use parameter binding already; this middleware
 *                strips control/NUL bytes that could smuggle payload fragments.
 *   - XSS:       outputs are Blade-escaped with {{ }}; trimming + control-char
 *                removal here reduces stored-marker noise.
 *   - Template:  no user input ever resolves a template path, so no action
 *                beyond normalization is required here.
 *   - Passwords: values in dedicated password fields are NEVER altered.
 */
class SanitizeInput
{
    private const SKIP_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'old_password',
        'new_password',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $this->sanitize($request->all());

        $request->replace($input);

        return $next($request);
    }

    /**
     * Recursively normalize request values.
     */
    protected function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                if (is_string($key) && $this->shouldSkip($key)) {
                    $result[$key] = $item;
                    continue;
                }

                $result[$key] = $this->sanitize($item);
            }

            return $result;
        }

        if (! is_string($value)) {
            return $value;
        }

        // Strip UTF-8 BOM.
        $value = preg_replace('/\xEF\xBB\xBF/', '', $value) ?? $value;

        // Strip NUL + control characters (except tab/newline/carriage return).
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? $value;

        // Normalize any invalid UTF-8 byte sequences.
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return trim($value);
    }

    /**
     * Password fields are never normalized/trimmed.
     */
    protected function shouldSkip(string $key): bool
    {
        $lower = strtolower($key);

        return in_array($lower, self::SKIP_KEYS, true)
            || str_ends_with($lower, '_password');
    }
}
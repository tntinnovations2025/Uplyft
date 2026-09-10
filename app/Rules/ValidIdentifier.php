<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a custom institutional identifier.
 *
 * Allowed formats:
 *  - STU-2026/0101
 *  - 10A-045
 *  - EMP#402
 *  - FAC-MATH-01
 *
 * Pattern: Alphanumeric characters plus hyphens (-), slashes (/), and hashes (#).
 * Minimum 3, maximum 50 characters.
 */
class ValidIdentifier implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow letters, numbers, hyphens, slashes, hashes, dots, underscores
        if (! preg_match('/^[A-Za-z0-9\-\/#._]{3,50}$/', $value)) {
            $fail('The :attribute must be 3–50 characters and may only contain letters, numbers, hyphens, slashes, hashes, dots, and underscores.');
        }
    }
}

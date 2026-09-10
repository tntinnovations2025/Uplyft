<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class ValidPasswordOtp implements Rule
{
    /**
     * Determine if the 6-digit OTP matches the one e-mailed to the
     * authenticated user and has not expired (2-minute lifetime).
     */
    public function passes($attribute, $value): bool
    {
        $storedHash = Cache::get('password_otp_'.auth()->id());

        if (! is_string($storedHash)) {
            return false;
        }

        return hash_equals($storedHash, hash('sha256', (string) $value));
    }

    public function message(): string
    {
        return 'The OTP you entered is incorrect or has expired. Codes are valid for 2 minutes.';
    }
}
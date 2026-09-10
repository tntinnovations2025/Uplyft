<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use App\Rules\ValidPasswordOtp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * Validates:
     * 1. Current password must match active hashed password.
     * 2. A 6-digit OTP (e-mailed on request, 2-minute lifetime) must match.
     * 3. New password must be different from current password.
     * 4. New password must adhere to UPLYFT StrongPassword complexity rules.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'otp' => ['required', new ValidPasswordOtp],
            'password' => ['required', 'confirmed', 'different:current_password', new StrongPassword],
        ], [
            'current_password.current_password' => 'The current password you entered is incorrect. Password has not been updated.',
            'password.different' => 'Your new password cannot be the same as your current password. Please choose a new password.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        Cache::forget('password_otp_'.$request->user()->id);

        return back()->with('status', 'password-updated');
    }
}

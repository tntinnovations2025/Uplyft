<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangeOtpMail;
use App\Models\InstituteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordOtpController extends Controller
{
    /**
     * Generate a 6-digit OTP (2-minute lifetime), e-mail it to the user's
     * registered address from the institute's configured notification
     * email, and store only its hash for later verification.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('password_otp_'.$user->id, hash('sha256', $otp), now()->addMinutes(2));

        $institute = $user->institute;
        $setting = $institute ? InstituteSetting::getForInstitute($institute->id) : null;
        $fromEmail = $setting?->notification_email ?: ($institute?->contact_email ?: config('mail.from.address'));
        $fromName = $institute?->name ?: config('mail.from.name');

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new PasswordChangeOtpMail($user, $otp, $fromEmail, $fromName));
            } catch (\Throwable $e) {
                Log::error('Failed to send password change OTP email to '.$user->email.': '.$e->getMessage());

                return back()->with('status', 'otp-send-failed');
            }
        }

        return back()->with('status', 'otp-sent');
    }
}
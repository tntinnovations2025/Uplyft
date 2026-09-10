<section>
    <header style="margin-bottom: 22px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:38px;height:38px;border-radius:10px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;font-size:18px">
                🛡️
            </div>
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px">
                    {{ __('Account Security & Email') }}
                </h2>
                <p style="font-size: 13px; color: #64748b; margin-top: 3px; font-weight: 500;">
                    {{ __('Verification status and secondary security options for your account.') }}
                </p>
            </div>
        </div>
    </header>

    <div style="display: flex; flex-direction: column; gap: 14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;">
            <div>
                <div style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.4px;">Registered Email</div>
                <div style="font-size:14px;font-weight:700;color:#0f172a;margin-top:2px;">{{ $user->email }}</div>
            </div>

            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <span style="flex-shrink:0;padding:5px 12px;border-radius:999px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:12px;font-weight:800;">⚠ Unverified</span>
            @else
                <span style="flex-shrink:0;padding:5px 12px;border-radius:999px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:12px;font-weight:800;">✓ Verified</span>
            @endif
        </div>

        @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" style="width:100%;padding:12px 18px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#334155;font-weight:700;font-size:13.5px;cursor:pointer;">
                    {{ __('Resend verification email') }}
                </button>
            </form>

            @if(session('status') === 'verification-link-sent')
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:10px 14px;border-radius:10px;color:#166534;font-weight:700;font-size:13px;">
                    {{ __('A new verification link has been sent to your email address.') }}
                </div>
            @endif
        @endif

        <div style="display:flex;align-items:flex-start;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;padding:12px 14px;border-radius:10px;">
            <span style="font-size:15px">🔑</span>
            <p style="margin:0;font-size:12.5px;color:#475569;line-height:1.55;">
                Changing your password requires an <strong>OTP</strong> that is e-mailed to your registered address (valid for 2 minutes). This e-mail is sent from your institute&apos;s configured notification address so you can always verify its origin.
            </p>
        </div>
    </div>
</section>
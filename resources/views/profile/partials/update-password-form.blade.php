<section>
    <header style="margin-bottom: 22px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:38px;height:38px;border-radius:10px;background:#fef3c7;color:#d97706;border:1px solid #fde68a;display:flex;align-items:center;justify-content:center;font-size:18px">
                🔑
            </div>
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px">
                    {{ __('Change Password') }}
                </h2>
                <p style="font-size: 13px; color: #64748b; margin-top: 3px; font-weight: 500;">
                    {{ __('Verify with your current password and an OTP sent to your registered email.') }}
                </p>
            </div>
        </div>
    </header>

    @if(session('status') === 'otp-sent')
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:11px 14px;border-radius:10px;color:#166534;font-weight:700;font-size:13px;margin-bottom:16px;">
            ✅ OTP sent to <strong>{{ $user->email }}</strong>. Codes are valid for 2 minutes.
        </div>
    @elseif(session('status') === 'otp-send-failed')
        <div style="background:#fef2f2;border:1px solid #fecaca;padding:11px 14px;border-radius:10px;color:#b91c1c;font-weight:700;font-size:13px;margin-bottom:16px;">
            ❌ Could not send the OTP e-mail. Please try again in a moment.
        </div>
    @endif

    <form method="post" action="{{ route('profile.password.send-otp') }}" style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 12px 16px;">
        @csrf
        <span style="font-size: 24px;">📧</span>
        <div style="flex:1;">
            <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Step 1 — Request your OTP</div>
            <div style="font-size: 12px; color: #64748b;">An OTP will be sent to <strong>{{ $user->email }}</strong> from your institute&apos;s notification address.</div>
        </div>
        <button type="submit" style="flex-shrink:0;padding:10px 18px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#334155;font-weight:800;font-size:12.5px;cursor:pointer;">
            {{ __('Send OTP') }}
        </button>
    </form>

    <form method="post" action="{{ route('password.update') }}" style="display: flex; flex-direction: column; gap: 18px;">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('Current Password') }} *
            </label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                placeholder="Enter current password..."
                class="form-input"
                style="width: 100%; padding: 11px 16px; background: #ffffff; border: 1px solid {{ $errors->updatePassword->has('current_password') ? '#ef4444' : '#cbd5e1' }}; border-radius: 10px; color: #0f172a; font-size: 14px; outline: none; transition: all 0.2s ease;" />

            @if($errors->updatePassword->has('current_password'))
                <div style="color: #dc2626; font-size: 12px; font-weight: 600; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span>
                    <span>{{ $errors->updatePassword->first('current_password') }}</span>
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_otp" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('Email OTP') }} *
            </label>
            <input id="update_password_otp" name="otp" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                placeholder="Enter 6-digit OTP..."
                style="width: 100%; padding: 11px 16px; background: #ffffff; border: 1px solid {{ $errors->updatePassword->has('otp') ? '#ef4444' : '#cbd5e1' }}; border-radius: 10px; color: #0f172a; font-size: 14px; letter-spacing: 4px; text-align: center; font-weight: 700; outline: none; transition: all 0.2s ease;" />
            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                Request a new OTP whenever a code expires. Each code is single-use and valid for 2 minutes.
            </p>

            @if($errors->updatePassword->has('otp'))
                <div style="color: #dc2626; font-size: 12px; font-weight: 600; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span>
                    <span>{{ $errors->updatePassword->first('otp') }}</span>
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('New Password') }} *
            </label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                placeholder="Enter new strong password (e.g. Pass123!#)..."
                class="form-input"
                style="width: 100%; padding: 11px 16px; background: #ffffff; border: 1px solid {{ $errors->updatePassword->has('password') ? '#ef4444' : '#cbd5e1' }}; border-radius: 10px; color: #0f172a; font-size: 14px; outline: none; transition: all 0.2s ease;" />
            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                Must contain uppercase, lowercase, number, and special character (min 8 characters).
            </p>

            @if($errors->updatePassword->has('password'))
                <div style="color: #dc2626; font-size: 12px; font-weight: 600; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span>
                    <span>{{ $errors->updatePassword->first('password') }}</span>
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('Confirm New Password') }} *
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                placeholder="Re-type new password..."
                class="form-input"
                style="width: 100%; padding: 11px 16px; background: #ffffff; border: 1px solid {{ $errors->updatePassword->has('password_confirmation') ? '#ef4444' : '#cbd5e1' }}; border-radius: 10px; color: #0f172a; font-size: 14px; outline: none; transition: all 0.2s ease;" />

            @if($errors->updatePassword->has('password_confirmation'))
                <div style="color: #dc2626; font-size: 12px; font-weight: 600; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>⚠️</span>
                    <span>{{ $errors->updatePassword->first('password_confirmation') }}</span>
                </div>
            @endif
        </div>

        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 8px;">
            @if (session('status') === 'password-updated')
                <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" style="color: #059669; font-weight: 700; font-size: 13px;">
                    ✓ {{ __('Password updated successfully.') }}
                </span>
            @endif

            <button type="submit" style="padding: 10px 24px; background: linear-gradient(135deg, #4f46e5, #6366f1); border: none; border-radius: 10px; color: #ffffff; font-weight: 800; font-size: 13.5px; cursor: pointer; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);">
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>
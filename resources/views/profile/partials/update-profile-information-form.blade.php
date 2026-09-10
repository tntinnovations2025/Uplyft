<section>
    <header style="margin-bottom: 22px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:38px;height:38px;border-radius:10px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;font-size:18px">
                👤
            </div>
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px">
                    {{ __('Personal Information') }}
                </h2>
                <p style="font-size: 13px; color: #64748b; margin-top: 3px; font-weight: 500;">
                    {{ __('Your identity details as registered with the institute.') }}
                </p>
            </div>
        </div>
    </header>

    <div style="display: flex; flex-direction: column; gap: 18px;">
        <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('Full Name') }}
            </label>
            <div style="width:100%; padding: 11px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; color: #334155; font-size: 14px; font-weight: 600;">
                {{ $user->name }}
            </div>
        </div>

        <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                {{ __('Email Address') }}
            </label>
            <div style="width:100%; padding: 11px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; color: #334155; font-size: 14px; font-weight: 600;">
                {{ $user->email }}
            </div>
        </div>

        <div style="display:flex;align-items:flex-start;gap:10px;background:#fefce8;border:1px solid #fde047;padding:12px 14px;border-radius:10px;">
            <span style="font-size:15px">🔒</span>
            <div style="font-size:12.5px;color:#713f12;line-height:1.55;">
                <p style="margin:0;font-weight:700;">These details are managed by your Institute Administration.</p>
                <p style="margin:2px 0 0 0;">Your Name &amp; Email can only be changed by your Principal or the administration. To request an update, please contact them directly.</p>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;padding:10px 14px;border-radius:10px;color:#166534;font-weight:700;font-size:13px;">
                ✓ {{ __('Profile Saved Successfully.') }}
            </div>
        @endif
    </div>
</section>
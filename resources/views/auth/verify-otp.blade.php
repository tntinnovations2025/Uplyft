<x-guest-layout>
    <style>
        .portal-header-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 10px;
        }

        .portal-mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 10px;
            font-size: 11px;
            line-height: 1.4;
            color: #475569;
        }

        .input-group { margin-bottom: 9px; }
        .input-label { display: block; font-size: 9.5px; font-weight: 800; color: #475569; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 10px; font-size: 12px; pointer-events: none; opacity: 0.75; }

        .custom-input {
            width: 100%;
            height: 32px;
            padding: 0 8px 0 28px !important;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #0f172a;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            outline: none;
            transition: all 0.18s ease;
        }

        .custom-input:focus {
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
        }

        .otp-input {
            letter-spacing: 4px;
            font-family: monospace;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
            padding-left: 10px;
        }

        .submit-btn {
            width: 100%;
            height: 36px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.38);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 8px;
            font-size: 11px;
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>

    <div class="portal-header-group">
        <div class="portal-mode-badge">
            <span>🔢</span>
            <span>VERIFY OTP &amp; RESET PASSWORD</span>
        </div>
    </div>

    <div class="info-card">
        Enter the <strong>6-digit OTP code</strong> sent to your email along with your new password.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-2" :status="session('status')" />

    <form method="POST" action="{{ route('password.otp.verify') }}">
        @csrf

        <!-- Email or Identifier -->
        <div class="input-group">
            <label for="credential" class="input-label">Email or Account ID</label>
            <div class="input-wrapper">
                <input id="credential" class="custom-input" type="text" name="credential"
                    value="{{ old('credential', $credential) }}" required autofocus
                    placeholder="e.g. user@school.edu, STU-2026-001" />
                <span class="input-icon">👤</span>
            </div>
            <x-input-error :messages="$errors->get('credential')" class="mt-1 text-xs text-red-500" />
        </div>

        <!-- OTP Code -->
        <div class="input-group">
            <label for="otp" class="input-label">6-Digit OTP Code</label>
            <div class="input-wrapper">
                <input id="otp" class="custom-input otp-input" type="text" name="otp" maxLength="6"
                    placeholder="123456" required />
            </div>
            <x-input-error :messages="$errors->get('otp')" class="mt-1 text-xs text-red-500" />
        </div>

        <!-- New Password -->
        <div class="input-group">
            <label for="password" class="input-label">New Password</label>
            <div class="input-wrapper">
                <input id="password" class="custom-input" type="password" name="password" required
                    placeholder="Enter new password" />
                <span class="input-icon">🔒</span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
            <label for="password_confirmation" class="input-label">Confirm New Password</label>
            <div class="input-wrapper">
                <input id="password_confirmation" class="custom-input" type="password" name="password_confirmation" required
                    placeholder="Re-enter password" />
                <span class="input-icon">🔑</span>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-500" />
        </div>

        <button type="submit" class="submit-btn">
            <span>Verify &amp; Update Password</span>
            <span>&rarr;</span>
        </button>

        <a href="{{ route('login') }}" class="back-link">&larr; Return to Login</a>
    </form>
</x-guest-layout>

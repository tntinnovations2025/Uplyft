<x-guest-layout>
    <style>
        .admin-auth-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 4px;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border: 1px solid rgba(79, 70, 229, 0.25);
            margin-bottom: 6px;
        }

        .admin-title {
            font-family: 'Outfit', sans-serif;
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .admin-subtitle {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
            margin-top: 3px;
        }

        .input-group {
            margin-bottom: 10px;
        }

        .input-label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 3px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 10px;
            width: 15px;
            height: 15px;
            color: #94a3b8;
            pointer-events: none;
        }

        .custom-input {
            width: 100%;
            height: 38px;
            padding: 0 34px 0 32px !important;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            color: #0f172a;
            font-size: 12.5px;
            font-family: inherit;
            font-weight: 500;
            outline: none;
            transition: all 0.18s ease;
        }

        .custom-input:focus {
            background: #ffffff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
        }

        .password-toggle-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        .auth-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
            margin: 4px 0 10px 0;
        }

        .remember-checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .remember-checkbox-label input {
            accent-color: #4f46e5;
            width: 13px;
            height: 13px;
        }

        .forgot-password-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 700;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
        }

        .admin-submit-btn {
            width: 100%;
            height: 38px;
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.32);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .admin-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.42);
        }

        .portal-back-link {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .portal-back-link a {
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
        }

        .portal-back-link a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="admin-auth-wrapper">
        <div class="admin-header">
            <div class="admin-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                <span>Restricted Access</span>
            </div>
            <h1 class="admin-title">Global Control Tower</h1>
            <p class="admin-subtitle">Master platform administration & network governance</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-1" :status="session('status')" />

        <form id="globalAdminLoginForm" method="POST" action="{{ route('globaladmin.login') }}">
            @csrf

            <!-- Master Admin Credential -->
            <div class="input-group">
                <label for="credential" class="input-label">Master Administrator Email</label>
                <div class="input-wrapper">
                    <input id="credential" class="custom-input" type="email" name="credential" value="{{ old('credential') }}" required autofocus autocomplete="username"
                        placeholder="admin@platform.com" />
                    <span class="input-icon-left">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                </div>
                <x-input-error :messages="$errors->get('credential')" class="mt-1 text-xs text-red-500 font-semibold" />
            </div>

            <!-- Password -->
            <div class="input-group">
                <label for="password" class="input-label">Master Security Key</label>
                <div class="input-wrapper">
                    <input id="password" class="custom-input" type="password" name="password" required autocomplete="current-password" placeholder="Enter master security key..." />
                    <span class="input-icon-left">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <button type="button" class="password-toggle-btn" onclick="toggleAdminPassword()" aria-label="Toggle password visibility">
                        <svg id="adminEyeIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="adminEyeOffIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500 font-semibold" />
            </div>

            <!-- Remember Me & Forgot Password Row -->
            <div class="auth-options-row">
                <label class="remember-checkbox-label">
                    <input type="checkbox" name="remember" />
                    <span>Remember terminal</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-password-link">Forgot Password?</a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="admin-submit-btn">
                <span>Authenticate to Control Tower</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </form>

        <div class="portal-back-link">
            <a href="{{ route('login') }}">&larr; Return to Campus Portals</a>
        </div>
    </div>

    <script>
        function toggleAdminPassword() {
            const input = document.getElementById('password');
            const eye = document.getElementById('adminEyeIcon');
            const eyeOff = document.getElementById('adminEyeOffIcon');
            if (input.type === 'password') {
                input.type = 'text';
                eye.style.display = 'none';
                eyeOff.style.display = 'block';
            } else {
                input.type = 'password';
                eye.style.display = 'block';
                eyeOff.style.display = 'none';
            }
        }
    </script>
</x-guest-layout>

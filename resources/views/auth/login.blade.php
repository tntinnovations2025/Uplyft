<x-guest-layout>
    <style>
        .input-group { margin-bottom: 8px; }
        .input-label { display: block; font-size: 8.5px; font-weight: 800; color: #475569; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.3px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 8px; font-size: 11px; pointer-events: none; opacity: 0.75; }
        
        .custom-input {
            width: 100%;
            height: 32px;
            padding: 0 8px 0 28px !important;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #0f172a;
            font-size: 11.5px;
            font-family: inherit;
            font-weight: 500;
            outline: none;
            transition: all 0.15s ease;
        }

        .custom-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2.5px rgba(2, 132, 199, 0.18);
        }

        .custom-input::placeholder {
            color: #94a3b8;
            font-size: 10.5px;
        }

        .login-submit-btn {
            width: 100%;
            height: 32px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            border-radius: 6px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 3px 10px rgba(2, 132, 199, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 8px;
        }

        .login-submit-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(2, 132, 199, 0.35);
        }
    </style>

    <!-- Session Status -->
    <x-auth-session-status class="mb-1" :status="session('status')" />

    @php
        $studentLogin = ($studentPortal ?? false) || request()->routeIs('student.login');
    @endphp

    <form id="loginForm" method="POST" action="{{ $studentLogin ? route('student.login.store') : route('login') }}">
        @csrf

        <!-- 1. Email (Student portal) or Email / Institutional ID (others) -->
        <div class="input-group">
            <label for="credential" class="input-label">{{ $studentLogin ? 'Email Address' : 'Email Address or ID' }}</label>
            <div class="input-wrapper">
                <input id="credential" class="custom-input" type="{{ $studentLogin ? 'email' : 'text' }}" name="credential" value="{{ old('credential') }}" required autofocus autocomplete="username"
                    placeholder="{{ $studentLogin ? 'e.g. student@apex.edu.pk' : 'e.g. teacher@apex.edu.pk, STU-2025-001' }}" />
                <span class="input-icon">✉️</span>
            </div>
            <x-input-error :messages="$errors->get('credential')" class="mt-1 text-xs text-red-500" />
        </div>

        <!-- 2. Security Password -->
        <div class="input-group">
            <label for="password" class="input-label">Security Password</label>
            <div class="input-wrapper">
                <input id="password" class="custom-input" type="password" name="password" required autocomplete="current-password" placeholder="Enter password..." />
                <span class="input-icon">🔒</span>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
        </div>

        <!-- 3. Remember Me & Forgot Password Row -->
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:9.5px;color:#64748b;margin-top:2px;">
            <label style="display:flex;align-items:center;gap:4px;cursor:pointer">
                <input type="checkbox" name="remember" style="accent-color:#0284c7;width:11px;height:11px;cursor:pointer">
                <span>Remember</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color:#0284c7;text-decoration:none;font-weight:700">Forgot Password?</a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="login-submit-btn">
            <span>Sign In to Portal</span>
            <span>&rarr;</span>
        </button>
    </form>
</x-guest-layout>

<x-guest-layout>
    @php
        $initialRole = old('role') ?? $selectedRole ?? request()->query('role');
        if (!in_array($initialRole, ['principal', 'faculty', 'student'], true)) {
            if (request()->routeIs('principal.login*')) {
                $initialRole = 'principal';
            } elseif (request()->routeIs('student.login*') || ($studentPortal ?? false)) {
                $initialRole = 'student';
            } elseif (request()->routeIs('faculty.login*') || request()->routeIs('teacher.login*')) {
                $initialRole = 'faculty';
            } else {
                $initialRole = null;
            }
        }

        $hasErrors = $errors->any();
        if ($hasErrors && !$initialRole) {
            $initialRole = old('role', 'student');
        }

        $formAction = match ($initialRole) {
            'principal' => route('principal.login.store'),
            'faculty'   => route('faculty.login.store'),
            'student'   => route('student.login.store'),
            default     => route('login'),
        };
    @endphp

    <style>
        .portal-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* ── Header ── */
        .portal-header {
            text-align: center;
            margin-bottom: 8px;
            position: relative;
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .portal-badge-default {
            background: rgba(2, 132, 199, 0.08);
            color: #0284c7;
            border: 1px solid rgba(2, 132, 199, 0.18);
        }

        .portal-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }

        .portal-subtitle {
            font-size: 10.5px;
            color: #64748b;
            font-weight: 500;
            margin-top: 1px;
            line-height: 1.3;
        }

        /* ── Top-Left Back Button (Appears only on Login Form) ── */
        .top-left-back-btn {
            position: absolute;
            top: -2px;
            left: -2px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            z-index: 10;
        }

        .top-left-back-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: scale(1.06);
        }

        .top-left-back-btn:active {
            transform: scale(0.96);
        }

        /* ── 3 Role Clickables (Overview) ── */
        .role-cards-container {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .role-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #ffffff;
            border: 1.2px solid #e2e8f0;
            border-radius: 9px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .role-card:hover {
            transform: translateY(-1px);
            border-color: var(--role-color, #0284c7);
            box-shadow: 0 4px 12px -2px var(--role-glow, rgba(2, 132, 199, 0.18));
        }

        .role-card:active {
            transform: translateY(0);
        }

        .role-card.role-principal {
            --role-color: #059669;
            --role-glow: rgba(5, 150, 105, 0.22);
            --role-bg-light: #ecfdf5;
            --role-text: #065f46;
        }

        .role-card.role-faculty {
            --role-color: #4f46e5;
            --role-glow: rgba(79, 70, 229, 0.22);
            --role-bg-light: #eef2ff;
            --role-text: #3730a3;
        }

        .role-card.role-student {
            --role-color: #0284c7;
            --role-glow: rgba(2, 132, 199, 0.22);
            --role-bg-light: #f0f9ff;
            --role-text: #075985;
        }

        .role-icon-box {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: var(--role-bg-light, #f1f5f9);
            color: var(--role-color, #0284c7);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.15s ease;
        }

        .role-card:hover .role-icon-box {
            transform: scale(1.06);
        }

        .role-info {
            flex: 1;
            min-width: 0;
        }

        .role-name-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-name {
            font-family: 'Outfit', sans-serif;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.2px;
        }

        .role-pill {
            font-size: 8px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 9999px;
            background: var(--role-bg-light);
            color: var(--role-text);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .role-desc {
            font-size: 10px;
            color: #64748b;
            margin-top: 1px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .role-arrow {
            color: #cbd5e1;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .role-card:hover .role-arrow {
            color: var(--role-color);
            transform: translateX(2px);
        }

        /* ── Form View ── */
        .form-view-container {
            display: flex;
            flex-direction: column;
        }

        .hidden-state {
            display: none !important;
        }

        .input-group {
            margin-bottom: 7px;
        }

        .input-label {
            display: block;
            font-size: 8.5px;
            font-weight: 800;
            color: #475569;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon-left {
            position: absolute !important;
            left: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 14px !important;
            height: 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #94a3b8 !important;
            pointer-events: none !important;
            z-index: 5 !important;
            transition: opacity 0.12s ease, visibility 0.12s ease !important;
        }

        .custom-input,
        .input-wrapper input,
        .input-wrapper input[type="text"],
        .input-wrapper input[type="password"],
        .input-wrapper input[type="email"] {
            width: 100% !important;
            height: 34px !important;
            padding-left: 34px !important;
            padding-right: 30px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            color: #0f172a !important;
            font-size: 11.5px !important;
            font-family: inherit !important;
            font-weight: 500 !important;
            outline: none !important;
            box-sizing: border-box !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, padding-left 0.15s ease !important;
        }

        /* Instantly vanish icon if even 1 character exists or autofilled */
        .input-wrapper.has-value .input-icon,
        .input-wrapper.has-value .input-icon-left,
        .input-wrapper.has-value .input-icon-left svg,
        .input-wrapper input:not(:placeholder-shown) ~ .input-icon,
        .input-wrapper input:not(:placeholder-shown) ~ .input-icon-left,
        .input-wrapper input:-webkit-autofill ~ .input-icon,
        .input-wrapper input:-webkit-autofill ~ .input-icon-left,
        .input-wrapper input:autofill ~ .input-icon,
        .input-wrapper input:autofill ~ .input-icon-left,
        .input-wrapper:has(input:not(:placeholder-shown)) .input-icon,
        .input-wrapper:has(input:not(:placeholder-shown)) .input-icon-left,
        .input-wrapper:has(input:-webkit-autofill) .input-icon,
        .input-wrapper:has(input:-webkit-autofill) .input-icon-left {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* When typed/autofilled, shift text to left edge with comfortable padding */
        .input-wrapper.has-value .custom-input,
        .input-wrapper.has-value input,
        .input-wrapper input:not(:placeholder-shown),
        .input-wrapper input:-webkit-autofill,
        .input-wrapper input:autofill {
            padding-left: 10px !important;
        }

        .custom-input:focus {
            background: #ffffff !important;
            border-color: var(--current-color, #0284c7) !important;
            box-shadow: 0 0 0 2.5px var(--current-focus-ring, rgba(2, 132, 199, 0.18)) !important;
        }

        .custom-input:focus ~ .input-icon-left {
            color: var(--current-color, #0284c7) !important;
        }

        .custom-input::placeholder {
            color: #94a3b8 !important;
            font-size: 11px !important;
            font-weight: 400 !important;
        }

        .password-toggle-btn {
            position: absolute;
            right: 6px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 3px;
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
            font-size: 9.5px;
            color: #64748b;
            margin: 2px 0 7px 0;
        }

        .remember-checkbox-label {
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox-label input {
            accent-color: var(--current-color, #0284c7);
            width: 11px;
            height: 11px;
            cursor: pointer;
        }

        .forgot-password-link {
            color: var(--current-color, #0284c7);
            text-decoration: none;
            font-weight: 700;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
        }

        .login-submit-btn {
            width: 100%;
            height: 32px;
            background: var(--current-gradient, linear-gradient(135deg, #0284c7 0%, #2563eb 100%));
            border: none;
            border-radius: 6px;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 3px 10px var(--current-btn-shadow, rgba(2, 132, 199, 0.25));
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .login-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px var(--current-btn-shadow, rgba(2, 132, 199, 0.35));
        }

        .login-submit-btn:active {
            transform: translateY(0);
        }

        /* Role Theme Dynamic Variables on Form */
        .portal-theme-principal {
            --current-color: #059669;
            --current-focus-ring: rgba(5, 150, 105, 0.18);
            --current-gradient: linear-gradient(135deg, #059669 0%, #10b981 100%);
            --current-btn-shadow: rgba(5, 150, 105, 0.25);
        }

        .portal-theme-faculty {
            --current-color: #4f46e5;
            --current-focus-ring: rgba(79, 70, 229, 0.18);
            --current-gradient: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
            --current-btn-shadow: rgba(79, 70, 229, 0.25);
        }

        .portal-theme-student {
            --current-color: #0284c7;
            --current-focus-ring: rgba(2, 132, 199, 0.18);
            --current-gradient: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --current-btn-shadow: rgba(2, 132, 199, 0.25);
        }
    </style>

    <div class="portal-wrapper">
        <!-- Top Left Back Button (Visible when inside a Role Login Form) -->
        <button type="button" id="topLeftBackBtn" class="top-left-back-btn {{ $initialRole ? '' : 'hidden-state' }}" onclick="showAllRoleCards()" title="Go back to select another role">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>

        <!-- Header -->
        <div class="portal-header">
            <div id="portalBadge" class="portal-badge portal-badge-default">
                <span id="portalBadgeIcon">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <span id="portalBadgeText">Academic Gateway</span>
            </div>
            <h1 id="portalHeaderTitle" class="portal-title">{{ $instituteBranding->name ?? 'Campus Portal' }}</h1>
            <p id="portalHeaderSubtitle" class="portal-subtitle">Select your role to access your academic workspace</p>
        </div>

        <!-- Session Status Notice -->
        <x-auth-session-status class="mb-1" :status="session('status')" />

        <!-- General Error Notice -->
        @if (session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:6px 10px;border-radius:6px;font-size:10.5px;font-weight:600;margin-bottom:6px;display:flex;align-items:center;gap:4px">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- =================================================================== -->
        <!-- MENU 1: 3 ROLE OPTIONS (Principal, Faculty, Student)               -->
        <!-- =================================================================== -->
        <div id="roleCardsView" class="role-cards-container {{ $initialRole ? 'hidden-state' : '' }}">
            <!-- 1. Principal -->
            <div class="role-card role-principal" onclick="selectRole('principal')">
                <div class="role-icon-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name-row">
                        <span class="role-name">Principal</span>
                        <span class="role-pill">Admin</span>
                    </div>
                    <p class="role-desc">Campus leadership, policy governance & accounts</p>
                </div>
                <div class="role-arrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </div>

            <!-- 2. Faculty -->
            <div class="role-card role-faculty" onclick="selectRole('faculty')">
                <div class="role-icon-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                        <path d="M6 6h10"/>
                        <path d="M6 10h10"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name-row">
                        <span class="role-name">Faculty</span>
                        <span class="role-pill">Academic</span>
                    </div>
                    <p class="role-desc">Teachers, class schedule, attendance & LMS</p>
                </div>
                <div class="role-arrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </div>

            <!-- 3. Student -->
            <div class="role-card role-student" onclick="selectRole('student')">
                <div class="role-icon-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name-row">
                        <span class="role-name">Student</span>
                        <span class="role-pill">Learner</span>
                    </div>
                    <p class="role-desc">Courses, timetables, fee receipts & report cards</p>
                </div>
                <div class="role-arrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MENU 2: ROLE LOGIN FORM (NO second set of 3 tabs, clean & compact)   -->
        <!-- =================================================================== -->
        <div id="formView" class="form-view-container {{ $initialRole ? '' : 'hidden-state' }} portal-theme-{{ $initialRole ?? 'student' }}">
            
            <form id="unifiedLoginForm" method="POST" action="{{ $formAction }}">
                @csrf
                <input type="hidden" id="formRoleInput" name="role" value="{{ $initialRole ?? 'student' }}" />

                <!-- Credential Input -->
                <div class="input-group">
                    <label id="credentialLabel" for="credential" class="input-label">Email Address or ID</label>
                    <div class="input-wrapper">
                        <input id="credential" class="custom-input" type="text" name="credential" value="{{ old('credential') }}" required autofocus autocomplete="username"
                            placeholder="Enter your email address" 
                            oninput="this.closest('.input-wrapper').classList.toggle('has-value', this.value.trim().length > 0)" />
                        <span class="input-icon input-icon-left">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                    </div>
                    <x-input-error :messages="$errors->get('credential')" class="mt-1 text-xs text-red-500 font-semibold" />
                </div>

                <!-- Password Input -->
                <div class="input-group">
                    <label for="password" class="input-label">Security Password</label>
                    <div class="input-wrapper">
                        <input id="password" class="custom-input" type="password" name="password" required autocomplete="current-password" placeholder="Enter password..." 
                            oninput="this.closest('.input-wrapper').classList.toggle('has-value', this.value.trim().length > 0)" />
                        <span class="input-icon input-icon-left">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <button type="button" id="togglePasswordBtn" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <svg id="eyeIcon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOffIcon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500 font-semibold" />
                </div>

                <!-- Remember Me & Forgot Password Row -->
                <div class="auth-options-row">
                    <label class="remember-checkbox-label">
                        <input type="checkbox" name="remember" />
                        <span>Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password-link">Forgot Password?</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitLoginBtn" class="login-submit-btn">
                    <span id="submitBtnText">Sign In to Portal</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Client-Side Role Management & Back Button Navigation -->
    <script>
        const roleData = {
            principal: {
                subtitle: "Principal & Administrative Governance",
                badgeText: "Principal Portal",
                credentialLabel: "Principal Email or Institutional ID",
                credentialPlaceholder: "principal@campus.edu or EMP-101",
                credentialType: "text",
                btnText: "Sign In as Principal",
                action: "{{ route('principal.login.store') }}"
            },
            faculty: {
                subtitle: "Faculty, Teachers & LMS Workspace",
                badgeText: "Faculty Portal",
                credentialLabel: "Faculty Email or Employee ID",
                credentialPlaceholder: "faculty@campus.edu or FAC-204",
                credentialType: "text",
                btnText: "Sign In to Faculty Portal",
                action: "{{ route('faculty.login.store') }}"
            },
            student: {
                subtitle: "Student Academic & Coursework Portal",
                badgeText: "Student Portal",
                credentialLabel: "Student Email Address",
                credentialPlaceholder: "student@campus.edu",
                credentialType: "email",
                btnText: "Sign In as Student",
                action: "{{ route('student.login.store') }}"
            }
        };

        let currentRole = "{{ $initialRole ?? '' }}";

        function selectRole(role) {
            currentRole = role;
            document.getElementById('roleCardsView').classList.add('hidden-state');
            document.getElementById('formView').classList.remove('hidden-state');
            document.getElementById('topLeftBackBtn').classList.remove('hidden-state');
            applyRoleTheme(role);
            updateUrl(role);
        }

        function showAllRoleCards() {
            currentRole = '';
            document.getElementById('formView').classList.add('hidden-state');
            document.getElementById('topLeftBackBtn').classList.add('hidden-state');
            document.getElementById('roleCardsView').classList.remove('hidden-state');
            
            // Reset header to default gateway
            document.getElementById('portalHeaderSubtitle').textContent = "Select your role to access your academic workspace";
            document.getElementById('portalBadgeText').textContent = "Academic Gateway";
            
            updateUrl('');
        }

        function applyRoleTheme(role) {
            const config = roleData[role] || roleData.student;
            const formView = document.getElementById('formView');
            
            // Update CSS theme class
            formView.className = 'form-view-container portal-theme-' + role;

            // Update labels, placeholders and inputs
            document.getElementById('portalHeaderSubtitle').textContent = config.subtitle;
            document.getElementById('portalBadgeText').textContent = config.badgeText;
            document.getElementById('credentialLabel').textContent = config.credentialLabel;
            
            const credentialInput = document.getElementById('credential');
            credentialInput.placeholder = config.credentialPlaceholder;
            credentialInput.type = config.credentialType;
            if (credentialInput.closest('.input-wrapper')) {
                credentialInput.closest('.input-wrapper').classList.toggle('has-value', credentialInput.value.trim().length > 0);
            }
            const pwdInput = document.getElementById('password');
            if (pwdInput && pwdInput.closest('.input-wrapper')) {
                pwdInput.closest('.input-wrapper').classList.toggle('has-value', pwdInput.value.trim().length > 0);
            }

            document.getElementById('submitBtnText').textContent = config.btnText;
            document.getElementById('formRoleInput').value = role;

            // Update form action route
            const form = document.getElementById('unifiedLoginForm');
            form.action = config.action;
        }

        function updateUrl(role) {
            const url = new URL(window.location);
            if (role) {
                url.searchParams.set('role', role);
            } else {
                url.searchParams.delete('role');
            }
            window.history.replaceState({}, '', url);
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        function checkInputHasValue(input) {
            const wrapper = input.closest('.input-wrapper');
            if (!wrapper) return;
            const hasVal = Boolean(input.value && input.value.trim().length > 0);
            if (hasVal) {
                wrapper.classList.add('has-value');
            } else {
                wrapper.classList.remove('has-value');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (currentRole && roleData[currentRole]) {
                applyRoleTheme(currentRole);
            }

            const inputs = document.querySelectorAll('.input-wrapper input');
            inputs.forEach(function(input) {
                ['input', 'keyup', 'keydown', 'change', 'paste', 'cut', 'focus', 'blur'].forEach(function(evt) {
                    input.addEventListener(evt, function() {
                        checkInputHasValue(this);
                    });
                });
                checkInputHasValue(input);
            });
        });
    </script>
</x-guest-layout>

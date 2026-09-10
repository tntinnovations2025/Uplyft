@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Onboard Faculty & Staff')
@section('breadcrumb', 'Onboard Faculty & Staff')

@section('content')
<style>
    .onboard-container {
        max-width: 880px;
        margin: 0 auto;
    }
    
    .onboard-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #334155;
        margin-bottom: 7px;
        display: block;
    }

    .form-input, .form-select {
        width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        color: #0f172a;
        padding: 11px 14px;
        font-size: 13.5px;
        font-weight: 500;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input:focus, .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .role-tile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        gap: 12px;
        margin-top: 8px;
    }

    .role-tile {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        padding: 14px 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .role-tile:hover {
        border-color: #818cf8;
        background: #eef2ff;
        transform: translateY(-1px);
    }

    .role-tile.selected {
        background: #eef2ff;
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25);
    }

    .role-tile .role-name-text {
        font-size: 13px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 4px;
    }

    .role-tile.selected .role-name-text {
        color: #4338ca !important;
    }

    .role-tile input[type="radio"] {
        display: none;
    }

    .section-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px;
    }

    .switch-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .switch-row:last-child {
        border-bottom: none;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .25s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .25s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    input:checked + .slider {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
    }

    input:checked + .slider:before {
        transform: translateX(20px);
    }

    .dual-toggle-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        padding: 4px 10px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
    }
    .toggle-pill-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .toggle-pill-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .toggle-switch-sm {
        position: relative;
        display: inline-block;
        width: 32px;
        height: 18px;
    }
    .toggle-switch-sm input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider-sm {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .25s;
        border-radius: 18px;
    }
    .slider-sm:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .25s;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .toggle-switch-sm input:checked + .slider-sm {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
    }
    .toggle-switch-sm input:checked + .slider-sm:before {
        transform: translateX(14px);
    }
</style>

<div class="onboard-container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
                🚀 Onboard Faculty &amp; Staff Member
            </h1>
            <div style="font-size:13.5px;color:#64748b;margin-top:3px;font-weight:500">
                Provision official accounts for teachers, coordinators, accountants, or custom administrative staff.
            </div>
        </div>

        <a href="{{ route('principal.staff.index') }}" class="btn btn-ghost">
            👥 View Staff Roster &rarr;
        </a>
    </div>

    <div class="onboard-card">
        <form method="POST" action="{{ route('principal.staff.store') }}" enctype="multipart/form-data" autocomplete="off">
            @csrf

            <div class="form-grid">
                <!-- Teacher Profile Picture -->
                <div class="full-width section-box" style="background:#eef2ff;border:1.5px solid #c7d2fe">
                    <label class="form-label" style="color:#312e81;font-size:12px">📸 Teacher / Staff Profile Picture * (Mandatory)</label>
                    <input type="file" name="profile_picture" accept="image/*" class="form-input" required style="background:#ffffff">
                    <span style="font-size:11.5px;color:#4338ca;margin-top:6px;display:block;font-weight:600">Upload a clean, professional profile photo (JPG, PNG, max 5MB).</span>
                    @error('profile_picture')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                </div>

                <!-- Full Name -->
                <div>
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Dr. Ahmed Hassan" value="{{ old('name') }}" class="form-input">
                    @error('name')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label class="form-label">Official Email Address *</label>
                    <input type="email" name="email" required placeholder="ahmed.hassan@school.edu.pk" value="{{ old('email') }}" class="form-input" autocomplete="off" spellcheck="false">
                    @error('email')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                </div>

                <!-- Contact Phone -->
                <div>
                    <label class="form-label">Phone Number</label>
                    <div style="display:flex;gap:6px">
                        <select id="staff_phone_country_code" name="phone_country_code" class="form-input" style="width:120px;flex-shrink:0" onchange="updateStaffPhoneValidation()">
                            <option value="+92">PK (+92)</option>
                            <option value="+1">US (+1)</option>
                            <option value="+44">GB (+44)</option>
                            <option value="+971">AE (+971)</option>
                            <option value="+966">SA (+966)</option>
                            <option value="+91">IN (+91)</option>
                            <option value="+974">QA (+974)</option>
                            <option value="+965">KW (+965)</option>
                            <option value="+968">OM (+968)</option>
                            <option value="+61">AU (+61)</option>
                        </select>
                        <input type="text" id="staff_phone" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="3001234567" maxlength="10" oninput="formatStaffPhoneInput()">
                    </div>
                    <span id="staff_phone_digit_hint" style="font-size:11px;color:#64748b;margin-top:4px;display:block;font-weight:600">Standard: 10 digits for Pakistan (+92)</span>
                </div>

                <!-- Basic Salary -->
                <div>
                    <label class="form-label">Basic Salary (PKR)</label>
                    <input type="number" name="basic_salary_pkr" step="500" placeholder="e.g. 75000" value="{{ old('basic_salary_pkr') }}" class="form-input">
                </div>

                <!-- Designation Tile Group (Dynamically Loaded from Default Authorities) -->
                <div class="full-width">
                    <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:12px;color:#0f172a">Designation / Role *</span>
                        <a href="{{ route('principal.staff.authorities') }}" target="_blank" style="font-size:11.5px;color:#4f46e5;text-decoration:none;font-weight:700">
                            ⚙️ Manage Roles &amp; Default Authorities &rarr;
                        </a>
                    </label>
                    <div class="role-tile-grid">
                        @php
                            $rolesList = isset($roleAuthorities) && $roleAuthorities->count() > 0 
                                ? $roleAuthorities 
                                : collect([
                                    (object)['role_slug' => 'teacher', 'role_name' => 'Teacher', 'permissions' => []],
                                    (object)['role_slug' => 'accountant', 'role_name' => 'Accountant', 'permissions' => []],
                                    (object)['role_slug' => 'coordinator', 'role_name' => 'Coordinator', 'permissions' => []],
                                    (object)['role_slug' => 'administration', 'role_name' => 'Administration', 'permissions' => []],
                                ]);
                        @endphp

                        @foreach($rolesList as $idx => $rAuth)
                            @php
                                $rSlug = $rAuth->role_slug;
                                $rName = $rAuth->role_name;
                                $rIcon = match($rSlug) {
                                    'teacher' => '👨‍🏫',
                                    'accountant' => '💳',
                                    'coordinator' => '📋',
                                    'administration' => '🏛️',
                                    default => '⭐'
                                };
                                $isFirst = ($idx === 0);
                            @endphp
                            <label class="role-tile {{ $isFirst ? 'selected' : '' }}" id="tile-{{ $rSlug }}" onclick="selectRole('{{ $rSlug }}')">
                                <input type="radio" name="role_option" value="{{ $rSlug }}" {{ $isFirst ? 'checked' : '' }}>
                                <div style="font-size:24px;margin-bottom:2px">{{ $rIcon }}</div>
                                <div class="role-name-text">{{ $rName }}</div>
                            </label>
                        @endforeach

                        <!-- Custom Role Option -->
                        <label class="role-tile" id="tile-custom" onclick="selectRole('custom')">
                            <input type="radio" name="role_option" value="custom">
                            <div style="font-size:24px;margin-bottom:2px">➕</div>
                            <div class="role-name-text" style="color:#4f46e5">+ Custom Role</div>
                        </label>
                    </div>
                </div>

                <!-- Conditional Custom Role Name -->
                <div class="full-width section-box" id="customRoleRow" style="display:none;background:#f0fdf4;border:1.5px solid #a7f3d0">
                    <label class="form-label" style="color:#047857;font-size:12px">⭐ Custom Role Title / Department Category *</label>
                    <input type="text" name="custom_role_name" id="custom_role_name" placeholder="e.g. Lab Assistant, Librarian, Vice Principal..." value="{{ old('custom_role_name') }}" class="form-input">
                    <span style="font-size:11.5px;color:#047857;margin-top:4px;display:block;font-weight:600">Enter the title for this custom role. It will automatically create a dedicated filter category in the Faculty &amp; Staff Directory.</span>
                    @error('custom_role_name')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                </div>

                <!-- MANDATORY ACADEMIC RESULT CERTIFICATES -->
                <div class="full-width section-box" style="background:#f0f9ff;border:1.5px solid #bae6fd">
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0369a1;margin-bottom:14px;display:flex;align-items:center;gap:8px">
                        <span>📜</span> Mandatory Academic Result Certificates (Matric, Inter &amp; Bachelors)
                    </h3>

                    <div class="form-grid">
                        <div>
                            <label class="form-label">Matric / O-Level Result Certificate *</label>
                            <input type="file" name="matriculation_cert" accept=".pdf,image/*" class="form-input" required style="background:#ffffff">
                            @error('matriculation_cert')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="form-label">Inter / A-Level Result Certificate *</label>
                            <input type="file" name="intermediate_cert" accept=".pdf,image/*" class="form-input" required style="background:#ffffff">
                            @error('intermediate_cert')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                        </div>

                        <div class="full-width">
                            <label class="form-label">Bachelors Result Certificate / Degree *</label>
                            <input type="file" name="bachelors_cert" accept=".pdf,image/*" class="form-input" required style="background:#ffffff">
                            @error('bachelors_cert')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- ADDITIONAL EDUCATIONAL DETAILS SECTION (MS & PHD) -->
                <div class="full-width section-box" style="background:#faf5ff;border:1.5px solid #e9d5ff">
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#6b21a8;margin-bottom:14px;display:flex;align-items:center;gap:8px">
                        <span>🔬</span> Additional Educational Details (MS / M.Phil &amp; PhD)
                    </h3>

                    <div class="form-grid">
                        <div>
                            <label class="form-label">MS / M.Phil Result Certificate (Optional)</label>
                            <input type="file" name="masters_cert" accept=".pdf,image/*" class="form-input" style="background:#ffffff">
                            @error('masters_cert')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="form-label">PhD Degree / Certificate (Optional)</label>
                            <input type="file" name="phd_cert" accept=".pdf,image/*" class="form-input" style="background:#ffffff">
                            @error('phd_cert')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <!-- Conditional Teacher Employment Type -->
                <div class="full-width" id="teacherTypeRow">
                    <label class="form-label">Teacher Employment Contract *</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <label style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:12px;padding:12px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;color:#0f172a">
                            <input type="radio" name="employment_type" value="permanent" checked style="accent-color:#4f46e5;width:18px;height:18px">
                            <div>
                                <div style="font-weight:800;font-size:13.5px;color:#0f172a">Permanent Faculty</div>
                                <div style="font-size:11.5px;color:#64748b;font-weight:600">Full-time salaried staff</div>
                            </div>
                        </label>
                        <label style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:12px;padding:12px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;color:#0f172a">
                            <input type="radio" name="employment_type" value="contractual" style="accent-color:#4f46e5;width:18px;height:18px">
                            <div>
                                <div style="font-weight:800;font-size:13.5px;color:#0f172a">Contractual Faculty</div>
                                <div style="font-size:11.5px;color:#64748b;font-weight:600">Visiting / hourly contractor</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Employee ID -->
                <div>
                    <label class="form-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span>Employee / Staff ID *</span>
                        <span style="color:#4338ca;font-size:10.5px;font-weight:800;text-transform:none">⚡ Auto-Generated</span>
                    </label>
                    <input type="text" name="identifier" placeholder="e.g. EMP-2026-0001" value="{{ old('identifier', $autoEmployeeId ?? '') }}" class="form-input" style="font-family:monospace;font-weight:800;color:#312e81;background:#eef2ff;border:1.5px solid #c7d2fe">
                    <span style="font-size:11px;color:#64748b;margin-top:4px;display:block;font-weight:600">Auto-assigned sequence ID for employee portal login and identification.</span>
                    @error('identifier')<span style="font-size:12px;color:#dc2626;margin-top:4px;display:block;font-weight:700">{{ $message }}</span>@enderror
                </div>

                <!-- Initial Password -->
                <div>
                    <label class="form-label">Login Password (Optional - Auto-generated if blank)</label>
                    <input type="password" name="password" placeholder="Leave blank to auto-generate" class="form-input" autocomplete="new-password">
                    <span style="font-size:11px;color:#64748b;margin-top:4px;display:block;font-weight:600">Leave blank and a strong random password will be generated for this account.</span>
                </div>

                <!-- Granular Access Rights & Portal Permissions Section -->
                <div class="full-width">
                    <div style="background:#ffffff;border:1.5px solid #c7d2fe;border-radius:18px;padding:22px;box-shadow:0 4px 16px rgba(79,70,229,0.06)">
                        <!-- Trigger Banner -->
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                            <div style="display:flex;align-items:center;gap:14px">
                                <div style="width:48px;height:48px;border-radius:14px;background:#eef2ff;border:1.5px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">
                                    ⚙️
                                </div>
                                <div>
                                    <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                        Comprehensive Granular Access Rights &amp; Portal Controls
                                    </div>
                                    <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:600" id="rights-summary-text">
                                        ⚡ Default Authorities loaded for selected role. Click to inspect or toggle granular access per feature module.
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary" id="toggle-rights-btn" onclick="toggleRightsContainer()">
                                👁️ View / ⚙️ Edit Rights &amp; Access <span id="rights-caret" style="margin-left:6px;transition:transform 0.25s">▼</span>
                            </button>
                        </div>

                        <!-- Collapsible Permissions Workspace -->
                        <div id="rights-container" style="display:none;margin-top:22px;padding-top:20px;border-top:1px solid #e2e8f0;animation:fadeIn 0.25s ease">
                            
                            <!-- Master Delegation Switch Card -->
                            <div style="background:#faf5ff;border:1.5px solid #e9d5ff;border-radius:14px;padding:16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:14px">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="font-size:26px">👑</div>
                                    <div>
                                        <div style="font-weight:800;font-size:14px;color:#581c87">Master Delegated Principal Rights</div>
                                        <div style="font-size:11.5px;color:#6b21a8;font-weight:600">Bypasses all module restrictions and grants total administrative oversight across the entire institute.</div>
                                    </div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="is_delegated_admin" value="1">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <!-- Detailed 8-Category Permission Cards Grid -->
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                                
                                <!-- 1. Search & Directory -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>🔍</span> 1. DIRECTORY, SEARCH &amp; EXPORT
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Master Directory Search</div>
                                            <div style="font-size:11px;color:#64748b">Global search for students, faculty, and roll numbers.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[directory_view]" value="1" id="perm_directory_view" onchange="syncViewEditToggle('directory')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[directory_edit]" value="1" id="perm_directory_edit" onchange="syncViewEditToggle('directory', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Export Directory &amp; Ledger Data</div>
                                            <div style="font-size:11px;color:#64748b">Export student/staff rosters to CSV/Excel formats.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[directory_export_view]" value="1" id="perm_directory_export_view" onchange="syncViewEditToggle('directory_export')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[directory_export_edit]" value="1" id="perm_directory_export_edit" onchange="syncViewEditToggle('directory_export', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Student Admissions & Records -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>🎓</span> 2. STUDENT ADMISSIONS &amp; RECORDS
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Register New Student</div>
                                            <div style="font-size:11px;color:#64748b">Access student admission &amp; registration form.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[student_registration_view]" value="1" id="perm_student_registration_view" onchange="syncViewEditToggle('student_registration')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[student_registration_edit]" value="1" id="perm_student_registration_edit" onchange="syncViewEditToggle('student_registration', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">View Enrolled Students Roster</div>
                                            <div style="font-size:11px;color:#64748b">Access enrolled student profiles.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[students_view]" value="1" id="perm_students_view" onchange="syncViewEditToggle('students')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[students_edit]" value="1" id="perm_students_edit" onchange="syncViewEditToggle('students', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Edit Student Profile &amp; Contact Info</div>
                                            <div style="font-size:11px;color:#64748b">Modify student demographic &amp; guardian details.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[students_profile_view]" value="1" id="perm_students_profile_view" onchange="syncViewEditToggle('students_profile')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[students_profile_edit]" value="1" id="perm_students_profile_edit" onchange="syncViewEditToggle('students_profile', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Fees, Billing & Financials -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>💳</span> 3. FEES, BILLING &amp; FINANCIALS
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Generate Fee Vouchers</div>
                                            <div style="font-size:11px;color:#64748b">Create monthly &amp; term fee slips.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[invoices_view]" value="1" id="perm_invoices_view" onchange="syncViewEditToggle('invoices')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[invoices_edit]" value="1" id="perm_invoices_edit" onchange="syncViewEditToggle('invoices', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Collect Payments &amp; Slips</div>
                                            <div style="font-size:11px;color:#64748b">Mark vouchers paid &amp; record transaction proof.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[invoices_collect_view]" value="1" id="perm_invoices_collect_view" onchange="syncViewEditToggle('invoices_collect')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[invoices_collect_edit]" value="1" id="perm_invoices_collect_edit" onchange="syncViewEditToggle('invoices_collect', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Faculty & Staff Governance -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>👥</span> 4. FACULTY &amp; STAFF GOVERNANCE
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Register / Onboard Staff</div>
                                            <div style="font-size:11px;color:#64748b">Onboard new faculty members &amp; degrees.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[staff_onboard_view]" value="1" id="perm_staff_onboard_view" onchange="syncViewEditToggle('staff_onboard')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[staff_onboard_edit]" value="1" id="perm_staff_onboard_edit" onchange="syncViewEditToggle('staff_onboard', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">View Staff Directory</div>
                                            <div style="font-size:11px;color:#64748b">View faculty directory &amp; basic info.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[staff_view]" value="1" id="perm_staff_view" onchange="syncViewEditToggle('staff')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[staff_edit]" value="1" id="perm_staff_edit" onchange="syncViewEditToggle('staff', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Academics & Curriculum -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>🏫</span> 5. ACADEMICS &amp; CURRICULUM
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Academic Terms &amp; Sessions</div>
                                            <div style="font-size:11px;color:#64748b">Create terms &amp; active session.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[academics_view]" value="1" id="perm_academics_view" onchange="syncViewEditToggle('academics')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[academics_edit]" value="1" id="perm_academics_edit" onchange="syncViewEditToggle('academics', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Classes &amp; Section Setup</div>
                                            <div style="font-size:11px;color:#64748b">Manage classes &amp; section letters.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[classes_view]" value="1" id="perm_classes_view" onchange="syncViewEditToggle('classes')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[classes_edit]" value="1" id="perm_classes_edit" onchange="syncViewEditToggle('classes', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Timetables & Workload -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>🗓️</span> 6. TIMETABLES &amp; WORKLOAD
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Faculty Availability &amp; Hours</div>
                                            <div style="font-size:11px;color:#64748b">Set daily teacher availability windows.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[faculty_hours_view]" value="1" id="perm_faculty_hours_view" onchange="syncViewEditToggle('faculty_hours')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[faculty_hours_edit]" value="1" id="perm_faculty_hours_edit" onchange="syncViewEditToggle('faculty_hours', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Timetable Matrix Engine</div>
                                            <div style="font-size:11px;color:#64748b">Generate automated timetable matrix.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[timetables_view]" value="1" id="perm_timetables_view" onchange="syncViewEditToggle('timetables')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[timetables_edit]" value="1" id="perm_timetables_edit" onchange="syncViewEditToggle('timetables', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. Digital LMS & AI Suite -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>📚</span> 7. DIGITAL LMS &amp; AI SUITE
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Upload Study Notes &amp; Syllabus</div>
                                            <div style="font-size:11px;color:#64748b">Publish learning resources for students.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[lms_notes_upload_view]" value="1" id="perm_lms_notes_upload_view" onchange="syncViewEditToggle('lms_notes_upload')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[lms_notes_upload_edit]" value="1" id="perm_lms_notes_upload_edit" onchange="syncViewEditToggle('lms_notes_upload', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 8. Exams, Grading & Security -->
                                <div class="section-box">
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a;margin-bottom:12px;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
                                        <span>📝</span> 8. EXAMS &amp; SECURITY
                                    </div>
                                    <div class="switch-row">
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a">Exam Datesheet Generator</div>
                                            <div style="font-size:11px;color:#64748b">Schedule examination datesheets.</div>
                                        </div>
                                        <div class="dual-toggle-box">
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#047857">👁️ View</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[exams_datesheet_view]" value="1" id="perm_exams_datesheet_view" onchange="syncViewEditToggle('exams_datesheet')">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                            <div style="width:1px;height:14px;background:#e2e8f0;margin:0 2px"></div>
                                            <div class="toggle-pill-group">
                                                <span class="toggle-pill-label" style="color:#d97706">✏️ Edit</span>
                                                <label class="toggle-switch-sm">
                                                    <input type="checkbox" name="permissions[exams_datesheet_edit]" value="1" id="perm_exams_datesheet_edit" onchange="syncViewEditToggle('exams_datesheet', true)">
                                                    <span class="slider-sm"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End of .form-grid -->

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:28px">
                <a href="{{ route('principal.staff.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px">
                    🚀 Create Staff Account &amp; Send Credentials
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const rolePermissionsMap = {
        @foreach($rolesList as $rAuth)
            '{{ $rAuth->role_slug }}': @json($rAuth->permissions ?? []),
        @endforeach
        'custom': {}
    };

    const countryPhoneSpecs = {
        '+92': { name: 'Pakistan', digits: 10, placeholder: '3001234567' },
        '+1': { name: 'USA/Canada', digits: 10, placeholder: '2025550143' },
        '+44': { name: 'UK', digits: 10, placeholder: '7911123456' },
        '+971': { name: 'UAE', digits: 9, placeholder: '501234567' },
        '+966': { name: 'Saudi Arabia', digits: 9, placeholder: '501234567' },
        '+91': { name: 'India', digits: 10, placeholder: '9876543210' },
        '+974': { name: 'Qatar', digits: 8, placeholder: '33123456' },
        '+965': { name: 'Kuwait', digits: 8, placeholder: '91234567' },
        '+968': { name: 'Oman', digits: 8, placeholder: '91234567' },
        '+61': { name: 'Australia', digits: 9, placeholder: '412345678' }
    };

    function updateStaffPhoneValidation() {
        const select = document.getElementById('staff_phone_country_code');
        const input = document.getElementById('staff_phone');
        const spec = countryPhoneSpecs[select.value] || { name: 'International', digits: 10, placeholder: '1234567890' };

        input.placeholder = spec.placeholder;
        input.maxLength = spec.digits;
        formatStaffPhoneInput();
    }

    function formatStaffPhoneInput() {
        const select = document.getElementById('staff_phone_country_code');
        const input = document.getElementById('staff_phone');
        const hint = document.getElementById('staff_phone_digit_hint');
        const spec = countryPhoneSpecs[select.value] || { name: 'International', digits: 10, placeholder: '1234567890' };

        let digits = input.value.replace(/\D/g, '');
        if (digits.length > spec.digits) {
            digits = digits.substring(0, spec.digits);
        }
        input.value = digits;

        if (digits.length === 0) {
            hint.innerHTML = `Standard: Requires ${spec.digits} digits for ${spec.name} (${select.value})`;
            hint.style.color = '#64748b';
        } else if (digits.length === spec.digits) {
            hint.innerHTML = `✓ Exact valid length for ${spec.name} (${digits.length}/${spec.digits} digits)`;
            hint.style.color = '#047857';
        } else {
            hint.innerHTML = `⚠️ Entering ${digits.length}/${spec.digits} required digits for ${spec.name}`;
            hint.style.color = '#d97706';
        }
    }

    function selectRole(roleVal) {
        document.querySelectorAll('.role-tile').forEach(tile => tile.classList.remove('selected'));
        
        const targetTile = document.getElementById(`tile-${roleVal}`);
        if (targetTile) {
            targetTile.classList.add('selected');
            const radio = targetTile.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        const teacherTypeRow = document.getElementById('teacherTypeRow');
        const customRoleRow = document.getElementById('customRoleRow');
        const customInput = document.getElementById('custom_role_name');

        if (roleVal === 'teacher') {
            if (teacherTypeRow) teacherTypeRow.style.display = 'block';
            if (customRoleRow) customRoleRow.style.display = 'none';
            if (customInput) customInput.required = false;
        } else if (roleVal === 'custom') {
            if (teacherTypeRow) teacherTypeRow.style.display = 'none';
            if (customRoleRow) customRoleRow.style.display = 'block';
            if (customInput) { customInput.required = true; customInput.focus(); }
        } else {
            if (teacherTypeRow) teacherTypeRow.style.display = 'none';
            if (customRoleRow) customRoleRow.style.display = 'none';
            if (customInput) customInput.required = false;
        }

        const defaultPerms = rolePermissionsMap[roleVal] || {};
        applyRolePermissions(defaultPerms, roleVal);
    }

    function applyRolePermissions(defaultPerms, roleVal) {
        const modules = [
            'directory',
            'directory_export',
            'student_registration',
            'students',
            'students_profile',
            'invoices',
            'invoices_collect',
            'staff_onboard',
            'staff',
            'academics',
            'classes',
            'faculty_hours',
            'timetables',
            'lms_notes_upload',
            'exams_datesheet'
        ];

        let activeCount = 0;

        modules.forEach(key => {
            const viewChk = document.getElementById(`perm_${key}_view`);
            const editChk = document.getElementById(`perm_${key}_edit`);

            if (!viewChk && !editChk) return;

            let isView = false;
            let isEdit = false;

            if (defaultPerms[key + '_edit'] !== undefined) {
                isEdit = !!defaultPerms[key + '_edit'];
            } else if (defaultPerms[key] !== undefined && defaultPerms[key] !== false && defaultPerms[key] !== 0) {
                isEdit = (defaultPerms[key] === true || defaultPerms[key] === 'edit' || defaultPerms[key] === 1);
            }

            if (defaultPerms[key + '_view'] !== undefined) {
                isView = !!defaultPerms[key + '_view'];
            } else if (defaultPerms[key] !== undefined) {
                isView = !!defaultPerms[key] || isEdit;
            } else {
                isView = isEdit;
            }

            if (viewChk) {
                viewChk.checked = isView;
                if (isView) activeCount++;
            }
            if (editChk) {
                editChk.checked = isEdit;
                if (isEdit) activeCount++;
            }
        });

        const summaryText = document.getElementById('rights-summary-text');
        if (summaryText) {
            const roleNameFormatted = roleVal ? (roleVal.charAt(0).toUpperCase() + roleVal.slice(1)) : 'Selected Role';
            summaryText.innerHTML = `⚡ Default Authorities loaded for <strong>${roleNameFormatted}</strong> (${activeCount} active permission toggles). Click to inspect or customize access per feature module.`;
        }
    }

    function syncViewEditToggle(key, isEditAction = false) {
        const viewInput = document.getElementById(`perm_${key}_view`);
        const editInput = document.getElementById(`perm_${key}_edit`);
        if (!viewInput || !editInput) return;

        if (isEditAction) {
            if (editInput.checked) {
                viewInput.checked = true;
            }
        } else {
            if (!viewInput.checked) {
                editInput.checked = false;
            }
        }
    }

    function toggleRightsContainer() {
        const container = document.getElementById('rights-container');
        const caret = document.getElementById('rights-caret');

        if (container.style.display === 'none' || container.style.display === '') {
            container.style.display = 'block';
            if (caret) caret.style.transform = 'rotate(180deg)';
        } else {
            container.style.display = 'none';
            if (caret) caret.style.transform = 'rotate(0deg)';
        }
    }

    // Apply default role permissions on initial DOM load
    document.addEventListener('DOMContentLoaded', function() {
        const selectedRadio = document.querySelector('input[name="role_option"]:checked');
        const initialRole = selectedRadio ? selectedRadio.value : 'teacher';
        selectRole(initialRole);
    });
</script>
@endsection

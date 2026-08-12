@extends('principal.layouts.app')
@section('title', 'Faculty & Staff Governance')
@section('breadcrumb', 'Faculty & Staff Governance')

@section('content')
<style>
    /* CSS Toggle Switch Component */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        vertical-align: middle;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #2e3d56;
        transition: 0.25s ease;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: #ffffff;
        transition: 0.25s ease;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #2ed573, #1eac54);
        border-color: rgba(46,213,115,0.4);
    }
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(22px);
    }
    .toggle-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        cursor: pointer;
        user-select: none;
    }

    .rights-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .rights-pill.active {
        background: rgba(46,213,115,0.15);
        color: #2ed573;
        border: 1px solid rgba(46,213,115,0.3);
    }

    /* Radio Card for Teacher Type */
    .radio-card-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 6px;
    }
    .radio-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #fff;
        transition: all 0.15s ease;
    }
    .radio-card:hover {
        border-color: var(--accent2);
    }
    .radio-card input[type="radio"] {
        accent-color: var(--accent);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Faculty & Staff Governance</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Onboard faculty with roles (Teacher, Accountant, Administration, Coordinator, Custom), select teacher employment type, and toggle ON/OFF rights per staff member.
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Left Column: Staff Roster & Rights Management Table -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">👥 Staff Roster & Rights Management</div>
                <span class="badge badge-purple">{{ $staffMembers->count() }} Total Members</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Designation / Role</th>
                        <th style="text-align:center">Master Admin</th>
                        <th style="text-align:center">Academics & Terms</th>
                        <th style="text-align:center">Timetables</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $staff)
                    @php 
                        $perms = $staff->permissions ?? []; 
                        $roleTitle = $staff->staff_role ?? ucfirst($staff->role);
                        if ($staff->employment_type) {
                            $roleTitle .= ' (' . ucfirst($staff->employment_type) . ')';
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong style="color:#fff;font-size:15px">{{ $staff->name }}</strong>
                            <div style="font-size:12px;color:var(--text-muted)">{{ $staff->email }}</div>
                            @if($staff->identifier)
                                <code style="font-size:11px;color:var(--accent2)">{{ $staff->identifier }}</code>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $staff->role === 'principal' ? 'badge-purple' : 'badge-green' }}">
                                {{ $roleTitle }}
                            </span>
                        </td>

                        <!-- Master Admin Rights Toggle -->
                        <td style="text-align:center">
                            @if($staff->role === 'teacher')
                                <form method="POST" action="{{ route('principal.staff.toggle-delegation', $staff) }}" style="display:inline-block">
                                    @csrf
                                    <label class="toggle-switch" title="Toggle Full Delegated Admin Rights">
                                        <input type="checkbox" onchange="this.form.submit()" {{ $staff->is_delegated_admin ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                                <div style="font-size:10px;margin-top:4px;font-weight:600;color:{{ $staff->is_delegated_admin ? '#2ed573' : '#94a3b8' }}">
                                    {{ $staff->is_delegated_admin ? 'ON' : 'OFF' }}
                                </div>
                            @else
                                <span class="rights-pill active">Master (Owner)</span>
                            @endif
                        </td>

                        <!-- Academics Rights Toggle -->
                        <td style="text-align:center">
                            @if($staff->role === 'teacher')
                                <form method="POST" action="{{ route('principal.staff.toggle-permission', $staff) }}" style="display:inline-block">
                                    @csrf
                                    <input type="hidden" name="permission_key" value="academics">
                                    <label class="toggle-switch" title="Toggle Academic Sessions & Classes Access">
                                        <input type="checkbox" onchange="this.form.submit()" {{ !empty($perms['academics']) ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                                <div style="font-size:10px;margin-top:4px;font-weight:600;color:{{ !empty($perms['academics']) ? '#2ed573' : '#94a3b8' }}">
                                    {{ !empty($perms['academics']) ? 'ON' : 'OFF' }}
                                </div>
                            @else
                                <span class="rights-pill active">Full Access</span>
                            @endif
                        </td>

                        <!-- Timetables Rights Toggle -->
                        <td style="text-align:center">
                            @if($staff->role === 'teacher')
                                <form method="POST" action="{{ route('principal.staff.toggle-permission', $staff) }}" style="display:inline-block">
                                    @csrf
                                    <input type="hidden" name="permission_key" value="timetables">
                                    <label class="toggle-switch" title="Toggle Timetable Matrix Scheduling Access">
                                        <input type="checkbox" onchange="this.form.submit()" {{ !empty($perms['timetables']) ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                                <div style="font-size:10px;margin-top:4px;font-weight:600;color:{{ !empty($perms['timetables']) ? '#2ed573' : '#94a3b8' }}">
                                    {{ !empty($perms['timetables']) ? 'ON' : 'OFF' }}
                                </div>
                            @else
                                <span class="rights-pill active">Full Access</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Onboard Staff/Faculty Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">➕ Onboard Faculty / Staff</div>
            </div>

            <form method="POST" action="{{ route('principal.staff.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input id="name" type="text" name="name" placeholder="e.g. Ms. Sara Ahmed" required value="{{ old('name') }}">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input id="email" type="email" name="email" placeholder="staff@school.edu.pk" required value="{{ old('email') }}">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <!-- Role Selection Dropdown -->
                <div class="form-group">
                    <label for="role_option">Role / Designation *</label>
                    <select id="role_option" name="role_option" required onchange="handleRoleChange(this.value)">
                        <option value="teacher" {{ old('role_option') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="accountant" {{ old('role_option') === 'accountant' ? 'selected' : '' }}>Accountant</option>
                        <option value="administration" {{ old('role_option') === 'administration' ? 'selected' : '' }}>Administration</option>
                        <option value="coordinator" {{ old('role_option') === 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                        <option value="custom" {{ old('role_option') === 'custom' ? 'selected' : '' }}>Custom Role</option>
                    </select>
                    @error('role_option')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <!-- Custom Role Box (Conditional) -->
                <div class="form-group" id="customRoleContainer" style="display: {{ old('role_option') === 'custom' ? 'block' : 'none' }}">
                    <label for="custom_role_name">Enter Custom Role Title *</label>
                    <input id="custom_role_name" type="text" name="custom_role_name" placeholder="e.g. HOD Science, Lab Superintendent" value="{{ old('custom_role_name') }}">
                    @error('custom_role_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <!-- Teacher Employment Type (Conditional Radio Selection) -->
                <div class="form-group" id="teacherTypeContainer" style="display: {{ old('role_option', 'teacher') === 'teacher' ? 'block' : 'none' }}">
                    <label>Teacher Employment Type * (Select One)</label>
                    <div class="radio-card-group">
                        <label class="radio-card">
                            <input type="radio" name="employment_type" value="permanent" {{ old('employment_type', 'permanent') === 'permanent' ? 'checked' : '' }}>
                            <span>Permanent</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="employment_type" value="contractual" {{ old('employment_type') === 'contractual' ? 'checked' : '' }}>
                            <span>Contractual</span>
                        </label>
                    </div>
                    @error('employment_type')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="identifier">Employee / Staff ID (Optional)</label>
                    <input id="identifier" type="text" name="identifier" placeholder="e.g. EMP#402, FAC-MATH-01" value="{{ old('identifier') }}">
                    @error('identifier')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <!-- Rights Toggles for New Staff -->
                <div style="background:var(--surface2);padding:14px;border-radius:10px;margin-bottom:20px;border:1px solid var(--border)">
                    <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px">Assign Initial Rights (ON / OFF)</div>
                    
                    <div style="display:flex;flex-direction:column;gap:10px">
                        <label class="toggle-label">
                            <span class="toggle-switch">
                                <input type="checkbox" name="is_delegated_admin" value="1">
                                <span class="toggle-slider"></span>
                            </span>
                            <span>Grant Master Delegated Admin Rights</span>
                        </label>

                        <label class="toggle-label">
                            <span class="toggle-switch">
                                <input type="checkbox" name="permissions[academics]" value="1" checked>
                                <span class="toggle-slider"></span>
                            </span>
                            <span>Academics & Terms Management</span>
                        </label>

                        <label class="toggle-label">
                            <span class="toggle-switch">
                                <input type="checkbox" name="permissions[timetables]" value="1" checked>
                                <span class="toggle-slider"></span>
                            </span>
                            <span>Timetable Matrix & Scheduling</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Create Staff Account</button>
            </form>
        </div>
    </div>
</div>

<script>
    function handleRoleChange(roleVal) {
        const teacherTypeContainer = document.getElementById('teacherTypeContainer');
        const customRoleContainer = document.getElementById('customRoleContainer');
        const customInput = document.getElementById('custom_role_name');

        if (roleVal === 'teacher') {
            teacherTypeContainer.style.display = 'block';
            customRoleContainer.style.display = 'none';
            customInput.required = false;
        } else if (roleVal === 'custom') {
            teacherTypeContainer.style.display = 'none';
            customRoleContainer.style.display = 'block';
            customInput.required = true;
        } else {
            teacherTypeContainer.style.display = 'none';
            customRoleContainer.style.display = 'none';
            customInput.required = false;
        }
    }
</script>
@endsection

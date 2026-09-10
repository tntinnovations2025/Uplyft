@extends('principal.layouts.app')
@section('title', 'Attendance Controls & Locking Window')
@section('breadcrumb', 'Attendance Controls')

@section('content')
<!-- Executive Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;font-size:11px;font-weight:800;color:#4338ca;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px">
            ⏱️ Institutional Governance
        </div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;margin:0">
            Attendance Controls &amp; Tracking Policy
        </h1>
        <p style="color:#64748b;font-size:13.5px;margin-top:4px;font-weight:500">
            Configure daily marking windows, subject-wise tracking modes, and edit lock restrictions for your faculty.
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('principal.settings.index') }}" class="btn btn-secondary" style="padding:10px 18px;border-radius:12px;font-weight:700;display:inline-flex;align-items:center;gap:8px;background:#ffffff;border:1px solid #cbd5e1;color:#334155;box-shadow:0 1px 3px rgba(0,0,0,0.03);transition:all 0.2s">
            ⚙️ Principal Settings
        </a>
    </div>
</div>

@if(session('success'))
    <div style="margin-bottom:24px;padding:14px 18px;background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:14px;color:#047857;font-size:13.5px;font-weight:700;display:flex;align-items:center;gap:10px;box-shadow:0 2px 8px rgba(16,185,129,0.08)">
        <span style="font-size:18px">✅</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@php
    $currentStatus = $setting->getLockStatusForDate(now()->format('Y-m-d'));
    $activeMode = old('attendance_mode', $setting->attendance_mode ?? 'daily');
@endphp

<div style="display:grid;grid-template-columns:minmax(0, 1.4fr) minmax(320px, 1fr);gap:24px;align-items:start">
    
    <!-- LEFT COLUMN: Settings Form Card -->
    <div class="card" style="background:#ffffff;border:1px solid rgba(226,232,240,0.95);box-shadow:0 4px 20px -2px rgba(15,23,42,0.04);border-radius:20px;padding:28px">
        <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin-bottom:22px;display:flex;align-items:center;gap:10px;padding-bottom:14px;border-bottom:1px solid #f1f5f9">
            <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:18px">
                🔒
            </div>
            <div>
                <div style="line-height:1.2">Attendance Marking Rules &amp; Schedule</div>
                <div style="font-size:11.5px;color:#64748b;font-weight:600;margin-top:2px">Set active parameters for teacher attendance logs</div>
            </div>
        </div>

        <form method="POST" action="{{ route('principal.attendance-settings.update') }}">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Attendance Tracking Mode Tiles -->
            <div class="form-group" style="margin-bottom:20px">
                <label style="font-size:11px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.6px;display:block;margin-bottom:8px">
                    ⚙️ Attendance Tracking Mode *
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    
                    {{-- Daily Attendance Tile --}}
                    <label id="mode-tile-daily" style="position:relative;display:flex;align-items:center;padding:12px 14px;background:{{ $activeMode === 'daily' ? 'linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%)' : '#ffffff' }};border:1.5px solid {{ $activeMode === 'daily' ? '#10b981' : '#e2e8f0' }};border-radius:12px;cursor:pointer;transition:all 0.22s cubic-bezier(0.16, 1, 0.3, 1);box-shadow:{{ $activeMode === 'daily' ? '0 2px 8px rgba(16,185,129,0.08)' : 'none' }}">
                        <input type="radio" name="attendance_mode" value="daily" {{ $activeMode === 'daily' ? 'checked' : '' }} onchange="updateModeTileStyles('daily')" style="accent-color:#10b981;width:16px;height:16px;cursor:pointer;margin-right:10px">
                        <span style="font-family:'Outfit',sans-serif;color:#0f172a;font-size:13px;font-weight:800">Daily Attendance System</span>
                        <span style="margin-left:auto;font-size:14px">⭕</span>
                    </label>

                    {{-- Subject / Lecture Wise Tile --}}
                    <label id="mode-tile-subject" style="position:relative;display:flex;align-items:center;padding:12px 14px;background:{{ $activeMode === 'subject' ? 'linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%)' : '#ffffff' }};border:1.5px solid {{ $activeMode === 'subject' ? '#a855f7' : '#e2e8f0' }};border-radius:12px;cursor:pointer;transition:all 0.22s cubic-bezier(0.16, 1, 0.3, 1);box-shadow:{{ $activeMode === 'subject' ? '0 2px 8px rgba(168,85,247,0.08)' : 'none' }}">
                        <input type="radio" name="attendance_mode" value="subject" {{ $activeMode === 'subject' ? 'checked' : '' }} onchange="updateModeTileStyles('subject')" style="accent-color:#a855f7;width:16px;height:16px;cursor:pointer;margin-right:10px">
                        <span style="font-family:'Outfit',sans-serif;color:#0f172a;font-size:13px;font-weight:800">Subject Wise Attendance</span>
                        <span style="margin-left:auto;font-size:14px">📚</span>
                    </label>

                </div>
            </div>

            <!-- SECTION 2: Daily Marking Hours Window -->
            <div style="margin-bottom:20px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                <div style="font-size:11px;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <span>🕒</span> Daily Marking Hours Window
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="font-size:10.5px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:4px">
                            Window Start Time *
                        </label>
                        <input type="time" name="start_time" value="{{ old('start_time', $setting->start_time) }}" required
                               style="width:100%;padding:8px 12px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:8px;color:#0f172a;outline:none;font-weight:700;font-size:13px;box-shadow:inset 0 1px 2px rgba(0,0,0,0.03);transition:all 0.15s" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>

                    <div>
                        <label style="font-size:10.5px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:4px">
                            Lock Cutoff Time *
                        </label>
                        <input type="time" name="end_time" value="{{ old('end_time', $setting->end_time) }}" required
                               style="width:100%;padding:8px 12px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:8px;color:#0f172a;outline:none;font-weight:700;font-size:13px;box-shadow:inset 0 1px 2px rgba(0,0,0,0.03);transition:all 0.15s" onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Security & Restriction Toggles -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
                
                <!-- Allow Past Date Edits Toggle -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.02)">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:28px;height:28px;border-radius:8px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
                            📅
                        </div>
                        <div style="font-size:12.5px;font-weight:800;color:#0f172a">Allow Past Edits</div>
                    </div>
                    <label style="position:relative;display:inline-block;width:36px;height:20px;flex-shrink:0;margin-left:10px">
                        <input type="checkbox" name="allow_past_edits" value="1" {{ $setting->allow_past_edits ? 'checked' : '' }} style="opacity:0;width:0;height:0" onchange="this.nextElementSibling.style.background = this.checked ? '#10b981' : '#cbd5e1'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(16px)' : 'translateX(0)'">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:{{ $setting->allow_past_edits ? '#10b981' : '#cbd5e1' }};border-radius:20px;transition:all 0.25s">
                            <span style="position:absolute;content:'';height:14px;width:14px;left:3px;bottom:3px;background:white;border-radius:50%;transition:all 0.25s;transform:{{ $setting->allow_past_edits ? 'translateX(16px)' : 'translateX(0)' }};box-shadow:0 1px 3px rgba(0,0,0,0.2)"></span>
                        </span>
                    </label>
                </div>

                <!-- Principal Force Unlock Override Toggle -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);border:1.5px solid #fde68a;border-radius:12px;box-shadow:0 1px 3px rgba(245,158,11,0.04)">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:28px;height:28px;border-radius:8px;background:#f59e0b;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;box-shadow:0 2px 4px rgba(245,158,11,0.2)">
                            ⚡
                        </div>
                        <div style="font-size:12.5px;font-weight:800;color:#92400e">Force Unlock</div>
                    </div>
                    <label style="position:relative;display:inline-block;width:36px;height:20px;flex-shrink:0;margin-left:10px">
                        <input type="checkbox" name="is_locked_override" value="1" {{ $setting->is_locked_override ? 'checked' : '' }} style="opacity:0;width:0;height:0" onchange="this.nextElementSibling.style.background = this.checked ? '#d97706' : '#cbd5e1'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(16px)' : 'translateX(0)'">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:{{ $setting->is_locked_override ? '#d97706' : '#cbd5e1' }};border-radius:20px;transition:all 0.25s">
                            <span style="position:absolute;content:'';height:14px;width:14px;left:3px;bottom:3px;background:white;border-radius:50%;transition:all 0.25s;transform:{{ $setting->is_locked_override ? 'translateX(16px)' : 'translateX(0)' }};box-shadow:0 1px 3px rgba(0,0,0,0.2)"></span>
                        </span>
                    </label>
                </div>

            </div>

            <!-- Submit Executive CTA -->
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-weight:800;background:linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);border:none;border-radius:14px;font-size:14px;box-shadow:0 4px 16px rgba(79,70,229,0.3);color:#ffffff;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                <span>💾</span> <span>Save Attendance Rules &amp; Mode</span>
            </button>
        </form>
    </div>

    <!-- RIGHT COLUMN: Live System Diagnostics & Status Card -->
    <div>
        <div class="card" style="background:#ffffff;border:1px solid rgba(226,232,240,0.95);box-shadow:0 4px 20px -2px rgba(15,23,42,0.04);border-radius:20px;padding:26px;position:sticky;top:90px">
            
            <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;padding-bottom:14px;border-bottom:1px solid #f1f5f9">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:18px">
                        📡
                    </div>
                    <div>
                        <div style="line-height:1.2">System Diagnostics</div>
                        <div style="font-size:11.5px;color:#64748b;font-weight:600;margin-top:2px">Real-time attendance window status</div>
                    </div>
                </div>
                <span style="font-size:10px;font-weight:800;background:#dcfce7;color:#15803d;padding:3px 9px;border-radius:10px;border:1px solid #bbf7d0;text-transform:uppercase;letter-spacing:0.5px">
                    LIVE
                </span>
            </div>

            <!-- Status Indicator Banner -->
            <div style="padding:14px;background:{{ $currentStatus['is_locked'] ? 'linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%)' : 'linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)' }};border:1.5px solid {{ $currentStatus['is_locked'] ? '#fecaca' : '#a7f3d0' }};border-radius:12px;margin-bottom:16px;box-shadow:0 2px 10px {{ $currentStatus['is_locked'] ? 'rgba(239,68,68,0.08)' : 'rgba(16,185,129,0.08)' }}">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:32px;height:32px;border-radius:8px;background:{{ $currentStatus['is_locked'] ? '#ef4444' : '#10b981' }};color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;box-shadow:0 3px 8px {{ $currentStatus['is_locked'] ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)' }}">
                        {{ $currentStatus['is_locked'] ? '🔒' : '🟢' }}
                    </div>
                    <div>
                        <div style="font-family:'Outfit',sans-serif;font-size:14px;font-weight:800;color:{{ $currentStatus['is_locked'] ? '#dc2626' : '#047857' }}">
                            {{ $currentStatus['is_locked'] ? 'ATTENDANCE LOCKED' : 'WINDOW OPEN' }}
                        </div>
                        <div style="font-size:11px;color:#64748b;font-weight:600">
                            {{ $currentStatus['reason'] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parameters Roster -->
            <div style="display:flex;flex-direction:column;gap:10px">
                
                {{-- Active Mode --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                    <span style="color:#64748b;font-size:12.5px;font-weight:600">Active Mode</span>
                    <span style="font-size:11.5px;font-weight:800;color:{{ $setting->attendance_mode === 'subject' ? '#7e22ce' : '#047857' }};background:{{ $setting->attendance_mode === 'subject' ? '#f3e8ff' : '#dcfce7' }};padding:3px 10px;border-radius:8px;border:1px solid {{ $setting->attendance_mode === 'subject' ? '#e9d5ff' : '#bbf7d0' }}">
                        {{ $setting->attendance_mode === 'subject' ? '📚 SUBJECT / LECTURE WISE' : '⭕ DAILY HOMEROOM' }}
                    </span>
                </div>

                {{-- Marking Hours --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                    <span style="color:#64748b;font-size:12.5px;font-weight:600">Marking Hours</span>
                    <span style="font-size:12.5px;font-weight:800;color:#0f172a">
                        {{ \Carbon\Carbon::createFromFormat('H:i', $setting->start_time)->format('g:i A') }} — {{ \Carbon\Carbon::createFromFormat('H:i', $setting->end_time)->format('g:i A') }}
                    </span>
                </div>

                {{-- Past Edits Policy --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                    <span style="color:#64748b;font-size:12.5px;font-weight:600">Past Dates Edit Policy</span>
                    <span style="font-size:11.5px;font-weight:800;color:{{ $setting->allow_past_edits ? '#047857' : '#dc2626' }};background:{{ $setting->allow_past_edits ? '#dcfce7' : '#fef2f2' }};padding:3px 10px;border-radius:8px;border:1px solid {{ $setting->allow_past_edits ? '#bbf7d0' : '#fecaca' }}">
                        {{ $setting->allow_past_edits ? 'ALLOWED' : 'LOCKED' }}
                    </span>
                </div>

                {{-- Principal Override --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                    <span style="color:#64748b;font-size:12.5px;font-weight:600">Principal Override</span>
                    <span style="font-size:11.5px;font-weight:800;color:{{ $setting->is_locked_override ? '#b45309' : '#64748b' }};background:{{ $setting->is_locked_override ? '#fef3c7' : '#f1f5f9' }};padding:3px 10px;border-radius:8px;border:1px solid {{ $setting->is_locked_override ? '#fde68a' : '#e2e8f0' }}">
                        {{ $setting->is_locked_override ? '⚡ ACTIVE (FORCE UNLOCKED)' : 'INACTIVE' }}
                    </span>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    function updateModeTileStyles(selectedMode) {
        const dailyTile = document.getElementById('mode-tile-daily');
        const subjectTile = document.getElementById('mode-tile-subject');
        
        if (selectedMode === 'daily') {
            dailyTile.style.background = 'linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%)';
            dailyTile.style.borderColor = '#10b981';
            dailyTile.style.boxShadow = '0 2px 8px rgba(16,185,129,0.08)';
            
            subjectTile.style.background = '#ffffff';
            subjectTile.style.borderColor = '#e2e8f0';
            subjectTile.style.boxShadow = 'none';
        } else {
            subjectTile.style.background = 'linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%)';
            subjectTile.style.borderColor = '#a855f7';
            subjectTile.style.boxShadow = '0 2px 8px rgba(168,85,247,0.08)';
            
            dailyTile.style.background = '#ffffff';
            dailyTile.style.borderColor = '#e2e8f0';
            dailyTile.style.boxShadow = 'none';
        }
    }
</script>
@endsection

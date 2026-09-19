@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Faculty Work Hours & Availability')
@section('breadcrumb', 'Faculty Work Hours')

@section('content')
<style>
    .avail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .teacher-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
    }
    
    .teacher-card:hover {
        border-color: #c7d2fe;
    }
    
    .teacher-card-header {
        padding: 18px 24px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        flex-wrap: wrap;
        gap: 16px;
        transition: background 0.2s ease;
    }
    
    .teacher-card-header:hover {
        background: #f8fafc;
    }
    
    .teacher-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 18px;
        color: #fff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.22);
    }

    .teacher-card.open .chevron-icon {
        transform: rotate(90deg);
    }
    
    .chevron-icon {
        transition: transform 0.2s ease;
        display: inline-block;
        color: #475569;
        font-weight: 800;
    }
    
    .teacher-card-body {
        display: none;
        padding: 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .teacher-card.open .teacher-card-body {
        display: block;
    }
    
    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 14px;
    }

    .day-box {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }

    .day-box.active {
        background: #ffffff;
        border-color: #c7d2fe;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.06);
    }

    .day-box.disabled {
        background: #f8fafc;
        opacity: 0.65;
        border-color: #cbd5e1;
    }

    .day-box.disabled .time-picker-input {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .day-name {
        font-family: 'Outfit', sans-serif;
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Sleek Switch Toggle */
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

    .time-picker-input {
        width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        color: #0f172a;
        padding: 9px 12px;
        font-size: 13.5px;
        font-weight: 600;
        outline: none;
        transition: all 0.2s;
    }

    .time-picker-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .preset-chip {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }

    .preset-chip:hover {
        background: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }
</style>

<div class="avail-header">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:25px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            ⏰ Faculty Work Hours &amp; Availability Schedule
        </h1>
        <div style="font-size:13.5px;color:#64748b;margin-top:4px;font-weight:500">
            Define working hours and active weekdays for faculty members (permanent &amp; contractual). Principals can update working days and shift hours anytime.
        </div>
    </div>

    <a href="{{ route('principal.timetables.index') }}" class="btn btn-primary">
        🗓️ Timetable &rarr;
    </a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:20px">
    <span>✓ {{ session('success') }}</span>
</div>
@endif

<!-- Global Faculty Working Hours Setup Card (Individual Day Timings) -->
<div class="card" style="margin-bottom:20px;border:1.5px solid #c7d2fe;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%);padding:18px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:9px;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 4px 10px rgba(79,70,229,0.22)">
                ⚡
            </div>
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:800;color:#0f172a;margin:0">
                    Global Faculty Working Hours &amp; Individual Day Timings Setup
                </h3>
                <div style="font-size:11.5px;color:#4338ca;font-weight:600">
                    Configure daily shift hours per weekday (e.g. Mon–Thu Full Day, Friday Half Day) and apply to all faculty.
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px">
            <button type="button" class="btn btn-ghost btn-sm" onclick="setPakistanGlobalPreset()" style="font-size:11.5px;border:1px solid #c7d2fe;background:#ffffff;color:#0f172a;padding:5px 12px">
                🇵🇰 Pakistan Preset (Mon-Thu Full, Fri Half)
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="applyGlobalWorkHoursToAll()" style="font-size:11.5px;padding:6px 14px">
                ⚡ Apply to All Faculty
            </button>
        </div>
    </div>

    <!-- Individual Day Shift Rows Grid in Global Setup -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:10px;margin-bottom:12px">
        @php
            $defaultDayConfigs = [
                'monday'    => ['name' => 'Monday', 'tag' => 'Full Day', 'start' => '08:00', 'end' => '14:00', 'bStart' => '12:00', 'bEnd' => '12:30', 'checked' => true],
                'tuesday'   => ['name' => 'Tuesday', 'tag' => 'Full Day', 'start' => '08:00', 'end' => '14:00', 'bStart' => '12:00', 'bEnd' => '12:30', 'checked' => true],
                'wednesday' => ['name' => 'Wednesday', 'tag' => 'Full Day', 'start' => '08:00', 'end' => '14:00', 'bStart' => '12:00', 'bEnd' => '12:30', 'checked' => true],
                'thursday'  => ['name' => 'Thursday', 'tag' => 'Full Day', 'start' => '08:00', 'end' => '14:00', 'bStart' => '12:00', 'bEnd' => '12:30', 'checked' => true],
                'friday'    => ['name' => 'Friday', 'tag' => '🕌 Half Day', 'start' => '08:00', 'end' => '12:30', 'bStart' => '', 'bEnd' => '', 'checked' => true],
                'saturday'  => ['name' => 'Saturday', 'tag' => 'Half/Off', 'start' => '08:00', 'end' => '13:00', 'bStart' => '', 'bEnd' => '', 'checked' => true],
                'sunday'    => ['name' => 'Sunday', 'tag' => 'Weekend', 'start' => '08:00', 'end' => '14:00', 'bStart' => '', 'bEnd' => '', 'checked' => false],
            ];
        @endphp

        @foreach($defaultDayConfigs as $dKey => $dConf)
        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px" id="global-day-box-{{ $dKey }}">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;font-weight:800;color:#0f172a;user-select:none">
                    <input type="checkbox" id="global_weekday_{{ $dKey }}" value="{{ $dKey }}" {{ $dConf['checked'] ? 'checked' : '' }} onchange="onGlobalWeekdayToggle('{{ $dKey }}')" style="accent-color:#4f46e5;width:14px;height:14px;margin:0">
                    <span>{{ $dConf['name'] }}</span>
                </label>
                <span style="font-size:10px;font-weight:700;color:{{ $dKey === 'friday' ? '#d97706' : '#059669' }};background:{{ $dKey === 'friday' ? '#fffbeb' : '#ecfdf5' }};padding:1px 6px;border-radius:5px" id="global-day-tag-{{ $dKey }}">
                    {{ $dConf['tag'] }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                <div>
                    <label style="font-size:9px;color:#64748b;font-weight:700;text-transform:uppercase;display:block;margin-bottom:2px">Start</label>
                    <input type="time" id="global_start_{{ $dKey }}" value="{{ $dConf['start'] }}" class="time-picker-input" style="padding:4px 6px;font-size:11.5px">
                </div>
                <div>
                    <label style="font-size:9px;color:#64748b;font-weight:700;text-transform:uppercase;display:block;margin-bottom:2px">End</label>
                    <input type="time" id="global_end_{{ $dKey }}" value="{{ $dConf['end'] }}" class="time-picker-input" style="padding:4px 6px;font-size:11.5px">
                </div>
            </div>
            <input type="hidden" id="global_bstart_{{ $dKey }}" value="{{ $dConf['bStart'] }}">
            <input type="hidden" id="global_bend_{{ $dKey }}" value="{{ $dConf['bEnd'] }}">
        </div>
        @endforeach
    </div>
</div>

@if($teachers->count() > 0)
    <div style="margin-bottom:16px;color:#64748b;font-size:13px;display:flex;align-items:center;justify-content:space-between">
        <span>Showing <strong style="color:#0f172a">{{ $teachers->count() }}</strong> Faculty Members</span>
        <button type="button" class="btn btn-ghost btn-sm" onclick="toggleAllCards()" style="font-size:12px">
            ↔️ Expand / Collapse All
        </button>
    </div>

    @foreach($teachers as $teacher)
    @php
        $teacherAvails = $teacher->availabilities->keyBy('day_of_week');
        $activeDaysCount = $teacher->availabilities->where('is_available', true)->count();
    @endphp

    <div class="teacher-card" id="teacher-card-{{ $teacher->id }}">
        <!-- Clickable Header -->
        <div class="teacher-card-header" onclick="toggleTeacherCard({{ $teacher->id }})">
            <div style="display:flex;align-items:center;gap:14px">
                <span class="chevron-icon" id="chevron-{{ $teacher->id }}">▶</span>
                <div class="teacher-avatar">
                    {{ strtoupper(substr($teacher->name, 0, 1)) }}
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">
                        {{ $teacher->name }}
                    </h3>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                        ✉️ {{ $teacher->email }} &bull; <span style="color:#059669;font-weight:700">{{ ucfirst($teacher->employment_type ?? 'Full Time') }} Faculty</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px" onclick="event.stopPropagation()">
                <span class="badge badge-purple" style="font-size:12px;padding:6px 12px">
                    🗓️ <span id="teacher-active-days-{{ $teacher->id }}">{{ $activeDaysCount }}</span> Working Days
                </span>
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleTeacherCard({{ $teacher->id }})" style="font-size:12px">
                    ⚙️ Edit Hours
                </button>
            </div>
        </div>

        <!-- Collapsible Body -->
        <div class="teacher-card-body">
            <form method="POST" action="{{ route('principal.teachers.availability.store') }}" id="form-teacher-{{ $teacher->id }}">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

                <div class="schedule-grid">
                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $idx => $day)
                    @php
                        $avail = $teacherAvails->get($day);
                        $isAvailable = $avail ? (bool)$avail->is_available : ($day !== 'sunday');
                        $startTime = $avail ? date('H:i', strtotime($avail->start_time)) : '08:00';
                        $endTime   = $avail ? date('H:i', strtotime($avail->end_time))   : '14:30';
                        $breakStartTime = $avail && $avail->break_start_time ? date('H:i', strtotime($avail->break_start_time)) : '';
                        $breakEndTime   = $avail && $avail->break_end_time ? date('H:i', strtotime($avail->break_end_time)) : '';
                    @endphp

                    <div class="day-box {{ $isAvailable ? 'active' : 'disabled' }}" id="day-box-{{ $teacher->id }}-{{ $day }}">
                        <div class="day-header">
                            <span class="day-name">{{ ucfirst($day) }}</span>

                            <label class="switch">
                                <input type="hidden" name="availabilities[{{ $idx }}][day_of_week]" value="{{ $day }}">
                                <input type="hidden" name="availabilities[{{ $idx }}][is_available]" value="0">
                                <input type="checkbox" 
                                       name="availabilities[{{ $idx }}][is_available]" 
                                       value="1" 
                                       {{ $isAvailable ? 'checked' : '' }}
                                       onchange="toggleDayState({{ $teacher->id }}, '{{ $day }}', this)">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <!-- Preset Chips -->
                        <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap">
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '08:00', '14:30', '13:00', '13:30')">Standard</span>
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '08:00', '12:30', '', '')">Half-Day</span>
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '09:00', '16:00', '13:00', '14:00')">Late Shift</span>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:8px">
                            <div>
                                <label style="font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase">Shift Start</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][start_time]" 
                                       id="start-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $startTime }}" 
                                       class="time-picker-input">
                            </div>

                            <div>
                                <label style="font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase">Shift End</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][end_time]" 
                                       id="end-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $endTime }}" 
                                       class="time-picker-input">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;background:#f8fafc;padding:6px 8px;border-radius:8px;border:1px dashed #cbd5e1">
                            <div>
                                <label style="font-size:9.5px;font-weight:700;color:#e11d48;display:block;margin-bottom:3px;text-transform:uppercase">☕ Break Start</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][break_start_time]" 
                                       id="break-start-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $breakStartTime }}" 
                                       placeholder="--:--"
                                       class="time-picker-input"
                                       style="padding:6px 8px;font-size:12px">
                            </div>

                            <div>
                                <label style="font-size:9.5px;font-weight:700;color:#e11d48;display:block;margin-bottom:3px;text-transform:uppercase">☕ Break End</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][break_end_time]" 
                                       id="break-end-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $breakEndTime }}" 
                                       placeholder="--:--"
                                       class="time-picker-input"
                                       style="padding:6px 8px;font-size:12px">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="toggleTeacherCard({{ $teacher->id }})" style="margin-right:8px">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:8px 20px">
                        💾 Save {{ $teacher->name }}'s Hours
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@else
<div class="card" style="text-align:center;padding:50px 20px">
    <div style="font-size:36px;margin-bottom:10px">👨‍🏫</div>
    <h3 style="color:#0f172a;font-weight:800">No Faculty Registered Yet</h3>
    <p style="color:#64748b;font-size:14px;max-width:400px;margin:8px auto 20px">Please onboard faculty members first in the Faculty &amp; Staff Roster menu.</p>
    <a href="{{ route('principal.staff.index') }}" class="btn btn-primary">
        👥 Go to Faculty Roster
    </a>
</div>
@endif

<script>
    function toggleTeacherCard(teacherId) {
        const card = document.getElementById(`teacher-card-${teacherId}`);
        if (card) {
            card.classList.toggle('open');
        }
    }

    function toggleAllCards() {
        const cards = document.querySelectorAll('.teacher-card');
        const anyClosed = Array.from(cards).some(c => !c.classList.contains('open'));
        cards.forEach(c => {
            if (anyClosed) {
                c.classList.add('open');
            } else {
                c.classList.remove('open');
            }
        });
    }

    function toggleDayState(teacherId, day, checkbox) {
        const box = document.getElementById(`day-box-${teacherId}-${day}`);
        if (checkbox.checked) {
            box.classList.add('active');
            box.classList.remove('disabled');
        } else {
            box.classList.remove('active');
            box.classList.add('disabled');
        }
    }

    function setPreset(teacherId, day, start, end, breakStart = '', breakEnd = '') {
        const startInput = document.getElementById(`start-${teacherId}-${day}`);
        const endInput = document.getElementById(`end-${teacherId}-${day}`);
        const breakStartInput = document.getElementById(`break-start-${teacherId}-${day}`);
        const breakEndInput = document.getElementById(`break-end-${teacherId}-${day}`);
        const checkbox = document.querySelector(`#day-box-${teacherId}-${day} input[type="checkbox"]`);
        
        if (startInput && endInput) {
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
                toggleDayState(teacherId, day, checkbox);
            }
            startInput.value = start;
            endInput.value = end;
            if (breakStartInput) breakStartInput.value = breakStart;
            if (breakEndInput) breakEndInput.value = breakEnd;
        }
    }

    function setPakistanGlobalPreset() {
        const pkConfig = {
            'monday':    { start: '08:00', end: '14:00', bStart: '12:00', bEnd: '12:30', active: true },
            'tuesday':   { start: '08:00', end: '14:00', bStart: '12:00', bEnd: '12:30', active: true },
            'wednesday': { start: '08:00', end: '14:00', bStart: '12:00', bEnd: '12:30', active: true },
            'thursday':  { start: '08:00', end: '14:00', bStart: '12:00', bEnd: '12:30', active: true },
            'friday':    { start: '08:00', end: '12:30', bStart: '',      bEnd: '',      active: true },
            'saturday':  { start: '08:00', end: '13:00', bStart: '',      bEnd: '',      active: true },
            'sunday':    { start: '08:00', end: '14:00', bStart: '',      bEnd: '',      active: false }
        };

        Object.keys(pkConfig).forEach(day => {
            const conf = pkConfig[day];
            const chk = document.getElementById(`global_weekday_${day}`);
            const sIn = document.getElementById(`global_start_${day}`);
            const eIn = document.getElementById(`global_end_${day}`);
            const bsIn = document.getElementById(`global_bstart_${day}`);
            const beIn = document.getElementById(`global_bend_${day}`);

            if (chk) chk.checked = conf.active;
            if (sIn) sIn.value = conf.start;
            if (eIn) eIn.value = conf.end;
            if (bsIn) bsIn.value = conf.bStart;
            if (beIn) beIn.value = conf.bEnd;
            onGlobalWeekdayToggle(day);
        });

        alert('🇵🇰 Pakistan Standard Schedule Preset loaded (Mon–Thu: 08:00-14:00, Fri: 08:00-12:30). Click "Apply to All Faculty" to update faculty shifts.');
    }

    function applyGlobalWorkHoursToAll() {
        const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        weekdays.forEach(day => {
            const chkElem = document.getElementById(`global_weekday_${day}`);
            const isDayChecked = chkElem ? chkElem.checked : true;
            const startTime = document.getElementById(`global_start_${day}`) ? document.getElementById(`global_start_${day}`).value : '08:00';
            const endTime = document.getElementById(`global_end_${day}`) ? document.getElementById(`global_end_${day}`).value : '14:00';
            const breakStart = document.getElementById(`global_bstart_${day}`) ? document.getElementById(`global_bstart_${day}`).value : '';
            const breakEnd = document.getElementById(`global_bend_${day}`) ? document.getElementById(`global_bend_${day}`).value : '';
            
            document.querySelectorAll(`.day-box[id$="-${day}"]`).forEach(box => {
                const checkbox = box.querySelector('input[type="checkbox"][name*="[is_available]"]');
                const startInput = box.querySelector('input[name*="[start_time]"]');
                const endInput = box.querySelector('input[name*="[end_time]"]');
                const breakStartInput = box.querySelector('input[name*="[break_start_time]"]');
                const breakEndInput = box.querySelector('input[name*="[break_end_time]"]');

                if (checkbox) {
                    checkbox.checked = isDayChecked;
                    const parts = box.id.split('-');
                    const teacherId = parts[2];
                    toggleDayState(teacherId, day, checkbox);
                }

                if (startInput) startInput.value = startTime;
                if (endInput) endInput.value = endTime;
                if (breakStartInput) breakStartInput.value = breakStart;
                if (breakEndInput) breakEndInput.value = breakEnd;
            });
        });

        alert('✅ Individual Day Timings applied across all faculty members! Click "Save Hours" on the teacher cards to persist.');
    }

    function onGlobalWeekdayToggle(day) {
        const chk = document.getElementById(`global_weekday_${day}`);
        const box = document.getElementById(`global-day-box-${day}`);
        if (chk && box) {
            box.style.borderColor = chk.checked ? '#c7d2fe' : '#e2e8f0';
            box.style.background = chk.checked ? '#ffffff' : '#f8fafc';
            box.style.opacity = chk.checked ? '1' : '0.6';
        }
    }
</script>
@endsection

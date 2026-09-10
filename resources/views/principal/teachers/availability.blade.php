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
        🗓️ Timetable &amp; Master Matrix &rarr;
    </a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:20px">
    <span>✓ {{ session('success') }}</span>
</div>
@endif

<!-- Global Faculty Working Hours Setup Card -->
<div class="card" style="margin-bottom:24px;border:1.5px solid #c7d2fe;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:38px;height:38px;border-radius:10px;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 4px 12px rgba(79,70,229,0.25)">
                ⚡
            </div>
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">
                    Global Faculty Working Hours &amp; Weekday Schedule Setup
                </h3>
                <div style="font-size:12px;color:#4338ca;font-weight:600">
                    Quickly configure standard working hours &amp; active weekdays for all faculty members at once.
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary" onclick="applyGlobalWorkHoursToAll()">
            ⚡ Apply Schedule to All Faculty
        </button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;background:#ffffff;padding:16px;border-radius:12px;border:1px solid #cbd5e1">
        <div>
            <label class="form-label" style="color:#0f172a;font-size:11.5px">Standard Shift Start Time *</label>
            <input type="time" id="global_start_time" value="08:00" class="time-picker-input">
        </div>
        <div>
            <label class="form-label" style="color:#0f172a;font-size:11.5px">Standard Shift End Time *</label>
            <input type="time" id="global_end_time" value="14:30" class="time-picker-input">
        </div>
    </div>

    <div>
        <label class="form-label" style="color:#0f172a;font-size:11.5px;margin-bottom:8px">Working Weekdays (Uncheck to Disable Working Hours for that day):</label>
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            @php $allWeekdays = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday']; @endphp
            @foreach($allWeekdays as $wKey => $wLabel)
                <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;cursor:pointer;font-size:13px;font-weight:700;color:#0f172a;user-select:none" id="global-weekday-lbl-{{ $wKey }}">
                    <input type="checkbox" id="global_weekday_{{ $wKey }}" value="{{ $wKey }}" {{ in_array($wKey, ['monday','tuesday','wednesday','thursday','friday','saturday']) ? 'checked' : '' }} onchange="onGlobalWeekdayToggle('{{ $wKey }}')" style="width:16px!important;height:16px!important;accent-color:#4f46e5;margin:0!important">
                    {{ $wLabel }}
                </label>
            @endforeach
        </div>
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
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '08:00', '14:30')">Standard</span>
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '08:00', '12:30')">Half-Day</span>
                            <span class="preset-chip" onclick="setPreset('{{ $teacher->id }}', '{{ $day }}', '09:00', '16:00')">Late Shift</span>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                            <div>
                                <label style="font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase">Start Time</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][start_time]" 
                                       id="start-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $startTime }}" 
                                       class="time-picker-input">
                            </div>

                            <div>
                                <label style="font-size:10px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;text-transform:uppercase">End Time</label>
                                <input type="time" 
                                       name="availabilities[{{ $idx }}][end_time]" 
                                       id="end-{{ $teacher->id }}-{{ $day }}" 
                                       value="{{ $endTime }}" 
                                       class="time-picker-input">
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

    function setPreset(teacherId, day, start, end) {
        const startInput = document.getElementById(`start-${teacherId}-${day}`);
        const endInput = document.getElementById(`end-${teacherId}-${day}`);
        const checkbox = document.querySelector(`#day-box-${teacherId}-${day} input[type="checkbox"]`);
        
        if (startInput && endInput) {
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
                toggleDayState(teacherId, day, checkbox);
            }
            startInput.value = start;
            endInput.value = end;
        }
    }

    function applyGlobalWorkHoursToAll() {
        const startTime = document.getElementById('global_start_time').value;
        const endTime = document.getElementById('global_end_time').value;
        const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        weekdays.forEach(day => {
            const isDayChecked = document.getElementById(`global_weekday_${day}`).checked;
            
            document.querySelectorAll(`.day-box[id$="-${day}"]`).forEach(box => {
                const checkbox = box.querySelector('input[type="checkbox"][name*="[is_available]"]');
                const startInput = box.querySelector('input[name*="[start_time]"]');
                const endInput = box.querySelector('input[name*="[end_time]"]');

                if (checkbox) {
                    checkbox.checked = isDayChecked;
                    const parts = box.id.split('-');
                    const teacherId = parts[2];
                    toggleDayState(teacherId, day, checkbox);
                }

                if (startInput) startInput.value = startTime;
                if (endInput) endInput.value = endTime;
            });
        });

        alert('✅ Global Faculty Work Hours applied! Click "Save Hours" on the teacher card to persist changes to the database.');
    }

    function onGlobalWeekdayToggle(day) {
        const chk = document.getElementById(`global_weekday_${day}`);
        const lbl = document.getElementById(`global-weekday-lbl-${day}`);
        if (chk && lbl) {
            lbl.style.borderColor = chk.checked ? '#4f46e5' : '#cbd5e1';
            lbl.style.background = chk.checked ? '#eef2ff' : '#ffffff';
        }
    }
</script>
@endsection

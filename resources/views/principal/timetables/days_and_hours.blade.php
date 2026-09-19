@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@section('title', 'Class Days & Work Hours Allocation')
@section('breadcrumb', 'Days & Hours Allocation Management')

@section('content')
@php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }

    $allWeekdays = [
        'monday'    => ['label' => 'Mon', 'fullName' => 'Monday', 'timing' => 'Full Day', 'hours' => '08:00 - 14:00', 'tag' => 'Full'],
        'tuesday'   => ['label' => 'Tue', 'fullName' => 'Tuesday', 'timing' => 'Full Day', 'hours' => '08:00 - 14:00', 'tag' => 'Full'],
        'wednesday' => ['label' => 'Wed', 'fullName' => 'Wednesday', 'timing' => 'Full Day', 'hours' => '08:00 - 14:00', 'tag' => 'Full'],
        'thursday'  => ['label' => 'Thu', 'fullName' => 'Thursday', 'timing' => 'Full Day', 'hours' => '08:00 - 14:00', 'tag' => 'Full'],
        'friday'    => ['label' => 'Fri', 'fullName' => 'Friday', 'timing' => 'Half Day', 'hours' => '08:00 - 12:30', 'tag' => 'Half'],
        'saturday'  => ['label' => 'Sat', 'fullName' => 'Saturday', 'timing' => 'Half / Off', 'hours' => '08:00 - 13:00', 'tag' => 'Custom'],
    ];

    // Group assignments by class first, then by section
    $assignmentsByClass = $assignments->groupBy(function($a) {
        return $a->section?->institute_class_id ?? 0;
    });

    $defaultClassId = $selectedClassId ?: ($classes->first()?->id ?? 'all');
@endphp

<style>
    /* Compact, sleek styling matching platform design */
    .class-switch-btn {
        padding: 6px 14px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
        white-space: nowrap;
    }
    .class-switch-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .class-switch-btn.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
    }
    .class-switch-btn.active .class-badge {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }
    .class-badge {
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 6px;
        background: #e2e8f0;
        color: #475569;
        font-weight: 800;
    }
    
    .timing-day-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }

    .alloc-card-compact {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 13px 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.025);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .alloc-card-compact:hover {
        border-color: #94a3b8;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .day-checkbox-pill {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 7px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
        user-select: none;
        transition: all 0.15s;
    }
    .day-checkbox-pill.avail {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        cursor: pointer;
    }
    .day-checkbox-pill.avail:hover {
        background: #d1fae5;
        border-color: #6ee7b7;
    }
    .day-checkbox-pill.unavail {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-control-compact {
        width: 100%;
        padding: 5px 8px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        color: #0f172a;
        font-size: 11.5px;
        font-weight: 600;
        outline: none;
        transition: border-color 0.15s;
    }
    .form-control-compact:focus {
        border-color: #4f46e5;
    }
</style>

<!-- HEADER TITLE & TOP MENU BAR -->
<div style="margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px;letter-spacing:-0.4px;margin:0">
                ⚙️ Class Days &amp; Work Hours Allocation
            </h1>
            <p style="color:#64748b;font-size:12px;margin-top:2px;font-weight:500">
                Configure permitted lecture days, duration, and weekly periods per subject in <strong>{{ $activeTerm?->name }}</strong>.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <a href="{{ route($routePrefix . 'teachers.availability.index') }}" 
               class="btn" style="border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe">
                ⏰ Faculty Work Hours
            </a>
            @if(auth()->user()->hasPermission('timetables', 'edit'))
                <form id="generateTimetableForm" method="POST" action="{{ route($routePrefix . 'timetables.generate') }}" style="display:inline">
                    @csrf
                    <button type="button" onclick="openClassyConfirmModal()" class="btn btn-primary" style="border-radius:8px;padding:6px 14px;font-size:11.5px;font-weight:700">
                        ⚡ Generate Timetable
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- SINGLE UNIFIED TOP NAVIGATION -->
    <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:11px;padding:8px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            @php
                $myDaysSlots = \App\Models\Timetable::whereHas('section.instituteClass', fn($q) => $q->where('institute_id', auth()->user()->institute_id))
                    ->where('teacher_id', auth()->id())->limit(1)->count();
            @endphp
            @if($myDaysSlots > 0)
            <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'my']) }}" 
               class="btn" style="border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;background:#F2EFEB;color:#68665D;border:1px solid #E1DFD7">
                👨‍🏫 My Lectures
            </a>
            @endif
            <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'section']) }}" 
               class="btn" style="border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;background:#F2EFEB;color:#68665D;border:1px solid #E1DFD7">
                📂 Section Wise
            </a>
            <a href="{{ route($routePrefix . 'timetables.grid') }}" 
               class="btn" style="border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700;background:#F2EFEB;color:#68665D;border:1px solid #E1DFD7">
                📊 Master Grid
            </a>
            <a href="{{ route($routePrefix . 'timetables.days-and-hours') }}" 
               class="btn btn-primary" style="border-radius:8px;padding:6px 12px;font-size:11.5px;font-weight:700">
                ⚙️ Days &amp; Work Hours Allocation
            </a>
        </div>
        <div style="font-size:11.5px;color:#059669;font-weight:700;display:flex;align-items:center;gap:6px">
            <span>🟢 Teacher Available</span>
            <span style="color:#cbd5e1">|</span>
            <span style="color:#ef4444">🔴 Unavailable (Off)</span>
        </div>
    </div>
</div>

<!-- INDIVIDUAL DAILY SCHEDULE TIMING BANNER (Mon-Thu Full Day, Friday Half Day) -->
<div class="card" style="margin-bottom:16px;padding:12px 16px;background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border:1px solid #cbd5e1;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:15px">🇵🇰</span>
            <span style="font-size:12px;font-weight:800;color:#0f172a">Individual Day Working Schedule:</span>
            <span style="font-size:11px;color:#64748b;font-weight:500">Each day's timings are individual (Mon–Thu Full Day, Fri Half Day)</span>
        </div>
        <a href="{{ route($routePrefix . 'teachers.availability.index') }}" style="font-size:11px;font-weight:700;color:#4f46e5;text-decoration:none">
            Customize Day Shifts &rarr;
        </a>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
        <div class="timing-day-chip" style="border-left:3px solid #059669">
            <strong style="color:#0f172a">Mon – Thu:</strong>
            <span style="color:#059669;font-weight:700">Full Day</span>
            <span style="color:#64748b;font-size:10.5px">(08:00 – 14:00)</span>
        </div>
        <div class="timing-day-chip" style="border-left:3px solid #d97706;background:#fffbeb">
            <strong style="color:#0f172a">Friday:</strong>
            <span style="color:#d97706;font-weight:700">🕌 Half Day</span>
            <span style="color:#64748b;font-size:10.5px">(08:00 – 12:30)</span>
        </div>
        <div class="timing-day-chip" style="border-left:3px solid #64748b">
            <strong style="color:#0f172a">Saturday:</strong>
            <span style="color:#475569;font-weight:700">Half / Extra</span>
            <span style="color:#64748b;font-size:10.5px">(08:00 – 13:00)</span>
        </div>
        <div class="timing-day-chip" style="border-left:3px solid #ef4444;background:#fef2f2">
            <strong style="color:#0f172a">Sunday:</strong>
            <span style="color:#ef4444;font-weight:700">Off / Weekend</span>
        </div>
    </div>
</div>

<!-- CLASS SWITCHER & SEARCH TOOLBAR (TOP CLASS SELECTOR) -->
<div class="card" style="margin-bottom:16px;padding:12px 16px;background:#ffffff;border:1px solid #cbd5e1;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:10px">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:13px;font-weight:800;color:#0f172a">🏫 Select Class:</span>
            <span style="font-size:11.5px;color:#64748b">Click a class to view only its subjects</span>
        </div>
        <!-- Search Input -->
        <div style="flex:1;max-width:340px">
            <input type="text" id="allocationSearchInput" onkeyup="filterAllocations()" 
                   placeholder="Search subject, code, or teacher..." 
                   style="padding:6px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;width:100%;outline:none">
        </div>
    </div>

    <!-- Class Switcher Buttons -->
    <div style="display:flex;align-items:center;gap:8px;overflow-x:auto;padding-bottom:4px" id="classSwitcherContainer">
        <button type="button" 
                class="class-switch-btn {{ $defaultClassId === 'all' ? 'active' : '' }}" 
                onclick="switchAllocationClass('all', this)">
            <span>🌐 All Classes</span>
            <span class="class-badge">{{ $assignments->count() }}</span>
        </button>

        @foreach($classes as $cls)
            @php
                $clsAssignCount = $assignmentsByClass->get($cls->id, collect())->count();
                $isActiveClass = ((string)$defaultClassId === (string)$cls->id);
            @endphp
            <button type="button" 
                    class="class-switch-btn {{ $isActiveClass ? 'active' : '' }}" 
                    data-class-id="{{ $cls->id }}"
                    onclick="switchAllocationClass('{{ $cls->id }}', this)">
                <span>🏫 {{ $cls->name }}</span>
                <span class="class-badge">{{ $clsAssignCount }}</span>
            </button>
        @endforeach
    </div>
</div>

@php
    $groupedAssignments = $assignments->groupBy(function($a) {
        $cName = $a->section->instituteClass->custom_name ?? ($a->section->instituteClass->name ?? 'Class');
        $sName = $a->section->section_name ?? 'Section';
        return "{$cName} — Section {$sName}";
    });
@endphp

<!-- CLASS SECTIONS & SUBJECT ALLOCATIONS -->
<div id="allocationCardsWrapper">
@forelse($classes as $cls)
    @php
        $clsAllocations = $assignmentsByClass->get($cls->id, collect());
        $isClassVisible = ($defaultClassId === 'all' || (string)$defaultClassId === (string)$cls->id);
    @endphp
    
    <div class="class-allocation-group" 
         id="class_group_{{ $cls->id }}" 
         data-class-id="{{ $cls->id }}"
         style="display: {{ $isClassVisible ? 'block' : 'none' }}; margin-bottom:18px">
        
        <!-- Class Section Banner -->
        <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:10px 14px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:18px">🏫</span>
                <div>
                    <h2 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;display:inline">{{ $cls->name }}</h2>
                    <span style="font-size:11.5px;color:#64748b;margin-left:6px;font-weight:500">
                        ({{ $clsAllocations->count() }} Allocated Subject{{ $clsAllocations->count() === 1 ? '' : 's' }})
                    </span>
                </div>
            </div>
            <span class="badge badge-purple" style="font-size:11px;padding:3px 10px;font-weight:700">
                {{ $cls->sections->count() }} Section(s)
            </span>
        </div>

        @if($clsAllocations->isEmpty())
            <div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:10px;padding:24px;text-align:center;color:#64748b;font-size:12.5px">
                No subject allocations found for <strong>{{ $cls->name }}</strong> in this academic term.
            </div>
        @else
            <!-- Subjects Cards Grid for this Class -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:12px">
                @foreach($clsAllocations as $alloc)
                    @php
                        $tAvails = $alloc->teacher ? $alloc->teacher->availabilities->keyBy(fn($a) => strtolower($a->day_of_week)) : collect();
                        $searchKeyword = strtolower("{$alloc->subject->subject_name} {$alloc->subject->subject_code} " . ($alloc->teacher->name ?? '') . " " . ($alloc->section->section_name ?? ''));
                        $sectionTitle = $alloc->section->section_name ?? 'A';
                    @endphp
                    <div class="alloc-card-compact allocation-item-card" data-keyword="{{ $searchKeyword }}">
                        <form method="POST" action="{{ route($routePrefix . 'timetables.allocations.update', $alloc->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Header: Subject & Teacher Info -->
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-bottom:8px">
                                <div>
                                    <div style="font-weight:800;color:#0f172a;font-size:13.5px;display:flex;align-items:center;gap:6px">
                                        <span>📘 {{ $alloc->subject->subject_name }}</span>
                                        <code style="font-size:10px;color:#D48A2E;background:#FBF3E8;padding:1px 5px;border-radius:4px;font-weight:700;border:1px solid #E8CEAA">
                                            {{ $alloc->subject->subject_code ?: 'SUB' }}
                                        </code>
                                    </div>
                                    <div style="font-size:11.5px;color:#475569;margin-top:2px;font-weight:600">
                                        👨‍🏫 {{ $alloc->teacher->name ?? 'Unassigned' }} &bull; <span style="color:#0f172a">Sec {{ $sectionTitle }}</span>
                                    </div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px">
                                    <span style="font-size:10.5px;font-weight:800;color:#059669;background:#ecfdf5;padding:2px 7px;border-radius:5px;border:1px solid #a7f3d0">
                                        ⏱️ {{ $alloc->formatted_duration }}
                                    </span>
                                </div>
                            </div>

                            <!-- Duration & Periods Input Row -->
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:8px;background:#f8fafc;padding:8px;border-radius:8px;border:1px solid #e2e8f0">
                                <div>
                                    <label style="font-size:9.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:2px">Hours</label>
                                    <select name="hours" class="form-control-compact">
                                        @for($h = 0; $h <= 4; $h++)
                                            <option value="{{ $h }}" {{ $alloc->duration_hours === $h ? 'selected' : '' }}>{{ $h }}h</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:9.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:2px">Minutes</label>
                                    <select name="minutes" class="form-control-compact">
                                        <option value="0" {{ $alloc->duration_remaining_minutes === 0 ? 'selected' : '' }}>0m</option>
                                        <option value="15" {{ $alloc->duration_remaining_minutes === 15 ? 'selected' : '' }}>15m</option>
                                        <option value="30" {{ $alloc->duration_remaining_minutes === 30 ? 'selected' : '' }}>30m</option>
                                        <option value="45" {{ $alloc->duration_remaining_minutes === 45 ? 'selected' : '' }}>45m</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:9.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:2px">Periods/Wk</label>
                                    <input type="number" name="periods_per_week" value="{{ $alloc->periods_per_week ?: 3 }}" min="1" max="20" class="form-control-compact">
                                </div>
                            </div>

                            <!-- Allowed Lecture Days Selection Strip -->
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;margin-bottom:8px">
                                <div style="font-size:10px;font-weight:800;color:#0f172a;margin-bottom:6px;display:flex;align-items:center;justify-content:space-between">
                                    <span>🗓️ Allowed Days &amp; Timings:</span>
                                    <span style="font-size:9px;color:#64748b">Mon-Thu (Full) &bull; Fri (Half)</span>
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:5px">
                                    @foreach($allWeekdays as $dayKey => $dayInfo)
                                        @php
                                            $isAvailRecord = $tAvails->get($dayKey);
                                            $isTeacherWorking = $isAvailRecord ? (bool)$isAvailRecord->is_available : true;
                                            $isDayChecked = $isTeacherWorking && $alloc->isDayAllowed($dayKey);
                                        @endphp
                                        <label class="day-checkbox-pill {{ $isTeacherWorking ? 'avail' : 'unavail' }}" 
                                               title="{{ $dayInfo['fullName'] }}: {{ $dayInfo['hours'] }} ({{ $dayInfo['timing'] }}). {{ $isTeacherWorking ? 'Teacher Available' : 'Teacher Unavailable (Disabled)' }}">
                                            <input type="checkbox" name="allowed_days[]" value="{{ $dayKey }}" 
                                                   {{ $isDayChecked ? 'checked' : '' }} 
                                                   {{ $isTeacherWorking ? '' : 'disabled' }} 
                                                   style="accent-color:#e1306c;margin:0;width:13px;height:13px">
                                            <span style="font-size:10.5px">
                                                {{ $dayInfo['label'] }}
                                            </span>
                                            <span style="font-size:8.5px;opacity:0.75;margin-left:auto">
                                                {{ $dayInfo['tag'] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;padding:6px;font-size:11.5px;font-weight:700;border:none;border-radius:7px">
                                💾 Save &amp; Re-generate Timetable
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@empty
    <div class="card" style="text-align:center;padding:36px 20px">
        <p style="color:#64748b;font-size:13.5px">No classes or subject allocations found for the active academic term.</p>
    </div>
@endforelse
</div>

<!-- CLASSY GENERATION CONFIRMATION MODAL -->
<div id="classyConfirmModal" class="modal-backdrop" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box" style="max-width:440px;text-align:center;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:26px;box-shadow:0 25px 60px rgba(0,0,0,0.15)">
        <div style="width:58px;height:58px;margin:0 auto 16px;background:#fdf4ff;border:1px solid #f5d0fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 4px 14px rgba(225,48,108,0.15)">
            ⚡
        </div>
        <h3 style="font-family:'Outfit',sans-serif;font-size:19px;font-weight:800;color:#0f172a;margin-bottom:8px">
            Generate Optimistic Timetable?
        </h3>
        <p style="color:#64748b;font-size:12.5px;line-height:1.5;margin-bottom:22px;font-weight:500">
            Automatically calculates conflict-free slots respecting individual day timings (Mon–Thu Full Day, Friday Half Day), teacher shifts, room capacities, and class durations.
        </p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button type="button" onclick="closeClassyConfirmModal()" class="btn btn-ghost" style="flex:1;border-radius:10px;padding:9px;font-weight:700;font-size:12px">
                Cancel
            </button>
            <button type="button" onclick="submitGenerateForm()" class="btn btn-primary" style="flex:1.4;border-radius:10px;padding:9px;font-weight:700;font-size:12px">
                ⚡ Yes, Generate
            </button>
        </div>
    </div>
</div>

<script>
let currentActiveClassId = '{{ $defaultClassId }}';

function switchAllocationClass(classId, btnElement) {
    currentActiveClassId = classId;

    // Update active state on buttons
    document.querySelectorAll('.class-switch-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (btnElement) {
        btnElement.classList.add('active');
    }

    // Toggle visibility of class groups
    const groups = document.querySelectorAll('.class-allocation-group');
    groups.forEach(group => {
        const gClassId = group.getAttribute('data-class-id');
        if (classId === 'all' || gClassId === classId) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    });

    // Re-run search filter if text exists
    filterAllocations();
}

function filterAllocations() {
    const query = document.getElementById('allocationSearchInput').value.toLowerCase().trim();
    const groups = document.querySelectorAll('.class-allocation-group');

    groups.forEach(group => {
        const gClassId = group.getAttribute('data-class-id');
        const isClassSelected = (currentActiveClassId === 'all' || gClassId === currentActiveClassId);

        if (!isClassSelected) {
            group.style.display = 'none';
            return;
        }

        const itemCards = group.querySelectorAll('.allocation-item-card');
        let hasMatch = false;

        itemCards.forEach(item => {
            const keyword = item.getAttribute('data-keyword') || '';
            if (!query || keyword.includes(query)) {
                item.style.display = 'flex';
                hasMatch = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (hasMatch || !query) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    });
}

function openClassyConfirmModal() {
    document.getElementById('classyConfirmModal').style.display = 'flex';
}
function closeClassyConfirmModal() {
    document.getElementById('classyConfirmModal').style.display = 'none';
}
function submitGenerateForm() {
    closeClassyConfirmModal();
    document.getElementById('generateTimetableForm').submit();
}
</script>
@endsection

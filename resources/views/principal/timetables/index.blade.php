@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Timetable & Master Matrix')
@section('breadcrumb', 'Timetable Matrix')

@section('content')
@php
    $todayName = strtolower(date('l'));
    $defaultDay = in_array($todayName, $days) ? $todayName : 'monday';
@endphp

<style>
    /* Modal Backdrop & Centered Pop-up */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        width: 100%;
        max-width: 540px;
        padding: 28px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.15);
        max-height: 90vh;
        overflow-y: auto;
        color: #0f172a;
    }

    /* Per-Section Day Selector Pills */
    .sec-day-pill {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .sec-day-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .sec-day-pill.active {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }

    /* Table Grid Styling */
    .timetable-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .timetable-grid th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 12px;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #f1f5f9;
        text-align: center;
    }
    .timetable-grid td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        vertical-align: top;
        background: #ffffff;
        min-height: 80px;
    }
    .timetable-grid td:last-child, .timetable-grid th:last-child {
        border-right: none;
    }
    .timetable-grid tr:last-child td {
        border-bottom: none;
    }

    /* Slot Card Styling */
    .slot-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 6px;
        position: relative;
        transition: all 0.2s ease;
    }
    .slot-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    .slot-card .subject {
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
    }
    .slot-card .teacher {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }
    .slot-card .room {
        font-size: 10px;
        font-weight: 700;
        color: #059669;
        margin-top: 2px;
    }
    .slot-card .delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 4px;
        width: 18px;
        height: 18px;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slot-card .delete-btn:hover { background: #dc2626; color: #fff; }

    /* Accordion Header */
    .grade-accordion-header {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 20px;
        margin-top: 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }
    .grade-accordion-header:hover {
        background: #fdf2f8;
        border-color: #fbcfe8;
    }
    .grade-accordion-title {
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

@php
    $routePrefix = request()->routeIs('teacher.*') ? 'teacher.' : 'principal.';
@endphp

<!-- HEADER TITLE & SINGLE UNIFIED TOP MENU BAR -->
<div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">🗓️ Timetable &amp; Master Matrix</h1>
            <p style="color:#64748b;font-size:13.5px;margin-top:2px;font-weight:500">
                Conflict-free master schedule adhering to teacher availability, room capacity, and active academic term rules in <strong>{{ $activeTerm?->name }}</strong>.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12.5px;color:#64748b;font-weight:700">
                Active View: <strong style="color:#4f46e5">{{ $viewType === 'teacher' ? 'Teacher Schedules' : 'Class Schedules' }}</strong>
            </span>
        </div>
    </div>

<!-- SINGLE UNIFIED TOP NAVIGATION & ACTION MENU -->
    <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'class']) }}" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;{{ $viewType === 'class' ? 'background:linear-gradient(135deg, #4f46e5, #4338ca);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,0.35)' : 'background:#f8fafc;color:#475569;border:1px solid #cbd5e1' }}">
                🏫 Class Schedules
            </a>
            <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'teacher']) }}" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;{{ $viewType === 'teacher' ? 'background:linear-gradient(135deg, #4f46e5, #4338ca);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,0.35)' : 'background:#f8fafc;color:#475569;border:1px solid #cbd5e1' }}">
                👨‍🏫 Teacher Schedules
            </a>
            <a href="{{ route($routePrefix . 'timetables.grid') }}" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                🏢 Campus Master Grid
            </a>
            <a href="{{ route($routePrefix . 'timetables.days-and-hours') }}" 
               class="btn" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                ⚙️ Bell Timings &amp; Days
            </a>
        </div>

            <a href="{{ route($routePrefix . 'timetables.export') }}" class="btn btn-ghost" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;border:1px solid #cbd5e1;background:#ffffff">
                📗 Download Excel (.xlsx)
            </a>
            @if(auth()->user()->hasPermission('timetables', 'edit'))
                <button type="button" onclick="openAddSlotModal()" class="btn btn-primary" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700">
                    ➕ Add Class Slot
                </button>
                <form id="generateTimetableForm" method="POST" action="{{ route($routePrefix . 'timetables.generate') }}" style="display:inline">
                    @csrf
                    <button type="button" onclick="openClassyConfirmModal()" class="btn btn-primary" style="border-radius:10px;padding:9px 16px;font-size:12px;font-weight:700;background:linear-gradient(135deg, #10b981, #059669);border:none">
                        ⚡ Auto-Generate Timetable
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>



@if($viewType === 'teacher')
    <!-- TEACHER SELECTOR & TEACHER-WISE SCHEDULE VIEW -->
    <div class="card" style="margin-bottom:24px;padding:20px;background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div>
                <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
                    👨‍🏫 Timetable Teacher-Wise
                </h2>
                <p style="color:#64748b;font-size:12.5px;margin-top:2px;font-weight:500">
                    Click on any teacher's name card to display their weekly timetable matrix schedule.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <!-- Quick Search Input -->
                <div style="position:relative;min-width:200px">
                    <input type="text" 
                           id="teacherSearchInput" 
                           onkeyup="filterTeacherCards()" 
                           placeholder="🔍 Search teacher name..." 
                           style="width:100%;padding:8px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:12.5px;outline:none">
                </div>

                <!-- Teacher Select Dropdown -->
                <div style="min-width:220px">
                    <select onchange="window.location.href='{{ route($routePrefix . 'timetables.index') }}?view_type=teacher&teacher_id=' + this.value" 
                            style="width:100%;padding:8px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:12.5px;font-weight:600;outline:none">
                        <option value="">-- All Teachers ({{ $teachers->count() }}) --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ (string)$selectedTeacherId === (string)$t->id ? 'selected' : '' }}>
                                👨‍🏫 {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Expand / Collapse All Buttons -->
                <div style="display:flex;align-items:center;gap:8px">
                    <button type="button" onclick="expandAllTeachers()" class="btn btn-ghost btn-sm" style="font-size:11.5px;padding:6px 12px">
                        📂 Expand All
                    </button>
                    <button type="button" onclick="collapseAllTeachers()" class="btn btn-ghost btn-sm" style="font-size:11.5px;padding:6px 12px">
                        📁 Collapse All
                    </button>
                </div>
            </div>
        </div>

        @php
            $displayTeachers = $selectedTeacherId ? $teachers->where('id', $selectedTeacherId) : $teachers;
            $allDaysList = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'];
        @endphp

        @forelse($displayTeachers as $t)
            @php
                $rawTSlots = $allSlots->filter(fn($s) => $s->teacher_id == $t->id);
                $mergedTSlots = \App\Models\Timetable::mergeContiguousSlots($rawTSlots);
                $isInitiallyExpanded = $selectedTeacherId == $t->id;
            @endphp
            <div class="teacher-schedule-card" data-teacher-name="{{ strtolower($t->name) }}" style="margin-bottom:16px;background:rgba(30,41,59,0.7);border:1px solid rgba(0,206,209,0.25);border-radius:14px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.25);transition:all 0.2s">
                <!-- Teacher Name Clickable Header Bar -->
                <div onclick="toggleTeacherTimetable('{{ $t->id }}')" 
                     style="background:linear-gradient(135deg, rgba(30,41,59,0.95), rgba(15,23,42,0.95));padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;cursor:pointer;user-select:none;border-bottom:{{ $isInitiallyExpanded ? '1px solid rgba(255,255,255,0.08)' : 'none' }}"
                     onmouseover="this.style.background='linear-gradient(135deg, rgba(30,41,59,1), rgba(15,23,42,1))'"
                     onmouseout="this.style.background='linear-gradient(135deg, rgba(30,41,59,0.95), rgba(15,23,42,0.95))'">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg, #00ced1, #3b82f6);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:16px;box-shadow:0 4px 12px rgba(0,206,209,0.3)">
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:800;color:#fff;font-size:16px;font-family:'Space Grotesk',sans-serif;display:inline-flex;align-items:center;gap:8px">
                                <span>👨‍🏫 {{ $t->name }}</span>
                                <button type="button" 
                                        onclick="event.stopPropagation(); openTeacherModalFromId({{ $t->id }})" 
                                        style="font-size:10px;background:rgba(0,206,209,0.15);color:#00ced1;padding:2px 7px;border-radius:12px;border:1px solid rgba(0,206,209,0.3);font-weight:700;cursor:pointer"
                                        title="Click to view full profile details">
                                    🔍 Details
                                </button>
                            </div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:2px">{{ $t->email }} &bull; {{ $mergedTSlots->count() }} Total Weekly Lecture(s)</div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge" style="background:rgba(0,206,209,0.15);color:#00ced1;border:1px solid rgba(0,206,209,0.3);padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700">
                            📚 {{ $mergedTSlots->count() }} Weekly Block(s)
                        </span>

                        <span id="teacher-acc-icon-{{ $t->id }}" style="font-size:12px;font-weight:800;color:{{ $isInitiallyExpanded ? '#fff' : '#00ced1' }};background:{{ $isInitiallyExpanded ? 'rgba(0,206,209,0.25)' : 'rgba(0,206,209,0.1)' }};padding:6px 14px;border-radius:8px;border:1px solid rgba(0,206,209,0.3);transition:all 0.2s">
                            {{ $isInitiallyExpanded ? '▲ Collapse Schedule' : '▼ Click to View Schedule' }}
                        </span>
                    </div>
                </div>

                <!-- Teacher Timetable Table Container (Expanded on Click) -->
                <div id="teacher-timetable-body-{{ $t->id }}" style="padding:18px;display:{{ $isInitiallyExpanded ? 'block' : 'none' }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                        <div style="font-size:13px;font-weight:700;color:#00ced1">
                            📅 Weekly Schedule Table for {{ $t->name }}
                        </div>
                        <!-- View Toggle Buttons (Grid vs List) -->
                        <div style="display:inline-flex;background:rgba(15,23,42,0.8);padding:3px;border-radius:8px;border:1px solid rgba(255,255,255,0.08)">
                            <button type="button" 
                                    id="view-toggle-grid-{{ $t->id }}" 
                                    onclick="switchTeacherViewMode('{{ $t->id }}', 'grid')" 
                                    style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:#00ced1;color:#0f172a;border:none">
                                🗓️ Weekly Matrix Grid
                            </button>
                            <button type="button" 
                                    id="view-toggle-list-{{ $t->id }}" 
                                    onclick="switchTeacherViewMode('{{ $t->id }}', 'list')" 
                                    style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:transparent;color:#94a3b8;border:none">
                                📋 List View
                            </button>
                        </div>
                    </div>

                    @if($mergedTSlots->isEmpty())
                        <div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px">
                            No scheduled lectures for {{ $t->name }} in active term {{ $activeTerm?->name }}.
                        </div>
                    @else
                        <!-- MODE 1: WEEKLY MATRIX GRID (DEFAULT - COMPACT 6 COLUMN LAYOUT) -->
                        <div id="teacher-view-grid-{{ $t->id }}" style="overflow-x:auto">
                            <table class="horiz-matrix-table" style="width:100%;min-width:720px;border-collapse:collapse;border:1px solid rgba(255,255,255,0.08);border-radius:12px;overflow:hidden">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.9)">
                                        @foreach($allDaysList as $dKey => $dName)
                                            @php $daySlotsCount = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); @endphp
                                            <th style="width:16.66%;padding:10px 8px;font-size:12px;font-weight:700;color:#fff;border-bottom:1px solid rgba(255,255,255,0.1);border-right:1px solid rgba(255,255,255,0.06);text-align:center">
                                                {{ $dName }}
                                                @if($daySlotsCount > 0)
                                                    <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 6px;border-radius:4px;margin-left:4px">{{ $daySlotsCount }}</span>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $dKey)
                                            @php 
                                                $daySlots = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->sortBy('start_time');
                                            @endphp
                                            <td style="vertical-align:top;padding:8px;background:rgba(15,23,42,0.4);border-right:1px solid rgba(255,255,255,0.06);border-bottom:none">
                                                @forelse($daySlots as $slot)
                                                    @php
                                                        $rNum = $slot->room?->room_number ?? '';
                                                        $cleanRoom = Str::startsWith(strtolower($rNum), 'room') ? $rNum : ($rNum ? 'Room ' . $rNum : 'Hall');
                                                    @endphp
                                                    <div style="margin-bottom:8px;background:linear-gradient(135deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95));border:1px solid rgba(0,206,209,0.3);border-radius:10px;padding:10px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">
                                                        <div style="font-size:11px;font-weight:800;color:#38bdf8;margin-bottom:4px">
                                                            ⏰ {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                        </div>
                                                        <div style="font-size:12.5px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.3;cursor:pointer"
                                                             onmouseover="this.style.color='#00ced1'"
                                                             onmouseout="this.style.color='#fff'"
                                                             onclick="showSubjectDetailsModal('{{ addslashes($slot->subject?->subject_name ?: 'Subject') }}', '{{ addslashes($slot->subject?->subject_code ?: '') }}', '{{ addslashes($slot->section?->instituteClass?->custom_name ?: '') }}', '{{ addslashes($t->name) }}', '{{ addslashes($cleanRoom) }}')">
                                                            📘 {{ $slot->subject?->subject_name ?: 'Subject' }}
                                                            @if($slot->subject?->subject_code)
                                                                <span style="font-size:9.5px;color:#00ced1;background:rgba(0,206,209,0.15);padding:1px 5px;border-radius:4px;margin-left:2px">{{ $slot->subject->subject_code }}</span>
                                                            @endif
                                                        </div>
                                                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#cbd5e1;margin-top:6px;padding-top:6px;border-top:1px solid rgba(255,255,255,0.06)">
                                                            <span style="font-weight:700;cursor:pointer;color:#38bdf8"
                                                                  onmouseover="this.style.textDecoration='underline'"
                                                                  onmouseout="this.style.textDecoration='none'"
                                                                  onclick="showSectionDetailsModal('{{ addslashes($slot->section?->instituteClass?->custom_name ?: 'Class') }}', '{{ addslashes($slot->section?->section_name ?: 'A') }}', '{{ $mergedTSlots->count() }}')">
                                                                🏫 {{ $slot->section?->instituteClass?->custom_name ?: 'Class' }}–{{ $slot->section?->section_name ?: 'A' }}
                                                            </span>
                                                            <span style="color:#2ed573;font-weight:700">🚪 {{ $cleanRoom }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div style="font-size:11px;color:#64748b;text-align:center;padding:16px 0;font-style:italic">— Off —</div>
                                                @endforelse
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- MODE 2: STREAMLINED LIST VIEW (COMPACT SLOTS ALWAYS SORTED CHRONOLOGICALLY) -->
                        <div id="teacher-view-list-{{ $t->id }}" style="display:none">
                            <!-- CLICKABLE DAY SELECTOR TABS -->
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:8px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.05)">
                                <span style="font-size:12px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                                <button type="button" 
                                        id="day-filter-btn-{{ $t->id }}-all" 
                                        class="day-filter-btn-{{ $t->id }}"
                                        onclick="filterDayLectures('all', '{{ $t->id }}')" 
                                        style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none">
                                    🗓️ All Days
                                </button>
                                @foreach($allDaysList as $dKey => $dName)
                                    @php $dayCount = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); @endphp
                                    <button type="button" 
                                            id="day-filter-btn-{{ $t->id }}-{{ $dKey }}" 
                                            class="day-filter-btn-{{ $t->id }}"
                                            onclick="filterDayLectures('{{ $dKey }}', '{{ $t->id }}')" 
                                            style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08)">
                                        {{ $dName }} @if($dayCount > 0) <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 5px;border-radius:4px;margin-left:2px">{{ $dayCount }}</span> @endif
                                    </button>
                                @endforeach
                            </div>

                            <div style="display:flex;flex-direction:column;gap:8px">
                                @foreach($mergedTSlots as $slot)
                                    @php
                                        $dKey = strtolower($slot->day_of_week);
                                        $rNum = $slot->room?->room_number ?? '';
                                        $cleanRoom = Str::startsWith(strtolower($rNum), 'room') ? $rNum : ($rNum ? 'Room ' . $rNum : 'Hall');
                                    @endphp
                                    <div class="lecture-card-{{ $t->id }}" data-day="{{ $dKey }}" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.25);border-radius:10px;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                                        <div style="display:flex;align-items:center;gap:12px">
                                            <span style="font-size:11px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:3px 8px;border-radius:6px">
                                                🗓️ {{ ucfirst($slot->day_of_week) }}
                                            </span>
                                            <span style="font-size:13px;font-weight:800;color:#38bdf8">
                                                ⏰ {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            </span>
                                            <span style="font-size:13px;font-weight:800;color:#fff;cursor:pointer"
                                                  onmouseover="this.style.color='#00ced1'"
                                                  onmouseout="this.style.color='#fff'"
                                                  onclick="showSubjectDetailsModal('{{ addslashes($slot->subject?->subject_name ?: 'Subject') }}', '{{ addslashes($slot->subject?->subject_code ?: '') }}', '{{ addslashes($slot->section?->instituteClass?->custom_name ?: '') }}', '{{ addslashes($t->name) }}', '{{ addslashes($cleanRoom) }}')">
                                                📘 {{ $slot->subject?->subject_name }} @if($slot->subject?->subject_code)<span style="font-size:10px;color:#00ced1">({{ $slot->subject->subject_code }})</span>@endif
                                            </span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:14px;font-size:12px;color:#cbd5e1">
                                            <span style="font-weight:700;cursor:pointer;color:#38bdf8"
                                                  onmouseover="this.style.textDecoration='underline'"
                                                  onmouseout="this.style.textDecoration='none'"
                                                  onclick="showSectionDetailsModal('{{ addslashes($slot->section?->instituteClass?->custom_name ?: 'Class') }}', '{{ addslashes($slot->section?->section_name ?: 'A') }}', '{{ $mergedTSlots->count() }}')">
                                                🏫 Class {{ $slot->section?->instituteClass?->custom_name }} — Section {{ $slot->section?->section_name }}
                                            </span>
                                            <span style="color:#2ed573;font-weight:700">🚪 {{ $cleanRoom }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                No teachers found.
            </div>
        @endforelse
    </div>
@endif

@if($viewType === 'time')
    <!-- TIME SLOT SELECTOR & TIME-WISE SCHEDULE VIEW WITH CLICKABLE DAYS & HORIZONTAL BARS -->
    <div class="card" style="margin-bottom:24px;padding:20px;background:rgba(15,23,42,0.85);border:1px solid rgba(0,206,209,0.25);border-radius:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:14px">
            <div>
                <h2 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px">
                    ⏰ View Timetable Time-Slot-Wise
                </h2>
                <p style="color:#94a3b8;font-size:12.5px;margin-top:2px">
                    Inspect scheduled lectures by specific time slots across all weekdays and classrooms.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;min-width:280px">
                <label style="font-size:12px;color:#94a3b8;font-weight:700;white-space:nowrap">Select Time Slot:</label>
                <select onchange="window.location.href='{{ route($routePrefix . 'timetables.index') }}?view_type=time&time_slot=' + this.value" 
                        style="width:100%;padding:9px 14px;background:#0f172a;border:1px solid rgba(0,206,209,0.4);border-radius:10px;color:#fff;font-size:13px;font-weight:600;outline:none">
                    <option value="">-- All Time Slots --</option>
                    @foreach($timeSlots as $ts)
                        <option value="{{ $ts['key'] }}" {{ (string)$selectedTimeSlot === (string)$ts['key'] ? 'selected' : '' }}>
                            ⏰ {{ $ts['start'] }} - {{ $ts['end'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $mergedTimeSlots = \App\Models\Timetable::mergeContiguousSlots($timeFilteredSlots);
            $allDaysList = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'];
        @endphp

        @if($mergedTimeSlots->isEmpty())
            <div style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                No scheduled lectures match the selected time slot.
            </div>
        @else
            <!-- CLICKABLE DAY SELECTOR TABS -->
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.05)">
                <span style="font-size:12px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                <button type="button" 
                        id="day-filter-btn-timeslot-all" 
                        class="day-filter-btn-timeslot"
                        onclick="filterDayLectures('all', 'timeslot')" 
                        style="padding:6px 14px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none;transition:all 0.2s">
                    🗓️ All Days
                </button>
                @foreach($allDaysList as $dKey => $dName)
                    @php $dayCount = $mergedTimeSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); @endphp
                    <button type="button" 
                            id="day-filter-btn-timeslot-{{ $dKey }}" 
                            class="day-filter-btn-timeslot"
                            onclick="filterDayLectures('{{ $dKey }}', 'timeslot')" 
                            style="padding:6px 14px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);transition:all 0.2s">
                        {{ $dName }} @if($dayCount > 0) <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 6px;border-radius:4px;margin-left:2px">{{ $dayCount }}</span> @endif
                    </button>
                @endforeach
            </div>

            <!-- HORIZONTAL LECTURE BARS LIST ALWAYS SORTED CHRONOLOGICALLY -->
            <div style="display:flex;flex-direction:column;gap:12px">
                @foreach($mergedTimeSlots as $slot)
                    @php
                        $dKey = strtolower($slot->day_of_week);
                        $startSec = strtotime($slot->start_time);
                        $endSec = strtotime($slot->end_time);
                        $diffMins = max(15, ($endSec - $startSec) / 60);
                        $hoursDecimal = round($diffMins / 60, 2);
                        $barWidthPercent = min(100, max(15, round(($diffMins / 180) * 100)));
                    @endphp
                    <div class="lecture-card-timeslot" data-day="{{ $dKey }}" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.3);border-radius:12px;padding:14px 18px;box-shadow:0 6px 18px rgba(0,0,0,0.3)">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <span style="font-size:11.5px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:4px 10px;border-radius:6px">
                                    🗓️ {{ ucfirst($slot->day_of_week) }}
                                </span>
                                <span style="font-size:13.5px;font-weight:800;color:#38bdf8">
                                    ⏰ {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:12px;font-weight:800;color:#2ed573;background:rgba(46,213,115,0.15);border:1px solid rgba(46,213,115,0.3);padding:4px 12px;border-radius:8px">
                                    ⏱️ Duration: {{ $hoursDecimal }} {{ Str::plural('Hour', $hoursDecimal) }} ({{ $diffMins }} mins)
                                </span>
                            </div>
                        </div>

                        <!-- Horizontal Duration Progress Bar -->
                        <div style="background:rgba(255,255,255,0.06);height:8px;border-radius:4px;overflow:hidden;margin-bottom:12px">
                            <div style="width:{{ $barWidthPercent }}%;height:100%;background:linear-gradient(90deg, #00ced1, #6c63ff);border-radius:4px"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;align-items:center">
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Subject</span>
                                <span style="font-size:13.5px;font-weight:800;color:#fff;display:flex;align-items:center;gap:6px;margin-top:2px;cursor:pointer"
                                      onmouseover="this.style.color='#00ced1'"
                                      onmouseout="this.style.color='#fff'"
                                      onclick="showSubjectDetailsModal('{{ addslashes($slot->subject?->subject_name ?: 'Subject') }}', '{{ addslashes($slot->subject?->subject_code ?: '') }}', '{{ addslashes($slot->section?->instituteClass?->custom_name ?: '') }}', '{{ addslashes($slot->teacher?->name ?: 'Unassigned') }}', '{{ addslashes($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall') }}')">
                                    📘 {{ $slot->subject?->subject_name ?: 'Subject' }}
                                    @if($slot->subject?->subject_code)
                                        <code style="font-size:10px;color:#38bdf8;background:rgba(56,189,248,0.12);padding:2px 6px;border-radius:4px">{{ $slot->subject->subject_code }}</code>
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Class &amp; Section</span>
                                <span style="font-size:13px;font-weight:700;color:#cbd5e1;margin-top:2px;display:block;cursor:pointer"
                                      onmouseover="this.style.color='#38bdf8'"
                                      onmouseout="this.style.color='#cbd5e1'"
                                      onclick="showSectionDetailsModal('{{ addslashes($slot->section?->instituteClass?->custom_name ?: 'Class') }}', '{{ addslashes($slot->section?->section_name ?: 'A') }}', 1)">
                                    🏫 {{ $slot->section?->instituteClass?->custom_name ?: 'Class' }} — Section {{ $slot->section?->section_name ?: 'A' }}
                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Faculty Teacher</span>
                                <span style="font-size:13px;font-weight:700;color:#38bdf8;margin-top:2px;display:block;cursor:pointer"
                                      onmouseover="this.style.textDecoration='underline'"
                                      onmouseout="this.style.textDecoration='none'"
                                      onclick="openTeacherModalFromId({{ $slot->teacher_id ?: 0 }})">
                                    👨‍🏫 {{ $slot->teacher?->name ?: 'Unassigned' }} 🔍
                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Room / Facility</span>
                                <span style="font-size:13px;font-weight:700;color:#2ed573;margin-top:2px;display:block">
                                    🚪 {{ $slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

@if($viewType === 'class')
<!-- CONTROL BAR: SEARCH SECTION & EXPAND/COLLAPSE BUTTONS -->
<div class="card" style="margin-bottom:24px;padding:16px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:400px">
            <span style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap">🔍 Search Section:</span>
            <input type="text" id="sectionSearchInput" onkeyup="filterSections()" placeholder="Type section e.g. 9A, 9-A, Grade 10..." style="padding:9px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:13px;width:100%;outline:none">
        </div>

        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:320px">
            <span style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap">Filter Section:</span>
            <select onchange="window.location.href='{{ route($routePrefix . 'timetables.index') }}?view_type=class&section_id=' + this.value" 
                    style="width:100%;padding:9px 14px;background:#0f172a;border:1px solid rgba(0,206,209,0.4);border-radius:10px;color:#fff;font-size:13px;font-weight:600;outline:none">
                <option value="">-- All Sections --</option>
                @foreach($sections as $sec)
                    @php
                        $cName = $sec->instituteClass?->custom_name ?: 'Grade 10';
                        $sName = $sec->section_name ?: 'A';
                        $sClean = trim(str_replace(['Sec', 'Section', 'sec', 'section'], '', $sName));
                        if (str_contains($sClean, '-')) {
                            $parts = explode('-', $sClean);
                            $sClean = trim(end($parts));
                        }
                        preg_match_all('/\d+/', $cName, $cMatches);
                        foreach ($cMatches[0] ?? [] as $num) {
                            if (str_starts_with($sClean, $num)) {
                                $sClean = trim(substr($sClean, strlen($num)));
                            }
                        }
                        $sClean = trim($sClean, ' -_');
                        $classSecLabel = !empty($sClean) ? ($cName . ' - ' . strtoupper($sClean)) : $cName;
                    @endphp
                    <option value="{{ $sec->id }}" {{ (string)$selectedSectionId === (string)$sec->id ? 'selected' : '' }}>
                        🏫 {{ $classSecLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <button type="button" onclick="expandAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📂 Expand All Grades
            </button>
            <button type="button" onclick="collapseAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📁 Collapse All Grades
            </button>
        </div>
    </div>
</div>
@endif

<!-- SECTION 3: GRADE ACCORDIONS WITH PER-SECTION CLICKABLE DAY SELECTORS & HORIZONTAL DURATION BARS -->
@if($viewType === 'class')
@if($groupedSections->isEmpty())
<div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:var(--text-muted);font-size:15px">No classes or sections configured yet.</p>
</div>
@else
    @foreach($groupedSections as $className => $classSections)
    @php 
        $classSlug = Str::slug($className); 
        $displaySections = $selectedSectionId ? $classSections->where('id', $selectedSectionId) : $classSections;
    @endphp
    @if($displaySections->isNotEmpty())
    <div class="grade-wrapper-block grade-group-{{ $classSlug }}" style="margin-bottom:20px">
        <div class="grade-accordion-header" onclick="toggleGradeAccordion('{{ $classSlug }}')">
            <div class="grade-accordion-title">
                🎓 {{ $className }}
                <span class="badge badge-purple" style="font-size:11px;padding:3px 10px">{{ $displaySections->count() }} Section(s)</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <button type="button" onclick="event.stopPropagation(); openAddSlotModal()" class="btn btn-ghost btn-sm" style="font-size:11px">
                    ➕ Add Slot
                </button>
                <span class="grade-accordion-icon grade-icon-{{ $classSlug }}" style="font-size:14px;color:var(--text-muted)">▼</span>
            </div>
        </div>

        <div id="grade-body-{{ $classSlug }}" class="grade-body-container" style="display:none">
            <div class="card" style="padding:18px;background:rgba(15,23,42,0.85);border:1px solid rgba(0,206,209,0.25);border-radius:14px">
                @foreach($displaySections as $sec)
                    @php
                        $secSlots = $allSlots->filter(fn($s) => $s->class_section_id == $sec->id);
                        $mergedSecSlots = \App\Models\Timetable::mergeContiguousSlots($secSlots);

                        $sClean = trim(str_replace(['Sec', 'Section', 'sec', 'section'], '', $sec->section_name));
                        if (str_contains($sClean, '-')) {
                            $parts = explode('-', $sClean);
                            $sClean = trim(end($parts));
                        }
                        preg_match_all('/\d+/', $className, $cMatches);
                        foreach ($cMatches[0] ?? [] as $num) {
                            if (str_starts_with($sClean, $num)) {
                                $sClean = trim(substr($sClean, strlen($num)));
                            }
                        }
                        $sClean = trim($sClean, ' -_');
                        $classSecTitle = !empty($sClean) ? ($className . ' - ' . strtoupper($sClean)) : $className;
                    @endphp
                    <div style="margin-bottom:20px;background:rgba(30,41,59,0.6);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.06);flex-wrap:wrap;gap:10px">
                            <div style="font-weight:800;color:#fff;font-size:15px;display:flex;align-items:center;gap:8px;cursor:pointer"
                                 onclick="showSectionDetailsModal('{{ addslashes($className) }}', '{{ addslashes($sec->section_name) }}', '{{ $mergedSecSlots->count() }}')">
                                🏫 {{ $classSecTitle }} <span style="font-size:11px;color:#00ced1;font-weight:600">(Click for details 🔍)</span>
                            </div>
                            <span style="font-size:12px;color:#00ced1;font-weight:700;background:rgba(0,206,209,0.12);border:1px solid rgba(0,206,209,0.25);padding:4px 10px;border-radius:6px">
                                📚 {{ $mergedSecSlots->count() }} Weekly Lecture Block(s)
                            </span>
                        </div>

                        <!-- CLICKABLE DAY FILTER TABS FOR THIS SECTION -->
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:8px 12px;border-radius:10px">
                            <span style="font-size:11.5px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                            <button type="button" 
                                    id="day-filter-btn-sec-{{ $sec->id }}-all" 
                                    class="day-filter-btn-sec-{{ $sec->id }}"
                                    onclick="filterDayLectures('all', 'sec-{{ $sec->id }}')" 
                                    style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none;transition:all 0.2s">
                                🗓️ All Days
                            </button>
                            @foreach(['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'] as $dKey => $dName)
                                @php $dayCount = $mergedSecSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); @endphp
                                <button type="button" 
                                        id="day-filter-btn-sec-{{ $sec->id }}-{{ $dKey }}" 
                                        class="day-filter-btn-sec-{{ $sec->id }}"
                                        onclick="filterDayLectures('{{ $dKey }}', 'sec-{{ $sec->id }}')" 
                                        style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);transition:all 0.2s">
                                    {{ $dName }} @if($dayCount > 0) <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 5px;border-radius:4px;margin-left:2px">{{ $dayCount }}</span> @endif
                                </button>
                            @endforeach
                        </div>

                        @if($mergedSecSlots->isEmpty())
                            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12.5px">
                                No scheduled lectures for Section {{ $sec->section_name }}.
                            </div>
                        @else
                            <!-- HORIZONTAL LECTURE BARS LIST ALWAYS SORTED CHRONOLOGICALLY -->
                            <div style="display:flex;flex-direction:column;gap:10px">
                                @foreach($mergedSecSlots as $slot)
                                    @php
                                        $dKey = strtolower($slot->day_of_week);
                                        $startSec = strtotime($slot->start_time);
                                        $endSec = strtotime($slot->end_time);
                                        $diffMins = max(15, ($endSec - $startSec) / 60);
                                        $hoursDecimal = round($diffMins / 60, 2);
                                        $barWidthPercent = min(100, max(15, round(($diffMins / 180) * 100)));
                                    @endphp
                                    <div class="lecture-card-sec-{{ $sec->id }}" data-day="{{ $dKey }}" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.3);border-radius:10px;padding:12px 16px;box-shadow:0 4px 14px rgba(0,0,0,0.25)">
                                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px">
                                            <div style="display:flex;align-items:center;gap:10px">
                                                <span style="font-size:11px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:3px 8px;border-radius:6px">
                                                    🗓️ {{ ucfirst($slot->day_of_week) }}
                                                </span>
                                                <span style="font-size:13px;font-weight:800;color:#38bdf8">
                                                    ⏰ {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                </span>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px">
                                                <span style="font-size:11.5px;font-weight:800;color:#2ed573;background:rgba(46,213,115,0.15);border:1px solid rgba(46,213,115,0.3);padding:3px 10px;border-radius:6px">
                                                    ⏱️ Duration: {{ $hoursDecimal }} {{ Str::plural('Hour', $hoursDecimal) }} ({{ $diffMins }} mins)
                                                </span>
                                                <form method="POST" action="{{ route('principal.timetables.destroy', $slot) }}" onsubmit="return classyConfirmForm(this, 'Remove Slot?', 'Are you sure you want to remove this timetable slot?', {danger: true, icon: '🗑️', confirmText: 'Yes, Remove'})" style="margin:0">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="delete-btn" style="background:rgba(239,68,68,0.2);color:#ef4444;border:none;border-radius:6px;width:24px;height:24px;font-size:14px;cursor:pointer" title="Delete Slot">&times;</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Horizontal Duration Progress Bar -->
                                        <div style="background:rgba(255,255,255,0.06);height:6px;border-radius:3px;overflow:hidden;margin-bottom:10px">
                                            <div style="width:{{ $barWidthPercent }}%;height:100%;background:linear-gradient(90deg, #00ced1, #6c63ff);border-radius:3px"></div>
                                        </div>

                                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:10px;align-items:center">
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Subject</span>
                                                <span style="font-size:13px;font-weight:800;color:#fff;display:flex;align-items:center;gap:6px;margin-top:2px;cursor:pointer"
                                                      onmouseover="this.style.color='#00ced1'"
                                                      onmouseout="this.style.color='#fff'"
                                                      onclick="showSubjectDetailsModal('{{ addslashes($slot->subject?->subject_name ?: 'Subject') }}', '{{ addslashes($slot->subject?->subject_code ?: '') }}', '{{ addslashes($className) }}', '{{ addslashes($slot->teacher?->name ?: 'Unassigned') }}', '{{ addslashes($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall') }}')">
                                                    📘 {{ $slot->subject?->subject_name ?: 'Subject' }}
                                                    @if($slot->subject?->subject_code)
                                                        <code style="font-size:10px;color:#38bdf8;background:rgba(56,189,248,0.12);padding:2px 5px;border-radius:4px">{{ $slot->subject->subject_code }}</code>
                                                    @endif
                                                </span>
                                            </div>
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Faculty Teacher</span>
                                                <span style="font-size:12.5px;font-weight:700;color:#38bdf8;margin-top:2px;display:block;cursor:pointer"
                                                      onmouseover="this.style.textDecoration='underline'"
                                                      onmouseout="this.style.textDecoration='none'"
                                                      onclick="openTeacherModalFromId({{ $slot->teacher_id ?: 0 }})">
                                                    👨‍🏫 {{ $slot->teacher?->name ?: 'Unassigned' }} 🔍
                                                </span>
                                            </div>
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Room / Facility</span>
                                                <span style="font-size:12.5px;font-weight:700;color:#2ed573;margin-top:2px;display:block">
                                                    🚪 {{ $slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endforeach
@endif
@endif

<!-- CONVERSATIONAL TIMETABLE ADJUSTER -->
@include('principal.timetables._chat')

<script>
let globalDay = '{{ $defaultDay }}';
const expandedGrades = new Set();
const sectionDays = {};

function filterTeacherCards() {
    const input = document.getElementById('teacherSearchInput');
    if (!input) return;
    const q = input.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.teacher-schedule-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-teacher-name') || '';
        if (!q || name.includes(q)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function switchTeacherViewMode(teacherId, mode) {
    const gridDiv = document.getElementById('teacher-view-grid-' + teacherId);
    const listDiv = document.getElementById('teacher-view-list-' + teacherId);
    const gridBtn = document.getElementById('view-toggle-grid-' + teacherId);
    const listBtn = document.getElementById('view-toggle-list-' + teacherId);

    if (mode === 'grid') {
        if (gridDiv) gridDiv.style.display = 'block';
        if (listDiv) listDiv.style.display = 'none';
        if (gridBtn) {
            gridBtn.style.background = '#00ced1';
            gridBtn.style.color = '#0f172a';
        }
        if (listBtn) {
            listBtn.style.background = 'transparent';
            listBtn.style.color = '#94a3b8';
        }
    } else {
        if (gridDiv) gridDiv.style.display = 'none';
        if (listDiv) listDiv.style.display = 'block';
        if (listBtn) {
            listBtn.style.background = '#00ced1';
            listBtn.style.color = '#0f172a';
        }
        if (gridBtn) {
            gridBtn.style.background = 'transparent';
            gridBtn.style.color = '#94a3b8';
        }
    }
}

function toggleViewModeDropdown() {
    const dropdown = document.getElementById('viewModeDropdown');
    if (dropdown) {
        dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const container = document.getElementById('viewModeDropdownContainer');
    const dropdown = document.getElementById('viewModeDropdown');
    if (container && dropdown && !container.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

function filterDayLectures(day, scopeId = 'all') {
    document.querySelectorAll('.day-filter-btn-' + scopeId).forEach(btn => {
        btn.style.background = 'rgba(30,41,59,0.8)';
        btn.style.color = '#94a3b8';
        btn.style.border = '1px solid rgba(255,255,255,0.08)';
        btn.style.boxShadow = 'none';
    });
    const activeBtn = document.getElementById('day-filter-btn-' + scopeId + '-' + day);
    if (activeBtn) {
        activeBtn.style.background = 'linear-gradient(135deg, #00ced1, #3b82f6)';
        activeBtn.style.color = '#fff';
        activeBtn.style.boxShadow = '0 4px 12px rgba(0,206,209,0.3)';
        activeBtn.style.border = 'none';
    }

    document.querySelectorAll('.lecture-card-' + scopeId).forEach(card => {
        if (day === 'all' || card.getAttribute('data-day') === day) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function selectGlobalDay(day) {
    globalDay = day;
    document.querySelectorAll('.day-pill').forEach(el => el.classList.remove('active'));
    const targetPill = document.getElementById('day-pill-' + day);
    if (targetPill) targetPill.classList.add('active');

    // Update day for all section accordions simultaneously
    document.querySelectorAll('.sec-day-pill').forEach(btn => {
        if (btn.id.endsWith('-' + day)) {
            btn.click();
        }
    });
}

function selectSectionDay(classSlug, day) {
    sectionDays[classSlug] = day;

    // Update active pill for this section
    document.querySelectorAll('.sec-day-pill-' + classSlug).forEach(el => el.classList.remove('active'));
    const pill = document.getElementById('sec-day-pill-' + classSlug + '-' + day);
    if (pill) pill.classList.add('active');

    // Update day label text
    const label = document.getElementById('sec-day-label-' + classSlug);
    if (label) label.innerText = day;

    // Show matrix table for this section and day
    document.querySelectorAll('.sec-matrix-' + classSlug).forEach(el => el.style.display = 'none');
    const targetTable = document.getElementById('sec-matrix-' + classSlug + '-' + day);
    if (targetTable) targetTable.style.display = 'block';
}

function filterClass(slug) {
    document.querySelectorAll('.class-pill').forEach(el => el.classList.remove('active'));
    document.getElementById('class-pill-' + slug).classList.add('active');

    if (slug === 'all') {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'block');
    } else {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.grade-group-' + slug).forEach(el => el.style.display = 'block');
    }
}

function toggleGradeAccordion(classSlug) {
    if (expandedGrades.has(classSlug)) {
        expandedGrades.delete(classSlug);
    } else {
        expandedGrades.add(classSlug);
    }
    applyGradeAccordionStates();
}

function applyGradeAccordionStates() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');

        if (classSlug) {
            const isExpanded = expandedGrades.has(classSlug);
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');

            if (bodyContainer) {
                bodyContainer.style.display = isExpanded ? 'block' : 'none';
            }
            if (icon) {
                icon.innerText = isExpanded ? '▲' : '▼';
            }
        }
    });
}

function expandAllGrades() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');
        if (classSlug) expandedGrades.add(classSlug);
    });
    applyGradeAccordionStates();
}

function collapseAllGrades() {
    expandedGrades.clear();
    applyGradeAccordionStates();
}

function filterSections() {
    const rawQuery = document.getElementById('sectionSearchInput').value.trim().toLowerCase();
    const cleanQuery = rawQuery.replace(/[^a-z0-9]/g, '');

    if (!rawQuery) {
        document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
            wrapper.style.display = 'block';
        });
        document.querySelectorAll('.section-row').forEach(row => {
            row.style.display = '';
        });
        applyGradeAccordionStates();
        return;
    }

    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        let hasMatchInGrade = false;
        const rows = wrapper.querySelectorAll('.section-row');

        rows.forEach(row => {
            const searchText = (row.getAttribute('data-section-name') || '').toLowerCase();
            const cleanSearchText = searchText.replace(/[^a-z0-9]/g, '');

            const isMatch = searchText.includes(rawQuery) || 
                            (cleanQuery.length > 0 && cleanSearchText.includes(cleanQuery));

            if (isMatch) {
                row.style.display = '';
                hasMatchInGrade = true;
            } else {
                row.style.display = 'none';
            }
        });

        if (hasMatchInGrade) {
            wrapper.style.display = 'block';
            // Automatically expand matching class accordion to reveal the searched section instantly
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');
            if (bodyContainer) bodyContainer.style.display = 'block';
            if (icon) icon.innerText = '▲';
        } else {
            wrapper.style.display = 'none';
        }
    });
}
</script>

<!-- POP-UP MODAL FOR MANUAL SLOT ADDITION -->
<div id="addSlotModal" class="modal-backdrop" onclick="if(event.target===this) closeAddSlotModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:12px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">➕ Add Timetable Slot Manually</h3>
            <button type="button" onclick="closeAddSlotModal()" style="background:none;border:none;color:var(--text-muted);font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form method="POST" action="{{ route('principal.timetables.store') }}">
            @csrf
            <div class="form-group">
                <label for="modal_class_section_id">Class Section *</label>
                <select id="modal_class_section_id" name="class_section_id" required>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                        {{ $sec->instituteClass->custom_name }} — {{ $sec->section_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_subject_id">Subject *</label>
                <select id="modal_subject_id" name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->instituteClass->custom_name }})</option>
                    @endforeach
                </select>
                @error('subject_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_teacher_id">Teacher / Faculty *</label>
                <select id="modal_teacher_id" name="teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->identifier ?? $teacher->email }})</option>
                    @endforeach
                </select>
                @error('teacher_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_day_of_week">Day of Week *</label>
                <select id="modal_day_of_week" name="day_of_week" required>
                    @foreach($days as $d)
                    <option value="{{ $d }}" {{ old('day_of_week', $defaultDay) == $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_room_id">Assign Room (Optional)</label>
                <select id="modal_room_id" name="room_id">
                    <option value="">-- No Room --</option>
                    @foreach($rooms as $rm)
                    <option value="{{ $rm->id }}" {{ old('room_id') == $rm->id ? 'selected' : '' }}>📍 {{ $rm->room_number }} ({{ $rm->capacity }} seats)</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_start_time">Start Time *</label>
                <input id="modal_start_time" type="time" name="start_time" required value="{{ old('start_time', '08:00') }}">
                @error('start_time')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_end_time">End Time *</label>
                <input id="modal_end_time" type="time" name="end_time" required value="{{ old('end_time', '09:00') }}">
                @error('end_time')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                <button type="button" onclick="closeAddSlotModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Slot</button>
            </div>
        </form>
    </div>
</div>

<!-- CLASSY GENERATION CONFIRMATION MODAL -->
<div id="classyConfirmModal" class="modal-backdrop" style="display:none">
    <div class="modal-box" style="max-width:480px;text-align:center;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.15);animation:modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards">
        <div style="width:68px;height:68px;margin:0 auto 20px;background:#fdf2f8;border:1px solid #fbcfe8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 0 25px rgba(225,48,108,0.2)">
            ⚡
        </div>
        <h3 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin-bottom:10px">
            Generate Optimistic Timetable?
        </h3>
        <p style="color:#64748b;font-size:13.5px;line-height:1.6;margin-bottom:28px;font-weight:500">
            This action will automatically calculate &amp; schedule conflict-free timetable slots based on active teacher allocations, faculty working hours, subject durations, and room capacities.
        </p>
        <div style="display:flex;gap:12px;justify-content:center">
            <button type="button" onclick="closeClassyConfirmModal()" class="btn" style="flex:1;background:#ffffff;color:#475569;border:1px solid #cbd5e1;border-radius:12px;padding:12px;font-weight:700;font-size:13px;transition:all 0.2s">
                Cancel
            </button>
            <button type="button" onclick="submitGenerateForm()" class="btn btn-primary" style="flex:1.4;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);color:#fff;border:none;border-radius:12px;padding:12px;font-weight:700;font-size:13px;box-shadow:0 6px 20px rgba(225,48,108,0.35);transition:all 0.2s">
                ⚡ Yes, Generate Timetable
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    0% { opacity: 0; transform: scale(0.9) translateY(10px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<!-- MODAL: QUICK ASSIGN DAYS & HOURS -->
<div id="configDaysHoursModal" class="modal-backdrop" onclick="if(event.target===this) closeConfigDaysHoursModal()">
    <div class="modal-box" style="max-width:540px;border:1px solid #e2e8f0;box-shadow:0 25px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px">
                ⚙️ Quick Assign Days &amp; Hours
            </h3>
            <button type="button" onclick="closeConfigDaysHoursModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="configDaysHoursForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;display:block;margin-bottom:4px">Select Subject Allocation *</label>
                <select id="modal_allocation_id" onchange="onModalAllocationSelectChange(this)" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:13px;outline:none">
                    <option value="">-- Select Class &amp; Subject Allocation --</option>
                    @foreach($assignments as $a)
                        @php
                            $cName = $a->section->instituteClass->custom_name ?? 'Class';
                            $sName = $a->section->section_name ?? 'Sec';
                            $subName = $a->subject->subject_name ?? 'Subject';
                            $tName = $a->teacher->name ?? 'Unassigned';
                            $updateUrl = route('principal.timetables.allocations.update', $a->id);
                        @endphp
                        <option value="{{ $a->id }}" 
                                data-update-url="{{ $updateUrl }}"
                                data-hours="{{ $a->duration_hours }}"
                                data-minutes="{{ $a->duration_remaining_minutes }}"
                                data-periods="{{ $a->periods_per_week ?: 3 }}"
                                data-days='@json($a->allowed_days_list)'
                                data-avails='@json($a->teacher ? $a->teacher->availabilities->keyBy(fn($av)=>strtolower($av->day_of_week))->map(fn($av)=>(bool)$av->is_available) : [])'>
                            {{ $cName }} — Section {{ $sName }} | {{ $subName }} (Faculty: {{ $tName }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Duration & Periods Input Row -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Hours</label>
                    <select id="modal_alloc_hours" name="hours" style="width:100%;padding:7px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                        @for($h = 0; $h <= 4; $h++)
                            <option value="{{ $h }}">{{ $h }} {{ Str::plural('Hour', $h) }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Minutes</label>
                    <select id="modal_alloc_minutes" name="minutes" style="width:100%;padding:7px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                        <option value="0">0 Mins</option>
                        <option value="15">15 Mins</option>
                        <option value="30">30 Mins</option>
                        <option value="45">45 Mins</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Periods / Wk</label>
                    <input type="number" id="modal_alloc_periods" name="periods_per_week" value="3" min="1" max="20" style="width:100%;padding:6px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                </div>
            </div>

            <!-- Allowed Days Checkboxes Strip -->
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:18px">
                <div style="font-size:11px;font-weight:800;color:#0f172a;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
                    <span>🗓️ Allowed Lecture Days:</span>
                    <span style="font-size:9.5px;color:#059669">🟢 Teacher Avail &nbsp;|&nbsp; 🔴 Unavail</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px">
                    @foreach(['monday'=>'Mon', 'tuesday'=>'Tue', 'wednesday'=>'Wed', 'thursday'=>'Thu', 'friday'=>'Fri', 'saturday'=>'Sat'] as $dayKey => $dayLabel)
                        <label style="display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:8px;background:#ffffff;border:1px solid #e2e8f0;cursor:pointer">
                            <input type="checkbox" class="modal-day-checkbox" name="allowed_days[]" value="{{ $dayKey }}" checked style="accent-color:#e1306c">
                            <span style="font-size:12px;font-weight:700;color:#0f172a">{{ $dayLabel }}</span>
                            <span class="modal-day-avail-badge" id="modal_avail_badge_{{ $dayKey }}" style="font-size:9px;margin-left:auto">🟢</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="closeConfigDaysHoursModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);font-weight:700">
                    💾 Save &amp; Re-generate Timetable
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TEACHER / FACULTY DETAILS POPUP MODAL -->
<div id="teacherDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeTeacherDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:540px;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:28px;box-shadow:0 25px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div style="display:flex;align-items:center;gap:14px">
                <div id="tdm_avatar" style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:22px;box-shadow:0 4px 16px rgba(225,48,108,0.35)">
                    T
                </div>
                <div>
                    <h3 id="tdm_name" style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;margin:0">
                        Teacher Name
                    </h3>
                    <div id="tdm_empid" style="font-size:12px;color:#e1306c;font-weight:700;margin-top:2px">
                        Employee ID: EMP-00
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeTeacherDetailsModal()" style="background:none;border:none;color:#64748b;font-size:28px;cursor:pointer;line-height:1">&times;</button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Email Address</span>
                <span id="tdm_email" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;display:block;word-break:break-all">email@domain.com</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Phone Number</span>
                <span id="tdm_phone" style="font-size:13px;font-weight:700;color:#0284c7;margin-top:2px;display:block">N/A</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Highest Qualification</span>
                <span id="tdm_qual" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;display:block">Faculty Member</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Teaching Experience</span>
                <span id="tdm_exp" style="font-size:13px;font-weight:700;color:#059669;margin-top:2px;display:block">N/A</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Monthly Basic Salary</span>
                <span id="tdm_salary" style="font-size:13px;font-weight:700;color:#059669;margin-top:2px;display:block">PKR 0</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Emergency Contact</span>
                <span id="tdm_emergency" style="font-size:13px;font-weight:700;color:#d97706;margin-top:2px;display:block">N/A</span>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px">
            <a id="tdm_profile_link" href="#" class="btn btn-primary" style="padding:9px 16px;font-size:12.5px;font-weight:700;text-decoration:none">
                👁️ Open Full Profile &amp; Directory Ledger &rarr;
            </a>
            <button type="button" onclick="closeTeacherDetailsModal()" class="btn btn-ghost" style="padding:9px 16px">
                Close
            </button>
        </div>
    </div>
</div>

<!-- SUBJECT DETAILS POPUP MODAL -->
<div id="subjectDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeSubjectDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:480px;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px">
            <h3 id="sdm_title" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Subject Details
            </h3>
            <button type="button" onclick="closeSubjectDetailsModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1">&times;</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Subject Name &amp; Code</span>
                <span id="sdm_name_code" style="font-size:14px;font-weight:800;color:#0284c7;margin-top:2px;display:block">Subject</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Class Grade</span>
                <span id="sdm_class" style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;display:block">Class</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Faculty Teacher In-Charge</span>
                <span id="sdm_teacher" style="font-size:13px;font-weight:800;color:#059669;margin-top:2px;display:block">Teacher</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Room / Facility Location</span>
                <span id="sdm_room" style="font-size:13px;font-weight:800;color:#d97706;margin-top:2px;display:block">Room</span>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <button type="button" onclick="closeSubjectDetailsModal()" class="btn btn-ghost">Close</button>
        </div>
    </div>
</div>

<!-- SECTION DETAILS POPUP MODAL -->
<div id="sectionDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeSectionDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:460px;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px">
            <h3 id="sec_dm_title" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Class Section Details
            </h3>
            <button type="button" onclick="closeSectionDetailsModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1">&times;</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Class Name</span>
                <span id="sec_dm_class" style="font-size:14px;font-weight:800;color:#0284c7;margin-top:2px;display:block">Class</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Section</span>
                <span id="sec_dm_sec" style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;display:block">Section</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Weekly Lecture Workload</span>
                <span id="sec_dm_count" style="font-size:13px;font-weight:800;color:#059669;margin-top:2px;display:block">0 Lecture Blocks</span>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <button type="button" onclick="closeSectionDetailsModal()" class="btn btn-ghost">Close</button>
        </div>
    </div>
</div>

<script>
const teachersData = {
    @foreach($teachers as $t)
        "{{ $t->id }}": {
            id: {{ $t->id }},
            name: @json($t->name),
            email: @json($t->email),
            phone: @json($t->phone ?? 'N/A'),
            empid: @json($t->employee_id ?? ('EMP-' . $t->id)),
            qualification: @json($t->qualification ?? 'Faculty Member'),
            experience: @json($t->years_of_experience ? ($t->years_of_experience . ' Years') : 'N/A'),
            salary: @json(number_format($t->basic_salary_pkr ?? 0)),
            emergency: @json($t->emergency_contact_phone ?? 'N/A'),
            profileUrl: @json(route($routePrefix . 'directory.teacher', $t->id))
        },
    @endforeach
};

function openTeacherModalFromId(teacherId) {
    if (!teacherId || !teachersData[teacherId]) return;
    const t = teachersData[teacherId];
    
    document.getElementById('tdm_avatar').innerText = (t.name.charAt(0) || 'T').toUpperCase();
    document.getElementById('tdm_name').innerText = t.name;
    document.getElementById('tdm_empid').innerText = 'Employee ID: ' + t.empid;
    document.getElementById('tdm_email').innerText = t.email;
    document.getElementById('tdm_phone').innerText = t.phone;
    document.getElementById('tdm_qual').innerText = t.qualification;
    document.getElementById('tdm_exp').innerText = t.experience;
    document.getElementById('tdm_salary').innerText = 'PKR ' + t.salary;
    document.getElementById('tdm_emergency').innerText = t.emergency;
    document.getElementById('tdm_profile_link').href = t.profileUrl;

    const modal = document.getElementById('teacherDetailsModal');
    modal.style.display = 'flex';
}

function closeTeacherDetailsModal() {
    document.getElementById('teacherDetailsModal').style.display = 'none';
}

function showSubjectDetailsModal(name, code, className, teacherName, room) {
    document.getElementById('sdm_title').innerText = '📘 ' + name + (code ? ' (' + code + ')' : '');
    document.getElementById('sdm_name_code').innerText = name + (code ? ' [' + code + ']' : '');
    document.getElementById('sdm_class').innerText = className || 'All Classes';
    document.getElementById('sdm_teacher').innerText = teacherName || 'Unassigned';
    document.getElementById('sdm_room').innerText = room || 'Campus Room';

    document.getElementById('subjectDetailsModal').style.display = 'flex';
}

function closeSubjectDetailsModal() {
    document.getElementById('subjectDetailsModal').style.display = 'none';
}

function showSectionDetailsModal(className, sectionName, count) {
    document.getElementById('sec_dm_title').innerText = '🏫 Class ' + className + ' — Section ' + sectionName;
    document.getElementById('sec_dm_class').innerText = className;
    document.getElementById('sec_dm_sec').innerText = 'Section ' + sectionName;
    document.getElementById('sec_dm_count').innerText = count + ' Lecture Block(s)';

    document.getElementById('sectionDetailsModal').style.display = 'flex';
}

function closeSectionDetailsModal() {
    document.getElementById('sectionDetailsModal').style.display = 'none';
}

function openConfigDaysHoursModal(allocationId = null) {
    const modal = document.getElementById('configDaysHoursModal');
    modal.classList.add('active');
    modal.style.display = 'flex';
    
    if (allocationId) {
        const select = document.getElementById('modal_allocation_id');
        select.value = allocationId;
        onModalAllocationSelectChange(select);
    }
}

function closeConfigDaysHoursModal() {
    const modal = document.getElementById('configDaysHoursModal');
    modal.classList.remove('active');
    modal.style.display = 'none';
}

function onModalAllocationSelectChange(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const updateUrl = selectedOption.getAttribute('data-update-url');
    const hours = selectedOption.getAttribute('data-hours') || 1;
    const minutes = selectedOption.getAttribute('data-minutes') || 0;
    const periods = selectedOption.getAttribute('data-periods') || 3;
    const days = JSON.parse(selectedOption.getAttribute('data-days') || '[]');
    const avails = JSON.parse(selectedOption.getAttribute('data-avails') || '{}');

    document.getElementById('configDaysHoursForm').action = updateUrl;
    document.getElementById('modal_alloc_hours').value = hours;
    document.getElementById('modal_alloc_minutes').value = minutes;
    document.getElementById('modal_alloc_periods').value = periods;

    const dayCheckboxes = document.querySelectorAll('.modal-day-checkbox');
    dayCheckboxes.forEach(cb => {
        const dayVal = cb.value;
        const badge = document.getElementById('modal_avail_badge_' + dayVal);
        const isWorking = avails[dayVal] !== undefined ? avails[dayVal] : true;
        const parentLabel = cb.closest('label');

        if (!isWorking) {
            cb.disabled = true;
            cb.checked = false;
            if (parentLabel) {
                parentLabel.style.opacity = '0.45';
                parentLabel.style.cursor = 'not-allowed';
            }
            if (badge) badge.innerText = '🔴 Off';
        } else {
            cb.disabled = false;
            cb.checked = days.length === 0 || days.includes(dayVal);
            if (parentLabel) {
                parentLabel.style.opacity = '1';
                parentLabel.style.cursor = 'pointer';
            }
            if (badge) badge.innerText = '🟢';
        }
    });
}

function openAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'flex';
}
function closeAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'none';
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
function toggleDurationAccordion(accId) {
    const el = document.getElementById(accId);
    const icon = document.getElementById(accId.replace('acc_', 'acc_icon_'));
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        if (icon) icon.innerHTML = '▲ Collapse Subjects';
    } else {
        el.style.display = 'none';
        if (icon) icon.innerHTML = '▼ Click to Expand';
    }
}

function toggleTeacherTimetable(teacherId) {
    const body = document.getElementById('teacher-timetable-body-' + teacherId);
    const icon = document.getElementById('teacher-acc-icon-' + teacherId);
    if (!body) return;

    if (body.style.display === 'none' || body.style.display === '') {
        body.style.display = 'block';
        if (icon) {
            icon.innerHTML = '▲ Collapse Schedule';
            icon.style.background = 'rgba(0,206,209,0.25)';
            icon.style.color = '#fff';
        }
    } else {
        body.style.display = 'none';
        if (icon) {
            icon.innerHTML = '▼ Click to View Schedule';
            icon.style.background = 'rgba(0,206,209,0.1)';
            icon.style.color = '#00ced1';
        }
    }
}

function expandAllTeachers() {
    document.querySelectorAll('[id^="teacher-timetable-body-"]').forEach(el => {
        el.style.display = 'block';
    });
    document.querySelectorAll('[id^="teacher-acc-icon-"]').forEach(icon => {
        icon.innerHTML = '▲ Collapse Schedule';
        icon.style.background = 'rgba(0,206,209,0.25)';
        icon.style.color = '#fff';
    });
}

function collapseAllTeachers() {
    document.querySelectorAll('[id^="teacher-timetable-body-"]').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('[id^="teacher-acc-icon-"]').forEach(icon => {
        icon.innerHTML = '▼ Click to View Schedule';
        icon.style.background = 'rgba(0,206,209,0.1)';
        icon.style.color = '#00ced1';
    });
}

@if($viewType === 'section')
document.addEventListener('DOMContentLoaded', function() {
    if (typeof expandAllGrades === 'function') {
        expandAllGrades();
    }
});
@endif
</script>
@endsection

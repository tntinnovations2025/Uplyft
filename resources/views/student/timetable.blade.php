@extends('layouts.app')

@section('title', 'Class Timetable & Schedule')
@section('page-header', 'Class Timetable')

@section('content')
<style>
    .timetable-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #0f172a;
    }

    /* 1. Header Strip */
    .timetable-header-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 6px 18px -3px rgba(15, 23, 42, 0.03);
    }

    .timetable-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .timetable-header-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        flex-shrink: 0;
    }

    .timetable-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.2;
    }

    .timetable-header-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .timetable-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
    }

    .view-switcher-group {
        display: inline-flex;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 3px;
        border-radius: 12px;
        gap: 2px;
    }

    .btn-view-switch {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view-switch.active {
        background: #ffffff;
        color: #4f46e5;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        font-weight: 800;
    }

    /* 2. Day Filter Quick Jump Navigation */
    .day-jump-strip {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .day-jump-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .day-jump-chip:hover {
        border-color: #c7d2fe;
        color: #4338ca;
        background: #f8fafc;
    }
    .day-jump-chip.active-today {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #e11d48;
        font-weight: 800;
    }

    /* 3. Horizontal Days Swimlanes Layout */
    .horizontal-days-stack {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .horizontal-day-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 10px 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 4px 14px -3px rgba(15, 23, 42, 0.03);
        display: grid;
        grid-template-columns: 90px 1fr;
        gap: 10px;
        align-items: center;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .horizontal-day-row:hover {
        border-color: #cbd5e1;
    }
    .horizontal-day-row.is-today-row {
        border: 1.5px solid #f43f5e;
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
        box-shadow: 0 4px 16px -2px rgba(244, 63, 94, 0.08);
    }

    @media (max-width: 860px) {
        .horizontal-day-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }
    }

    .day-pillar-card {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding-right: 12px;
        border-right: 1px solid #f1f5f9;
    }

    @media (max-width: 860px) {
        .day-pillar-card {
            border-right: none;
            border-bottom: 1px solid #f1f5f9;
            padding-right: 0;
            padding-bottom: 8px;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .day-name-heading {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: capitalize;
    }

    .day-today-tag {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #e11d48, #f43f5e);
        color: #ffffff;
        padding: 1px 6px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        box-shadow: 0 2px 6px rgba(225, 29, 72, 0.2);
        width: fit-content;
    }

    .day-count-badge {
        font-size: 10.5px;
        font-weight: 700;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2px 7px;
        border-radius: 6px;
        width: fit-content;
    }

    /* Horizontal Lectures Track - Fits 5 slots without scroll */
    .day-lectures-track {
        display: flex;
        flex-direction: row;
        gap: 6px;
        align-items: stretch;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    @media (max-width: 860px) {
        .day-lectures-track {
            padding-bottom: 2px;
        }
    }

    .lecture-slot-card {
        min-width: 0;
        flex: 1 1 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 7px 8px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: all 0.2s ease;
        flex-shrink: 0;
        width: 105px;
    }
    .lecture-slot-card:hover {
        border-color: #818cf8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.1);
    }

    .lecture-time-badge {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        font-size: 9px;
        font-weight: 800;
        font-family: monospace;
        color: #4338ca;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        padding: 1px 5px;
        border-radius: 5px;
        white-space: nowrap;
    }

    .lecture-code-badge {
        font-size: 8px;
        font-weight: 800;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 0.5px 4px;
        border-radius: 3px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lecture-subject-title {
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        font-weight: 800;
        color: #0f172a;
        margin: 1px 0 0;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lecture-class-tag {
        font-size: 8.5px;
        font-weight: 700;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        width: fit-content;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lecture-footer-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4px;
        padding-top: 6px;
        border-top: 1px solid #f1f5f9;
        font-size: 10px;
        color: #64748b;
        font-weight: 600;
    }

    .lecture-room-tag {
        font-size: 8.5px;
        font-weight: 800;
        color: #059669;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .empty-day-banner {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
        width: 100%;
    }

    /* 4. Weekly Calendar Matrix View */
    .matrix-view-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 6px 18px -3px rgba(15, 23, 42, 0.03);
    }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .matrix-table th {
        background: #f8fafc;
        padding: 12px 14px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569;
        border: 1px solid #e2e8f0;
        text-align: center;
    }

    .matrix-table td {
        padding: 10px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
        min-width: 150px;
    }

    .matrix-time-header {
        background: #f8fafc;
        font-family: monospace;
        font-size: 11.5px;
        font-weight: 800;
        color: #4338ca;
        text-align: center;
        white-space: nowrap;
    }
</style>

<div class="timetable-wrapper">

    <!-- 1. HEADER HERO STRIP -->
    <div class="timetable-header-card">
        <div class="timetable-header-left">
            <div class="timetable-header-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <h1 class="timetable-header-title">Class Timetable &amp; Schedule</h1>
                <div class="timetable-header-sub">
                    <span>Weekly academic schedule</span>
                    <span class="timetable-meta-pill">
                        <i class="fa-solid fa-school text-indigo-500"></i>
                        <span>Class: <strong>{{ $classSection?->instituteClass?->custom_name ?? $classSection?->instituteClass?->class_name ?? 'Class 10' }} ({{ $classSection?->section_name ?? '10-A' }})</strong></span>
                    </span>
                    <span class="timetable-meta-pill">
                        <i class="fa-solid fa-graduation-cap text-indigo-500"></i>
                        <span>Student: <strong>{{ $student->first_name ?? 'Student' }} {{ $student->last_name ?? '' }}</strong></span>
                    </span>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <!-- View Switcher -->
            <div class="view-switcher-group">
                <button type="button" id="btn-swimlane" onclick="switchTimetableView('swimlane')" class="btn-view-switch active">
                    <i class="fa-solid fa-bars-staggered"></i>
                    <span>Horizontal Days</span>
                </button>
                <button type="button" id="btn-matrix" onclick="switchTimetableView('matrix')" class="btn-view-switch">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Weekly Matrix</span>
                </button>
            </div>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="btn-view-switch" style="background:#ffffff;border:1px solid #cbd5e1;padding:8px 14px">
                <i class="fa-solid fa-print text-slate-500"></i>
                <span style="color:#334155;font-weight:700">Print</span>
            </button>
        </div>
    </div>

    <!-- 2. QUICK DAY JUMP STRIP -->
    <div class="day-jump-strip">
        <span style="font-size:11.5px;font-weight:800;color:#64748b;margin-right:4px;text-transform:uppercase;letter-spacing:0.5px">
            Days:
        </span>
        @php $todayDay = strtolower(date('l')); @endphp
        @foreach($days as $d)
            @php
                $isToday = ($todayDay === $d);
                $daySlotCount = $timetablesByDay->get($d, collect())->count();
            @endphp
            <a href="#day-{{ $d }}" class="day-jump-chip {{ $isToday ? 'active-today' : '' }}">
                <i class="fa-regular fa-calendar {{ $isToday ? 'text-rose-600' : 'text-slate-400' }}"></i>
                <span style="text-transform:capitalize">{{ $d }}</span>
                <span style="font-size:10.5px;padding:1px 5px;border-radius:10px;background:{{ $isToday ? '#ffe4e6' : '#f1f5f9' }};color:{{ $isToday ? '#e11d48' : '#64748b' }};font-weight:800">
                    {{ $daySlotCount }}
                </span>
                @if($isToday)
                    <span style="font-size:9.5px;font-weight:800;color:#e11d48;letter-spacing:0.3px">• TODAY</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- 3. VIEW 1: HORIZONTAL DAYS SWIMLANES (PRIMARY DEFAULT) -->
    <div id="view-swimlanes" class="horizontal-days-stack">
        @foreach($days as $day)
            @php
                $daySlots = $timetablesByDay->get($day, collect());
                $isToday = ($todayDay === $day);
                $secLabel = trim(($classSection?->instituteClass?->custom_name ?? $classSection?->instituteClass?->class_name ?? '') . ' ' . ($classSection?->section_name ?? ''));
            @endphp

            <div id="day-{{ $day }}" class="horizontal-day-row {{ $isToday ? 'is-today-row' : '' }}">
                
                <!-- Left Day Pillar Column -->
                <div class="day-pillar-card">
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <div class="day-name-heading">
                            <i class="fa-regular fa-calendar-check {{ $isToday ? 'text-rose-600' : 'text-indigo-600' }}"></i>
                            <span>{{ $day }}</span>
                        </div>
                        @if($isToday)
                            <div class="day-today-tag">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Today</span>
                            </div>
                        @endif
                    </div>

                    <div class="day-count-badge">
                        {{ $daySlots->count() }} {{ Str::plural('Lecture', $daySlots->count()) }}
                    </div>
                </div>

                <!-- Right Horizontal Lectures Track -->
                <div>
                    @if($daySlots->isNotEmpty())
                        <div class="day-lectures-track">
                            @foreach($daySlots as $slot)
                                @php
                                    $roomNum = $slot->room?->room_number ?? '1';
                                    $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                                @endphp
                                <div class="lecture-slot-card">
                                    <div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px">
                                            <span class="lecture-time-badge">
                                                <i class="fa-regular fa-clock"></i>
                                                <span>{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</span>
                                            </span>
                                            <span class="lecture-code-badge">
                                                {{ $slot->subject?->subject_code ?? 'SUB' }}
                                            </span>
                                        </div>

                                        <h3 class="lecture-subject-title">
                                            {{ $slot->subject?->subject_name ?? 'Subject Lecture' }}
                                        </h3>

                                        <div style="margin-top:6px">
                                            <span class="lecture-class-tag">
                                                <i class="fa-solid fa-chalkboard text-indigo-500"></i>
                                                <span>{{ $secLabel ?: 'Class Section' }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="lecture-footer-meta">
                                        <span style="display:flex;align-items:center;gap:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                            <i class="fa-solid fa-user-tie text-slate-400"></i>
                                            <span class="truncate">{{ $slot->teacher?->name ?? 'Faculty' }}</span>
                                        </span>
                                        <span class="lecture-room-tag">
                                            <i class="fa-solid fa-door-open"></i>
                                            <span>{{ $cleanRoom }}</span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-day-banner">
                            <span style="font-size:16px">☕</span>
                            <span>No classes scheduled for <strong>{{ ucfirst($day) }}</strong> — Academic Free Day.</span>
                        </div>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

    <!-- 4. VIEW 2: WEEKLY CALENDAR MATRIX GRID (OPTIONAL TOGGLE) -->
    <div id="view-matrix" class="matrix-view-card" style="display:none">
        <div style="overflow-x:auto">
            <table class="matrix-table" style="min-width:850px">
                <thead>
                    <tr>
                        <th style="width:130px;background:#eef2ff;color:#4338ca">Time</th>
                        @foreach($days as $day)
                            @php $isToday = ($todayDay === $day); @endphp
                            <th style="{{ $isToday ? 'background:#fff1f2;color:#e11d48;border-color:#fecdd3;' : '' }}">
                                <div style="display:flex;align-items:center;justify-content:center;gap:5px">
                                    <span>{{ ucfirst($day) }}</span>
                                    @if($isToday)
                                        <span style="font-size:9px;background:#e11d48;color:#ffffff;padding:1px 5px;border-radius:4px">TODAY</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($timeSlotsList as $tSlot)
                        <tr>
                            <td class="matrix-time-header">
                                <i class="fa-regular fa-clock"></i>
                                <div>{{ $tSlot }}</div>
                            </td>
                            @foreach($days as $day)
                                @php $cell = $weeklyGrid[$tSlot][$day] ?? null; @endphp
                                @if($cell)
                                    @php
                                        $roomNum = $cell->room?->room_number ?? '1';
                                        $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                                    @endphp
                                    <td>
                                        <div style="background:#ffffff;border:1px solid #c7d2fe;border-radius:10px;padding:8px 10px;box-shadow:0 1px 2px rgba(15,23,42,0.02)">
                                            <div style="font-size:12.5px;font-weight:800;color:#0f172a">
                                                {{ $cell->subject?->subject_name }}
                                            </div>
                                            <div style="font-size:11px;color:#4338ca;font-weight:700;margin-top:2px">
                                                {{ $cell->subject?->subject_code ?? 'SUB' }}
                                            </div>
                                            <div style="font-size:11px;color:#64748b;margin-top:4px;display:flex;align-items:center;justify-content:space-between">
                                                <span>{{ $cell->teacher?->name ?? 'Faculty' }}</span>
                                                <span class="lecture-room-tag" style="font-size:10px;padding:1px 5px">
                                                    {{ $cleanRoom }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td style="background:#fbfcfe;text-align:center">
                                        <span style="font-size:11px;color:#94a3b8;font-style:italic">—</span>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:36px;color:#64748b">
                                <div style="font-size:28px;margin-bottom:6px">🗓️</div>
                                <div style="font-weight:800;color:#0f172a">No Timetable Slots Scheduled</div>
                                <div style="font-size:12px;margin-top:2px">No active timetable slots have been scheduled for your enrolled section.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function switchTimetableView(mode) {
        const swimlaneView = document.getElementById('view-swimlanes');
        const matrixView = document.getElementById('view-matrix');
        const btnSwimlane = document.getElementById('btn-swimlane');
        const btnMatrix = document.getElementById('btn-matrix');

        if (mode === 'swimlane') {
            swimlaneView.style.display = 'flex';
            matrixView.style.display = 'none';
            btnSwimlane.classList.add('active');
            btnMatrix.classList.remove('active');
        } else {
            swimlaneView.style.display = 'none';
            matrixView.style.display = 'block';
            btnMatrix.classList.add('active');
            btnSwimlane.classList.remove('active');
        }
    }
</script>
@endsection


@extends('layouts.app')

@section('title', 'Class Timetable - Student Portal')
@section('page-header', 'Class Timetable')

@section('content')
<style>
    /* =========================================================================
       UPLYFT STUDENT CLASS TIMETABLE: INK & AMBER DESIGN SYSTEM
       Clean, Highly Readable, Spacious, Responsive
       ========================================================================= */
    .timetable-wrapper {
        display: flex;
        flex-direction: column;
        gap: 16px;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #1B1A17;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    /* 1. Header Hero Card */
    .timetable-header-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }

    .timetable-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .timetable-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        flex-shrink: 0;
    }

    .timetable-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        line-height: 1.2;
    }

    .timetable-header-sub {
        font-size: 12px;
        color: #64748B;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .timetable-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
    }

    .timetable-actions-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .view-switcher-group {
        display: inline-flex;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        padding: 3px;
        border-radius: 10px;
        gap: 2px;
    }

    .btn-view-switch {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #64748B;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-view-switch.active {
        background: #FFFFFF;
        color: #4F46E5;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        font-weight: 800;
    }

    .btn-action-outline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #334155;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-action-outline:hover {
        background: #F8FAFC;
        border-color: #94A3B8;
    }

    .btn-action-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid #4338CA;
        background: #4F46E5;
        color: #FFFFFF;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-action-primary:hover {
        background: #4338CA;
    }

    /* 2. Interactive Day Filter Bar */
    .day-tabs-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
        padding: 4px 0 2px;
        scrollbar-width: thin;
    }

    .day-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .day-tab-btn:hover {
        border-color: #CBD5E1;
        background: #F8FAFC;
        color: #0F172A;
    }
    .day-tab-btn.active {
        background: #0F172A !important;
        border-color: #0F172A !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18) !important;
    }
    .day-tab-btn.active .day-pill-count {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #FFFFFF !important;
    }
    .day-tab-btn.is-today-btn {
        border-color: #FECDD3;
        background: #FFF1F2;
        color: #E11D48;
    }

    .day-pill-count {
        font-size: 10.5px;
        font-weight: 800;
        padding: 1px 6px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #64748B;
    }

    /* 3. Single-Day Detailed Timeline Cards (Spacious & Clean) */
    .day-panel {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .day-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
    }

    .period-timeline-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    }
    .period-timeline-card:hover {
        border-color: #94A3B8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .period-timeline-card.is-current-period {
        border-color: #D48A2E;
        background: #FDFBF7;
        box-shadow: 0 4px 14px rgba(212, 138, 46, 0.12);
    }

    .period-left-time {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 170px;
    }

    .period-num-badge {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #EEF2FF;
        color: #4F46E5;
        border: 1px solid #C7D2FE;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .period-time-text {
        font-family: monospace;
        font-size: 13px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.2;
    }

    .period-duration-text {
        font-size: 11px;
        color: #64748B;
        font-weight: 600;
    }

    .period-center-subject {
        flex: 1;
        min-width: 0;
    }

    .period-subject-name {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 2px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .period-code-pill {
        font-size: 10px;
        font-weight: 800;
        color: #4338CA;
        background: #EEF2FF;
        border: 1px solid #C7D2FE;
        padding: 1px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .period-right-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .period-teacher-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }

    .period-room-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #059669;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
    }

    @media (max-width: 768px) {
        .period-timeline-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .period-right-meta {
            width: 100%;
            justify-content: space-between;
            padding-top: 8px;
            border-top: 1px dashed #E2E8F0;
        }
    }

    /* 4. All Days Overview: Responsive Clean Grid */
    .all-days-container {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .all-day-section {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    }
    .all-day-section.is-today-section {
        border-color: #F43F5E;
        background: linear-gradient(180deg, #FFFFFF 0%, #FFFAFA 100%);
    }

    .all-day-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #F1F5F9;
    }

    .lecture-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 8px;
    }

    .grid-lecture-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .grid-lecture-card:hover {
        background: #FFFFFF;
        border-color: #4F46E5;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(79, 70, 229, 0.08);
    }

    .empty-state-card {
        background: #FFFFFF;
        border: 1px dashed #CBD5E1;
        border-radius: 12px;
        padding: 28px 16px;
        text-align: center;
        color: #64748B;
    }

    /* 5. Weekly Matrix View */
    .matrix-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
        overflow-x: auto;
    }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 780px;
    }
    .matrix-table th {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        text-align: center;
    }
    .matrix-table td {
        border: 1px solid #E2E8F0;
        padding: 8px;
        vertical-align: top;
        height: 70px;
    }
    .matrix-time-col {
        background: #F8FAFC;
        font-family: monospace;
        font-size: 11px;
        font-weight: 800;
        color: #4338CA;
        text-align: center;
        white-space: nowrap;
        padding: 8px;
    }

    /* Print Optimization */
    @media print {
        .day-tabs-bar, .view-switcher-group, .btn-action-outline, .btn-action-primary, .app-sidebar, header {
            display: none !important;
        }
        .timetable-wrapper {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .matrix-table {
            min-width: 100% !important;
        }
    }
</style>

<div class="timetable-wrapper">

    {{-- 1. HEADER HERO STRIP --}}
    <div class="timetable-header-card">
        <div class="timetable-header-left">
            <div class="timetable-header-icon">
                📅
            </div>
            <div>
                <h1 class="timetable-header-title">Class Timetable</h1>
                <div class="timetable-header-sub">
                    <span>Weekly lecture &amp; room schedule</span>
                    <span class="timetable-meta-pill">
                        🏫 <strong>{{ $classSection?->instituteClass?->custom_name ?? $classSection?->instituteClass?->class_name ?? 'Class' }} — {{ $classSection?->section_name ?? 'Section' }}</strong>
                    </span>
                    <span class="timetable-meta-pill">
                        🎓 <strong>{{ $student->first_name ?? 'Student' }} {{ $student->last_name ?? '' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="timetable-actions-group">
            {{-- View Mode Switcher --}}
            <div class="view-switcher-group">
                <button type="button" id="btn-view-tabs" onclick="switchMainLayout('tabs')" class="btn-view-switch active">
                    📋 Day View
                </button>
                <button type="button" id="btn-view-all" onclick="switchMainLayout('all')" class="btn-view-switch">
                    📑 All Days
                </button>
                <button type="button" id="btn-view-matrix" onclick="switchMainLayout('matrix')" class="btn-view-switch">
                    📊 Matrix
                </button>
            </div>

            {{-- Download & Print --}}
            <a href="{{ route('student.timetable.download') }}" class="btn-action-primary" title="Export Timetable as Excel (.xlsx)">
                📥 Download
            </a>
            <button type="button" onclick="window.print()" class="btn-action-outline" title="Print Timetable">
                🖨️ Print
            </button>
        </div>
    </div>

    @php
        $todayDay = strtolower(date('l'));
        // Find default active day: today if it has classes, otherwise first day with classes, or monday
        $hasTodaySlots = $timetablesByDay->get($todayDay, collect())->isNotEmpty();
        $defaultDay = $hasTodaySlots ? $todayDay : ($timetablesByDay->keys()->first() ?? 'monday');
    @endphp

    {{-- 2. DAY SELECTOR TABS BAR --}}
    <div class="day-tabs-bar" id="dayTabsContainer">
        @foreach($days as $d)
            @php
                $isToday = ($todayDay === $d);
                $count = $timetablesByDay->get($d, collect())->count();
                $isActive = ($d === $defaultDay);
            @endphp
            <button type="button" class="day-tab-btn {{ $isActive ? 'active' : '' }} {{ $isToday ? 'is-today-btn' : '' }}" onclick="selectDayTab('{{ $d }}')" id="tab-btn-{{ $d }}">
                <span>{{ ucfirst($d) }}</span>
                @if($isToday)
                    <span style="font-size: 8.5px; font-weight: 800; background: #E11D48; color: #FFFFFF; padding: 1px 4px; border-radius: 3px;">TODAY</span>
                @endif
                <span class="day-pill-count">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    {{-- 3. LAYOUT 1: SINGLE DAY DETAILED VIEW (Active by default) --}}
    <div id="layout-day-tabs" style="display: flex; flex-direction: column; gap: 12px;">
        @foreach($days as $d)
            @php
                $daySlots = $timetablesByDay->get($d, collect());
                $isToday = ($todayDay === $d);
                $isShown = ($d === $defaultDay);
            @endphp
            <div class="day-panel" id="panel-day-{{ $d }}" style="display: {{ $isShown ? 'flex' : 'none' }};">
                
                {{-- Day Banner Header --}}
                <div class="day-panel-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">🗓️</span>
                        <span style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 800; color: #0F172A;">
                            {{ ucfirst($d) }}'s Schedule
                        </span>
                        @if($isToday)
                            <span style="font-size: 9.5px; font-weight: 800; background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; padding: 2px 7px; border-radius: 5px;">
                                Active Today
                            </span>
                        @endif
                    </div>
                    <span style="font-size: 11.5px; font-weight: 700; color: #64748B;">
                        {{ $daySlots->count() }} {{ \Illuminate\Support\Str::plural('Period', $daySlots->count()) }} Scheduled
                    </span>
                </div>

                {{-- Periods List --}}
                @if($daySlots->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($daySlots as $idx => $slot)
                            @php
                                $roomNum = $slot->room?->room_number ?? '1';
                                $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                                $startFormatted = substr($slot->start_time, 0, 5);
                                $endFormatted = substr($slot->end_time, 0, 5);

                                // Calculate duration in minutes if possible
                                try {
                                    $t1 = \Carbon\Carbon::parse($slot->start_time);
                                    $t2 = \Carbon\Carbon::parse($slot->end_time);
                                    $durationMins = $t1->diffInMinutes($t2) . ' mins';
                                } catch (\Throwable $e) {
                                    $durationMins = '45 mins';
                                }
                            @endphp

                            <div class="period-timeline-card">
                                {{-- Left: Period & Time --}}
                                <div class="period-left-time">
                                    <div class="period-num-badge">
                                        P{{ $idx + 1 }}
                                    </div>
                                    <div>
                                        <div class="period-time-text">
                                            {{ $startFormatted }} – {{ $endFormatted }}
                                        </div>
                                        <div class="period-duration-text">
                                            ⏱️ {{ $durationMins }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Center: Subject --}}
                                <div class="period-center-subject">
                                    <div class="period-subject-name">
                                        <span>{{ $slot->subject?->subject_name ?? 'Subject Lecture' }}</span>
                                        @if($slot->subject?->subject_code)
                                            <span class="period-code-pill">{{ $slot->subject->subject_code }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Right: Teacher & Room (No Truncation) --}}
                                <div class="period-right-meta">
                                    <div class="period-teacher-pill" title="Assigned Faculty">
                                        <span>👨‍🏫</span>
                                        <span>{{ $slot->teacher?->name ?? 'Faculty' }}</span>
                                    </div>
                                    <div class="period-room-pill" title="Classroom / Lab Venue">
                                        <span>🚪</span>
                                        <span>{{ $cleanRoom }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-card">
                        <div style="font-size: 26px; margin-bottom: 6px;">🎉</div>
                        <div style="font-size: 13.5px; font-weight: 800; color: #0F172A; margin-bottom: 2px;">
                            No Classes Scheduled for {{ ucfirst($d) }}
                        </div>
                        <div style="font-size: 11.5px; color: #64748B;">
                            This is an academic free day or self-study period for your class section.
                        </div>
                    </div>
                @endif

            </div>
        @endforeach
    </div>

    {{-- 4. LAYOUT 2: ALL DAYS OVERVIEW GRID --}}
    <div id="layout-all-days" class="all-days-container" style="display: none;">
        @foreach($days as $d)
            @php
                $daySlots = $timetablesByDay->get($d, collect());
                $isToday = ($todayDay === $d);
            @endphp
            <div class="all-day-section {{ $isToday ? 'is-today-section' : '' }}">
                <div class="all-day-title-row">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #0F172A;">
                            {{ ucfirst($d) }}
                        </span>
                        @if($isToday)
                            <span style="font-size: 9px; font-weight: 800; background: #FFF1F2; color: #E11D48; border: 1px solid #FECDD3; padding: 1px 5px; border-radius: 4px;">
                                TODAY
                            </span>
                        @endif
                    </div>
                    <span style="font-size: 11px; font-weight: 700; color: #64748B;">
                        {{ $daySlots->count() }} {{ \Illuminate\Support\Str::plural('Lecture', $daySlots->count()) }}
                    </span>
                </div>

                @if($daySlots->isNotEmpty())
                    <div class="lecture-grid">
                        @foreach($daySlots as $slot)
                            @php
                                $roomNum = $slot->room?->room_number ?? '1';
                                $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                            @endphp
                            <div class="grid-lecture-card">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                    <span style="font-family: monospace; font-size: 11px; font-weight: 800; color: #4338CA; background: #EEF2FF; padding: 2px 6px; border-radius: 5px;">
                                        {{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}
                                    </span>
                                    @if($slot->subject?->subject_code)
                                        <span style="font-size: 9.5px; font-weight: 800; color: #475569; background: #F1F5F9; padding: 1px 5px; border-radius: 4px;">
                                            {{ $slot->subject->subject_code }}
                                        </span>
                                    @endif
                                </div>

                                <div style="font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 800; color: #0F172A; line-height: 1.25;">
                                    {{ $slot->subject?->subject_name }}
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-top: auto; padding-top: 6px; border-top: 1px dashed #E2E8F0; font-size: 11px;">
                                    <span style="color: #475569; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        👤 {{ $slot->teacher?->name ?? 'Faculty' }}
                                    </span>
                                    <span style="font-size: 10px; font-weight: 800; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 1px 5px; border-radius: 4px; flex-shrink: 0;">
                                        {{ $cleanRoom }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size: 11.5px; color: #94A3B8; font-style: italic; padding: 8px 0;">
                        No lectures scheduled.
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- 5. LAYOUT 3: WEEKLY MATRIX CALENDAR TABLE --}}
    <div id="layout-matrix" class="matrix-card" style="display: none;">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th style="width: 120px;">Time Slot</th>
                    @foreach($days as $d)
                        @php $isToday = ($todayDay === $d); @endphp
                        <th style="{{ $isToday ? 'background:#FFF1F2;color:#E11D48;border-color:#FECDD3;' : '' }}">
                            {{ ucfirst($d) }}
                            @if($isToday)
                                <span style="font-size: 8px; background: #E11D48; color: #FFFFFF; padding: 1px 4px; border-radius: 3px; margin-left: 2px;">TODAY</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($timeSlotsList as $tSlot)
                    <tr>
                        <td class="matrix-time-col">
                            {{ $tSlot }}
                        </td>
                        @foreach($days as $d)
                            @php $cell = $weeklyGrid[$tSlot][$d] ?? null; @endphp
                            <td>
                                @if($cell)
                                    @php
                                        $roomNum = $cell->room?->room_number ?? '1';
                                        $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                                    @endphp
                                    <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 7px; padding: 6px 8px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                                        <div style="font-size: 12px; font-weight: 800; color: #0F172A; line-height: 1.25;">
                                            {{ $cell->subject?->subject_name }}
                                        </div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-top: 4px; font-size: 10px;">
                                            <span style="color: #475569; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $cell->teacher?->name ?? 'Faculty' }}
                                            </span>
                                            <span style="font-weight: 800; color: #059669;">
                                                {{ $cleanRoom }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div style="text-align: center; color: #CBD5E1; font-size: 11px; padding-top: 18px;">
                                        —
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #64748B;">
                            No active timetable slots configured.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    // Tab Switching for Days
    function selectDayTab(day) {
        // Hide all day panels
        document.querySelectorAll('.day-panel').forEach(p => p.style.display = 'none');
        // Deactivate all day tab buttons
        document.querySelectorAll('.day-tab-btn').forEach(b => b.classList.remove('active'));

        // Show target panel and activate button
        const targetPanel = document.getElementById('panel-day-' + day);
        const targetBtn = document.getElementById('tab-btn-' + day);
        if (targetPanel) targetPanel.style.display = 'flex';
        if (targetBtn) targetBtn.classList.add('active');

        // Make sure we are on the tabs view
        switchMainLayout('tabs', false);
    }

    // Main Layout Mode Switcher (Day View vs All Days vs Matrix)
    function switchMainLayout(mode, selectFirstDay = true) {
        const layoutTabs = document.getElementById('layout-day-tabs');
        const layoutAll = document.getElementById('layout-all-days');
        const layoutMatrix = document.getElementById('layout-matrix');
        const dayTabsBar = document.getElementById('dayTabsContainer');

        const btnTabs = document.getElementById('btn-view-tabs');
        const btnAll = document.getElementById('btn-view-all');
        const btnMatrix = document.getElementById('btn-view-matrix');

        // Reset button states
        [btnTabs, btnAll, btnMatrix].forEach(b => b.classList.remove('active'));

        if (mode === 'tabs') {
            layoutTabs.style.display = 'flex';
            layoutAll.style.display = 'none';
            layoutMatrix.style.display = 'none';
            dayTabsBar.style.display = 'flex';
            btnTabs.classList.add('active');
        } else if (mode === 'all') {
            layoutTabs.style.display = 'none';
            layoutAll.style.display = 'flex';
            layoutMatrix.style.display = 'none';
            dayTabsBar.style.display = 'none';
            btnAll.classList.add('active');
        } else if (mode === 'matrix') {
            layoutTabs.style.display = 'none';
            layoutAll.style.display = 'none';
            layoutMatrix.style.display = 'block';
            dayTabsBar.style.display = 'none';
            btnMatrix.classList.add('active');
        }
    }
</script>
@endsection

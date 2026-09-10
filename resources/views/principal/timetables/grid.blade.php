@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Master Timetable Grid')
@section('breadcrumb', 'Timetable Grid')

@section('content')
@php
    $routePrefix = request()->routeIs('teacher.*') ? 'teacher.' : 'principal.';
@endphp
<style>
    .horiz-matrix-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        table-layout: fixed;
    }
    .horiz-matrix-table th {
        background: #f1f5f9;
        color: #0f172a;
        text-align: center;
        padding: 14px 10px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid #e2e8f0;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .horiz-matrix-table td {
        padding: 10px 8px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
        min-height: 70px;
        font-size: 12px;
        background: #ffffff;
        transition: background 0.15s ease;
    }
    .horiz-matrix-table td:hover {
        background: #fdf2f8;
    }
    .horiz-matrix-table .section-col {
        background: #f8fafc;
        font-weight: 800;
        color: #0f172a;
        font-size: 13px;
        width: 160px;
        text-align: left;
        padding-left: 14px;
    }
    .tt-slot {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-left: 4px solid #e1306c;
        border-radius: 8px;
        padding: 8px 6px;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 3px;
        text-align: left;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .tt-slot .subject {
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
    }
    .tt-slot .teacher {
        font-size: 11px;
        color: #475569;
        font-weight: 600;
    }
    .tt-slot .room {
        font-size: 10px;
        color: #059669;
        font-weight: 700;
        margin-top: 2px;
    }
    .empty-cell {
        color: #94a3b8;
        font-size: 18px;
        font-weight: 300;
    }

    .day-tab {
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .day-tab:hover {
        border-color: #e1306c;
        color: #be185d;
    }
    .day-tab.active {
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(225, 48, 108, 0.25);
    }

    .grade-header-row td {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 800 !important;
        text-align: left !important;
        padding-left: 14px !important;
        font-size: 13px !important;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }

    @media print {
        body { background: #fff !important; color: #000 !important; }
        .sidebar, .topbar, .no-print { display: none !important; }
        .main { margin: 0; }
        .content { padding: 10px; }
        .horiz-matrix-table th { background: #f0f0f0 !important; color: #000 !important; }
        .horiz-matrix-table td { background: #fff !important; color: #000 !important; }
        .tt-slot { background: #f9f9f9 !important; border-color: #ccc !important; }
        .tt-slot .subject { color: #000 !important; }
        .tt-slot .teacher { color: #444 !important; }
        .tt-slot .room { color: #666 !important; }
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px" class="no-print">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">📋 Master Tabular Timetable Grid</h1>
        <p style="color:#64748b;font-size:13px;margin-top:4px;font-weight:500">
            Single unified timetable showing <strong>All Class Sections on the left Y-axis</strong> and <strong>Time Slots across top X-axis</strong> for <strong>{{ $activeTerm?->name }}</strong>.
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'class']) }}" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            🏫 Class-Wise
        </a>
        <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'teacher']) }}" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            👨‍🏫 Teacher-Wise
        </a>
        <a href="{{ route($routePrefix . 'timetables.export') }}" class="btn btn-primary" style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
            📗 Download Excel (.xlsx)
        </a>
        <button type="button" onclick="window.print()" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            🖨️ Print
        </button>
    </div>
</div>

<!-- Day Selector Tabs -->
<div style="display:flex;gap:10px;margin-bottom:24px;overflow-x:auto;padding-bottom:6px" class="no-print">
    @foreach($days as $day)
        <a href="{{ route('principal.timetables.grid', ['day' => $day]) }}" 
           class="day-tab {{ strtolower($selectedDay) === strtolower($day) ? 'active' : '' }}">
            📅 {{ ucfirst($day) }}
        </a>
    @endforeach
</div>

@if($timeSlots->isEmpty())
<div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:#64748b;font-size:16px;margin-bottom:16px">No timetable slots have been generated yet.</p>
    <a href="{{ route('principal.timetables.index') }}" class="btn btn-primary">
        ← Go to Timetable Matrix to Generate
    </a>
</div>
@else
@php
    $hasUnassignedRoom = isset($roomTimeGrid[0][strtolower($selectedDay)]) && count($roomTimeGrid[0][strtolower($selectedDay)]) > 0;

    // 1. Determine timeline bounds from timeSlots
    $minStartSec = null;
    $maxEndSec = null;
    foreach($timeSlots as $ts) {
        $st = strtotime($ts['start']);
        $et = strtotime($ts['end']);
        if ($minStartSec === null || $st < $minStartSec) $minStartSec = $st;
        if ($maxEndSec === null || $et > $maxEndSec) $maxEndSec = $et;
    }
    $minStartSec = $minStartSec ?? strtotime('08:00');
    $maxEndSec = $maxEndSec ?? strtotime('16:00');

    $timelineStartMin = (int) date('H', $minStartSec) * 60 + (int) date('i', $minStartSec);
    $timelineEndMin = (int) date('H', $maxEndSec) * 60 + (int) date('i', $maxEndSec);
    if ($timelineEndMin <= $timelineStartMin) {
        $timelineEndMin = $timelineStartMin + 480;
    }
    $totalTimelineMins = $timelineEndMin - $timelineStartMin;
@endphp

<div class="card" style="margin-bottom:32px;padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
        <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
            📅 Master Daily Schedule — <span style="color:#e1306c;text-transform:capitalize">{{ $selectedDay }}</span>
        </h2>
        <span style="font-size:12px;color:#64748b;font-weight:600">
            Timeline Schedule across {{ $timeSlots->count() }} Time Slots
        </span>
    </div>

    <!-- HORIZONTAL GANTT TIMELINE TRACK -->
    <div style="overflow-x:auto;border-radius:12px;border:1px solid #e2e8f0;background:#ffffff">
        <div style="min-width:1400px">
            
            <!-- TIMELINE HEADER TRACK -->
            <div style="display:flex;width:100%;background:#f8fafc;border-bottom:2px solid #e2e8f0">
                <div style="width:170px;flex-shrink:0;padding:14px;font-size:12px;font-weight:800;color:#0f172a;border-right:1px solid #e2e8f0;text-transform:uppercase">
                    ROOM NUMBER
                </div>
                <div style="flex:1;position:relative;display:flex;align-items:center;height:48px">
                    @foreach($timeSlots as $ts)
                        @php
                            $tsStartSec = strtotime($ts['start']);
                            $tsEndSec = strtotime($ts['end']);
                            $tsStartMin = (int) date('H', $tsStartSec) * 60 + (int) date('i', $tsStartSec);
                            $tsEndMin = (int) date('H', $tsEndSec) * 60 + (int) date('i', $tsEndSec);
                            $tsWidthPct = (($tsEndMin - $tsStartMin) / $totalTimelineMins) * 100;
                        @endphp
                        <div style="width:{{ $tsWidthPct }}%;height:100%;border-right:1px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#e1306c">
                            ⏰ {{ $ts['start'] }} – {{ $ts['end'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ROOM ROWS -->
            @forelse($rooms as $rm)
                @php
                    $rawRoomSlots = $allSlots->filter(fn($s) => $s->room_id == $rm->id && strtolower($s->day_of_week) === strtolower($selectedDay));
                    $mergedRoomSlots = \App\Models\Timetable::mergeContiguousSlots($rawRoomSlots);
                @endphp
                <div style="display:flex;width:100%;border-bottom:1px solid #e2e8f0;background:#ffffff;min-height:104px">
                    <!-- Room Info Column -->
                    <div style="width:170px;flex-shrink:0;padding:14px;background:#f8fafc;border-right:1px solid #e2e8f0;display:flex;flex-direction:column;justify-content:center">
                        <div style="font-size:15px;font-weight:800;color:#0f172a">📍 {{ $rm->room_number }}</div>
                        <div style="font-size:11px;color:#0284c7;font-weight:700;margin-top:2px">{{ $rm->room_type }}</div>
                    </div>

                    <!-- Room Timeline Track Area -->
                    <div style="flex:1;position:relative;min-height:104px">
                        <!-- Hour Column Grid Lines background -->
                        <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;pointer-events:none">
                            @foreach($timeSlots as $ts)
                                @php
                                    $tsStartSec = strtotime($ts['start']);
                                    $tsEndSec = strtotime($ts['end']);
                                    $tsStartMin = (int) date('H', $tsStartSec) * 60 + (int) date('i', $tsStartSec);
                                    $tsEndMin = (int) date('H', $tsEndSec) * 60 + (int) date('i', $tsEndSec);
                                    $tsWidthPct = (($tsEndMin - $tsStartMin) / $totalTimelineMins) * 100;
                                @endphp
                                <div style="width:{{ $tsWidthPct }}%;height:100%;border-right:1px dashed #e2e8f0"></div>
                            @endforeach
                        </div>

                        <!-- Lecture Horizontal Rectangle Bars -->
                        @forelse($mergedRoomSlots as $slot)
                            @php
                                $sStartSec = strtotime($slot->start_time);
                                $sEndSec = strtotime($slot->end_time);
                                $sStartMin = (int) date('H', $sStartSec) * 60 + (int) date('i', $sStartSec);
                                $sEndMin = (int) date('H', $sEndSec) * 60 + (int) date('i', $sEndSec);

                                $durMins = max(15, $sEndMin - $sStartMin);
                                $leftPct = max(0, (($sStartMin - $timelineStartMin) / $totalTimelineMins) * 100);
                                $widthPct = min(100 - $leftPct, ($durMins / $totalTimelineMins) * 100);

                                // Format as "Grade 10 - A"
                                $cName = $slot->section->instituteClass->custom_name ?? 'Class';
                                $sName = $slot->section->section_name ?? 'A';
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
                                $shortClassCode = !empty($sClean) ? ($cName . ' - ' . strtoupper($sClean)) : $cName;

                                // Duration label
                                $h = floor($durMins / 60);
                                $m = $durMins % 60;
                                $durLabel = ($h > 0 ? $h . 'h ' : '') . ($m > 0 ? $m . 'm' : '');
                                $durLabel = trim($durLabel) ?: '1h';
                            @endphp

                            <div class="tt-horizontal-rect-card" 
                                 style="position:absolute;left:calc({{ $leftPct }}% + 4px);width:calc({{ $widthPct }}% - 8px);top:9px;height:86px;background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #e1306c;border-radius:12px;padding:9px 12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;z-index:2;transition:all 0.15s ease;"
                                 onmouseover="this.style.transform='scale(1.02)';this.style.zIndex='10';this.style.boxShadow='0 8px 24px rgba(225,48,108,0.2)';"
                                 onmouseout="this.style.transform='scale(1)';this.style.zIndex='2';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)';">
                                
                                <!-- LINE 1: CLASS & SUBJECT -->
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                                    <span style="font-size:11px;font-weight:800;color:#0284c7;background:#f0f9ff;border:1px solid #bae6fd;padding:2px 7px;border-radius:6px;white-space:nowrap">
                                        🎓 {{ $shortClassCode }}
                                    </span>
                                    <span style="font-size:12.5px;font-weight:800;color:#0f172a;white-space:nowrap;text-overflow:ellipsis;overflow:hidden">
                                        📚 {{ $slot->subject->subject_name }}
                                    </span>
                                </div>

                                <!-- LINE 2: TEACHER NAME -->
                                <div style="font-size:11.5px;font-weight:700;color:#334155;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;display:flex;align-items:center;gap:4px">
                                    <span>👨‍🏫</span>
                                    <span style="text-overflow:ellipsis;overflow:hidden">{{ $slot->teacher->name }}</span>
                                </div>

                                <!-- LINE 3: EXACT TIME RANGE & DURATION PILL -->
                                <div style="display:flex;align-items:center;justify-content:space-between;font-size:10.5px;font-weight:800;color:#e1306c">
                                    <span>⏰ {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}</span>
                                    <span style="background:#fdf2f8;border:1px solid #fbcfe8;color:#be185d;padding:1.5px 6px;border-radius:5px;font-size:10px">{{ $durLabel }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="display:flex;align-items:center;justify-content:center;height:104px;color:#94a3b8;font-size:13px">
                                — No lectures scheduled —
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:#64748b">
                    No rooms configured yet.
                </div>
            @endforelse

            <!-- UNASSIGNED ROOM ROW (IF ANY) -->
            @if($hasUnassignedRoom)
                @php
                    $rawUnassignedSlots = $allSlots->filter(fn($s) => empty($s->room_id) && strtolower($s->day_of_week) === strtolower($selectedDay));
                    $mergedUnassignedSlots = \App\Models\Timetable::mergeContiguousSlots($rawUnassignedSlots);
                @endphp
                <div style="display:flex;width:100%;border-bottom:1px solid #e2e8f0;background:#fef2f2;min-height:104px">
                    <div style="width:170px;flex-shrink:0;padding:14px;background:#fee2e2;border-right:1px solid #fecaca;display:flex;flex-direction:column;justify-content:center">
                        <div style="font-size:15px;font-weight:800;color:#ef4444">📍 Unassigned</div>
                        <div style="font-size:11px;color:#dc2626;font-weight:700;margin-top:2px">No Room Allocated</div>
                    </div>
                    <div style="flex:1;position:relative;min-height:104px">
                        <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;pointer-events:none">
                            @foreach($timeSlots as $ts)
                                @php
                                    $tsStartSec = strtotime($ts['start']);
                                    $tsEndSec = strtotime($ts['end']);
                                    $tsStartMin = (int) date('H', $tsStartSec) * 60 + (int) date('i', $tsStartSec);
                                    $tsEndMin = (int) date('H', $tsEndSec) * 60 + (int) date('i', $tsEndSec);
                                    $tsWidthPct = (($tsEndMin - $tsStartMin) / $totalTimelineMins) * 100;
                                @endphp
                                <div style="width:{{ $tsWidthPct }}%;height:100%;border-right:1px dashed #fecaca"></div>
                            @endforeach
                        </div>

                        @foreach($mergedUnassignedSlots as $uSlot)
                            @php
                                $sStartSec = strtotime($uSlot->start_time);
                                $sEndSec = strtotime($uSlot->end_time);
                                $sStartMin = (int) date('H', $sStartSec) * 60 + (int) date('i', $sStartSec);
                                $sEndMin = (int) date('H', $sEndSec) * 60 + (int) date('i', $sEndSec);

                                $durMins = max(15, $sEndMin - $sStartMin);
                                $leftPct = max(0, (($sStartMin - $timelineStartMin) / $totalTimelineMins) * 100);
                                $widthPct = min(100 - $leftPct, ($durMins / $totalTimelineMins) * 100);

                                $cName = $uSlot->section->instituteClass->custom_name ?? 'Class';
                                $sName = $uSlot->section->section_name ?? 'A';
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
                                $shortClassCode = !empty($sClean) ? ($cName . ' - ' . strtoupper($sClean)) : $cName;

                                $h = floor($durMins / 60);
                                $m = $durMins % 60;
                                $durLabel = ($h > 0 ? $h . 'h ' : '') . ($m > 0 ? $m . 'm' : '');
                                $durLabel = trim($durLabel) ?: '1h';
                            @endphp

                            <div class="tt-horizontal-rect-card" 
                                 style="position:absolute;left:calc({{ $leftPct }}% + 4px);width:calc({{ $widthPct }}% - 8px);top:9px;height:86px;background:#ffffff;border:1px solid #fca5a5;border-left:5px solid #ef4444;border-radius:12px;padding:9px 12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;z-index:2;transition:all 0.15s ease;">
                                
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                                    <span style="font-size:11px;font-weight:800;color:#dc2626;background:#fee2e2;border:1px solid #fecaca;padding:2px 7px;border-radius:6px;white-space:nowrap">
                                        🎓 {{ $shortClassCode }}
                                    </span>
                                    <span style="font-size:12.5px;font-weight:800;color:#0f172a;white-space:nowrap;text-overflow:ellipsis;overflow:hidden">
                                        📚 {{ $uSlot->subject->subject_name }}
                                    </span>
                                </div>

                                <div style="font-size:11.5px;font-weight:700;color:#334155;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;display:flex;align-items:center;gap:4px">
                                    <span>👨‍🏫</span>
                                    <span style="text-overflow:ellipsis;overflow:hidden">{{ $uSlot->teacher->name }}</span>
                                </div>

                                <div style="display:flex;align-items:center;justify-content:space-between;font-size:10.5px;font-weight:800;color:#ef4444">
                                    <span>⏰ {{ \Carbon\Carbon::parse($uSlot->start_time)->format('g:i') }} – {{ \Carbon\Carbon::parse($uSlot->end_time)->format('g:i A') }}</span>
                                    <span style="background:#fee2e2;color:#dc2626;padding:1.5px 6px;border-radius:5px;font-size:10px">{{ $durLabel }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endif

@include('principal.timetables._chat')
@endsection

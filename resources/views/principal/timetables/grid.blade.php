@extends('principal.layouts.app')
@section('title', 'Master Timetable Grid')
@section('breadcrumb', 'Timetable Grid')

@section('content')
<style>
    .horiz-matrix-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        table-layout: fixed;
    }
    .horiz-matrix-table th {
        background: linear-gradient(135deg, rgba(108,99,255,0.25), rgba(0,206,209,0.15));
        color: #fff;
        text-align: center;
        padding: 14px 10px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid var(--border);
        letter-spacing: 0.5px;
    }
    .horiz-matrix-table td {
        padding: 10px 8px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid var(--border);
        min-height: 70px;
        font-size: 12px;
        background: var(--surface);
        transition: background 0.15s ease;
    }
    .horiz-matrix-table td:hover {
        background: rgba(30, 41, 59, 0.8);
    }
    .horiz-matrix-table .section-col {
        background: linear-gradient(135deg, rgba(108,99,255,0.14), rgba(30,41,59,0.7));
        font-weight: 700;
        color: #fff;
        font-size: 13px;
        width: 160px;
        text-align: left;
        padding-left: 14px;
    }
    .tt-slot {
        background: linear-gradient(135deg, rgba(108,99,255,0.15), rgba(0,206,209,0.1));
        border: 1px solid rgba(108,99,255,0.3);
        border-radius: 8px;
        padding: 8px 6px;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 3px;
        text-align: left;
    }
    .tt-slot .subject {
        font-weight: 700;
        font-size: 12px;
        color: #fff;
    }
    .tt-slot .teacher {
        font-size: 11px;
        color: var(--accent2);
    }
    .tt-slot .room {
        font-size: 10px;
        color: #2ed573;
        font-weight: 600;
        margin-top: 2px;
    }
    .empty-cell {
        color: rgba(148,163,184,0.3);
        font-size: 18px;
        font-weight: 300;
    }

    .day-tab {
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--surface2);
        border: 1px solid var(--border);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .day-tab.active {
        background: linear-gradient(135deg, #6c63ff, #00ced1);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(108,99,255,0.3);
    }

    .grade-header-row td {
        background: linear-gradient(90deg, rgba(108,99,255,0.25), rgba(30,41,59,0.9)) !important;
        color: #fff !important;
        font-weight: 700 !important;
        text-align: left !important;
        padding-left: 14px !important;
        font-size: 13px !important;
        letter-spacing: 0.5px;
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
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📋 Master Tabular Timetable Grid</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Single unified timetable showing <strong>All Class Sections on the left Y-axis</strong> and <strong>Time Slots across top X-axis</strong> for <strong>{{ $activeTerm?->name }}</strong>.
        </p>
    </div>
    <div style="display:flex;gap:12px">
        <a href="{{ route('principal.timetables.index') }}" class="btn btn-ghost">
            ← Back to Timetable Matrix
        </a>
        <a href="{{ route('principal.timetables.export') }}" download="UPLYFT_Master_Timetable.csv" class="btn btn-primary" style="background:linear-gradient(135deg, #2ed573, #1e90ff)">
            📥 Download CSV / Excel
        </a>
        <button type="button" onclick="window.print()" class="btn btn-ghost">
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
    <p style="color:var(--text-muted);font-size:16px;margin-bottom:16px">No timetable slots have been generated yet.</p>
    <a href="{{ route('principal.timetables.index') }}" class="btn btn-primary">
        ← Go to Timetable Matrix to Generate
    </a>
</div>
@else
@php
    $groupedGridSections = $sections->groupBy(fn($s) => $s->instituteClass->custom_name ?? 'Other');
@endphp

<div class="card" style="margin-bottom:32px;padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">
            📅 Master Daily Schedule — <span style="color:var(--accent2);text-transform:capitalize">{{ $selectedDay }}</span>
        </h2>
        <span style="font-size:12px;color:var(--text-muted)">
            All {{ $sections->count() }} Class Sections in 1 Unified Table across {{ $timeSlots->count() }} Time Slots
        </span>
    </div>

    <!-- SINGLE MASTER TABLE FOR THE SELECTED DAY -->
    <div style="overflow-x:auto;border-radius:12px;border:1px solid var(--border)">
        <table class="horiz-matrix-table">
            <thead>
                <tr>
                    <th style="width:160px;text-align:left;padding-left:14px">Class &amp; Section</th>
                    @foreach($timeSlots as $ts)
                    <th>⏰ {{ $ts['start'] }} – {{ $ts['end'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($groupedGridSections as $className => $classSections)
                    <!-- Grade Section Divider Row in Unified Table -->
                    <tr class="grade-header-row">
                        <td colspan="{{ count($timeSlots) + 1 }}">
                            🎓 {{ $className }} <span style="font-size:11px;font-weight:400;color:var(--text-muted)">({{ $classSections->count() }} Section(s))</span>
                        </td>
                    </tr>
                    @foreach($classSections as $sec)
                    <tr>
                        <td class="section-col">
                            <div style="font-weight:700">{{ $sec->instituteClass->custom_name }}</div>
                            <div style="font-size:11px;color:var(--accent2)">Section {{ $sec->section_name }}</div>
                        </td>
                        @foreach($timeSlots as $ts)
                        @php
                            $slot = $grid[$sec->id][$selectedDay][$ts['key']] ?? null;
                        @endphp
                        <td>
                            @if($slot)
                            <div class="tt-slot">
                                <div class="subject">{{ $slot->subject->subject_name }}</div>
                                <div class="teacher">👨‍🏫 {{ $slot->teacher->name }}</div>
                                <div class="room">📍 Room: {{ $slot->room ? $slot->room->room_number : 'Unassigned' }}</div>
                            </div>
                            @else
                            <div class="empty-cell">—</div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

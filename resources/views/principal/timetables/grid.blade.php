@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Master Timetable Grid')
@section('breadcrumb', 'Timetable Grid')

@section('content')
@php
    $routePrefix = request()->routeIs('teacher.*') ? 'teacher.' : 'principal.';
    $selectedDay = strtolower(request()->get('day', 'all'));
    $displayDays = $selectedDay === 'all' ? $days : [$selectedDay];

    $timeOverlap = function (string $s1, string $e1, string $s2, string $e2): bool {
        return ($s1 < $e2) && ($e1 > $s2);
    };
@endphp

<style>
    .excel-timetable-container {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        margin-bottom: 32px;
    }

    .excel-timetable-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .excel-timetable-table th,
    .excel-timetable-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        text-align: center;
        vertical-align: middle;
    }

    .excel-timetable-table thead th {
        background: #f8fafc;
        color: #0f172a;
        font-size: 12.5px;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        padding: 12px 10px;
        border-bottom: 2px solid #94a3b8;
    }

    .excel-timetable-table .col-day-hdr {
        width: 130px;
        min-width: 110px;
        background: #f1f5f9;
        text-align: left;
        padding-left: 14px;
    }

    .excel-timetable-table .col-room-hdr {
        width: 140px;
        min-width: 130px;
        background: #f1f5f9;
        text-align: left;
        padding-left: 14px;
    }

    .excel-timetable-table .col-time-hdr {
        min-width: 150px;
        color: #1e1b4b;
    }

    .excel-timetable-table tbody tr:hover td {
        background-color: #fdf2f8 !important;
    }

    .day-cell {
        background: #ffffff;
        font-weight: 800;
        font-size: 13.5px;
        color: #0f172a;
        text-align: left !important;
        padding-left: 14px !important;
        border-right: 2px solid #cbd5e1 !important;
    }

    .room-cell {
        background: #ffffff;
        font-weight: 700;
        font-size: 12.5px;
        color: #1e293b;
        text-align: left !important;
        padding-left: 14px !important;
        border-right: 2px solid #cbd5e1 !important;
    }

    .room-cell .room-type {
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        display: block;
        margin-top: 2px;
    }

    .slot-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 6px 4px;
        min-height: 72px;
        line-height: 1.35;
    }

    .slot-subject {
        font-weight: 800;
        font-size: 12.5px;
        color: #0f172a;
        text-align: center;
    }

    .slot-class {
        font-weight: 800;
        font-size: 12px;
        color: #4338ca;
        text-align: center;
    }

    .slot-teacher {
        font-weight: 700;
        font-size: 11.5px;
        color: #e11d48;
        text-align: center;
    }

    .empty-slot {
        color: #cbd5e1;
        font-weight: 600;
        font-size: 14px;
    }

    .day-tab {
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
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

    @media print {
        body { background: #fff !important; color: #000 !important; }
        .sidebar, .topbar, .no-print { display: none !important; }
        .main { margin: 0; }
        .content { padding: 8px; }
        .excel-timetable-table th { background: #f0f0f0 !important; color: #000 !important; }
        .excel-timetable-table td { background: #fff !important; color: #000 !important; }
        .slot-subject { color: #000 !important; }
        .slot-class { color: #000 !important; }
        .slot-teacher { color: #333 !important; }
    }
</style>

<!-- Top Action Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px" class="no-print">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            📊 Excel-Style Timetable Master Sheet
        </h1>
        <p style="color:#64748b;font-size:13px;margin-top:4px;font-weight:500">
            Official Timetable Grid structured as <strong>Day &bull; Room &bull; Horizontal Time Slots</strong> for <strong>{{ $activeTerm?->name ?? 'Active Session' }}</strong>.
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'class']) }}" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            🏫 Class-Wise View
        </a>
        <a href="{{ route($routePrefix . 'timetables.index', ['view_type' => 'teacher']) }}" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            👨‍🏫 Teacher-Wise View
        </a>
        <!-- Professional Export Excel Dropdown -->
        <div style="position:relative;display:inline-block">
            <button type="button" id="btnExportMenu" class="btn btn-primary" onclick="toggleExportMenu(event)" style="font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                📗 Export Excel (.xlsx) ▾
            </button>
            <div id="exportDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);min-width:260px;z-index:100;padding:6px 0;">
                <a href="{{ route($routePrefix . 'timetables.export') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;color:#0f172a;text-decoration:none;font-size:12.5px;font-weight:700;transition:background 0.15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <span style="font-size:14px">🏛️</span>
                    <div>
                        <div>Whole Institute Timetable</div>
                        <div style="font-size:10.5px;font-weight:500;color:#64748b">Complete multi-sheet workbook (.xlsx)</div>
                    </div>
                </a>
                @if(isset($sections) && $sections->isNotEmpty())
                    <div style="border-top:1px solid #f1f5f9;margin:4px 0;padding:8px 16px 3px;font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">
                        Download Specific Class:
                    </div>
                    <div style="max-height:220px;overflow-y:auto">
                        @foreach($sections as $sec)
                            @php
                                $cName = $sec->instituteClass?->custom_name ?: ($sec->instituteClass?->class_name ?: 'Class');
                                $sName = $sec->section_name ?: 'A';
                            @endphp
                            <a href="{{ route($routePrefix . 'timetables.export', ['class_section_id' => $sec->id]) }}" style="display:flex;align-items:center;justify-content:space-between;padding:7px 16px;color:#334155;text-decoration:none;font-size:12px;font-weight:600;transition:background 0.15s" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <span>🏫 {{ $cName }} ({{ $sName }})</span>
                                <span style="font-size:10px;color:#4f46e5;font-weight:700">.xlsx</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <button type="button" onclick="window.print()" class="btn btn-ghost" style="font-size:12.5px;font-weight:700">
            🖨️ Print
        </button>
    </div>
</div>

<script>
function toggleExportMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('exportDropdownMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('exportDropdownMenu');
    const btn = document.getElementById('btnExportMenu');
    if (menu && menu.style.display === 'block' && (!btn || !btn.contains(e.target)) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>

<!-- Day Selector Tabs -->
<div style="display:flex;gap:10px;margin-bottom:24px;overflow-x:auto;padding-bottom:6px" class="no-print">
    <a href="{{ route('principal.timetables.grid', ['day' => 'all']) }}" 
       class="day-tab {{ $selectedDay === 'all' ? 'active' : '' }}">
        📑 All Weekdays
    </a>
    @foreach($days as $day)
        <a href="{{ route('principal.timetables.grid', ['day' => $day]) }}" 
           class="day-tab {{ $selectedDay === strtolower($day) ? 'active' : '' }}">
            📅 {{ ucfirst($day) }}
        </a>
    @endforeach
</div>

@if(empty($allSlots) || count($allSlots) === 0)
    <div class="card" style="text-align:center;padding:48px 24px">
        <div style="font-size:36px;margin-bottom:12px">🗓️</div>
        <h3 style="font-family:'Outfit',sans-serif;font-weight:800;color:#0f172a">No Timetable Generated Yet</h3>
        <p style="color:#64748b;font-size:14px;max-width:460px;margin:8px auto 20px">
            Generate an AI conflict-free timetable matrix using your configured faculty hours, class breaks, and course allocations.
        </p>
        <form method="POST" action="{{ route('principal.timetables.generate') }}" style="display:inline-block">
            @csrf
            <button type="submit" class="btn btn-primary" style="font-size:13px;padding:10px 24px">
                ⚡ Generate AI Timetable Now
            </button>
        </form>
    </div>
@else
    <!-- EXCEL-STYLE TIMETABLE MASTER SHEET (Day | Room | Time 1 | Time 2 | ...) -->
    <div class="excel-timetable-container">
        <div style="overflow-x:auto">
            <table class="excel-timetable-table">
                <thead>
                    <tr>
                        <th class="col-day-hdr">Day</th>
                        <th class="col-room-hdr">Room</th>
                        @foreach($timeSlots as $ts)
                            <th class="col-time-hdr">
                                {{ date('g:i', strtotime($ts['start'])) }} - {{ date('g:i A', strtotime($ts['end'])) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $slotsByDayAndRoom = $allSlots->groupBy(function ($s) {
                            $d = strtolower($s->day_of_week);
                            $r = $s->room_id ?: 0;
                            return "{$d}_{$r}";
                        });
                    @endphp

                    @foreach($displayDays as $day)
                        @php
                            $cleanDay = strtolower($day);
                            $roomList = $rooms->isNotEmpty() ? $rooms : collect([(object)['id' => 0, 'room_number' => 'Main Room', 'room_type' => 'Default']]);
                        @endphp

                        @foreach($roomList as $rIdx => $rm)
                            @php
                                $rId = $rm->id;
                                $drKey = "{$cleanDay}_{$rId}";
                                $roomSlots = $slotsByDayAndRoom->get($drKey) ?? collect();
                            @endphp

                            <tr>
                                <!-- Column 1: Day (on left) -->
                                @if($rIdx === 0)
                                    <td class="day-cell" rowspan="{{ $roomList->count() }}">
                                        {{ ucfirst($day) }}
                                    </td>
                                @endif

                                <!-- Column 2: Room (next to Day) -->
                                <td class="room-cell">
                                    {{ $rm->room_number }}
                                    @if(!empty($rm->room_type) && $rm->room_type !== 'Standard')
                                        <span class="room-type">{{ $rm->room_type }}</span>
                                    @endif
                                </td>

                                <!-- Columns 3+: Horizontal Time Slots -->
                                @foreach($timeSlots as $ts)
                                    @php
                                        $tsStart = $ts['start'];
                                        $tsEnd   = $ts['end'];

                                        // Find matching slot for this room, day and time window
                                        $matchedSlot = $roomSlots->first(function ($s) use ($tsStart, $tsEnd, $timeOverlap) {
                                            $sStart = substr($s->start_time, 0, 5);
                                            $sEnd   = substr($s->end_time, 0, 5);
                                            return $timeOverlap($sStart, $sEnd, $tsStart, $tsEnd);
                                        });
                                    @endphp

                                    <td style="background: {{ $matchedSlot ? '#ffffff' : '#fafafa' }};">
                                        @if($matchedSlot)
                                            <div class="slot-card">
                                                <!-- Subject Name -->
                                                <div class="slot-subject">
                                                    {{ $matchedSlot->subject->subject_name ?? 'Subject' }}
                                                </div>

                                                <!-- Class / Section Name -->
                                                <div class="slot-class">
                                                    {{ $matchedSlot->section->instituteClass->custom_name ?? 'Class' }} - {{ $matchedSlot->section->section_name ?? 'A' }}
                                                </div>

                                                <!-- Teacher Name -->
                                                <div class="slot-teacher">
                                                    {{ $matchedSlot->teacher->name ?? 'Teacher' }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="empty-slot">-</span>
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

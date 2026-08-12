@extends('principal.layouts.app')
@section('title', 'Teacher Availability Schedules')
@section('breadcrumb', 'Teacher Availability')

@section('content')
<style>
    .teacher-avail-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        margin-bottom: 20px;
        padding: 24px;
    }
    .day-row {
        display: grid;
        grid-template-columns: 140px 100px 1fr 1fr;
        align-items: center;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .day-row:last-child { border-bottom: none; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Teacher Availability Windows</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Configure working days and daily time windows for each teacher. The Timetable Generator engine strictly enforces these windows.
        </p>
    </div>
    <a href="{{ route('principal.timetables.index') }}" class="btn btn-ghost">
        📅 Back to Timetable Matrix
    </a>
</div>

@if(session('success'))
<div style="background:rgba(46,213,115,0.15);border:1px solid rgba(46,213,115,0.3);color:#2ed573;padding:14px 18px;border-radius:10px;margin-bottom:20px">
    ✓ {{ session('success') }}
</div>
@endif

@if($teachers->count() > 0)
    @foreach($teachers as $teacher)
    @php
        $teacherAvails = $teacher->availabilities->keyBy('day_of_week');
    @endphp
    <div class="teacher-avail-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff">
                    {{ strtoupper(substr($teacher->name, 0, 1)) }}
                </div>
                <div>
                    <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">{{ $teacher->name }}</h3>
                    <span style="font-size:12px;color:var(--text-muted)">{{ $teacher->email }} • {{ ucfirst($teacher->employment_type ?? 'Permanent') }} Teacher</span>
                </div>
            </div>
            <span class="badge badge-purple">
                {{ $teacher->availabilities->where('is_available', true)->count() }} Active Working Day(s)
            </span>
        </div>

        <form method="POST" action="{{ route('principal.teachers.availability.store') }}">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

            @foreach($days as $idx => $day)
            @php
                $avail = $teacherAvails->get($day);
                $isAvailable = $avail ? $avail->is_available : true;
                $startTime = $avail ? date('H:i', strtotime($avail->start_time)) : '08:00';
                $endTime   = $avail ? date('H:i', strtotime($avail->end_time))   : '15:00';
            @endphp
            <div class="day-row">
                <!-- Day Name -->
                <strong style="color:#fff;font-size:14px;text-transform:capitalize">{{ $day }}</strong>

                <!-- Checkbox -->
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text);font-size:13px">
                    <input type="hidden" name="availabilities[{{ $idx }}][day_of_week]" value="{{ $day }}">
                    <input type="hidden" name="availabilities[{{ $idx }}][is_available]" value="0">
                    <input type="checkbox" name="availabilities[{{ $idx }}][is_available]" value="1" {{ $isAvailable ? 'checked' : '' }}>
                    Working
                </label>

                <!-- Start Time -->
                <div>
                    <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:2px">Available From *</label>
                    <input type="time" name="availabilities[{{ $idx }}][start_time]" value="{{ $startTime }}" required style="padding:6px 10px;font-size:13px">
                </div>

                <!-- End Time -->
                <div>
                    <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:2px">Available Until *</label>
                    <input type="time" name="availabilities[{{ $idx }}][end_time]" value="{{ $endTime }}" required style="padding:6px 10px;font-size:13px">
                </div>
            </div>
            @endforeach

            <div style="display:flex;justify-content:flex-end;margin-top:16px">
                <button type="submit" class="btn btn-primary btn-sm" style="padding:8px 18px">
                    💾 Save {{ $teacher->name }}'s Availability
                </button>
            </div>
        </form>
    </div>
    @endforeach
@else
<div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:var(--text-muted);font-size:15px">No teachers registered yet. Please onboard teachers first in Faculty & Staff Roster.</p>
</div>
@endif
@endsection

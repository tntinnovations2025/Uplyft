@extends('principal.layouts.app')
@section('title', 'Principal Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div style="margin-bottom:28px">
    <h1 style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700">
        Welcome, {{ $user->name }} 👋
    </h1>
    <p style="color:var(--text-muted);font-size:14px;margin-top:4px">
        Manage academic sessions, course offerings, timetables, and staff for <strong>{{ $institute->name }}</strong>.
    </p>
</div>

@if(!$activeTerm)
<div class="alert alert-warning" style="margin-bottom:28px">
    <div>
        <strong>⚠️ Active Academic Term Required</strong>
        <p style="font-size:13px;margin-top:2px">Please create and activate an academic session (e.g., 2025–2026) to enable timetable creation and class scheduling.</p>
    </div>
    <a href="{{ route('principal.academic-terms.index') }}" class="btn btn-primary btn-sm">Set Active Term &rarr;</a>
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:32px">
    <div class="card" style="margin-bottom:0">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:8px">Active Session</div>
        <div style="font-size:22px;font-weight:700;color:{{ $activeTerm ? 'var(--success)' : 'var(--warning)' }}">
            {{ $activeTerm ? $activeTerm->name : 'None Active' }}
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
            {{ $activeTerm ? $activeTerm->start_date->format('M Y') . ' — ' . $activeTerm->end_date->format('M Y') : 'Action Required' }}
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:8px">Institute Classes</div>
        <div style="font-size:28px;font-weight:700">{{ $classesCount }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Provisioned Levels/Modules</div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:8px">Faculty & Staff</div>
        <div style="font-size:28px;font-weight:700">{{ $staffCount }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Active Staff Members</div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;font-weight:600;margin-bottom:8px">Scheduled Slots</div>
        <div style="font-size:28px;font-weight:700;color:var(--accent2)">{{ $slotsCount }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Timetable Matrix Entries</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header">
            <div class="card-title">🚀 Quick Actions</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <a href="{{ route('principal.academic-terms.index') }}" class="btn btn-ghost" style="justify-content:flex-start">
                <span>📅</span> Manage Academic Terms
            </a>
            <a href="{{ route('principal.classes-subjects.index') }}" class="btn btn-ghost" style="justify-content:flex-start">
                <span>📚</span> Provision Classes & Subjects
            </a>
            <a href="{{ route('principal.sections.index') }}" class="btn btn-ghost" style="justify-content:flex-start">
                <span>🏫</span> Allocate Class Sections
            </a>
            <a href="{{ route('principal.timetables.index') }}" class="btn btn-ghost" style="justify-content:flex-start">
                <span>🗓️</span> Open Conflict-Free Timetable Matrix
            </a>
            <a href="{{ route('principal.staff.index') }}" class="btn btn-ghost" style="justify-content:flex-start">
                <span>👥</span> Faculty & Staff Roster
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">ℹ️ Institute Profile</div>
            <span class="badge badge-purple">{{ ucfirst($institute->subscription_tier) }} Plan</span>
        </div>
        <table>
            <tr>
                <td style="color:var(--text-muted);width:140px">Institute</td>
                <td><strong>{{ $institute->name }}</strong></td>
            </tr>
            <tr>
                <td style="color:var(--text-muted)">City</td>
                <td>{{ $institute->city ?? 'Not set' }}</td>
            </tr>
            <tr>
                <td style="color:var(--text-muted)">Contact Email</td>
                <td>{{ $institute->contact_email ?? auth()->user()->email }}</td>
            </tr>
            <tr>
                <td style="color:var(--text-muted)">Education System</td>
                <td>
                    @if(!empty($institute->education_systems))
                        @foreach($institute->education_systems as $sys)
                            <span class="badge badge-purple" style="font-size:10px">{{ $sys }}</span>
                        @endforeach
                    @else
                        Standard
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection

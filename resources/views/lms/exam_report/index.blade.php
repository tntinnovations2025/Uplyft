@extends('lms.layouts.app')

@section('title', 'Term Exam Reports')
@section('breadcrumb', 'Term Exam Reports')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📜 Term Exam Reports &amp; Marksheets</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Official Midterm &amp; Final Term examination marksheets and student performance scorecards.
        </p>
    </div>
    @if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin())
        <a href="{{ route('lms.datesheet.index') }}" class="btn btn-primary">
            📅 Exam Datesheet Builder
        </a>
    @endif
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
    <form method="GET" action="{{ route('lms.exam-report.index') }}" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📝 Exam Title Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Midterm 2026..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">🏫 Class Section</label>
            <select name="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Classes</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>
                        {{ $cs->instituteClass?->custom_name ?? 'Class' }} - {{ $cs->section_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📘 Subject Filter</label>
            <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Subjects</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="height:42px">Filter Exams</button>
        @if(request()->hasAny(['search', 'class_section_id', 'subject_id']))
            <a href="{{ route('lms.exam-report.index') }}" class="btn btn-ghost" style="height:42px;color:var(--danger)">Reset</a>
        @endif
    </form>
</div>

{{-- Grouped Exams Directory --}}
@forelse($groupedExams as $className => $examList)
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent2)">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">🏫</span>
                <div>
                    <h3 class="card-title" style="font-size:18px;font-weight:700">{{ $className }} — Formal Examinations</h3>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        {{ $examList->count() }} Formal Exam Marksheet(s) Scheduled
                    </p>
                </div>
            </div>
            <span class="badge badge-purple">{{ $examList->unique('subject_id')->count() }} Subjects</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:16px;margin-top:16px">
            @foreach($examList as $exam)
                @php
                    $examDate = $exam->start_time ? $exam->start_time->format('M d, Y') : $exam->created_at->format('M d, Y');
                    $typeBadge = match($exam->type) {
                        'midterm' => 'badge-purple',
                        'final' => 'badge-red',
                        default => 'badge-yellow',
                    };
                @endphp
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;flex-direction:column;justify-content:space-between">
                    <div>
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                            <h4 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:var(--text);margin:0">
                                📝 {{ $exam->title }}
                            </h4>
                            <span class="badge {{ $typeBadge }}">{{ strtoupper($exam->type) }}</span>
                        </div>

                        <div style="font-size:13px;color:var(--text-muted);margin-top:10px;display:flex;flex-direction:column;gap:6px">
                            <div>📘 <b>Subject:</b> {{ $exam->subject->subject_name ?? '—' }}</div>
                            <div>📅 <b>Datesheet Date:</b> {{ $examDate }}</div>
                            <div>🎯 <b>Total Marks (Admin Set):</b> <b style="color:var(--accent)">{{ $exam->total_marks }} pts</b></div>
                            <div>👤 <b>Created By:</b> {{ $exam->creator->name ?? 'Administration' }}</div>
                        </div>
                    </div>

                    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                        @if($exam->is_marksheet_saved)
                            <span class="badge badge-green" style="font-size:11px">💾 Marksheet Ready</span>
                        @else
                            <span class="badge badge-yellow" style="font-size:11px">Pending Marks</span>
                        @endif

                        <a href="{{ route('lms.exam-report.marksheet', $exam->id) }}" class="btn btn-primary btn-sm" style="padding:8px 14px;font-weight:600">
                            📝 Insert / Edit Marks
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card" style="text-align:center;padding:60px 20px">
        <div style="font-size:48px;margin-bottom:16px">📜</div>
        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">
            No Formal Exams Listed Yet
        </h3>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px;margin:0 auto 24px;line-height:1.6">
            When Principal or Administration schedules Midterm or Final Term examinations on the Datesheet, the official exam marksheets will be cataloged here.
        </p>
        @if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin())
            <a href="{{ route('lms.datesheet.index') }}" class="btn btn-primary btn-lg">
                📅 Schedule Exam on Datesheet
            </a>
        @endif
    </div>
@endforelse
@endsection

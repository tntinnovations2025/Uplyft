@extends('lms.layouts.app')

@section('title', 'Test Results & Grading')
@section('breadcrumb', 'Test Results')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📋 Test Results &amp; Grading</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Classroom quizzes, assignments, and test evaluation reports for student performance tracking.
        </p>
    </div>
    @if(auth()->user()->isTeacher() || auth()->user()->isPrincipal())
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('lms.assessments.paperMarksheet.form') }}" class="btn btn-ghost" style="border:1px solid var(--accent)">
                📝 Create Paper Marksheet
            </a>
            <a href="{{ route('lms.assessments.index') }}" class="btn btn-primary">
                ➕ New Online Assessment
            </a>
        </div>
    @endif
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
    <form method="GET" action="{{ route('lms.test-results.index') }}" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">👤 Student Name / Roll</label>
            <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="e.g. Student Name..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📝 Test Name</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Test 1..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">🏫 Class Filter</label>
            <select name="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Classes</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>
                        {{ $cs->instituteClass?->custom_name ?? 'Class' }} - {{ $cs->section_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📘 Subject Filter</label>
            <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Subjects</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="height:42px">Filter Directory</button>
        @if(request()->hasAny(['search', 'subject_id', 'class_section_id', 'student_search']))
            <a href="{{ route('lms.test-results.index') }}" class="btn btn-ghost" style="height:42px;color:var(--danger)">Reset</a>
        @endif
    </form>
</div>

{{-- Student Specific Marks Banner (When Searching Student) --}}
@if($searchedStudentResults !== null)
    <div style="margin-bottom:28px">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:10px">
            <span>👤 Student Academic Performance Profile</span>
            <span class="badge badge-purple" style="font-size:12px">{{ count($searchedStudentResults) }} Student(s) Found</span>
        </h2>

        @forelse($searchedStudentResults as $stRes)
            <div class="card" style="margin-bottom:20px;border-left:4px solid var(--accent2)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--text)">
                            {{ $stRes['name'] }} <span style="font-size:13px;color:var(--text-muted)">({{ $stRes['roll_number'] }})</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:2px">
                            🏫 Class: <b>{{ $stRes['class_name'] }}</b> · Email: {{ $stRes['email'] }}
                        </div>
                    </div>
                    <span class="badge badge-green" style="font-size:12px">Enrolled Subjects Marks</span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:16px">
                    @foreach($stRes['subject_scores'] as $subjScore)
                        @php
                            $badgeClass = match($subjScore['grade']) {
                                'A+', 'A' => 'badge-green',
                                'B', 'C' => 'badge-purple',
                                'D' => 'badge-yellow',
                                default => 'badge-red',
                            };
                        @endphp
                        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                                <h4 style="font-size:15px;font-weight:700;color:var(--accent2);margin:0">
                                    📘 {{ $subjScore['subject_name'] }}
                                </h4>
                                <span class="badge {{ $badgeClass }}">{{ $subjScore['grade'] }} ({{ $subjScore['percentage'] }}%)</span>
                            </div>

                            <div style="font-size:13px;margin-bottom:10px">
                                Marks: <b>{{ $subjScore['total_obtained'] }}</b> / {{ $subjScore['total_max'] }} pts
                            </div>

                            {{-- Individual Tests breakdown --}}
                            @if(!empty($subjScore['tests']))
                                <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:6px">
                                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted)">Test Breakdown:</div>
                                    @foreach($subjScore['tests'] as $tInfo)
                                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
                                            <span>📝 {{ $tInfo['test_title'] }}</span>
                                            @if($tInfo['has_attempted'])
                                                <span style="font-weight:600;color:var(--success)">{{ $tInfo['obtained_marks'] }} / {{ $tInfo['total_marks'] }}</span>
                                            @else
                                                <span class="muted" style="font-size:11px">Not Attempted</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;padding:30px;color:var(--text-muted)">
                No student records found matching "{{ request('student_search') }}".
            </div>
        @endforelse
    </div>
@endif

{{-- Hierarchical Directory View: Class -> Subject -> Test --}}
@forelse($groupedByClass as $className => $classAssessments)
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent)">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">🏫</span>
                <div>
                    <h3 class="card-title" style="font-size:18px;font-weight:700">{{ $className ?: 'General Class' }}</h3>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        {{ $classAssessments->count() }} Test Marksheets Available
                    </p>
                </div>
            </div>
            <span class="badge badge-purple">{{ $classAssessments->unique('subject_id')->count() }} Subjects</span>
        </div>

        {{-- Group Assessments by Subject --}}
        @php
            $subjectGrouped = $classAssessments->groupBy(fn($a) => $a->subject->subject_name ?? 'General Subject');
        @endphp

        <div style="display:flex;flex-direction:column;gap:16px;margin-top:16px">
            @foreach($subjectGrouped as $subjectName => $tests)
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)">
                        <span style="font-size:16px">📘</span>
                        <h4 style="font-size:15px;font-weight:700;color:var(--accent2);margin:0">
                            Subject: {{ $subjectName }}
                        </h4>
                        <span class="badge badge-green" style="font-size:10px">{{ $tests->count() }} Test(s)</span>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:14px">
                        @foreach($tests as $assessment)
                            <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;display:flex;flex-direction:column;justify-content:space-between">
                                <div>
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                                        <h5 style="font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;color:var(--text);margin:0">
                                            📝 {{ $assessment->title }}
                                        </h5>
                                        @if($assessment->is_marksheet_saved)
                                            <span class="badge badge-green" style="font-size:10px">💾 Saved</span>
                                        @endif
                                    </div>

                                    <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;flex-direction:column;gap:4px">
                                        <div>🎯 <b>Total Marks:</b> {{ $assessment->total_marks }} pts</div>
                                        <div>🔢 <b>Questions:</b> {{ $assessment->questions->count() }}</div>
                                        @if($assessment->has_time_limit)
                                            <div>⏱️ <b>Duration:</b> {{ $assessment->duration_minutes }} mins</div>
                                        @endif
                                        @if($assessment->creator)
                                            <div>👤 <b>Created By:</b> {{ $assessment->creator->name }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
                                    @if(!$assessment->is_marksheet_saved && (auth()->user()->isTeacher() || auth()->user()->isPrincipal()))
                                        <form method="POST" action="{{ route('lms.test-results.saveMarksheet', $assessment->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm" title="Archive Marksheet">💾 Save</button>
                                        </form>
                                    @else
                                        <div></div>
                                    @endif

                                    <a href="{{ route('lms.test-results.marksheet', $assessment->id) }}" class="btn btn-primary btn-sm" style="padding:8px 14px;font-weight:600">
                                        👁️ View Complete Marksheet
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card" style="text-align:center;padding:60px 20px">
        <div style="font-size:48px;margin-bottom:16px">📂</div>
        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">
            No Test Marksheets Available Yet
        </h3>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px;margin:0 auto 24px;line-height:1.6">
            When teachers create assessments and students attempt tests, the class marksheets will automatically be cataloged here organized by Class and Subject.
        </p>
        @if(auth()->user()->isTeacher() || auth()->user()->isPrincipal())
            <a href="{{ route('lms.assessments.index') }}" class="btn btn-primary btn-lg">
                ✨ Create First Test / Assessment
            </a>
        @endif
    </div>
@endforelse
@endsection

@extends('lms.layouts.app')

@section('title', 'Exam Datesheet Builder')
@section('breadcrumb', 'Exam Datesheets')

@section('content')
<style>
    @media print {
        body { background: #fff !important; color: #000 !important; }
        .no-print, header, sidebar, .btn, form, .modal-backdrop, .filter-card { display: none !important; }
        .card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; margin-bottom: 20px !important; }
        body.printing-single-class .class-datesheet-card { display: none !important; }
        body.printing-single-class .class-datesheet-card.active-print-target { display: block !important; }
        .class-card-body { display: block !important; }
        table { border-collapse: collapse !important; width: 100% !important; }
        th, td { border: 1px solid #cbd5e1 !important; padding: 8px !important; color: #0f172a !important; font-size: 11px !important; }
    }

    /* Ink & Amber Cards & Containers */
    .class-datesheet-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .class-datesheet-card:hover {
        border-color: #D48A2E;
        box-shadow: 0 4px 12px rgba(212, 138, 46, 0.08);
    }
    .class-header-row {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        background: #F9F8F5;
        transition: background 0.15s ease;
    }
    .class-header-row:hover {
        background: #F4F2EB;
    }
    .class-card-body {
        border-top: 1px solid #E1DFD7;
        background: #FFFFFF;
        padding: 20px;
    }

    /* Table Styling */
    .datesheet-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        border: 1px solid #E1DFD7;
        border-radius: 8px;
        overflow: hidden;
    }
    .datesheet-table th {
        background: #F2EFEB;
        color: #68665D;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 11px 14px;
        text-align: left;
        border-bottom: 1px solid #E1DFD7;
    }
    .datesheet-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #EAE8E0;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1B1A17;
        background: #FFFFFF;
    }
    .datesheet-table tr:last-child td {
        border-bottom: none;
    }
    .datesheet-table tr:hover td {
        background: #FAF9F6;
    }

    /* Status Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .badge-complete { 
        background: #E3EFE2; 
        color: #2E6E42; 
        border: 1px solid #C5DDC3; 
    }
    .badge-pending { 
        background: #F8E9D3; 
        color: #8A5A10; 
        border: 1px solid #E8CEAA; 
    }
    .badge-danger {
        background: #F6E4E1;
        color: #A2412C;
        border: 1px solid #E9C6BE;
    }

    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        text-decoration: none;
    }
    .btn-outline { 
        background: #FFFFFF; 
        border: 1px solid #E1DFD7; 
        color: #1B1A17; 
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .btn-outline:hover { 
        background: #FAF9F6; 
        border-color: #D48A2E; 
        color: #D48A2E; 
    }
    .btn-primary-custom { 
        background: #D48A2E; 
        color: #FFFFFF; 
        border: 1px solid #C07A22;
        box-shadow: 0 1px 3px rgba(212, 138, 46, 0.25);
    }
    .btn-primary-custom:hover { 
        background: #C07A22; 
        color: #FFFFFF;
    }
    .btn-danger-custom { 
        background: #F6E4E1; 
        color: #A2412C; 
        border: 1px solid #E9C6BE; 
    }
    .btn-danger-custom:hover { 
        background: #EDD3CF; 
        color: #8E301D;
    }

    /* Form Controls */
    .ink-input, .ink-select {
        background: #FFFFFF;
        border: 1px solid #E1DFD7;
        border-radius: 6px;
        color: #1B1A17;
        font-size: 12px;
        padding: 7px 10px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ink-input:focus, .ink-select:focus {
        border-color: #D48A2E;
        box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.12);
    }
</style>

@php
    $roomsList = $registeredRooms ?? collect();
    $roomOptionsHtml = '<option value="" selected>-- Select Room / Hall --</option>';
    if ($roomsList->isNotEmpty()) {
        foreach($roomsList as $rm) {
            $rawRoomNo = trim($rm->room_number ?? '');
            $cleanRoomNo = preg_replace('/^(room\s*)+/i', '', $rawRoomNo);
            $rName = 'Room '.$cleanRoomNo.($rm->building_block ? ' ('.$rm->building_block.')' : '');
            $roomOptionsHtml .= '<option value="'.e($rName).'">'.e($rName).'</option>';
        }
    } else {
        $roomOptionsHtml .= '<option value="Room 1">Room 1</option><option value="Room 2">Room 2</option><option value="Room 3">Room 3</option><option value="Hall A">Hall A</option><option value="Hall B">Hall B</option><option value="Auditorium">Auditorium</option>';
    }
@endphp

{{-- Main Page Header --}}
<div class="no-print" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Manrope', 'Space Grotesk', sans-serif;font-size:24px;font-weight:800;color:#1B1A17;margin:0;letter-spacing:-0.4px">
            Examination Datesheets
        </h1>
        <p style="color:#68665D;font-size:13px;margin:4px 0 0;font-weight:500">
            @if(auth()->user()->isAdministration())
                Manage, schedule and publish official examination datesheets across institute classes.
            @else
                Official examination schedule directory.
            @endif
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="btn-action btn-outline" onclick="printWholeInstitute()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            <span>Print Institute Schedule</span>
        </button>
        @if(auth()->user()->isAdministration())
            <button class="btn-action btn-primary-custom" onclick="openModal('create-datesheet-modal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Create Exam Schedule</span>
            </button>
        @endif
    </div>
</div>

{{-- Interactive Builder Workspace --}}
@if(auth()->user()->isAdministration())
    <div id="datesheet-builder-workspace" class="card no-print" style="display:none;margin-bottom:24px;border:1px solid #D48A2E;background:#F9F8F5;border-radius:14px;box-shadow:0 4px 20px rgba(212,138,46,0.08);overflow:hidden">
        <div style="background:#F2EFEB;padding:16px 20px;border-bottom:1px solid #E1DFD7;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <h3 id="ws-exam-title-display" style="font-family:'Manrope',sans-serif;font-weight:800;font-size:16px;color:#1B1A17;margin:0">
                    Midterm Examination
                </h3>
                <p style="font-size:12px;color:#68665D;margin:2px 0 0;font-weight:500">
                    Configure dates, timing, rooms and marks for selected classes.
                </p>
            </div>
            <button type="button" class="btn-action btn-outline" onclick="openModal('create-datesheet-modal')">
                Configure Exam Settings
            </button>
        </div>

        <form method="POST" action="{{ route('lms.datesheet.store') }}" onsubmit="return validateDatesheetForm(event)" style="padding:20px">
            @csrf
            <input type="hidden" id="ws-form-title" name="title" value="Midterm Examination">
            <input type="hidden" id="ws-form-type" name="type" value="Midterm Examination">
            <input type="hidden" id="ws-form-term-id" name="academic_term_id" value="{{ $academicTerms->first()?->id ?? '' }}">

            <div style="margin-bottom:18px">
                <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:8px;display:block;text-transform:uppercase;letter-spacing:0.05em">
                    Select Class Section to Schedule:
                </label>
                <div id="class-tabs-container" style="display:flex;flex-wrap:wrap;gap:8px"></div>
            </div>

            <div style="margin-bottom:20px">
                @foreach($classSections as $cs)
                    @php
                        $cName = $cs->instituteClass?->custom_name ?? 'Class';
                        $csSubjects = $cs->subjects ?? collect();
                        $subOptsHtml = '';
                        foreach($csSubjects as $optSub) {
                            $subOptsHtml .= '<option value="'.$optSub->id.'">'.e($optSub->subject_name).'</option>';
                        }
                    @endphp
                    <div id="class-block-{{ $cs->id }}" class="class-schedule-block" style="display:none;padding:18px;background:#FFFFFF;border:1px solid #E1DFD7;border-radius:10px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #E1DFD7;flex-wrap:wrap;gap:12px">
                            <div>
                                <h4 style="font-family:'Manrope',sans-serif;font-weight:800;font-size:15px;color:#1B1A17;margin:0">
                                    {{ $cName }} — Section {{ $cs->section_name }}
                                </h4>
                                <span style="font-size:12px;color:#68665D;font-weight:500">{{ $csSubjects->count() }} Subjects Enrolled</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                                <div style="display:flex;align-items:center;gap:10px;background:#F9F8F5;padding:6px 12px;border-radius:8px;border:1px solid #E1DFD7">
                                    <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:11.5px;color:#1B1A17;font-weight:600">
                                        <input type="hidden" name="class_visibility[{{ $cs->id }}][is_published_teacher]" value="0">
                                        <input type="checkbox" name="class_visibility[{{ $cs->id }}][is_published_teacher]" value="1" checked style="accent-color:#D48A2E">
                                        <span>Teacher Portal</span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:11.5px;color:#1B1A17;font-weight:600">
                                        <input type="hidden" name="class_visibility[{{ $cs->id }}][is_published_student]" value="0">
                                        <input type="checkbox" name="class_visibility[{{ $cs->id }}][is_published_student]" value="1" checked style="accent-color:#D48A2E">
                                        <span>Student Portal</span>
                                    </label>
                                </div>
                                <span style="font-size:12px;color:#68665D;font-weight:600">Start Date:</span>
                                <input type="date" id="auto-date-{{ $cs->id }}" value="{{ date('Y-m-d') }}" class="ink-input" style="padding:6px 10px;font-size:12px">
                                <button type="button" class="btn-action btn-outline" onclick="autoFillDatesForClass({{ $cs->id }})" style="font-size:11.5px">
                                    Auto-Assign Sequential Dates
                                </button>
                            </div>
                        </div>

                        <div id="paper-rows-container-{{ $cs->id }}" style="display:flex;flex-direction:column;gap:12px">
                            @if($csSubjects->isNotEmpty())
                                @foreach($csSubjects as $idx => $sub)
                                    <div class="paper-row-item-{{ $cs->id }}" style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:8px;padding:14px">
                                        <div style="display:grid;grid-template-columns:2.5fr 1.5fr 2fr 1fr 30px;gap:10px;align-items:center;margin-bottom:10px">
                                            <div>
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">SUBJECT</label>
                                                <select name="class_schedules[{{ $cs->id }}][{{ $idx }}][subject_id]" class="ink-select" style="width:100%;font-weight:600">
                                                    {!! str_replace('value="'.$sub->id.'"', 'value="'.$sub->id.'" selected', $subOptsHtml) !!}
                                                </select>
                                            </div>
                                            <div>
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">EXAM DATE</label>
                                                <input type="date" name="class_schedules[{{ $cs->id }}][{{ $idx }}][exam_date]" class="cs-exam-date-{{ $cs->id }} ink-input" value="{{ date('Y-m-d', strtotime("+{$idx} days")) }}" style="width:100%;font-weight:600">
                                            </div>
                                            <div>
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">TIME SLOT</label>
                                                <div style="display:flex;gap:6px">
                                                    <input type="time" name="class_schedules[{{ $cs->id }}][{{ $idx }}][start_time]" value="09:00" class="ink-input" style="width:50%;padding:6px 6px;font-size:11.5px">
                                                    <input type="time" name="class_schedules[{{ $cs->id }}][{{ $idx }}][end_time]" value="11:30" class="ink-input" style="width:50%;padding:6px 6px;font-size:11.5px">
                                                </div>
                                            </div>
                                            <div>
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">MARKS</label>
                                                <input type="number" name="class_schedules[{{ $cs->id }}][{{ $idx }}][total_marks]" value="100" min="1" max="1000" class="ink-input" style="width:100%;font-weight:700">
                                            </div>
                                            <div style="text-align:center;padding-top:16px">
                                                <button type="button" onclick="this.closest('.paper-row-item-{{ $cs->id }}').remove()" style="background:none;border:none;color:#A2412C;cursor:pointer;font-size:14px;font-weight:bold" title="Remove Subject">✕</button>
                                            </div>
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:center;padding:10px 12px;background:#FFFFFF;border:1px solid #E1DFD7;border-radius:6px">
                                            <div style="display:flex;align-items:center;gap:8px">
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D">ROOM:</label>
                                                <select name="class_schedules[{{ $cs->id }}][{{ $idx }}][room]" class="cs-room-select-{{ $cs->id }} ink-select" style="width:100%;font-size:11.5px;padding:5px 8px">
                                                    {!! $roomOptionsHtml !!}
                                                </select>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px">
                                                <label style="font-size:10.5px;font-weight:700;color:#68665D">DEADLINE:</label>
                                                <input type="date" name="class_schedules[{{ $cs->id }}][{{ $idx }}][result_deadline]" class="cs-res-date-{{ $cs->id }} ink-input" value="{{ date('Y-m-d', strtotime("+".($idx+7)." days")) }}" style="width:100%;font-size:11.5px;padding:5px 8px">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div style="margin-top:14px">
                            <button type="button" class="btn-action btn-outline" onclick="addPaperRowForClass({{ $cs->id }})" style="font-size:11.5px">
                                + Add Additional Subject Row
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:14px;border-top:1px solid #E1DFD7">
                <button type="button" class="btn-action btn-outline" onclick="closeWorkspace()">Close</button>
                <button type="submit" class="btn-action btn-primary-custom">
                    Publish Exam Datesheet
                </button>
            </div>
        </form>
    </div>
@endif

{{-- Streamlined Filter Bar --}}
<div class="card no-print" style="margin-bottom:20px;padding:14px 18px;background:#F9F8F5;border:1px solid #E1DFD7;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.02)">
    <form method="GET" action="{{ route('lms.datesheet.index') }}" style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:260px">
            <span style="font-size:11.5px;font-weight:700;color:#68665D;text-transform:uppercase;letter-spacing:0.05em">Class Filter:</span>
            <select name="class_section_id" onchange="this.form.submit()" class="ink-select" style="font-weight:600;font-size:13px;flex:1;max-width:320px">
                <option value="">All Institute Classes</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>
                        {{ $cs->instituteClass?->custom_name ?? 'Class' }} - {{ $cs->section_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request()->has('class_section_id'))
            <a href="{{ route('lms.datesheet.index') }}" class="btn-action btn-outline" style="font-size:11.5px">Reset Filter</a>
        @endif
    </form>
</div>

{{-- Class Directory Cards --}}
@php
    $displayedSections = $classSections;
    if (request()->filled('class_section_id')) {
        $displayedSections = $classSections->where('id', request('class_section_id'));
    }
@endphp

<div id="classes-directory-container">
    @forelse($displayedSections as $cs)
        @php
            $cName = trim(($cs->instituteClass?->custom_name ?? 'Class').' - '.$cs->section_name);
            $exams = $groupedDatesheet->get($cName, collect());
            $csSubjects = $cs->subjects ?? collect();
            $scheduledSubIds = $exams->pluck('subject_id')->filter()->toArray();
            $unscheduledSubjects = $csSubjects->filter(fn($sub) => !in_array($sub->id, $scheduledSubIds));
            $isComplete = $unscheduledSubjects->isEmpty() && $exams->isNotEmpty();
        @endphp

        <div id="class-card-display-{{ $cs->id }}" class="class-datesheet-card">
            {{-- Class Summary Header --}}
            <div class="class-header-row" onclick="toggleClassBody({{ $cs->id }})">
                <div style="display:flex;align-items:center;gap:14px">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <h3 style="font-family:'Manrope', 'Space Grotesk', sans-serif;font-size:15.5px;font-weight:800;color:#1B1A17;margin:0">
                                {{ $cName }}
                            </h3>
                            @if($isComplete)
                                <span class="badge-status badge-complete">Complete</span>
                            @else
                                <span class="badge-status badge-pending">{{ $unscheduledSubjects->count() }} Subject(s) Missing</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:#68665D;margin-top:3px;font-weight:500">
                            {{ $exams->count() }} Exam Papers Scheduled
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap" onclick="event.stopPropagation()">
                    @if($exams->isNotEmpty())
                        @php
                            $classPubTeacher = $exams->every(fn($e) => $e->is_published_teacher);
                            $classPubStudent = $exams->every(fn($e) => $e->is_published_student);
                        @endphp
                        <span class="badge-status {{ $classPubTeacher ? 'badge-complete' : 'badge-danger' }}" style="font-size:11px">
                            Teacher: {{ $classPubTeacher ? 'Visible' : 'Hidden' }}
                        </span>
                        <span class="badge-status {{ $classPubStudent ? 'badge-complete' : 'badge-danger' }}" style="font-size:11px">
                            Student: {{ $classPubStudent ? 'Visible' : 'Hidden' }}
                        </span>
                    @endif

                    <button type="button" class="btn-action btn-outline no-print" onclick="printClassDatesheet({{ $cs->id }})">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        <span>Print</span>
                    </button>
                    @if(auth()->user()->isAdministration() && $exams->isNotEmpty())
                        <form method="POST" action="{{ route('lms.datesheet.destroyClass', $cs->id) }}" onsubmit="return confirm('Delete entire examination datesheet for {{ $cName }}?')" class="no-print" style="margin:0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-danger-custom">Delete</button>
                        </form>
                    @endif
                    <div id="accordion-arrow-{{ $cs->id }}" style="color:#A19E92;margin-left:4px;transition:transform 0.2s ease">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            {{-- Collapsible Class Body Details --}}
            <div id="class-card-body-{{ $cs->id }}" class="class-card-body" style="display:none">
                
                {{-- Unscheduled Subjects Alert Callout --}}
                @if($unscheduledSubjects->isNotEmpty())
                    <div style="margin-bottom:18px;padding:12px 16px;background:#FDF8EE;border:1px solid #F0DDBE;border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                        <div>
                            <div style="font-weight:700;color:#8A5A10;font-size:12px;text-transform:uppercase;letter-spacing:0.04em">
                                Pending Subject Datesheets:
                            </div>
                            <div style="font-size:12.5px;color:#68665D;margin-top:3px">
                                @foreach($unscheduledSubjects as $unSub)
                                    <span style="display:inline-block;margin-right:12px">• {{ $unSub->subject_name }} (No datesheet created)</span>
                                @endforeach
                            </div>
                        </div>
                        @if(auth()->user()->isAdministration())
                            <button type="button" class="btn-action btn-outline" onclick="quickCreateSubjectExam({{ $cs->id }})" style="font-size:11.5px;border-color:#E8CEAA;color:#8A5A10;background:#FFFFFF">
                                + Schedule Missing Papers
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Whole-Class Datesheet Portal Visibility Controls Callout --}}
                @if(auth()->user()->isAdministration() && $exams->isNotEmpty())
                    <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="no-print">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#1B1A17;text-transform:uppercase;letter-spacing:0.04em">
                                Class Datesheet Portal Visibility Actions
                            </div>
                            <div style="font-size:12px;color:#68665D;margin-top:2px">
                                Turn ON/OFF datesheet visibility for all subjects of {{ $cName }} at once on Teacher and Student portals.
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <form method="POST" action="{{ route('lms.datesheet.toggleVisibility', $cs->id) }}" style="margin:0">
                                @csrf
                                <input type="hidden" name="target" value="teacher">
                                <button type="submit" class="btn-action {{ $classPubTeacher ? 'btn-danger-custom' : 'btn-primary-custom' }}" style="font-size:11.5px;padding:6px 14px">
                                    {{ $classPubTeacher ? 'Turn OFF Teacher Portal Visibility' : 'Turn ON Teacher Portal Visibility' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('lms.datesheet.toggleVisibility', $cs->id) }}" style="margin:0">
                                @csrf
                                <input type="hidden" name="target" value="student">
                                <button type="submit" class="btn-action {{ $classPubStudent ? 'btn-danger-custom' : 'btn-primary-custom' }}" style="font-size:11.5px;padding:6px 14px">
                                    {{ $classPubStudent ? 'Turn OFF Student Portal Visibility' : 'Turn ON Student Portal Visibility' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Scheduled Exams Table --}}
                @if($exams->isNotEmpty())
                    <table class="datesheet-table">
                        <thead>
                            <tr>
                                <th style="width:24%">Subject</th>
                                <th style="width:26%">Date &amp; Session</th>
                                <th style="width:16%">Room</th>
                                <th style="width:18%">Title / Marks</th>
                                <th style="width:16%">Deadline</th>
                                @if(auth()->user()->isAdministration())
                                    <th class="no-print" style="width:12%;text-align:right">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exams as $idx => $exam)
                                @php
                                    $startDate = $exam->start_time ? $exam->start_time->format('D, M d, Y') : '—';
                                    $rawExamDate = $exam->start_time ? $exam->start_time->format('Y-m-d') : '';
                                    $rawStartTime = $exam->start_time ? $exam->start_time->format('H:i') : '09:00';
                                    $rawEndTime = $exam->end_time ? $exam->end_time->format('H:i') : '11:30';
                                    $rawDeadline = $exam->result_deadline ? $exam->result_deadline->format('Y-m-d') : '';
                                    $startTime = $exam->start_time ? $exam->start_time->format('h:i A') : '—';
                                    $endTime = $exam->end_time ? $exam->end_time->format('h:i A') : '—';
                                    $resultDeadline = $exam->result_deadline ? $exam->result_deadline->format('M d, Y') : '—';
                                    $rawRoomStr = trim($exam->room ?: 'Unassigned');
                                    $roomName = preg_replace('/^(room\s*)+/i', 'Room ', $rawRoomStr);
                                    $pubTeacher = $exam->is_published_teacher ?? true;
                                    $pubStudent = $exam->is_published_student ?? true;
                                    
                                    $startHour = $exam->start_time ? (int)$exam->start_time->format('H') : 9;
                                    $sessionLabel = $startHour < 12 ? 'Morning Session' : ($startHour < 15 ? 'Afternoon Session' : 'Evening Session');
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:700;color:#1B1A17;font-size:13px">{{ $exam->subject->subject_name ?? 'Subject' }}</div>
                                        <div style="font-size:11.5px;color:#A19E92;margin-top:1px">{{ $exam->subject->subject_code ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:#1B1A17">{{ $startDate }}</div>
                                        <div style="font-size:11.5px;color:#D48A2E;font-weight:600">{{ $startTime }} - {{ $endTime }}</div>
                                        <div style="font-size:10.5px;color:#A19E92">{{ $sessionLabel }}</div>
                                    </td>
                                    <td>
                                        <span style="font-size:11.5px;font-weight:600;color:#1B1A17;background:#F2EFEB;border:1px solid #E1DFD7;padding:3px 9px;border-radius:6px;display:inline-block">
                                            {{ $roomName }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#1B1A17">{{ $exam->title }}</div>
                                        <div style="font-size:11.5px;color:#68665D">{{ $exam->total_marks }} Marks</div>
                                    </td>
                                    <td>
                                        <div style="font-size:11.5px;color:#8A5A10;font-weight:600">{{ $resultDeadline }}</div>
                                    </td>
                                    @if(auth()->user()->isAdministration())
                                        <td class="no-print" style="text-align:right">
                                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                                <button type="button" class="btn-action btn-outline" onclick="openEditModal({{ $exam->id }}, '{{ addslashes($exam->title) }}', '{{ $exam->type }}', {{ $exam->academic_term_id ?? 'null' }}, '{{ $rawExamDate }}', '{{ $rawStartTime }}', '{{ $rawEndTime }}', {{ $exam->total_marks }}, '{{ $rawDeadline }}', '{{ addslashes($exam->instructions ?? '') }}', '{{ addslashes($roomName) }}', {{ $pubTeacher ? 1 : 0 }}, {{ $pubStudent ? 1 : 0 }})" style="padding:4px 9px;font-size:11.5px">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('lms.datesheet.destroy', $exam->id) }}" onsubmit="return confirm('Remove this exam paper?')" style="margin:0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-danger-custom" style="padding:4px 9px;font-size:11.5px">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align:center;padding:28px;color:#68665D;font-size:13px;font-weight:500">
                        No exam papers scheduled for {{ $cName }} yet.
                    </div>
                @endif

            </div>
        </div>
    @empty
        <div class="card" style="text-align:center;padding:48px 20px;background:#F9F8F5;border:1px solid #E1DFD7;border-radius:14px">
            <h3 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin-bottom:6px">
                No Examination Schedules Found
            </h3>
            <p style="color:#68665D;font-size:13px;max-width:400px;margin:0 auto 16px">
                No active exam datesheets have been scheduled yet.
            </p>
            @if(auth()->user()->isAdministration())
                <button class="btn-action btn-primary-custom" onclick="openModal('create-datesheet-modal')">
                    Create Exam Schedule
                </button>
            @endif
        </div>
    @endforelse
</div>

{{-- Setup Modal --}}
@if(auth()->user()->isAdministration())
    <div class="modal-backdrop" id="create-datesheet-modal" style="display:none;position:fixed;inset:0;background:rgba(27,26,23,0.55);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:20px">
        <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:14px;width:100%;max-width:720px;box-shadow:0 25px 50px rgba(0,0,0,0.15);display:flex;flex-direction:column;max-height:85vh;overflow:hidden">
            <div style="background:#F2EFEB;padding:16px 20px;border-bottom:1px solid #E1DFD7;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 style="font-family:'Manrope',sans-serif;font-weight:800;font-size:16px;color:#1B1A17;margin:0">
                        Create Exam Schedule
                    </h3>
                    <p style="font-size:12px;color:#68665D;margin:2px 0 0;font-weight:500">Select title and target class sections.</p>
                </div>
                <button onclick="closeModal('create-datesheet-modal')" style="background:none;border:none;color:#68665D;font-size:18px;cursor:pointer;padding:4px">✕</button>
            </div>

            <div style="padding:20px;overflow-y:auto;flex:1">
                <div style="margin-bottom:18px">
                    <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:6px;display:block;text-transform:uppercase;letter-spacing:0.04em">Exam Title / Type *</label>
                    <input type="text" id="modal-exam-name" required value="Midterm Examination" class="ink-input" style="width:100%;font-weight:600;font-size:13px;padding:9px 12px">
                </div>

                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <label style="font-size:12px;font-weight:700;color:#1B1A17">Select Target Classes:</label>
                        <button type="button" class="btn-action btn-outline" onclick="toggleAllClassCheckboxes(this)" style="font-size:11.5px;padding:4px 9px">
                            Select All
                        </button>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:8px;padding:14px;background:#FFFFFF;border:1px solid #E1DFD7;border-radius:10px">
                        @foreach($classSections as $cs)
                            @php
                                $cName = $cs->instituteClass?->custom_name ?? 'Class';
                            @endphp
                            <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:#1B1A17;cursor:pointer;padding:8px 10px;border-radius:6px;background:#F9F8F5;border:1px solid #E1DFD7;transition:all 0.15s ease">
                                <input type="checkbox" id="class-chk-{{ $cs->id }}" value="{{ $cs->id }}" data-class-name="{{ $cName }} - {{ $cs->section_name }}" class="class-builder-checkbox" style="accent-color:#D48A2E">
                                <div>
                                    <div style="font-weight:600;color:#1B1A17">{{ $cName }} - {{ $cs->section_name }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="background:#F2EFEB;padding:14px 20px;border-top:1px solid #E1DFD7;display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn-action btn-outline" onclick="closeModal('create-datesheet-modal')">Cancel</button>
                <button type="button" class="btn-action btn-primary-custom" onclick="saveClassSelectionAndOpenWorkspace()">
                    Open Schedule Builder
                </button>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal-backdrop" id="edit-datesheet-modal" style="display:none;position:fixed;inset:0;background:rgba(27,26,23,0.55);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;padding:20px">
        <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:14px;width:100%;max-width:540px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,0.15)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #E1DFD7">
                <h3 style="font-family:'Manrope',sans-serif;font-weight:800;font-size:16px;color:#1B1A17;margin:0">
                    Edit Schedule Entry
                </h3>
                <button onclick="closeModal('edit-datesheet-modal')" style="background:none;border:none;color:#68665D;font-size:18px;cursor:pointer">✕</button>
            </div>

            <form id="edit-datesheet-form" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-term-id" name="academic_term_id" value="">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="grid-column:1/-1">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Exam Title *</label>
                        <input type="text" id="edit-title" name="title" required class="ink-input" style="width:100%;font-weight:600">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Exam Date *</label>
                        <input type="date" id="edit-exam-date" name="exam_date" required class="ink-input" style="width:100%;font-weight:600">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Room *</label>
                        <select id="edit-room" name="room" class="ink-select" style="width:100%;font-weight:600">
                            {!! $roomOptionsHtml !!}
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Start Time *</label>
                        <input type="time" id="edit-start-time" name="start_time" required class="ink-input" style="width:100%">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">End Time *</label>
                        <input type="time" id="edit-end-time" name="end_time" required class="ink-input" style="width:100%">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Total Marks *</label>
                        <input type="number" id="edit-total-marks" name="total_marks" required min="1" max="1000" class="ink-input" style="width:100%;font-weight:700">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11.5px;font-weight:700;color:#68665D;margin-bottom:4px;display:block">Result Deadline</label>
                        <input type="date" id="edit-result-deadline" name="result_deadline" class="ink-input" style="width:100%">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;background:#FFFFFF;border:1px solid #E1DFD7;padding:12px;border-radius:8px">
                        <label style="font-size:11.5px;font-weight:700;color:#1B1A17;margin-bottom:8px;display:block">Portal Visibility</label>
                        <div style="display:flex;gap:18px">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:#1B1A17;font-weight:600">
                                <input type="hidden" name="is_published_teacher" value="0">
                                <input type="checkbox" id="edit-pub-teacher" name="is_published_teacher" value="1" style="accent-color:#D48A2E">
                                <span>Teacher Portal</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:#1B1A17;font-weight:600">
                                <input type="hidden" name="is_published_student" value="0">
                                <input type="checkbox" id="edit-pub-student" name="is_published_student" value="1" style="accent-color:#D48A2E">
                                <span>Student Portal</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px">
                    <button type="button" class="btn-action btn-outline" onclick="closeModal('edit-datesheet-modal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary-custom">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    function toggleClassBody(sectionId) {
        const body = document.getElementById(`class-card-body-${sectionId}`);
        const arrow = document.getElementById(`accordion-arrow-${sectionId}`);
        if (body.style.display === 'none' || !body.style.display) {
            body.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            body.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function printWholeInstitute() {
        document.body.classList.remove('printing-single-class');
        document.querySelectorAll('.class-card-body').forEach(b => b.style.display = 'block');
        window.print();
    }

    function printClassDatesheet(sectionId) {
        document.body.classList.add('printing-single-class');
        document.querySelectorAll('.class-datesheet-card').forEach(c => c.classList.remove('active-print-target'));
        const targetCard = document.getElementById(`class-card-display-${sectionId}`);
        const targetBody = document.getElementById(`class-card-body-${sectionId}`);
        if (targetCard) targetCard.classList.add('active-print-target');
        if (targetBody) targetBody.style.display = 'block';
        window.print();
        document.body.classList.remove('printing-single-class');
    }

    function quickCreateSubjectExam(sectionId) {
        const chk = document.getElementById(`class-chk-${sectionId}`);
        if (chk) chk.checked = true;
        saveClassSelectionAndOpenWorkspace();
        selectClassTab(sectionId);
    }

    function openModal(id) { 
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex'; 
    }
    function closeModal(id) { 
        const el = document.getElementById(id);
        if (el) el.style.display = 'none'; 
    }
    function closeWorkspace() { 
        const ws = document.getElementById('datesheet-builder-workspace');
        if (ws) ws.style.display = 'none'; 
    }

    function openEditModal(id, title, type, termId, examDate, startTime, endTime, totalMarks, resultDeadline, instructions, room, pubTeacher, pubStudent) {
        const form = document.getElementById('edit-datesheet-form');
        form.action = "{{ url('/lms/datesheet') }}/" + id;
        document.getElementById('edit-title').value = title;
        if (termId) document.getElementById('edit-term-id').value = termId;
        document.getElementById('edit-exam-date').value = examDate;
        document.getElementById('edit-start-time').value = startTime;
        document.getElementById('edit-end-time').value = endTime;
        document.getElementById('edit-total-marks').value = totalMarks;
        document.getElementById('edit-result-deadline').value = resultDeadline;
        document.getElementById('edit-room').value = room || '';
        document.getElementById('edit-pub-teacher').checked = !!pubTeacher;
        document.getElementById('edit-pub-student').checked = !!pubStudent;
        openModal('edit-datesheet-modal');
    }

    function toggleAllClassCheckboxes(btn) {
        const checkboxes = document.querySelectorAll('.class-builder-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        btn.textContent = !allChecked ? 'Deselect All' : 'Select All';
    }

    function saveClassSelectionAndOpenWorkspace() {
        const examNameVal = document.getElementById('modal-exam-name').value.trim() || 'Midterm Examination';
        document.getElementById('ws-form-title').value = examNameVal;
        document.getElementById('ws-form-type').value = examNameVal;
        document.getElementById('ws-exam-title-display').textContent = examNameVal;

        const checkedBoxes = document.querySelectorAll('.class-builder-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one class checkbox.');
            return;
        }

        const tabsContainer = document.getElementById('class-tabs-container');
        tabsContainer.innerHTML = '';
        document.querySelectorAll('.class-schedule-block').forEach(b => b.style.display = 'none');

        let firstSectionId = null;
        checkedBoxes.forEach((cb, idx) => {
            const sectionId = cb.value;
            const className = cb.getAttribute('data-class-name') || `Class #${sectionId}`;
            if (idx === 0) firstSectionId = sectionId;

            const tabBtn = document.createElement('button');
            tabBtn.type = 'button';
            tabBtn.id = `class-tab-btn-${sectionId}`;
            tabBtn.className = 'class-nav-tab btn-action btn-outline';
            tabBtn.style.fontSize = '12px';
            tabBtn.innerHTML = className;
            tabBtn.onclick = () => selectClassTab(sectionId);
            tabsContainer.appendChild(tabBtn);
        });

        closeModal('create-datesheet-modal');
        const workspace = document.getElementById('datesheet-builder-workspace');
        workspace.style.display = 'block';

        if (firstSectionId) selectClassTab(firstSectionId);
        workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function selectClassTab(sectionId) {
        document.querySelectorAll('.class-nav-tab').forEach(tab => {
            tab.style.background = '#FFFFFF';
            tab.style.borderColor = '#E1DFD7';
            tab.style.color = '#1B1A17';
        });
        const activeTab = document.getElementById(`class-tab-btn-${sectionId}`);
        if (activeTab) {
            activeTab.style.background = '#D48A2E';
            activeTab.style.borderColor = '#C07A22';
            activeTab.style.color = '#FFFFFF';
        }
        document.querySelectorAll('.class-schedule-block').forEach(b => b.style.display = 'none');
        const activeBlock = document.getElementById(`class-block-${sectionId}`);
        if (activeBlock) activeBlock.style.display = 'block';
    }

    function autoFillDatesForClass(sectionId) {
        const startDateInput = document.getElementById(`auto-date-${sectionId}`);
        if (!startDateInput || !startDateInput.value) return;
        let currDate = new Date(startDateInput.value);
        const examDateInputs = document.querySelectorAll(`.cs-exam-date-${sectionId}`);
        const resDateInputs = document.querySelectorAll(`.cs-res-date-${sectionId}`);
        
        examDateInputs.forEach((inp, idx) => {
            if (currDate.getDay() === 0) currDate.setDate(currDate.getDate() + 1);
            let dateStr = currDate.toISOString().split('T')[0];
            inp.value = dateStr;
            if (resDateInputs[idx]) {
                let deadline = new Date(currDate);
                deadline.setDate(deadline.getDate() + 7);
                resDateInputs[idx].value = deadline.toISOString().split('T')[0];
            }
            currDate.setDate(currDate.getDate() + 1);
        });
    }

    function addPaperRowForClass(sectionId) {
        const container = document.getElementById(`paper-rows-container-${sectionId}`);
        if (!container) return;
        const firstSelect = container.querySelector('select');
        let optionsHtml = firstSelect ? firstSelect.innerHTML : '<option value="">Select Subject</option>';
        const roomSelect = container.querySelector('.cs-room-select-' + sectionId);
        let roomOptionsHtml = roomSelect ? roomSelect.innerHTML : '<option value="" selected>-- Select Room / Hall --</option>';

        const idx = container.children.length;
        let targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + idx);
        if (targetDate.getDay() === 0) targetDate.setDate(targetDate.getDate() + 1);
        
        const dateStr = targetDate.toISOString().split('T')[0];
        const deadlineDate = new Date(targetDate);
        deadlineDate.setDate(deadlineDate.getDate() + 7);
        const deadlineStr = deadlineDate.toISOString().split('T')[0];

        const rowDiv = document.createElement('div');
        rowDiv.className = `paper-row-item-${sectionId}`;
        rowDiv.style.cssText = 'background:#F9F8F5;border:1px solid #E1DFD7;border-radius:8px;padding:14px';
        rowDiv.innerHTML = `
            <div style="display:grid;grid-template-columns:2.5fr 1.5fr 2fr 1fr 30px;gap:10px;align-items:center;margin-bottom:10px">
                <div>
                    <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">SUBJECT</label>
                    <select name="class_schedules[${sectionId}][${idx}][subject_id]" class="ink-select" style="width:100%;font-weight:600">
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">EXAM DATE</label>
                    <input type="date" name="class_schedules[${sectionId}][${idx}][exam_date]" class="cs-exam-date-${sectionId} ink-input" value="${dateStr}" style="width:100%;font-weight:600">
                </div>
                <div>
                    <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">TIME SLOT</label>
                    <div style="display:flex;gap:6px">
                        <input type="time" name="class_schedules[${sectionId}][${idx}][start_time]" value="09:00" class="ink-input" style="width:50%;padding:6px 6px;font-size:11.5px">
                        <input type="time" name="class_schedules[${sectionId}][${idx}][end_time]" value="11:30" class="ink-input" style="width:50%;padding:6px 6px;font-size:11.5px">
                    </div>
                </div>
                <div>
                    <label style="font-size:10.5px;font-weight:700;color:#68665D;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:0.04em">MARKS</label>
                    <input type="number" name="class_schedules[${sectionId}][${idx}][total_marks]" value="100" min="1" max="1000" class="ink-input" style="width:100%;font-weight:700">
                </div>
                <div style="text-align:center;padding-top:16px">
                    <button type="button" onclick="this.closest('.paper-row-item-${sectionId}').remove()" style="background:none;border:none;color:#A2412C;cursor:pointer;font-size:14px;font-weight:bold" title="Remove Subject">✕</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:center;padding:10px 12px;background:#FFFFFF;border:1px solid #E1DFD7;border-radius:6px">
                <div style="display:flex;align-items:center;gap:8px">
                    <label style="font-size:10.5px;font-weight:700;color:#68665D">ROOM:</label>
                    <select name="class_schedules[${sectionId}][${idx}][room]" class="cs-room-select-${sectionId} ink-select" style="width:100%;font-size:11.5px;padding:5px 8px">
                        ${roomOptionsHtml}
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <label style="font-size:10.5px;font-weight:700;color:#68665D">DEADLINE:</label>
                    <input type="date" name="class_schedules[${sectionId}][${idx}][result_deadline]" class="cs-res-date-${sectionId} ink-input" value="${deadlineStr}" style="width:100%;font-size:11.5px;padding:5px 8px">
                </div>
            </div>
        `;
        container.appendChild(rowDiv);
    }

    function validateDatesheetForm(event) {
        const workspace = document.getElementById('datesheet-builder-workspace');
        if (!workspace || workspace.style.display === 'none') return true;

        const classBlocks = workspace.querySelectorAll('.class-schedule-block');

        for (let block of classBlocks) {
            const classTitleEl = block.querySelector('h4');
            const className = classTitleEl ? classTitleEl.textContent.trim() : 'Class';
            const rows = block.querySelectorAll('[class^="paper-row-item-"]');

            const paperData = [];
            rows.forEach(row => {
                const subSel = row.querySelector('select[name*="[subject_id]"]');
                const dateInp = row.querySelector('input[name*="[exam_date]"]');
                const startInp = row.querySelector('input[name*="[start_time]"]');
                const endInp = row.querySelector('input[name*="[end_time]"]');

                if (subSel && dateInp && startInp && endInp && dateInp.value) {
                    const subText = subSel.options[subSel.selectedIndex] ? subSel.options[subSel.selectedIndex].text : 'Subject';
                    paperData.push({
                        subject: subText,
                        date: dateInp.value,
                        startTime: startInp.value,
                        endTime: endInp.value
                    });
                }
            });

            const dateGroups = {};
            paperData.forEach(p => {
                if (!dateGroups[p.date]) dateGroups[p.date] = [];
                dateGroups[p.date].push(p);
            });

            for (let dStr in dateGroups) {
                const group = dateGroups[dStr];
                if (group.length > 1) {
                    for (let i = 0; i < group.length; i++) {
                        for (let j = i + 1; j < group.length; j++) {
                            const p1 = group[i];
                            const p2 = group[j];
                            if (p1.startTime < p2.endTime && p2.startTime < p1.endTime) {
                                alert(`Schedule Conflict Error!\n\nFor ${className} on ${dStr}:\n• "${p1.subject}" (${p1.startTime} - ${p1.endTime})\n• "${p2.subject}" (${p2.startTime} - ${p2.endTime})\n\nExams scheduled on the same day must have non-overlapping timings.`);
                                event.preventDefault();
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
</script>
@endsection

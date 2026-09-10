@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Assign Subject & Teacher')
@section('breadcrumb', 'Subject & Teacher Assignments')

@section('content')
<style>
    .assignments-table {
        width: 100%;
        border-collapse: collapse;
    }
    .assignments-table th {
        background: #f1f5f9;
        color: #64748b;
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .assignments-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        vertical-align: middle;
        color: #334155;
    }
    .assignments-table tr:hover td {
        background: #fdf2f8;
    }

    /* Accordion Cards */
    .allocation-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 14px;
        margin-bottom: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .allocation-card.open {
        border-color: rgba(225, 48, 108, 0.4);
        box-shadow: 0 8px 24px rgba(225, 48, 108, 0.08);
    }
    .allocation-card-header {
        padding: 14px 18px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease;
    }
    .allocation-card-header:hover {
        background: #f8fafc;
    }
    .allocation-card-body {
        display: none;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .allocation-card.open .allocation-card-body {
        display: block;
    }
    .chevron-icon {
        font-size: 12px;
        color: #e1306c;
        transition: transform 0.2s ease;
        display: inline-block;
        width: 14px;
        text-align: center;
    }

    /* View Mode Toggle Buttons */
    .view-toggle-btn {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        background: transparent;
        color: #64748b;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .view-toggle-btn.active {
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(225, 48, 108, 0.25);
    }
    .view-toggle-btn:hover:not(.active) {
        color: #0f172a;
        background: #e2e8f0;
    }

    /* Search bar styling */
    .search-input-box {
        width: 100%;
        padding: 10px 14px 10px 38px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        color: #0f172a;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }
    .search-input-box:focus {
        border-color: #e1306c;
        box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.2);
    }

    /* Modal Backdrop & Box */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.active { display: flex; }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        width: 100%;
        max-width: 520px;
        padding: 24px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        color: #0f172a;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            📌 Subject &amp; Teacher Allocations
        </h1>
        <p style="color:#64748b;font-size:13px;margin-top:4px;font-weight:500">
            Map faculty to class subjects. Toggle views by Class Name, Teacher Name, or Unassigned Subjects.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:20px">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:20px">
        ⚠️ {{ session('error') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px">
    <!-- Left Column: Create / Allocate Teacher Form -->
    <div>
        <div class="card" style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);box-shadow:0 4px 20px -2px rgba(0,0,0,0.04);border-radius:16px;padding:24px">
            <div style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin-bottom:20px">
                ➕ Assign Teacher to Subject
            </div>

            @php
                $storeRoute = request()->routeIs('teacher.*')
                    ? route('teacher.assignments.store')
                    : route('principal.assignments.store');
                $unassignedCount = $offerings->where('is_assigned', false)->count();
            @endphp

            <form id="leftAssignForm" method="POST" action="{{ $storeRoute }}" onsubmit="return confirmLeftAssignmentForm(event)">
                @csrf

                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Select Teacher *</label>
                    <select id="teacher_id" name="teacher_id" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                        <option value="">-- Select Teacher --</option>
                        @foreach($staffMembers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->staff_role ?? ucfirst($t->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Select Class &amp; Section *</label>
                    <select id="class_section_id" name="class_section_id" required onchange="onFormSelectionChange()" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                        <option value="">-- Select Section --</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" data-class-id="{{ $sec->institute_class_id }}">
                                {{ $sec->instituteClass->custom_name }} — Section {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Select Subject *</label>
                    <select id="subject_id" name="subject_id" required onchange="updateAssignmentStatusNotice()" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                        <option value="">-- Select Section First --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" data-class-id="{{ $sub->institute_class_id }}" class="subject-option" style="display:none">
                                {{ $sub->subject_name }} @if($sub->subject_code)({{ $sub->subject_code }})@endif
                            </option>
                        @endforeach
                    </select>

                    <!-- DYNAMIC HELPER NOTICE -->
                    <div id="assignmentStatusNotice" style="margin-top:8px"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-weight:800">Assign Teacher</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Active Allocations with 3 Top Menu View Switches -->
    <div>
        <div class="card" style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);box-shadow:0 4px 20px -2px rgba(0,0,0,0.04);border-radius:16px;padding:24px">
            <!-- Header & 3 TOP MENU OPTIONS (SINGLE ROW) -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px">
                <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;white-space:nowrap">
                    📖 Session Allocations
                </div>

                <!-- 3 TOP MENU TOGGLE BUTTONS IN A SINGLE ROW -->
                <div style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;padding:4px;border-radius:10px;border:1px solid #e2e8f0;flex-wrap:nowrap;white-space:nowrap;overflow-x:auto;max-width:100%">
                    <button type="button" id="btnViewClass" class="view-toggle-btn active" onclick="switchAllocationView('class')">
                        🏫 View by Class
                    </button>
                    <button type="button" id="btnViewTeacher" class="view-toggle-btn" onclick="switchAllocationView('teacher')">
                        👨‍🏫 View by Teacher
                    </button>
                    <button type="button" id="btnViewUnassigned" class="view-toggle-btn" onclick="switchAllocationView('unassigned')">
                        ⚠️ Unassigned 
                        <span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;font-size:10px;padding:1px 5px;margin-left:4px;font-weight:800">
                            {{ $unassignedCount }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- SEARCH BAR -->
            <div style="position:relative;margin-bottom:20px">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b;font-size:14px">🔍</span>
                <input type="text" 
                       id="assignmentSearchInput" 
                       class="search-input-box" 
                       placeholder="Search by Class (e.g. Class 9), Teacher Name, Subject, or Code..." 
                       oninput="filterAssignmentsTable()">
            </div>

            @if($offerings->count() > 0)
                <!-- VIEW 1: GROUPED BY CLASS NAME -->
                <div id="viewByClassContainer">
                    @foreach($byClass as $className => $classOfferings)
                        @php
                            $cardId = "class-card-".Str::slug($className);
                            $chevId = "chev-cls-".Str::slug($className);
                            $unassignedInClass = $classOfferings->where('is_assigned', false)->count();
                        @endphp
                        <div class="allocation-card" id="{{ $cardId }}" data-group-search="{{ strtolower($className) }}">
                            <div class="allocation-card-header" onclick="toggleAllocationCard('{{ $cardId }}', '{{ $chevId }}')">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <span id="{{ $chevId }}" class="chevron-icon">▶</span>
                                    <div>
                                        <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">
                                            🏫 {{ $className }}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:1px;font-weight:500">
                                            {{ $classOfferings->count() }} Subject Offering(s) • Click to expand
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:8px" onclick="event.stopPropagation()">
                                    @if($unassignedInClass > 0)
                                        <span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;font-size:11px;font-weight:800">
                                            ⚠️ {{ $unassignedInClass }} Unassigned
                                        </span>
                                    @endif
                                    <span class="badge badge-purple" style="font-weight:700;font-size:11px">
                                        📖 {{ $classOfferings->count() }} Offering(s)
                                    </span>
                                </div>
                            </div>

                            <div class="allocation-card-body">
                                <div style="overflow-x:auto">
                                    <table class="assignments-table">
                                        <thead>
                                            <tr>
                                                <th>Teacher</th>
                                                <th>Section</th>
                                                <th>Subject</th>
                                                <th style="text-align:right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classOfferings as $off)
                                                @php
                                                    $secName = $off->section->section_name ?? '';
                                                    $subName = $off->subject->subject_name ?? '';
                                                    $subCode = $off->subject->subject_code ?? '';
                                                    $tName = $off->teacher ? $off->teacher->name : '';
                                                    $tRole = $off->teacher ? ($off->teacher->staff_role ?? ucfirst($off->teacher->role)) : '';
                                                    $searchData = strtolower("{$className} Class {$className} Section {$secName} {$subName} {$subCode} {$tName}");
                                                @endphp
                                                <tr class="offering-row" data-search-text="{{ $searchData }}">
                                                    <td>
                                                        @if($off->is_assigned)
                                                            <strong style="color:#0f172a">{{ $off->teacher->name }}</strong>
                                                            <div style="font-size:11px;color:#64748b">{{ $tRole }}</div>
                                                        @else
                                                            <span style="color:#ef4444;font-weight:800;font-size:13px;display:inline-flex;align-items:center;gap:4px">
                                                                ⚠️ Unassigned
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="color:#0284c7;font-weight:700">Section {{ $secName }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-purple" style="font-weight:700">
                                                            {{ $subName }}
                                                        </span>
                                                        @if($subCode)
                                                            <code style="color:#7c3aed;background:#f5f3ff;border:1px solid #ddd6fe;padding:2px 6px;border-radius:4px;font-size:11px;margin-left:4px;font-family:monospace">
                                                                {{ $subCode }}
                                                            </code>
                                                        @endif
                                                    </td>
                                                    <td style="text-align:right">
                                                        <div style="display:inline-flex;gap:6px">
                                                            @if($off->is_assigned)
                                                                <button type="button" 
                                                                        class="btn btn-ghost btn-sm" 
                                                                        onclick="openReplaceModal({{ $off->section->id }}, '{{ addslashes($className) }} — Section {{ addslashes($secName) }}', {{ $off->subject->id }}, '{{ addslashes($subName) }}', {{ $off->teacher->id }}, '{{ addslashes($off->teacher->name) }}')"
                                                                        style="font-size:11px;padding:4px 10px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                                                                    ✏️ Replace Teacher
                                                                </button>
                                                                <form method="POST" action="{{ request()->routeIs('teacher.*') ? route('teacher.assignments.destroy', $off->assignment) : route('principal.assignments.destroy', $off->assignment) }}" onsubmit="return confirm('Remove teacher assignment for {{ addslashes($subName) }} in {{ addslashes($className) }}?')" style="margin:0;display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm" style="font-size:11px;padding:4px 8px">🗑️</button>
                                                                </form>
                                                            @else
                                                                <button type="button" 
                                                                        class="btn btn-primary btn-sm" 
                                                                        onclick="openReplaceModal({{ $off->section->id }}, '{{ addslashes($className) }} — Section {{ addslashes($secName) }}', {{ $off->subject->id }}, '{{ addslashes($subName) }}', null, null)"
                                                                        style="font-size:11px;padding:4px 10px">
                                                                    ➕ Assign Teacher
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- VIEW 2: GROUPED BY TEACHER NAME -->
                <div id="viewByTeacherContainer" style="display:none">
                    @foreach($byTeacher as $tKey => $tGroup)
                        @php
                            $cardId = "teacher-card-".Str::slug($tKey);
                            $chevId = "chev-t-".Str::slug($tKey);
                            $tOfferings = $tGroup->offerings;
                        @endphp
                        <div class="allocation-card" id="{{ $cardId }}" data-group-search="{{ strtolower($tKey) }}">
                            <div class="allocation-card-header" onclick="toggleAllocationCard('{{ $cardId }}', '{{ $chevId }}')">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <span id="{{ $chevId }}" class="chevron-icon">▶</span>
                                    <div>
                                        <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">
                                            👨‍🏫 {{ $tKey }}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:1px;font-weight:500">
                                            {{ $tOfferings->count() }} Assigned Subject(s) • Click to expand
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:8px" onclick="event.stopPropagation()">
                                    <span class="badge {{ $tOfferings->count() > 0 ? 'badge-purple' : 'badge-ghost' }}" style="font-weight:700;font-size:11px">
                                        📖 {{ $tOfferings->count() }} Assigned Subject(s)
                                    </span>
                                </div>
                            </div>

                            <div class="allocation-card-body">
                                @if($tOfferings->count() > 0)
                                    <div style="overflow-x:auto">
                                        <table class="assignments-table">
                                            <thead>
                                              <tr>
                                                    <th>Class &amp; Section</th>
                                                    <th>Assigned Subject</th>
                                                    <th style="text-align:right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tOfferings as $off)
                                                    @php
                                                        $clsName = $off->section->instituteClass->custom_name ?? '';
                                                        $secName = $off->section->section_name ?? '';
                                                        $subName = $off->subject->subject_name ?? '';
                                                        $subCode = $off->subject->subject_code ?? '';
                                                        $tName = $off->teacher ? $off->teacher->name : '';
                                                        $searchData = strtolower("{$clsName} Class {$clsName} Section {$secName} {$subName} {$subCode} {$tName} {$tKey}");
                                                    @endphp
                                                    <tr class="offering-row" data-search-text="{{ $searchData }}">
                                                        <td>
                                                            <span style="font-weight:800;color:#0f172a">{{ $clsName }}</span> — 
                                                            <span style="color:#0284c7;font-weight:700">Section {{ $secName }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-purple" style="font-weight:700">
                                                                {{ $subName }}
                                                            </span>
                                                            @if($subCode)
                                                                <code style="color:#7c3aed;background:#f5f3ff;border:1px solid #ddd6fe;padding:2px 6px;border-radius:4px;font-size:11px;margin-left:4px;font-family:monospace">
                                                                    {{ $subCode }}
                                                                </code>
                                                            @endif
                                                        </td>
                                                        <td style="text-align:right">
                                                            <div style="display:inline-flex;gap:6px">
                                                                <button type="button" 
                                                                        class="btn btn-ghost btn-sm" 
                                                                        onclick="openReplaceModal({{ $off->section->id }}, '{{ addslashes($clsName) }} — Section {{ addslashes($secName) }}', {{ $off->subject->id }}, '{{ addslashes($subName) }}', {{ $off->teacher->id }}, '{{ addslashes($off->teacher->name) }}')"
                                                                        style="font-size:11px;padding:4px 10px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                                                                    ✏️ Replace Teacher
                                                                </button>
                                                                <form method="POST" action="{{ request()->routeIs('teacher.*') ? route('teacher.assignments.destroy', $off->assignment) : route('principal.assignments.destroy', $off->assignment) }}" onsubmit="return confirm('Remove teacher assignment for {{ addslashes($subName) }} in {{ addslashes($clsName) }}?')" style="margin:0;display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm" style="font-size:11px;padding:4px 8px">🗑️</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div style="text-align:center;padding:24px;color:#64748b">
                                        <div style="font-size:13px">No subjects currently assigned to <strong>{{ $tGroup->teacher->name ?? 'this teacher' }}</strong>.</div>
                                        @if($tGroup->teacher)
                                            <button type="button" class="btn btn-primary btn-sm" onclick="selectTeacherInForm({{ $tGroup->teacher->id }})" style="margin-top:10px;font-size:11px">
                                                ➕ Assign Subject to {{ $tGroup->teacher->name }}
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- VIEW 3: UNASSIGNED SUBJECTS MENU -->
                <div id="viewByUnassignedContainer" style="display:none">
                    @forelse($unassignedByClass as $uClassName => $uOfferings)
                        @php
                            $cardId = "unassigned-card-".Str::slug($uClassName);
                            $chevId = "chev-u-".Str::slug($uClassName);
                        @endphp
                        <div class="allocation-card" id="{{ $cardId }}" data-group-search="{{ strtolower($uClassName) }}">
                            <div class="allocation-card-header" onclick="toggleAllocationCard('{{ $cardId }}', '{{ $chevId }}')">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <span id="{{ $chevId }}" class="chevron-icon">▶</span>
                                    <div>
                                        <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#ef4444">
                                            🏫 {{ $uClassName }}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:1px;font-weight:500">
                                            {{ $uOfferings->count() }} Unassigned Subject(s) • Click to expand
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:8px" onclick="event.stopPropagation()">
                                    <span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;font-size:11px;font-weight:800">
                                        🔴 {{ $uOfferings->count() }} Unassigned
                                    </span>
                                </div>
                            </div>

                            <div class="allocation-card-body">
                                <div style="overflow-x:auto">
                                    <table class="assignments-table">
                                        <thead>
                                            <tr>
                                                <th>Section</th>
                                                <th>Unassigned Subject</th>
                                                <th style="text-align:right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($uOfferings as $off)
                                                @php
                                                    $secName = $off->section->section_name ?? '';
                                                    $subName = $off->subject->subject_name ?? '';
                                                    $subCode = $off->subject->subject_code ?? '';
                                                    $searchData = strtolower("{$uClassName} Class {$uClassName} Section {$secName} {$subName} {$subCode} unassigned");
                                                @endphp
                                                <tr class="offering-row" data-search-text="{{ $searchData }}">
                                                    <td>
                                                        <span style="color:#0284c7;font-weight:700">Section {{ $secName }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-purple" style="font-weight:700">
                                                            {{ $subName }}
                                                        </span>
                                                        @if($subCode)
                                                            <code style="color:#7c3aed;background:#f5f3ff;border:1px solid #ddd6fe;padding:2px 6px;border-radius:4px;font-size:11px;margin-left:4px;font-family:monospace">
                                                                {{ $subCode }}
                                                            </code>
                                                        @endif
                                                    </td>
                                                    <td style="text-align:right">
                                                        <button type="button" 
                                                                class="btn btn-primary btn-sm" 
                                                                onclick="openReplaceModal({{ $off->section->id }}, '{{ addslashes($uClassName) }} — Section {{ addslashes($secName) }}', {{ $off->subject->id }}, '{{ addslashes($subName) }}', null, null)"
                                                                style="font-size:11px;padding:4px 10px">
                                                            ➕ Assign Teacher
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:48px 20px;color:#64748b">
                            <div style="font-size:32px;margin-bottom:8px">🎉</div>
                            <div style="font-size:16px;font-weight:800;color:#059669">All Subjects Fully Allocated!</div>
                            <div style="font-size:12px;margin-top:2px;font-weight:500">Every subject offering across all classes has been assigned to a teacher.</div>
                        </div>
                    @endforelse
                </div>

                <!-- EMPTY SEARCH NOTICE -->
                <div id="noSearchResults" style="display:none;text-align:center;padding:36px;color:#64748b">
                    <div style="font-size:28px;margin-bottom:8px">🔍</div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a">No Matching Allocations Found</div>
                    <div style="font-size:12px;margin-top:2px">Try searching by class name, teacher, subject, or code.</div>
                </div>

            @else
                <div style="text-align:center;padding:48px 20px;color:#64748b">
                    <p style="font-size:15px;margin-bottom:8px;color:#0f172a;font-weight:800">No subject offerings available.</p>
                    <span style="font-size:12px">Create classes and subjects to map teacher assignments.</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: REPLACE OR ASSIGN TEACHER -->
<div id="replaceTeacherModal" class="modal-backdrop" onclick="if(event.target===this) closeReplaceModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
            <h3 id="replaceModalTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                🔄 Replace Teacher
            </h3>
            <button type="button" onclick="closeReplaceModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="replaceTeacherForm" method="POST" action="{{ $storeRoute }}">
            @csrf
            <input type="hidden" name="class_section_id" id="replace_section_id">
            <input type="hidden" name="subject_id" id="replace_subject_id">

            <!-- Context Info Summary -->
            <div style="margin-bottom:20px;padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px">
                <div style="font-size:12px;color:#64748b;margin-bottom:4px;font-weight:600">Target Allocation:</div>
                <div style="font-size:14px;font-weight:800;color:#0f172a" id="replaceModalClassSubject"></div>
                <div style="font-size:12px;color:#334155;margin-top:6px;font-weight:600" id="replaceModalCurrentTeacher"></div>
            </div>

            <div class="form-group mb-4">
                <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Select Teacher *</label>
                <select id="replace_teacher_id" name="teacher_id" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                    <option value="">-- Select Teacher --</option>
                    @foreach($staffMembers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->staff_role ?? ucfirst($t->role) }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:20px;font-size:11px;color:#64748b;line-height:1.4">
                ⚠️ Confirming will update the teacher assignment for this subject and automatically re-generate the active timetable matrix.
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="closeReplaceModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm &amp; Assign Teacher</button>
            </div>
        </form>
    </div>
</div>

<!-- CLASSY CENTERED CONFIRMATION MODAL FOR LEFT FORM -->
<div id="leftFormConfirmModal" class="modal-backdrop" onclick="if(event.target===this) closeLeftFormConfirmModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div style="display:flex;align-items:center;gap:10px">
                <span id="leftFormModalIcon" style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;background:#fef3c7;border:1px solid #fde68a;font-size:18px;color:#d97706">
                    ⚠️
                </span>
                <div>
                    <h3 id="leftFormModalTitle" style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">
                        Confirm Teacher Reassignment
                    </h3>
                    <div style="font-size:11px;color:#64748b">Subject allocation confirmation</div>
                </div>
            </div>
            <button type="button" onclick="closeLeftFormConfirmModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <!-- Subject & Section Context Card -->
        <div style="margin-bottom:20px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px;color:#64748b">
                <span>Class &amp; Section:</span>
                <strong style="color:#0f172a" id="leftModalClassSection">---</strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:12px;color:#64748b">
                <span>Subject:</span>
                <span class="badge badge-purple" id="leftModalSubjectName" style="font-weight:700">---</span>
            </div>
            <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:4px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;font-size:12px">
                    <span style="color:#64748b">Current Faculty:</span>
                    <strong id="leftModalCurrentFaculty" style="color:#ef4444">---</strong>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
                    <span style="color:#64748b">Replacement Faculty:</span>
                    <strong id="leftModalNewFaculty" style="color:#059669">---</strong>
                </div>
            </div>
        </div>

        <div style="margin-bottom:22px;padding:10px 14px;background:#fef3c7;border:1px solid #fde68a;border-radius:10px;color:#b45309;font-size:11px;line-height:1.4;font-weight:600">
            ⚠️ Confirming will update this subject allocation and automatically re-validate active timetable slot schedules.
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px">
            <button type="button" onclick="closeLeftFormConfirmModal()" class="btn btn-ghost" style="padding:9px 16px;font-size:12px">
                Cancel
            </button>
            <button type="button" onclick="submitLeftFormNow()" class="btn btn-primary" style="padding:9px 18px;font-size:12px;font-weight:800">
                Confirm &amp; Reassign
            </button>
        </div>
    </div>
</div>

<script>
    let currentViewMode = 'class';
    const assignmentsMap = @json($assignmentsMap ?? []);

    function switchAllocationView(mode) {
        currentViewMode = mode;
        const btnClass = document.getElementById('btnViewClass');
        const btnTeacher = document.getElementById('btnViewTeacher');
        const btnUnassigned = document.getElementById('btnViewUnassigned');
        
        const containerClass = document.getElementById('viewByClassContainer');
        const containerTeacher = document.getElementById('viewByTeacherContainer');
        const containerUnassigned = document.getElementById('viewByUnassignedContainer');

        [btnClass, btnTeacher, btnUnassigned].forEach(b => b && b.classList.remove('active'));
        [containerClass, containerTeacher, containerUnassigned].forEach(c => c && (c.style.display = 'none'));

        if (mode === 'class') {
            if (btnClass) btnClass.classList.add('active');
            if (containerClass) containerClass.style.display = 'block';
        } else if (mode === 'teacher') {
            if (btnTeacher) btnTeacher.classList.add('active');
            if (containerTeacher) containerTeacher.style.display = 'block';
        } else if (mode === 'unassigned') {
            if (btnUnassigned) btnUnassigned.classList.add('active');
            if (containerUnassigned) containerUnassigned.style.display = 'block';
        }

        filterAssignmentsTable();
    }

    function toggleAllocationCard(cardId, chevronId) {
        const card = document.getElementById(cardId);
        const chevron = document.getElementById(chevronId);
        if (card.classList.contains('open')) {
            card.classList.remove('open');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        } else {
            card.classList.add('open');
            if (chevron) chevron.style.transform = 'rotate(90deg)';
        }
    }

    function selectTeacherInForm(teacherId) {
        const teacherSelect = document.getElementById('teacher_id');
        if (teacherSelect) {
            teacherSelect.value = teacherId;
            teacherSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function onFormSelectionChange() {
        const sectionSelect = document.getElementById('class_section_id');
        filterSubjectsBySection(sectionSelect, 'subject_id');
        updateAssignmentStatusNotice();
    }

    function filterSubjectsBySection(sectionSelect, subjectSelectId, targetSubjectId = null) {
        const subjectSelect = document.getElementById(subjectSelectId);
        const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
        const classId = selectedOption ? selectedOption.getAttribute('data-class-id') : null;

        let count = 0;
        const options = subjectSelect.querySelectorAll('.subject-option');
        
        options.forEach(opt => {
            const optClassId = opt.getAttribute('data-class-id');
            if (classId && optClassId === classId) {
                opt.style.display = '';
                opt.disabled = false;
                count++;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        });

        const defaultOption = subjectSelect.options[0];
        if (!classId) {
            defaultOption.text = '-- Select Section First --';
            subjectSelect.value = '';
        } else if (count === 0) {
            defaultOption.text = '-- No Subjects Registered For This Class --';
            subjectSelect.value = '';
        } else {
            defaultOption.text = '-- Select Subject --';
            if (targetSubjectId) {
                subjectSelect.value = targetSubjectId;
            } else {
                subjectSelect.value = '';
            }
        }
    }

    function updateAssignmentStatusNotice() {
        const sectionId = document.getElementById('class_section_id').value;
        const subjectId = document.getElementById('subject_id').value;
        const noticeElem = document.getElementById('assignmentStatusNotice');

        if (!sectionId || !subjectId) {
            noticeElem.innerHTML = '';
            return;
        }

        const key = sectionId + '_' + subjectId;
        if (assignmentsMap[key]) {
            const teacherName = assignmentsMap[key].teacher_name;
            noticeElem.innerHTML = '<div style="padding:8px 12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;color:#0284c7;font-size:12px;font-weight:700">ℹ️ Already assigned to: <strong>' + teacherName + '</strong></div>';
        } else {
            noticeElem.innerHTML = '<div style="padding:8px 12px;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:12px;font-weight:700">🔴 Status: Unassigned (No teacher allocated yet)</div>';
        }
    }

    function confirmLeftAssignmentForm(event) {
        if (event) event.preventDefault();

        const teacherSelect = document.getElementById('teacher_id');
        const sectionSelect = document.getElementById('class_section_id');
        const subjectSelect = document.getElementById('subject_id');

        if (!teacherSelect.value || !sectionSelect.value || !subjectSelect.value) {
            return true;
        }

        const teacherName = teacherSelect.options[teacherSelect.selectedIndex]?.text || '';
        const sectionName = sectionSelect.options[sectionSelect.selectedIndex]?.text || '';
        const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';

        const sectionId = sectionSelect.value;
        const subjectId = subjectSelect.value;
        const key = sectionId + '_' + subjectId;

        const modalIcon = document.getElementById('leftFormModalIcon');
        const modalTitle = document.getElementById('leftFormModalTitle');
        const classSecElem = document.getElementById('leftModalClassSection');
        const subElem = document.getElementById('leftModalSubjectName');
        const curFacElem = document.getElementById('leftModalCurrentFaculty');
        const newFacElem = document.getElementById('leftModalNewFaculty');

        classSecElem.innerText = sectionName;
        subElem.innerText = subjectName;
        newFacElem.innerText = teacherName;

        if (assignmentsMap[key]) {
            const currentTeacher = assignmentsMap[key].teacher_name;
            modalIcon.innerHTML = '🔄';
            modalIcon.style.background = '#fef3c7';
            modalIcon.style.borderColor = '#fde68a';
            modalIcon.style.color = '#d97706';
            modalTitle.innerText = 'Confirm Teacher Replacement';
            curFacElem.innerHTML = currentTeacher;
            curFacElem.style.color = '#ef4444';
        } else {
            modalIcon.innerHTML = '📌';
            modalIcon.style.background = '#ecfdf5';
            modalIcon.style.borderColor = '#a7f3d0';
            modalIcon.style.color = '#059669';
            modalTitle.innerText = 'Confirm Subject Assignment';
            curFacElem.innerHTML = '<span style="color:#ef4444">⚠️ Unassigned</span>';
        }

        document.getElementById('leftFormConfirmModal').classList.add('active');
        return false;
    }

    function closeLeftFormConfirmModal() {
        document.getElementById('leftFormConfirmModal').classList.remove('active');
    }

    function submitLeftFormNow() {
        document.getElementById('leftAssignForm').submit();
    }

    // SEARCH FILTER logic
    function filterAssignmentsTable() {
        const query = document.getElementById('assignmentSearchInput').value.trim().toLowerCase();
        let activeContainer;
        if (currentViewMode === 'class') activeContainer = document.getElementById('viewByClassContainer');
        else if (currentViewMode === 'teacher') activeContainer = document.getElementById('viewByTeacherContainer');
        else if (currentViewMode === 'unassigned') activeContainer = document.getElementById('viewByUnassignedContainer');

        if (!activeContainer) return;

        const cards = activeContainer.querySelectorAll('.allocation-card');
        let totalVisibleCards = 0;

        cards.forEach(card => {
            const groupText = card.getAttribute('data-group-search') || '';
            const rows = card.querySelectorAll('.offering-row');
            let matchingRowsInCard = 0;

            rows.forEach(row => {
                const rowText = row.getAttribute('data-search-text') || '';
                if (query === '' || rowText.includes(query) || groupText.includes(query)) {
                    row.style.display = '';
                    matchingRowsInCard++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (query === '') {
                card.style.display = '';
                totalVisibleCards++;
            } else if (matchingRowsInCard > 0 || groupText.includes(query)) {
                card.style.display = '';
                card.classList.add('open');
                totalVisibleCards++;
            } else {
                card.style.display = 'none';
            }
        });

        const noResultsElem = document.getElementById('noSearchResults');
        if (noResultsElem) {
            noResultsElem.style.display = totalVisibleCards === 0 ? 'block' : 'none';
        }
    }

    // REPLACE / ASSIGN TEACHER MODAL HANDLERS
    function openReplaceModal(sectionId, sectionName, subjectId, subjectName, currentTeacherId = null, currentTeacherName = null) {
        document.getElementById('replace_section_id').value = sectionId;
        document.getElementById('replace_subject_id').value = subjectId;

        const titleElem = document.getElementById('replaceModalTitle');
        const classSubElem = document.getElementById('replaceModalClassSubject');
        const teacherElem = document.getElementById('replaceModalCurrentTeacher');
        const teacherSelect = document.getElementById('replace_teacher_id');

        classSubElem.innerHTML = subjectName + ' (' + sectionName + ')';

        if (currentTeacherId && currentTeacherName) {
            titleElem.innerText = '🔄 Replace Teacher for ' + subjectName;
            teacherElem.innerHTML = 'Current Faculty: <strong style="color:#0284c7">' + currentTeacherName + '</strong>';
            teacherSelect.value = currentTeacherId;
        } else {
            titleElem.innerText = '➕ Assign Teacher for ' + subjectName;
            teacherElem.innerHTML = 'Current Status: <strong style="color:#ef4444">⚠️ Unassigned</strong>';
            teacherSelect.value = '';
        }

        document.getElementById('replaceTeacherModal').classList.add('active');
    }

    function closeReplaceModal() {
        document.getElementById('replaceTeacherModal').classList.remove('active');
    }
</script>
@endsection

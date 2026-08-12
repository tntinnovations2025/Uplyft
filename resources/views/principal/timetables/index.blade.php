@extends('principal.layouts.app')
@section('title', 'Weekly Timetable Engine')
@section('breadcrumb', 'Timetable Matrix')

@section('content')
@php
    $todayName = strtolower(date('l'));
    $defaultDay = in_array($todayName, $days) ? $todayName : 'monday';
@endphp

<style>
    /* Modal Backdrop & Centered Pop-up */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        width: 100%;
        max-width: 540px;
        padding: 24px;
        box-shadow: 0 24px 48px rgba(0,0,0,0.6);
        max-height: 90vh;
        overflow-y: auto;
    }

    /* Per-Section Day Selector Pills */
    .sec-day-pill {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--surface2);
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sec-day-pill:hover {
        color: #fff;
        background: rgba(108,99,255,0.2);
    }
    .sec-day-pill.active {
        background: linear-gradient(135deg, #6c63ff, #00ced1);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(108,99,255,0.3);
    }

    /* CSS Grid Layout for Timetable Matrix */
    .horiz-matrix-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
    }
    .horiz-matrix-table th {
        background: var(--surface2);
        color: #fff;
        text-align: center;
        padding: 12px 10px;
        font-size: 12px;
        font-weight: 700;
        border-bottom: 1px solid var(--border);
        border-right: 1px solid var(--border);
    }
    .horiz-matrix-table td {
        padding: 8px;
        text-align: center;
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        border-right: 1px solid var(--border);
        min-height: 60px;
        font-size: 12px;
    }
    .horiz-matrix-table .section-col {
        background: var(--surface);
        font-weight: 700;
        color: #fff;
        font-size: 13px;
        width: 150px;
        text-align: left;
        padding-left: 12px;
    }
    .slot-card {
        background: linear-gradient(135deg, rgba(108,99,255,0.15), rgba(0,206,209,0.1));
        border: 1px solid rgba(108,99,255,0.3);
        border-radius: 6px;
        padding: 8px 6px;
        position: relative;
        text-align: left;
        min-height: 54px;
    }
    .slot-card .subject {
        font-weight: 700;
        font-size: 12px;
        color: #fff;
    }
    .slot-card .teacher {
        font-size: 11px;
        color: var(--accent2);
        margin-top: 2px;
    }
    .slot-card .room {
        font-size: 10px;
        font-weight: 600;
        color: #2ed573;
        margin-top: 2px;
    }
    .slot-card .delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(255,71,87,0.2);
        color: #ff4757;
        border: none;
        border-radius: 4px;
        width: 18px;
        height: 18px;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slot-card .delete-btn:hover { background: #ff4757; color: #fff; }

    /* Accordion Header */
    .grade-accordion-header {
        background: linear-gradient(135deg, rgba(108,99,255,0.15), rgba(0,206,209,0.05));
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 20px;
        margin-top: 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }
    .grade-accordion-header:hover {
        background: linear-gradient(135deg, rgba(108,99,255,0.25), rgba(0,206,209,0.1));
    }
    .grade-accordion-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 17px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Weekly Timetable Engine</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Automated Optimistic Generator adhering to Teacher Availabilities, Room Capacities, and zero-conflict constraints in <strong>{{ $activeTerm?->name }}</strong>.
        </p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <button type="button" onclick="openAddSlotModal()" class="btn btn-primary" style="background:linear-gradient(135deg, #6c63ff, #3b82f6)">
            ➕ Add Manual Slot
        </button>
        <a href="{{ route('principal.timetables.grid') }}" class="btn btn-ghost">
            📊 Master Tabular Grid
        </a>
        <a href="{{ route('principal.timetables.export') }}" download="UPLYFT_Master_Timetable.csv" class="btn btn-primary" style="background:linear-gradient(135deg, #2ed573, #1e90ff)">
            📥 Download CSV / Excel
        </a>
        <form method="POST" action="{{ route('principal.timetables.generate') }}" onsubmit="return confirm('⚡ Generate Optimistic Timetable?\n\nThis will auto-calculate slots based on teacher assignments, teacher availabilities, and room limits.')">
            @csrf
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #00ced1, #6c63ff)">
                ⚡ Generate Timetable
            </button>
        </form>
    </div>
</div>

<!-- CONTROL BAR: SEARCH SECTION & EXPAND/COLLAPSE BUTTONS -->
<div class="card" style="margin-bottom:24px;padding:16px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:450px">
            <span style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap">🔍 Search Section:</span>
            <input type="text" id="sectionSearchInput" onkeyup="filterSections()" placeholder="Type section e.g. 9A, 9-A, Grade 10..." style="padding:9px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:13px;width:100%;outline:none">
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <button type="button" onclick="expandAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📂 Expand All Grades
            </button>
            <button type="button" onclick="collapseAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📁 Collapse All Grades
            </button>
        </div>
    </div>
</div>

<!-- SECTION 3: GRADE ACCORDIONS WITH PER-SECTION CLICKABLE DAY SELECTORS -->
@if($groupedSections->isEmpty())
<div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:var(--text-muted);font-size:15px">No classes or sections configured yet.</p>
</div>
@else
    @foreach($groupedSections as $className => $classSections)
    @php $classSlug = Str::slug($className); @endphp
    <div class="grade-wrapper-block grade-group-{{ $classSlug }}" style="margin-bottom:20px">
        <div class="grade-accordion-header" onclick="toggleGradeAccordion('{{ $classSlug }}')">
            <div class="grade-accordion-title">
                🎓 {{ $className }}
                <span class="badge badge-purple" style="font-size:11px;padding:3px 10px">{{ $classSections->count() }} Section(s)</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <button type="button" onclick="event.stopPropagation(); openAddSlotModal()" class="btn btn-ghost btn-sm" style="font-size:11px">
                    ➕ Add Slot
                </button>
                <span class="grade-accordion-icon grade-icon-{{ $classSlug }}" style="font-size:14px;color:var(--text-muted)">▼</span>
            </div>
        </div>

        <div id="grade-body-{{ $classSlug }}" class="grade-body-container" style="display:none">
            <div class="card" style="padding:16px">
                <!-- PER-SECTION CLICKABLE DAY SELECTOR STRIP -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-size:12px;color:var(--text-muted);font-weight:600">Select Day for {{ $className }}:</span>
                        @foreach($days as $d)
                        <button type="button" 
                                onclick="selectSectionDay('{{ $classSlug }}', '{{ $d }}')" 
                                id="sec-day-pill-{{ $classSlug }}-{{ $d }}" 
                                class="sec-day-pill sec-day-pill-{{ $classSlug }} {{ $d === $defaultDay ? 'active' : '' }}">
                            📅 {{ ucfirst($d) }}
                        </button>
                        @endforeach
                    </div>
                    <span style="font-size:12px;color:var(--text-muted)">
                        Viewing: <strong id="sec-day-label-{{ $classSlug }}" style="color:var(--accent2);text-transform:capitalize">{{ $defaultDay }}</strong> schedule
                    </span>
                </div>

                <!-- TABLES FOR EACH DAY FOR THIS SECTION -->
                @foreach($days as $day)
                <div id="sec-matrix-{{ $classSlug }}-{{ $day }}" class="sec-matrix-{{ $classSlug }}" style="{{ $day === $defaultDay ? '' : 'display:none' }};overflow-x:auto">
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
                            @foreach($classSections as $section)
                            @php
                                $cName = $section->instituteClass->custom_name;
                                $sName = $section->section_name;
                                $cleanCombined = preg_replace('/[^a-zA-Z0-9]/', '', $cName . $sName);
                                $fullSearchText = strtolower($cName . ' ' . $sName . ' ' . $cleanCombined);
                            @endphp
                            <tr class="section-row" data-section-name="{{ $fullSearchText }}">
                                <td class="section-col">
                                    <div style="font-weight:700">{{ $section->instituteClass->custom_name }}</div>
                                    <div style="font-size:11px;color:var(--accent2)">Section {{ $section->section_name }}</div>
                                </td>
                                @foreach($timeSlots as $ts)
                                @php
                                    $slot = $gridData[$section->id][$day][$ts['key']] ?? null;
                                @endphp
                                <td>
                                    @if($slot)
                                    <div class="slot-card">
                                        <form method="POST" action="{{ route('principal.timetables.destroy', $slot) }}" onsubmit="return confirm('Remove this slot?')" style="margin:0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="delete-btn" title="Delete Slot">&times;</button>
                                        </form>
                                        <div class="subject">{{ $slot->subject->subject_name }}</div>
                                        <div class="teacher">👨‍🏫 {{ $slot->teacher->name }}</div>
                                        <div class="room">📍 Room: {{ $slot->room ? $slot->room->room_number : 'Unassigned' }}</div>
                                    </div>
                                    @else
                                    <span style="color:rgba(148,163,184,0.25);font-size:16px">—</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
@endif

<!-- CONVERSATIONAL TIMETABLE ADJUSTER -->
@include('principal.timetables._chat')

<script>
let globalDay = '{{ $defaultDay }}';
const expandedGrades = new Set();
const sectionDays = {};

function selectGlobalDay(day) {
    globalDay = day;
    document.querySelectorAll('.day-pill').forEach(el => el.classList.remove('active'));
    const targetPill = document.getElementById('day-pill-' + day);
    if (targetPill) targetPill.classList.add('active');

    // Update day for all section accordions simultaneously
    document.querySelectorAll('.sec-day-pill').forEach(btn => {
        if (btn.id.endsWith('-' + day)) {
            btn.click();
        }
    });
}

function selectSectionDay(classSlug, day) {
    sectionDays[classSlug] = day;

    // Update active pill for this section
    document.querySelectorAll('.sec-day-pill-' + classSlug).forEach(el => el.classList.remove('active'));
    const pill = document.getElementById('sec-day-pill-' + classSlug + '-' + day);
    if (pill) pill.classList.add('active');

    // Update day label text
    const label = document.getElementById('sec-day-label-' + classSlug);
    if (label) label.innerText = day;

    // Show matrix table for this section and day
    document.querySelectorAll('.sec-matrix-' + classSlug).forEach(el => el.style.display = 'none');
    const targetTable = document.getElementById('sec-matrix-' + classSlug + '-' + day);
    if (targetTable) targetTable.style.display = 'block';
}

function filterClass(slug) {
    document.querySelectorAll('.class-pill').forEach(el => el.classList.remove('active'));
    document.getElementById('class-pill-' + slug).classList.add('active');

    if (slug === 'all') {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'block');
    } else {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.grade-group-' + slug).forEach(el => el.style.display = 'block');
    }
}

function toggleGradeAccordion(classSlug) {
    if (expandedGrades.has(classSlug)) {
        expandedGrades.delete(classSlug);
    } else {
        expandedGrades.add(classSlug);
    }
    applyGradeAccordionStates();
}

function applyGradeAccordionStates() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');

        if (classSlug) {
            const isExpanded = expandedGrades.has(classSlug);
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');

            if (bodyContainer) {
                bodyContainer.style.display = isExpanded ? 'block' : 'none';
            }
            if (icon) {
                icon.innerText = isExpanded ? '▲' : '▼';
            }
        }
    });
}

function expandAllGrades() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');
        if (classSlug) expandedGrades.add(classSlug);
    });
    applyGradeAccordionStates();
}

function collapseAllGrades() {
    expandedGrades.clear();
    applyGradeAccordionStates();
}

function filterSections() {
    const rawQuery = document.getElementById('sectionSearchInput').value.trim().toLowerCase();
    const cleanQuery = rawQuery.replace(/[^a-z0-9]/g, '');

    if (!rawQuery) {
        document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
            wrapper.style.display = 'block';
        });
        document.querySelectorAll('.section-row').forEach(row => {
            row.style.display = '';
        });
        applyGradeAccordionStates();
        return;
    }

    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        let hasMatchInGrade = false;
        const rows = wrapper.querySelectorAll('.section-row');

        rows.forEach(row => {
            const searchText = (row.getAttribute('data-section-name') || '').toLowerCase();
            const cleanSearchText = searchText.replace(/[^a-z0-9]/g, '');

            const isMatch = searchText.includes(rawQuery) || 
                            (cleanQuery.length > 0 && cleanSearchText.includes(cleanQuery));

            if (isMatch) {
                row.style.display = '';
                hasMatchInGrade = true;
            } else {
                row.style.display = 'none';
            }
        });

        if (hasMatchInGrade) {
            wrapper.style.display = 'block';
            // Automatically expand matching class accordion to reveal the searched section instantly
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');
            if (bodyContainer) bodyContainer.style.display = 'block';
            if (icon) icon.innerText = '▲';
        } else {
            wrapper.style.display = 'none';
        }
    });
}
</script>

<!-- POP-UP MODAL FOR MANUAL SLOT ADDITION -->
<div id="addSlotModal" class="modal-backdrop" onclick="if(event.target===this) closeAddSlotModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:12px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">➕ Add Timetable Slot Manually</h3>
            <button type="button" onclick="closeAddSlotModal()" style="background:none;border:none;color:var(--text-muted);font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form method="POST" action="{{ route('principal.timetables.store') }}">
            @csrf
            <div class="form-group">
                <label for="modal_class_section_id">Class Section *</label>
                <select id="modal_class_section_id" name="class_section_id" required>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                        {{ $sec->instituteClass->custom_name }} — {{ $sec->section_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_subject_id">Subject *</label>
                <select id="modal_subject_id" name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->instituteClass->custom_name }})</option>
                    @endforeach
                </select>
                @error('subject_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_teacher_id">Teacher / Faculty *</label>
                <select id="modal_teacher_id" name="teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->identifier ?? $teacher->email }})</option>
                    @endforeach
                </select>
                @error('teacher_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_day_of_week">Day of Week *</label>
                <select id="modal_day_of_week" name="day_of_week" required>
                    @foreach($days as $d)
                    <option value="{{ $d }}" {{ old('day_of_week', $defaultDay) == $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_room_id">Assign Room (Optional)</label>
                <select id="modal_room_id" name="room_id">
                    <option value="">-- No Room --</option>
                    @foreach($rooms as $rm)
                    <option value="{{ $rm->id }}" {{ old('room_id') == $rm->id ? 'selected' : '' }}>📍 {{ $rm->room_number }} ({{ $rm->capacity }} seats)</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="modal_start_time">Start Time *</label>
                <input id="modal_start_time" type="time" name="start_time" required value="{{ old('start_time', '08:00') }}">
                @error('start_time')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="modal_end_time">End Time *</label>
                <input id="modal_end_time" type="time" name="end_time" required value="{{ old('end_time', '09:00') }}">
                @error('end_time')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                <button type="button" onclick="closeAddSlotModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Slot</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'flex';
}
function closeAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'none';
}
</script>
@endsection

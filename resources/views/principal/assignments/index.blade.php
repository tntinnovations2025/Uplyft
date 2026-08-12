@extends('principal.layouts.app')
@section('title', 'Assign Subject & Teacher')
@section('breadcrumb', 'Subject & Teacher Assignments')

@section('content')
<style>
    .assignments-table {
        width: 100%;
        border-collapse: collapse;
    }
    .assignments-table th {
        background: var(--surface2);
        color: #fff;
        padding: 12px 14px;
        font-size: 13px;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .assignments-table td {
        padding: 14px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        vertical-align: middle;
    }
    .assignments-table tr:hover {
        background: rgba(255,255,255,0.02);
    }

    /* Modal Backdrop & Box */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        z-index: 999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📌 Subject & Teacher Assignments</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Map subject faculty to specific grade class sections. The master timetable auto-regenerates instantly upon assignment changes.
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
    <!-- Left Column: Create Assignment Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">➕ Assign Teacher to Subject</div>
            </div>

            <form method="POST" action="{{ route('principal.assignments.store') }}">
                @csrf

                <div class="form-group">
                    <label for="teacher_id">Select Teacher *</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">-- Select Teacher --</option>
                        @foreach($staffMembers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->staff_role ?? ucfirst($t->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="class_section_id">Select Section *</label>
                    <select id="class_section_id" name="class_section_id" required>
                        <option value="">-- Select Section --</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->instituteClass->custom_name }} — {{ $sec->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject_id">Select Subject *</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->instituteClass->custom_name }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Assign Teacher</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Well-Organized Assignments Table -->
    <div>
        <div class="card">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
                <div class="card-title">📖 Active Session Allocations</div>
                <span class="badge badge-purple">{{ $assignments->count() }} Total Allocation(s)</span>
            </div>

            @if($assignments->count() > 0)
            <div style="overflow-x:auto">
                <table class="assignments-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Class &amp; Section</th>
                            <th>Assigned Subject</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assign)
                        <tr>
                            <td>
                                <strong style="color:#fff">{{ $assign->teacher->name }}</strong>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $assign->teacher->staff_role ?? ucfirst($assign->teacher->role) }}</div>
                            </td>
                            <td>
                                <span style="font-weight:600">{{ $assign->section->instituteClass->custom_name }}</span> — 
                                <span style="color:var(--accent2)">Section {{ $assign->section->section_name }}</span>
                            </td>
                            <td>
                                <span class="badge badge-purple">{{ $assign->subject->subject_name }}</span>
                            </td>
                            <td style="text-align:right">
                                <div style="display:inline-flex;gap:8px">
                                    <button type="button" 
                                            class="btn btn-ghost btn-sm" 
                                            onclick="openEditModal({{ $assign->id }}, {{ $assign->teacher_id }}, {{ $assign->class_section_id }}, {{ $assign->subject_id }})">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" action="{{ route('principal.assignments.destroy', $assign) }}" onsubmit="return confirm('Remove this subject assignment?')" style="margin:0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="text-align:center;padding:48px 20px">
                <p style="color:var(--text-muted);font-size:15px;margin-bottom:12px">No subject allocations created yet for the active session.</p>
                <span style="font-size:12px;color:var(--text-muted)">Use the form on the left to assign teachers to class subjects.</span>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- EDIT ASSIGNMENT MODAL INTERFACE -->
<div id="editAssignmentModal" class="modal-backdrop" onclick="if(event.target===this) closeEditModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:12px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">✏️ Edit Subject Assignment</h3>
            <button type="button" onclick="closeEditModal()" style="background:none;border:none;color:var(--text-muted);font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="editAssignmentForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="edit_teacher_id">Select Teacher *</label>
                <select id="edit_teacher_id" name="teacher_id" required>
                    @foreach($staffMembers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->staff_role ?? ucfirst($t->role) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="edit_class_section_id">Select Section *</label>
                <select id="edit_class_section_id" name="class_section_id" required>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}">{{ $sec->instituteClass->custom_name }} — {{ $sec->section_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="edit_subject_id">Select Subject *</label>
                <select id="edit_subject_id" name="subject_id" required>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->subject_name }} ({{ $sub->instituteClass->custom_name }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px">
                <button type="button" onclick="closeEditModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes &amp; Re-generate</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, teacherId, sectionId, subjectId) {
    const form = document.getElementById('editAssignmentForm');
    form.action = "{{ url('/principal/assignments') }}/" + id;

    document.getElementById('edit_teacher_id').value = teacherId;
    document.getElementById('edit_class_section_id').value = sectionId;
    document.getElementById('edit_subject_id').value = subjectId;

    document.getElementById('editAssignmentModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editAssignmentModal').style.display = 'none';
}
</script>
@endsection

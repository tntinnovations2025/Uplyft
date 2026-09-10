@extends('lms.layouts.app')

@section('title', 'Manual Paper Test Marksheet Entry')
@section('breadcrumb', 'Paper Marksheet Entry')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📝 Manual Paper Test Marksheet Entry</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Enter marks for physical paper-based tests. All enrolled students are listed by default for instant grading.
        </p>
    </div>
    <a href="{{ route('lms.test-results.index') }}" class="btn btn-ghost">← Back to Test Directory</a>
</div>

<form method="POST" action="{{ route('lms.assessments.paperMarksheet.store') }}">
    @csrf

    {{-- Test Configuration Card --}}
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent)">
        <h3 class="card-title" style="margin-bottom:16px;font-size:16px;font-weight:700">⚙️ Paper Test Details & Weightage</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px">
            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Class Section *</label>
                <select name="class_section_id" id="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" onchange="window.location.href='{{ route('lms.assessments.paperMarksheet.form') }}?class_section_id=' + this.value" required>
                    @foreach($classSections as $cs)
                        <option value="{{ $cs->id }}" @selected($selectedSectionId == $cs->id)>
                            {{ $cs->instituteClass?->custom_name ?? 'Class' }} - {{ $cs->section_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Subject *</label>
                <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->subject_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Academic Term *</label>
                <select name="academic_term_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required>
                    @foreach($academicTerms as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Test Title *</label>
                <input type="text" name="title" placeholder="e.g. Test 1, Monthly Exam..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Total Paper Marks *</label>
                <input type="number" name="total_marks" id="total_marks_input" value="100" min="1" step="1" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required oninput="updateCalculatedWeightage()">
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Test Weightage % *</label>
                <input type="number" name="weightage_percentage" id="weightage_input" value="10" min="0.1" max="100" step="0.5" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required oninput="updateCalculatedWeightage()">
                <div style="font-size:11px;color:var(--accent2);margin-top:4px" id="weightage_hint">
                    💡 Marks out of 100 will contribute <b>10%</b> to the annual score.
                </div>
            </div>
        </div>
    </div>

    {{-- Student Roster & Marks Input --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
                <h3 class="card-title">👥 Enrolled Class Roster & Marks Entry</h3>
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0">
                    All students in this section are listed below. Enter obtained marks for each student.
                </p>
            </div>
            <span class="badge badge-purple" style="font-size:12px">{{ $students->count() }} Student(s) Enrolled</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Student Name</th>
                    <th>Email / ID</th>
                    <th>Obtained Marks (out of <span class="max-marks-display">100</span>)</th>
                    <th>Calculated Weightage Contribution</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $student)
                    <tr>
                        <td style="font-weight:700;color:var(--text-muted)">{{ $idx + 1 }}</td>
                        <td style="font-weight:600;color:var(--text)">{{ $student->name }}</td>
                        <td class="muted" style="font-size:12px">{{ $student->email }}</td>
                        <td style="width:200px">
                            <input type="number" name="student_marks[{{ $student->id }}]" class="student-mark-input" data-student-id="{{ $student->id }}" placeholder="e.g. 85" min="0" max="100" step="0.5" style="width:100%;padding:8px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700" oninput="calculateSingleStudentContribution(this)">
                        </td>
                        <td style="font-weight:700;color:var(--accent)">
                            <span id="weighted_score_{{ $student->id }}">0.0</span> / <span class="weightage-display">10</span> pts
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">
                            No students found in this class section. Please select a section with enrolled students.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($students->isNotEmpty())
            <div style="display:flex;justify-content:flex-end;margin-top:24px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary btn-lg" style="padding:12px 28px;font-weight:700">
                    💾 Save Paper Test Marksheet
                </button>
            </div>
        @endif
    </div>
</form>

<script>
    function updateCalculatedWeightage() {
        const totalMarks = parseFloat(document.getElementById('total_marks_input').value) || 100;
        const weightage = parseFloat(document.getElementById('weightage_input').value) || 10;

        document.querySelectorAll('.max-marks-display').forEach(el => el.textContent = totalMarks);
        document.querySelectorAll('.weightage-display').forEach(el => el.textContent = weightage);
        
        document.getElementById('weightage_hint').innerHTML = `💡 Marks out of <b>${totalMarks}</b> will contribute <b>${weightage}%</b> to annual score.`;

        // Update all student inputs max attribute
        document.querySelectorAll('.student-mark-input').forEach(input => {
            input.max = totalMarks;
            calculateSingleStudentContribution(input);
        });
    }

    function calculateSingleStudentContribution(input) {
        const studentId = input.dataset.studentId;
        const obtained = parseFloat(input.value) || 0;
        const totalMarks = parseFloat(document.getElementById('total_marks_input').value) || 100;
        const weightage = parseFloat(document.getElementById('weightage_input').value) || 10;

        const weightedScore = totalMarks > 0 ? ((obtained / totalMarks) * weightage).toFixed(2) : '0.0';
        const displayEl = document.getElementById('weighted_score_' + studentId);
        if (displayEl) {
            displayEl.textContent = weightedScore;
        }
    }
</script>
@endsection

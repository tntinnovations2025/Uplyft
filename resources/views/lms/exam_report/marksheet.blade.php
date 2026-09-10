@extends('lms.layouts.app')

@section('title', 'Official Exam Marksheet — ' . $assessment->title)
@section('breadcrumb', 'Official Exam Marksheet')

@section('content')
@php
    $resultDeadline = $assessment->result_deadline ? $assessment->result_deadline->format('M d, Y') : null;
    $isPastDeadline = $assessment->result_deadline ? $assessment->result_deadline->isPast() : false;
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <div style="font-size:12px;text-transform:uppercase;color:var(--text-muted);font-weight:700;letter-spacing:1px">Official Examination Marksheet</div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700;margin-top:2px">
            📜 {{ $assessment->title }}
        </h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Subject: <b>{{ $assessment->subject->subject_name ?? '—' }}</b> ·
            Class Section: <b>{{ $assessment->classSection->instituteClass?->custom_name ?? 'Class' }} - {{ $assessment->classSection->section_name ?? '—' }}</b> ·
            Exam Date: <b>📅 {{ $assessment->start_time ? $assessment->start_time->format('M d, Y') : $assessment->created_at->format('M d, Y') }}</b>
        </p>
    </div>

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <a href="{{ route('lms.exam-report.index') }}" class="btn btn-ghost">← Back to Exams Directory</a>
        @if(auth()->user()->isAdministration())
            <span class="badge badge-purple" style="font-size:13px;padding:8px 16px">👑 Principal / Admin Override Authority Enabled</span>
        @endif
    </div>
</div>

{{-- Summary Header Cards --}}
<div class="grid-4" style="margin-bottom:24px;display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px">
    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Enrolled Students</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700;margin-top:4px">
            {{ count($marksheet) }} <span style="font-size:14px;color:var(--text-muted)">Students</span>
        </div>
        <div style="font-size:12px;color:var(--success);margin-top:4px">Class Roster List</div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Exam Date</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;margin-top:4px;color:var(--accent2)">
            🗓️ {{ $assessment->start_time ? $assessment->start_time->format('M d, Y') : 'Set by Admin' }}
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Scheduled Conduct Date</div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Result Submission Deadline</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;margin-top:4px;color:{{ $isPastDeadline ? 'var(--danger)' : 'var(--warning)' }}">
            ⏳ {{ $resultDeadline ?: 'No Deadline Set' }}
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
            {{ $isPastDeadline ? '⚠️ Deadline Passed' : 'Target Result Entry Deadline' }}
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Total Exam Marks</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700;margin-top:4px;color:var(--accent)">
            <span id="header-total-marks">{{ $assessment->total_marks }}</span> <span style="font-size:14px;color:var(--text-muted)">Points</span>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Sum of Question Weightages</div>
    </div>
</div>

<form method="POST" action="{{ route('lms.exam-report.storeMarks', $assessment->id) }}">
    @csrf

    {{-- Question Breakdown Configurator --}}
    @if($canEdit)
        <div class="card" style="margin-bottom:24px;border:1px solid var(--accent);background:var(--surface)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <div>
                    <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;color:var(--accent)">
                        ⚙️ Configure Question &amp; Marks Structure (Q1, Q2, Q3...)
                    </h3>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">
                        Define questions (e.g. Q1, Q2, Q3) and set max marks for each. The sum automatically becomes the Total Exam Marks.
                    </p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="addQuestionRow()" style="border:1px solid var(--accent);color:var(--accent)">
                    ➕ Add Question Breakdown
                </button>
            </div>

            <div id="questions-container" style="display:flex;flex-direction:column;gap:10px">
                @foreach($questions as $index => $q)
                    <div class="question-row" style="display:flex;align-items:center;gap:12px;padding:10px;background:var(--surface2);border-radius:8px;border:1px solid var(--border)">
                        <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $q->id }}">
                        <span style="font-weight:700;font-size:13px;width:30px">#{{ $index + 1 }}</span>
                        <div style="flex:2">
                            <input type="text" name="questions[{{ $index }}][statement]" value="{{ $q->statement ?: ('Q' . ($index + 1)) }}" placeholder="Question Name / Label (e.g. Q1, Q2, Short Qs)" required style="width:100%;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:600;font-size:13px">
                        </div>
                        <div style="flex:1;display:flex;align-items:center;gap:8px">
                            <span style="font-size:12px;color:var(--text-muted)">Max Marks:</span>
                            <input type="number" name="questions[{{ $index }}][marks]" value="{{ $q->marks }}" min="1" max="500" required onchange="recalculateTotalQuestionMarks()" class="q-max-marks-input" style="width:90px;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                        </div>
                        @if($questions->count() > 1)
                            <button type="button" class="btn btn-ghost btn-sm" onclick="removeQuestionRow(this)" style="color:var(--danger)">🗑️</button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Student Marks Entry Table --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
                <h3 class="card-title">📊 Student Score Matrix</h3>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Enter achieved marks per question for each student. Total and Grade will calculate automatically.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <input type="text" id="student-search-input" placeholder="🔍 Search student..." style="padding:8px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;width:220px;outline:none" onkeyup="filterStudentTable()">
                @if($canEdit)
                    <button type="submit" class="btn btn-primary">💾 Save &amp; Publish Exam Results</button>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto;margin-top:12px">
            <table id="marks-table">
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        @foreach($questions as $q)
                            <th>{{ $q->statement ?: ('Q' . $q->sort_order) }} <br><span class="muted" style="font-size:11px">({{ $q->marks }} pts)</span></th>
                        @endforeach
                        <th>Total Obtained / <span id="table-total-max">{{ $assessment->total_marks }}</span></th>
                        <th>Percentage</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marksheet as $row)
                        @php
                            $badgeClass = match($row['grade']) {
                                'A+', 'A' => 'badge-green',
                                'B', 'C' => 'badge-purple',
                                'D' => 'badge-yellow',
                                default => 'badge-red',
                            };
                        @endphp
                        <tr class="student-row" data-student-id="{{ $row['student_id'] }}">
                            <td style="font-weight:700;color:var(--text-muted)">{{ $row['roll_number'] }}</td>
                            <td>
                                <div style="font-weight:600;color:var(--text)">{{ $row['student_name'] }}</div>
                                <div class="muted" style="font-size:12px">{{ $row['email'] }}</div>
                            </td>
                            <td>
                                @if($row['has_attempted'])
                                    <span class="badge badge-green">Marks Inserted</span>
                                @else
                                    <span class="badge badge-yellow">Pending Entry</span>
                                @endif
                            </td>

                            @foreach($questions as $q)
                                @php $qScore = $row['question_marks'][$q->id] ?? null; @endphp
                                <td>
                                    @if($canEdit)
                                        <input type="number" step="0.5" min="0" max="{{ $q->marks }}"
                                               name="student_question_marks[{{ $row['student_id'] }}][{{ $q->id }}]"
                                               value="{{ $qScore !== null ? $qScore : '' }}"
                                               placeholder="0"
                                               class="q-score-input"
                                               data-q-max="{{ $q->marks }}"
                                               oninput="calculateStudentRow(this.closest('tr'))"
                                               style="width:75px;padding:6px 8px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:14px">
                                    @else
                                        <span style="font-weight:700">{{ $qScore !== null ? $qScore : '—' }}</span>
                                    @endif
                                </td>
                            @endforeach

                            <td>
                                <span class="row-total-obtained" style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:15px;color:var(--accent)">
                                    {{ $row['total_obtained'] !== null ? $row['total_obtained'] : '—' }}
                                </span>
                                <span style="font-size:12px;color:var(--text-muted)">/ <span class="row-max-total">{{ $assessment->total_marks }}</span> pts</span>
                            </td>
                            <td>
                                <span class="row-percentage-badge badge {{ $badgeClass }}">{{ $row['percentage'] }}%</span>
                            </td>
                            <td class="row-grade-cell" style="font-weight:700">{{ $row['grade'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($questions) }}" style="text-align:center;padding:40px;color:var(--text-muted)">
                                No student records available for this class section.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($canEdit)
            <div style="display:flex;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save &amp; Submit Official Exam Marksheet</button>
            </div>
        @endif
    </div>
</form>

<script>
    let qCount = {{ count($questions) }};

    function addQuestionRow() {
        qCount++;
        const container = document.getElementById('questions-container');
        const div = document.createElement('div');
        div.className = 'question-row';
        div.style = 'display:flex;align-items:center;gap:12px;padding:10px;background:var(--surface2);border-radius:8px;border:1px solid var(--border)';
        div.innerHTML = `
            <span style="font-weight:700;font-size:13px;width:30px">#${qCount}</span>
            <div style="flex:2">
                <input type="text" name="questions[${qCount-1}][statement]" value="Q${qCount}" placeholder="Question Name (e.g. Q${qCount})" required style="width:100%;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:600;font-size:13px">
            </div>
            <div style="flex:1;display:flex;align-items:center;gap:8px">
                <span style="font-size:12px;color:var(--text-muted)">Max Marks:</span>
                <input type="number" name="questions[${qCount-1}][marks]" value="10" min="1" max="500" required onchange="recalculateTotalQuestionMarks()" class="q-max-marks-input" style="width:90px;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="removeQuestionRow(this)" style="color:var(--danger)">🗑️</button>
        `;
        container.appendChild(div);
        recalculateTotalQuestionMarks();
    }

    function removeQuestionRow(btn) {
        btn.closest('.question-row').remove();
        recalculateTotalQuestionMarks();
    }

    function recalculateTotalQuestionMarks() {
        let total = 0;
        document.querySelectorAll('.q-max-marks-input').forEach(inp => {
            total += (parseFloat(inp.value) || 0);
        });
        document.getElementById('header-total-marks').textContent = total;
        document.getElementById('table-total-max').textContent = total;
        document.querySelectorAll('.row-max-total').forEach(el => el.textContent = total);
        
        document.querySelectorAll('.student-row').forEach(row => calculateStudentRow(row));
    }

    function calculateStudentRow(tr) {
        let sum = 0;
        let hasAny = false;
        const inputs = tr.querySelectorAll('.q-score-input');
        inputs.forEach(inp => {
            if (inp.value !== '') {
                hasAny = true;
                sum += (parseFloat(inp.value) || 0);
            }
        });

        const totalMax = parseFloat(document.getElementById('header-total-marks').textContent) || 100;
        const totalSpan = tr.querySelector('.row-total-obtained');
        const badgeSpan = tr.querySelector('.row-percentage-badge');
        const gradeCell = tr.querySelector('.row-grade-cell');

        if (!hasAny) {
            totalSpan.textContent = '—';
            badgeSpan.textContent = '0%';
            badgeSpan.className = 'row-percentage-badge badge badge-yellow';
            gradeCell.textContent = 'Pending';
            return;
        }

        totalSpan.textContent = sum;
        const pct = totalMax > 0 ? ((sum / totalMax) * 100).toFixed(1) : 0;
        badgeSpan.textContent = `${pct}%`;

        let grade = 'F';
        let bClass = 'badge-red';
        if (pct >= 90) { grade = 'A+'; bClass = 'badge-green'; }
        else if (pct >= 80) { grade = 'A'; bClass = 'badge-green'; }
        else if (pct >= 70) { grade = 'B'; bClass = 'badge-purple'; }
        else if (pct >= 60) { grade = 'C'; bClass = 'badge-purple'; }
        else if (pct >= 50) { grade = 'D'; bClass = 'badge-yellow'; }

        badgeSpan.className = `row-percentage-badge badge ${bClass}`;
        gradeCell.textContent = grade;
    }

    function filterStudentTable() {
        const input = document.getElementById('student-search-input');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('tbody tr.student-row');
        
        rows.forEach(r => {
            const text = r.textContent.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection

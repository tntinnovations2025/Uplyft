

<?php $__env->startSection('title', 'Test Marksheet — ' . $assessment->title); ?>
<?php $__env->startSection('breadcrumb', 'Test Marksheet'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <div style="font-size:12px;text-transform:uppercase;color:var(--text-muted);font-weight:700;letter-spacing:1px">Official Class Marksheet</div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700;margin-top:2px">
            📝 <?php echo e($assessment->title); ?>

        </h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Subject: <b><?php echo e($assessment->subject->subject_name ?? '—'); ?></b> ·
            Class Section: <b><?php echo e($assessment->classSection->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($assessment->classSection->section_name ?? '—'); ?></b> ·
            Test Date: <b>📅 <?php echo e($assessment->start_time ? $assessment->start_time->format('M d, Y') : $assessment->created_at->format('M d, Y')); ?></b> ·
            Created by: <b><?php echo e($assessment->creator->name ?? 'Teacher'); ?></b>
        </p>
    </div>

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <a href="<?php echo e(route('lms.test-results.index')); ?>" class="btn btn-ghost">← Back to Test Directory</a>
        <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
            <span class="badge badge-green" style="font-size:14px;padding:8px 16px">💾 Marksheet Saved in Directory</span>
        <?php endif; ?>
    </div>
</div>


<div class="grid-3" style="margin-bottom:24px">
    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Total Class Strength</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-top:4px">
            <?php echo e(count($marksheet)); ?> <span style="font-size:14px;color:var(--text-muted)">Students</span>
        </div>
        <div style="font-size:12px;color:var(--success);margin-top:4px">
            ⚡ <?php echo e($attemptedStudentsCount); ?> Attempted / Evaluated
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Test Total Marks</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-top:4px;color:var(--accent)">
            <?php echo e($assessment->total_marks); ?> <span style="font-size:14px;color:var(--text-muted)">Points</span>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
            Test Name: <b><?php echo e($assessment->title); ?></b>
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="muted" style="font-size:12px;text-transform:uppercase;font-weight:700">Class Average Score</div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-top:4px;color:var(--accent2)">
            <?php echo e($classAverage); ?> <span style="font-size:14px;color:var(--text-muted)">/ <?php echo e($assessment->total_marks); ?></span>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
            Mean performance across section
        </div>
    </div>
</div>

<div class="card">
    <form method="POST" action="<?php echo e(route('lms.test-results.updateStudentMarks', $assessment->id)); ?>">
        <?php echo csrf_field(); ?>
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
                <h3 class="card-title">📊 Student Marks Entry & Evaluation</h3>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Enter or update student marks directly below for <b><?php echo e($assessment->title); ?></b> (Date: <?php echo e($assessment->start_time ? $assessment->start_time->format('M d, Y') : $assessment->created_at->format('M d, Y')); ?>).
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <input type="text" id="student-search-input" placeholder="🔍 Search student..." style="padding:8px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;width:220px;outline:none" onkeyup="filterStudentTable()">
                <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
                    <button type="submit" class="btn btn-primary">💾 Save Student Marks</button>
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Roll No / ID</th>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>Recorded Marks / Max Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th style="text-align:right">Detailed Evaluation</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $marksheet; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $badgeClass = match($row['grade']) {
                            'A+', 'A' => 'badge-green',
                            'B', 'C' => 'badge-purple',
                            'D' => 'badge-yellow',
                            default => 'badge-red',
                        };
                    ?>
                    <tr>
                        <td style="font-weight:700;color:var(--text-muted)"><?php echo e($row['roll_number']); ?></td>
                        <td>
                            <div style="font-weight:600;color:var(--text)"><?php echo e($row['student_name']); ?></div>
                            <div class="muted" style="font-size:12px"><?php echo e($row['email']); ?></div>
                        </td>
                        <td>
                            <?php if($row['has_attempted']): ?>
                                <span class="badge badge-green">Evaluated</span>
                            <?php else: ?>
                                <span class="badge badge-yellow">Pending Marks</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <input type="number" step="0.5" min="0" max="<?php echo e($assessment->total_marks); ?>"
                                           name="student_marks[<?php echo e($row['student_id']); ?>]"
                                           value="<?php echo e($row['has_attempted'] ? $row['total_obtained'] : ''); ?>"
                                           placeholder="0.0"
                                           style="width:90px;padding:6px 10px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:14px">
                                    <span style="font-size:13px;color:var(--text-muted)">/ <?php echo e($assessment->total_marks); ?> pts</span>
                                </div>
                            <?php else: ?>
                                <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px">
                                    <?php echo e($row['total_obtained']); ?> <span style="font-size:12px;color:var(--text-muted)">/ <?php echo e($row['max_marks']); ?></span>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($row['percentage']); ?>%</span>
                        </td>
                        <td style="font-weight:700"><?php echo e($row['grade']); ?></td>
                        <td style="text-align:right">
                            <?php if($row['has_attempted']): ?>
                                <button type="button" class="btn btn-ghost btn-sm"
                                        data-student-name="<?php echo e($row['student_name']); ?>"
                                        data-answers-detail="<?php echo e(e(json_encode($row['answers_detail'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT))); ?>"
                                        onclick="openStudentDetailModal(this)">
                                    🔍 View Details
                                </button>
                            <?php else: ?>
                                <span class="muted" style="font-size:12px">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            No student records available for this class section.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
            <div style="display:flex;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save Marksheet &amp; All Marks</button>
            </div>
        <?php endif; ?>
    </form>
</div>


<div class="modal-backdrop" id="student-detail-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:720px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5);max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">
                Student Evaluation Details — <span id="modal-student-name" style="color:var(--accent)"></span>
            </h3>
            <button onclick="closeModal('student-detail-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>

        <div id="modal-answers-content"></div>

        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-ghost" onclick="closeModal('student-detail-modal')">Close</button>
        </div>
    </div>
</div>

<script>
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function esc(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function openStudentDetailModal(button) {
        let answersDetail = [];
        let studentName = '';

        try {
            studentName = button.dataset.studentName || '';
            answersDetail = JSON.parse(button.dataset.answersDetail || '[]');
        } catch (e) {
            answersDetail = [];
        }

        document.getElementById('modal-student-name').textContent = studentName;
        const container = document.getElementById('modal-answers-content');
        container.innerHTML = '';

        if (!answersDetail || answersDetail.length === 0) {
            container.innerHTML = '<div class="muted">No detailed answers recorded for this student.</div>';
        } else {
            answersDetail.forEach((ans, idx) => {
                const item = document.createElement('div');
                item.style.cssText = 'background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px;font-size:13px';

                const obtained = ans.marks_awarded !== null && ans.marks_awarded !== undefined ? ans.marks_awarded : 0;
                const max = ans.max_marks;
                const provided = ans.provided_answer ? esc(ans.provided_answer) : '<i>(No Answer)</i>';
                const correct = ans.correct_answer ? esc(ans.correct_answer) : 'N/A';
                const feedback = ans.feedback ? esc(ans.feedback).replace(/\n/g, '<br>') : '';

                item.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px">
                        <b>Q${idx + 1}: ${esc(ans.statement)}</b>
                        <span class="badge ${obtained == max ? 'badge-green' : (obtained > 0 ? 'badge-yellow' : 'badge-red')}">
                            ${obtained} / ${max} pts
                        </span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px">
                        <div style="background:var(--surface);border:1px solid var(--border);padding:10px;border-radius:6px">
                            <div style="font-size:10px;text-transform:uppercase;color:var(--text-muted);font-weight:700;margin-bottom:2px">Student's Response</div>
                            <div>${provided}</div>
                        </div>
                        <div style="background:var(--surface);border:1px solid var(--border);padding:10px;border-radius:6px">
                            <div style="font-size:10px;text-transform:uppercase;color:var(--accent2);font-weight:700;margin-bottom:2px">Model Answer</div>
                            <div>${correct}</div>
                        </div>
                    </div>
                    ${feedback ? `<div style="margin-top:8px;padding:8px 12px;background:rgba(108,99,255,0.1);border-left:3px solid var(--accent);border-radius:4px"><b>Rubric Feedback:</b> ${feedback}</div>` : ''}
                `;
                container.appendChild(item);
            });
        }
        document.getElementById('student-detail-modal').style.display = 'flex';
    }

    function filterStudentTable() {
        const input = document.getElementById('student-search-input');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(r => {
            const text = r.textContent.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\test_results\marksheet.blade.php ENDPATH**/ ?>
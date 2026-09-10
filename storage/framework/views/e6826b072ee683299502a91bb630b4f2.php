

<?php $__env->startSection('title', 'Manual Paper Test Marksheet Entry'); ?>
<?php $__env->startSection('breadcrumb', 'Paper Marksheet Entry'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📝 Manual Paper Test Marksheet Entry</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Enter marks for physical paper-based tests. All enrolled students are listed by default for instant grading.
        </p>
    </div>
    <a href="<?php echo e(route('lms.test-results.index')); ?>" class="btn btn-ghost">← Back to Test Directory</a>
</div>

<form method="POST" action="<?php echo e(route('lms.assessments.paperMarksheet.store')); ?>">
    <?php echo csrf_field(); ?>

    
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent)">
        <h3 class="card-title" style="margin-bottom:16px;font-size:16px;font-weight:700">⚙️ Paper Test Details & Weightage</h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px">
            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Class Section *</label>
                <select name="class_section_id" id="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" onchange="window.location.href='<?php echo e(route('lms.assessments.paperMarksheet.form')); ?>?class_section_id=' + this.value" required>
                    <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cs->id); ?>" <?php if($selectedSectionId == $cs->id): echo 'selected'; endif; ?>>
                            <?php echo e($cs->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($cs->section_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Subject *</label>
                <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"><?php echo e($s->subject_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group" style="margin:0">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">Academic Term *</label>
                <select name="academic_term_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)" required>
                    <?php $__currentLoopData = $academicTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
                <h3 class="card-title">👥 Enrolled Class Roster & Marks Entry</h3>
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0">
                    All students in this section are listed below. Enter obtained marks for each student.
                </p>
            </div>
            <span class="badge badge-purple" style="font-size:12px"><?php echo e($students->count()); ?> Student(s) Enrolled</span>
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
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-weight:700;color:var(--text-muted)"><?php echo e($idx + 1); ?></td>
                        <td style="font-weight:600;color:var(--text)"><?php echo e($student->name); ?></td>
                        <td class="muted" style="font-size:12px"><?php echo e($student->email); ?></td>
                        <td style="width:200px">
                            <input type="number" name="student_marks[<?php echo e($student->id); ?>]" class="student-mark-input" data-student-id="<?php echo e($student->id); ?>" placeholder="e.g. 85" min="0" max="100" step="0.5" style="width:100%;padding:8px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700" oninput="calculateSingleStudentContribution(this)">
                        </td>
                        <td style="font-weight:700;color:var(--accent)">
                            <span id="weighted_score_<?php echo e($student->id); ?>">0.0</span> / <span class="weightage-display">10</span> pts
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">
                            No students found in this class section. Please select a section with enrolled students.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if($students->isNotEmpty()): ?>
            <div style="display:flex;justify-content:flex-end;margin-top:24px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary btn-lg" style="padding:12px 28px;font-weight:700">
                    💾 Save Paper Test Marksheet
                </button>
            </div>
        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\assessments\paper_marksheet.blade.php ENDPATH**/ ?>
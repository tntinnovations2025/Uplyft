

<?php $__env->startSection('title', 'Grade Reports'); ?>
<?php $__env->startSection('breadcrumb', 'Grade Reports'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $instituteId = auth()->user()->institute_id;
    $subjects  = \App\Models\Subject::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))->orderBy('subject_name')->get();
    $sections  = \App\Models\ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))->with('instituteClass')->orderBy('section_name')->get();
    $terms     = \App\Models\AcademicTerm::where('institute_id', $instituteId)->orderByDesc('is_active')->orderBy('name')->get();
    $isPrincipal = auth()->user()->isPrincipal();
    $isTeacher   = auth()->user()->isTeacher();
    $isStudent   = auth()->user()->isStudent();
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📊 Annual Grade Reports & Weightage Breakdown</h1>
        <p class="muted" style="margin-top:4px">
            <?php if($isStudent): ?>
                View your normalized annual academic performance out of 100 calculated with test weightages.
            <?php else: ?>
                View student performance out of 100 across tests, including manual paper marksheets and weightage calculations.
            <?php endif; ?>
        </p>
    </div>
    <?php if(!$isStudent): ?>
        <a href="<?php echo e(route('lms.assessments.paperMarksheet.form')); ?>" class="btn btn-primary">
            📝 Create Manual Paper Marksheet
        </a>
    <?php endif; ?>
</div>


<div class="card">
    <form id="report-filter" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Subject *</label>
            <select id="rp-subject" required>
                <option value="">Select Subject</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>"><?php echo e($s->subject_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Academic Term *</label>
            <select id="rp-term" required>
                <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>" <?php if($t->is_active): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Class Section *</label>
            <select id="rp-section" required>
                <option value="">Select Section</option>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sec->id); ?>"><?php echo e($sec->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($sec->section_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php if($isStudent): ?>
            <button type="button" class="btn btn-primary" onclick="loadStudentReport()">📊 My Report</button>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="loadClassReport()">📊 Load Class Report</button>
        <?php endif; ?>
    </form>
</div>


<div id="report-container" style="display:none">

    
    <div id="class-stats-row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="text-align:center;padding:20px;margin-bottom:0">
            <div class="muted" style="font-size:12px;margin-bottom:4px">Class Average</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--accent)" id="stat-avg">—</div>
        </div>
        <div class="card" style="text-align:center;padding:20px;margin-bottom:0">
            <div class="muted" style="font-size:12px;margin-bottom:4px">Highest Score</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--success)" id="stat-max">—</div>
        </div>
        <div class="card" style="text-align:center;padding:20px;margin-bottom:0">
            <div class="muted" style="font-size:12px;margin-bottom:4px">Lowest Score</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--danger)" id="stat-min">—</div>
        </div>
        <div class="card" style="text-align:center;padding:20px;margin-bottom:0">
            <div class="muted" style="font-size:12px;margin-bottom:4px">Fully Graded</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--accent2)" id="stat-graded">—</div>
        </div>
    </div>

    
    <div id="student-report-card" style="display:none" class="card">
        <div class="card-header">
            <h3 class="card-title">📋 Your Grade Breakdown</h3>
            <div style="display:flex;gap:8px;align-items:center">
                <span id="sr-letter" class="badge badge-green" style="font-size:16px;padding:6px 14px"></span>
                <span id="sr-gpa" class="badge badge-purple" style="font-size:14px;padding:6px 12px"></span>
            </div>
        </div>
        <div style="margin-bottom:16px">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">Normalized Score</div>
            <div style="width:100%;height:24px;background:var(--surface2);border-radius:12px;overflow:hidden;border:1px solid var(--border)">
                <div id="sr-score-bar" style="height:100%;border-radius:12px;background:linear-gradient(90deg,var(--accent),var(--accent2));transition:width 0.6s ease;display:flex;align-items:center;justify-content:flex-end;padding-right:8px;font-weight:700;font-size:11px;color:#fff"></div>
            </div>
        </div>
        <div id="sr-breakdown"></div>
        <div id="sr-missing" style="display:none;margin-top:12px" class="alert alert-warning"></div>
    </div>

    
    <div id="class-report-table" class="card">
        <div class="card-header">
            <h3 class="card-title">📈 Class Report</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Normalized Score</th>
                    <th>Letter Grade</th>
                    <th>GPA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="class-report-body"></tbody>
        </table>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function getGradeColor(score) {
        if (score >= 85) return 'var(--success)';
        if (score >= 70) return 'var(--accent2)';
        if (score >= 50) return 'var(--warning)';
        return 'var(--danger)';
    }

    async function loadStudentReport() {
        const subjectId = document.getElementById('rp-subject').value;
        const termId = document.getElementById('rp-term').value;
        const sectionId = document.getElementById('rp-section').value;

        if (!subjectId || !termId || !sectionId) {
            alert('Please select all filters.');
            return;
        }

        try {
            const res = await fetch(`/lms/grades/student-report?student_id=<?php echo e(auth()->id()); ?>&subject_id=${subjectId}&academic_term_id=${termId}&class_section_id=${sectionId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            const report = data.report;

            document.getElementById('report-container').style.display = 'block';
            document.getElementById('class-stats-row').style.display = 'none';
            document.getElementById('class-report-table').style.display = 'none';
            document.getElementById('student-report-card').style.display = 'block';

            // Score bar
            const scoreBar = document.getElementById('sr-score-bar');
            scoreBar.style.width = report.normalized_score + '%';
            scoreBar.textContent = report.normalized_score.toFixed(1) + '%';

            document.getElementById('sr-letter').textContent = report.letter_grade;
            document.getElementById('sr-gpa').textContent = 'GPA: ' + report.gpa.toFixed(2);

            // Breakdown
            const breakdown = document.getElementById('sr-breakdown');
            const tests = report.test_breakdown || [];
            if (tests.length === 0) {
                breakdown.innerHTML = '<div class="muted" style="padding:20px;text-align:center">No tests or paper marksheets created for this subject yet.</div>';
            } else {
                let html = `
                    <div style="margin-top:16px">
                        <h4 style="font-size:14px;font-weight:700;margin-bottom:10px">📝 Conducted Tests & Weightage Breakdown</h4>
                        <table style="width:100%;font-size:13px">
                            <thead>
                                <tr>
                                    <th>Test Title</th>
                                    <th>Mode</th>
                                    <th>Obtained / Total Marks</th>
                                    <th>Percentage</th>
                                    <th>Assigned Weightage</th>
                                    <th>Calculated Marks Contribution</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                tests.forEach(t => {
                    html += `
                        <tr>
                            <td style="font-weight:600">📝 ${t.test_title}</td>
                            <td>${t.is_paper_test ? '<span class="badge badge-yellow">Paper Exam</span>' : '<span class="badge badge-green">Online RAG</span>'}</td>
                            <td><b>${t.obtained_marks}</b> / ${t.max_marks} pts</td>
                            <td>${t.percentage}%</td>
                            <td style="font-weight:600">${t.weightage_percentage}%</td>
                            <td style="font-weight:700;color:var(--accent)">${t.weighted_contribution} / ${t.weightage_percentage} pts</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding:12px 16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border)">
                            <span style="font-weight:700">🎯 Calculated Annual Performance Score:</span>
                            <span style="font-size:18px;font-weight:700;color:var(--accent)">
                                ${report.total_weighted_score} / ${report.total_weightage_capacity} pts (${report.normalized_score}%)
                            </span>
                        </div>
                    </div>
                `;
                breakdown.innerHTML = html;
            }
        } catch (err) {
            alert('Failed to load report: ' + err.message);
        }
    }

    async function loadClassReport() {
        const subjectId = document.getElementById('rp-subject').value;
        const termId = document.getElementById('rp-term').value;
        const sectionId = document.getElementById('rp-section').value;

        if (!subjectId || !termId || !sectionId) {
            alert('Please select all filters.');
            return;
        }

        // Get students for this section
        try {
            const res = await fetch(`/lms/grades/class-report?subject_id=${subjectId}&academic_term_id=${termId}&class_section_id=${sectionId}&student_ids[]=<?php echo e(auth()->id()); ?>`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();

            document.getElementById('report-container').style.display = 'block';
            document.getElementById('class-stats-row').style.display = 'grid';
            document.getElementById('class-report-table').style.display = 'block';
            document.getElementById('student-report-card').style.display = 'none';

            // Stats
            const stats = data.statistics;
            document.getElementById('stat-avg').textContent = (stats.class_average || 0).toFixed(1) + '%';
            document.getElementById('stat-max').textContent = (stats.highest_score || 0).toFixed(1) + '%';
            document.getElementById('stat-min').textContent = (stats.lowest_score || 0).toFixed(1) + '%';
            document.getElementById('stat-graded').textContent = (stats.fully_graded || 0) + '/' + (stats.total_students || 0);

            // Table
            const tbody = document.getElementById('class-report-body');
            tbody.innerHTML = (data.reports || []).map((r, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td style="font-weight:600">Student #${r.student_id}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="flex:1;height:6px;background:var(--surface2);border-radius:3px;overflow:hidden;max-width:120px">
                                <div style="height:100%;width:${r.normalized_score}%;background:${getGradeColor(r.normalized_score)};border-radius:3px"></div>
                            </div>
                            <span style="font-weight:600">${r.normalized_score.toFixed(1)}%</span>
                        </div>
                    </td>
                    <td><span class="badge" style="background:${getGradeColor(r.normalized_score)}20;color:${getGradeColor(r.normalized_score)};border:1px solid ${getGradeColor(r.normalized_score)}40">${r.letter_grade}</span></td>
                    <td style="font-weight:600">${r.gpa.toFixed(2)}</td>
                    <td>${r.is_complete ? '<span class="badge badge-green">Complete</span>' : '<span class="badge badge-yellow">Incomplete</span>'}</td>
                </tr>
            `).join('');
        } catch (err) {
            alert('Failed to load report: ' + err.message);
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\grades\report.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Term Exam Reports'); ?>
<?php $__env->startSection('breadcrumb', 'Term Exam Reports'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📜 Term Exam Reports &amp; Marksheets</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Official Midterm &amp; Final Term examination marksheets and student performance scorecards.
        </p>
    </div>
    <?php if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin()): ?>
        <a href="<?php echo e(route('lms.datesheet.index')); ?>" class="btn btn-primary">
            📅 Exam Datesheet Builder
        </a>
    <?php endif; ?>
</div>


<div class="card" style="margin-bottom:24px">
    <form method="GET" action="<?php echo e(route('lms.exam-report.index')); ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📝 Exam Title Search</label>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="e.g. Midterm 2026..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">🏫 Class Section</label>
            <select name="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Classes</option>
                <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cs->id); ?>" <?php if(request('class_section_id') == $cs->id): echo 'selected'; endif; ?>>
                        <?php echo e($cs->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($cs->section_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📘 Subject Filter</label>
            <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Subjects</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>" <?php if(request('subject_id') == $s->id): echo 'selected'; endif; ?>><?php echo e($s->subject_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="height:42px">Filter Exams</button>
        <?php if(request()->hasAny(['search', 'class_section_id', 'subject_id'])): ?>
            <a href="<?php echo e(route('lms.exam-report.index')); ?>" class="btn btn-ghost" style="height:42px;color:var(--danger)">Reset</a>
        <?php endif; ?>
    </form>
</div>


<?php $__empty_1 = true; $__currentLoopData = $groupedExams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $examList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent2)">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">🏫</span>
                <div>
                    <h3 class="card-title" style="font-size:18px;font-weight:700"><?php echo e($className); ?> — Formal Examinations</h3>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        <?php echo e($examList->count()); ?> Formal Exam Marksheet(s) Scheduled
                    </p>
                </div>
            </div>
            <span class="badge badge-purple"><?php echo e($examList->unique('subject_id')->count()); ?> Subjects</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:16px;margin-top:16px">
            <?php $__currentLoopData = $examList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $examDate = $exam->start_time ? $exam->start_time->format('M d, Y') : $exam->created_at->format('M d, Y');
                    $typeBadge = match($exam->type) {
                        'midterm' => 'badge-purple',
                        'final' => 'badge-red',
                        default => 'badge-yellow',
                    };
                ?>
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;flex-direction:column;justify-content:space-between">
                    <div>
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                            <h4 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:var(--text);margin:0">
                                📝 <?php echo e($exam->title); ?>

                            </h4>
                            <span class="badge <?php echo e($typeBadge); ?>"><?php echo e(strtoupper($exam->type)); ?></span>
                        </div>

                        <div style="font-size:13px;color:var(--text-muted);margin-top:10px;display:flex;flex-direction:column;gap:6px">
                            <div>📘 <b>Subject:</b> <?php echo e($exam->subject->subject_name ?? '—'); ?></div>
                            <div>📅 <b>Datesheet Date:</b> <?php echo e($examDate); ?></div>
                            <div>🎯 <b>Total Marks (Admin Set):</b> <b style="color:var(--accent)"><?php echo e($exam->total_marks); ?> pts</b></div>
                            <div>👤 <b>Created By:</b> <?php echo e($exam->creator->name ?? 'Administration'); ?></div>
                        </div>
                    </div>

                    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                        <?php if($exam->is_marksheet_saved): ?>
                            <span class="badge badge-green" style="font-size:11px">💾 Marksheet Ready</span>
                        <?php else: ?>
                            <span class="badge badge-yellow" style="font-size:11px">Pending Marks</span>
                        <?php endif; ?>

                        <a href="<?php echo e(route('lms.exam-report.marksheet', $exam->id)); ?>" class="btn btn-primary btn-sm" style="padding:8px 14px;font-weight:600">
                            📝 Insert / Edit Marks
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card" style="text-align:center;padding:60px 20px">
        <div style="font-size:48px;margin-bottom:16px">📜</div>
        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">
            No Formal Exams Listed Yet
        </h3>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px;margin:0 auto 24px;line-height:1.6">
            When Principal or Administration schedules Midterm or Final Term examinations on the Datesheet, the official exam marksheets will be cataloged here.
        </p>
        <?php if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin()): ?>
            <a href="<?php echo e(route('lms.datesheet.index')); ?>" class="btn btn-primary btn-lg">
                📅 Schedule Exam on Datesheet
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\exam_report\index.blade.php ENDPATH**/ ?>
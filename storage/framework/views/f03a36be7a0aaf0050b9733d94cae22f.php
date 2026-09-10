

<?php $__env->startSection('title', 'Test Results & Grading'); ?>
<?php $__env->startSection('breadcrumb', 'Test Results'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📋 Test Results &amp; Grading</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Classroom quizzes, assignments, and test evaluation reports for student performance tracking.
        </p>
    </div>
    <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="<?php echo e(route('lms.assessments.paperMarksheet.form')); ?>" class="btn btn-ghost" style="border:1px solid var(--accent)">
                📝 Create Paper Marksheet
            </a>
            <a href="<?php echo e(route('lms.assessments.index')); ?>" class="btn btn-primary">
                ➕ New Online Assessment
            </a>
        </div>
    <?php endif; ?>
</div>


<div class="card" style="margin-bottom:24px">
    <form method="GET" action="<?php echo e(route('lms.test-results.index')); ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">👤 Student Name / Roll</label>
            <input type="text" name="student_search" value="<?php echo e(request('student_search')); ?>" placeholder="e.g. Student Name..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📝 Test Name</label>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="e.g. Test 1..." style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">🏫 Class Filter</label>
            <select name="class_section_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Classes</option>
                <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cs->id); ?>" <?php if(request('class_section_id') == $cs->id): echo 'selected'; endif; ?>>
                        <?php echo e($cs->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($cs->section_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block">📘 Subject Filter</label>
            <select name="subject_id" style="width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text)">
                <option value="">All Subjects</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>" <?php if(request('subject_id') == $s->id): echo 'selected'; endif; ?>><?php echo e($s->subject_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="height:42px">Filter Directory</button>
        <?php if(request()->hasAny(['search', 'subject_id', 'class_section_id', 'student_search'])): ?>
            <a href="<?php echo e(route('lms.test-results.index')); ?>" class="btn btn-ghost" style="height:42px;color:var(--danger)">Reset</a>
        <?php endif; ?>
    </form>
</div>


<?php if($searchedStudentResults !== null): ?>
    <div style="margin-bottom:28px">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:10px">
            <span>👤 Student Academic Performance Profile</span>
            <span class="badge badge-purple" style="font-size:12px"><?php echo e(count($searchedStudentResults)); ?> Student(s) Found</span>
        </h2>

        <?php $__empty_1 = true; $__currentLoopData = $searchedStudentResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stRes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card" style="margin-bottom:20px;border-left:4px solid var(--accent2)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--text)">
                            <?php echo e($stRes['name']); ?> <span style="font-size:13px;color:var(--text-muted)">(<?php echo e($stRes['roll_number']); ?>)</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:2px">
                            🏫 Class: <b><?php echo e($stRes['class_name']); ?></b> · Email: <?php echo e($stRes['email']); ?>

                        </div>
                    </div>
                    <span class="badge badge-green" style="font-size:12px">Enrolled Subjects Marks</span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:16px">
                    <?php $__currentLoopData = $stRes['subject_scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjScore): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $badgeClass = match($subjScore['grade']) {
                                'A+', 'A' => 'badge-green',
                                'B', 'C' => 'badge-purple',
                                'D' => 'badge-yellow',
                                default => 'badge-red',
                            };
                        ?>
                        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                                <h4 style="font-size:15px;font-weight:700;color:var(--accent2);margin:0">
                                    📘 <?php echo e($subjScore['subject_name']); ?>

                                </h4>
                                <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($subjScore['grade']); ?> (<?php echo e($subjScore['percentage']); ?>%)</span>
                            </div>

                            <div style="font-size:13px;margin-bottom:10px">
                                Marks: <b><?php echo e($subjScore['total_obtained']); ?></b> / <?php echo e($subjScore['total_max']); ?> pts
                            </div>

                            
                            <?php if(!empty($subjScore['tests'])): ?>
                                <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:6px">
                                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-muted)">Test Breakdown:</div>
                                    <?php $__currentLoopData = $subjScore['tests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
                                            <span>📝 <?php echo e($tInfo['test_title']); ?></span>
                                            <?php if($tInfo['has_attempted']): ?>
                                                <span style="font-weight:600;color:var(--success)"><?php echo e($tInfo['obtained_marks']); ?> / <?php echo e($tInfo['total_marks']); ?></span>
                                            <?php else: ?>
                                                <span class="muted" style="font-size:11px">Not Attempted</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card" style="text-align:center;padding:30px;color:var(--text-muted)">
                No student records found matching "<?php echo e(request('student_search')); ?>".
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php $__empty_1 = true; $__currentLoopData = $groupedByClass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $classAssessments): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--accent)">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">🏫</span>
                <div>
                    <h3 class="card-title" style="font-size:18px;font-weight:700"><?php echo e($className ?: 'General Class'); ?></h3>
                    <p style="font-size:12px;color:var(--text-muted);margin:0">
                        <?php echo e($classAssessments->count()); ?> Test Marksheets Available
                    </p>
                </div>
            </div>
            <span class="badge badge-purple"><?php echo e($classAssessments->unique('subject_id')->count()); ?> Subjects</span>
        </div>

        
        <?php
            $subjectGrouped = $classAssessments->groupBy(fn($a) => $a->subject->subject_name ?? 'General Subject');
        ?>

        <div style="display:flex;flex-direction:column;gap:16px;margin-top:16px">
            <?php $__currentLoopData = $subjectGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectName => $tests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)">
                        <span style="font-size:16px">📘</span>
                        <h4 style="font-size:15px;font-weight:700;color:var(--accent2);margin:0">
                            Subject: <?php echo e($subjectName); ?>

                        </h4>
                        <span class="badge badge-green" style="font-size:10px"><?php echo e($tests->count()); ?> Test(s)</span>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:14px">
                        <?php $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assessment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:16px;display:flex;flex-direction:column;justify-content:space-between">
                                <div>
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                                        <h5 style="font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;color:var(--text);margin:0">
                                            📝 <?php echo e($assessment->title); ?>

                                        </h5>
                                        <?php if($assessment->is_marksheet_saved): ?>
                                            <span class="badge badge-green" style="font-size:10px">💾 Saved</span>
                                        <?php endif; ?>
                                    </div>

                                    <div style="font-size:12px;color:var(--text-muted);margin-top:8px;display:flex;flex-direction:column;gap:4px">
                                        <div>🎯 <b>Total Marks:</b> <?php echo e($assessment->total_marks); ?> pts</div>
                                        <div>🔢 <b>Questions:</b> <?php echo e($assessment->questions->count()); ?></div>
                                        <?php if($assessment->has_time_limit): ?>
                                            <div>⏱️ <b>Duration:</b> <?php echo e($assessment->duration_minutes); ?> mins</div>
                                        <?php endif; ?>
                                        <?php if($assessment->creator): ?>
                                            <div>👤 <b>Created By:</b> <?php echo e($assessment->creator->name); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
                                    <?php if(!$assessment->is_marksheet_saved && (auth()->user()->isTeacher() || auth()->user()->isPrincipal())): ?>
                                        <form method="POST" action="<?php echo e(route('lms.test-results.saveMarksheet', $assessment->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-ghost btn-sm" title="Archive Marksheet">💾 Save</button>
                                        </form>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>

                                    <a href="<?php echo e(route('lms.test-results.marksheet', $assessment->id)); ?>" class="btn btn-primary btn-sm" style="padding:8px 14px;font-weight:600">
                                        👁️ View Complete Marksheet
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card" style="text-align:center;padding:60px 20px">
        <div style="font-size:48px;margin-bottom:16px">📂</div>
        <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px">
            No Test Marksheets Available Yet
        </h3>
        <p style="color:var(--text-muted);font-size:14px;max-width:480px;margin:0 auto 24px;line-height:1.6">
            When teachers create assessments and students attempt tests, the class marksheets will automatically be cataloged here organized by Class and Subject.
        </p>
        <?php if(auth()->user()->isTeacher() || auth()->user()->isPrincipal()): ?>
            <a href="<?php echo e(route('lms.assessments.index')); ?>" class="btn btn-primary btn-lg">
                ✨ Create First Test / Assessment
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\test_results\index.blade.php ENDPATH**/ ?>
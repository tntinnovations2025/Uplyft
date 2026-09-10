

<?php
    $routePrefix = 'principal.students.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.students.';
    }
?>

<?php $__env->startSection('title', 'Student Directory & Profiles'); ?>
<?php $__env->startSection('breadcrumb', 'Student Directory'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        color: #059669;
    }
    .filter-bar {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-input {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        color: #0f172a;
        font-size: 13px;
        outline: none;
    }
    .filter-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    /* Clean 4-Column Table */
    .student-table {
        width: 100%;
        border-collapse: collapse;
    }
    .student-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    .student-table td {
        padding: 12px 16px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    .student-table tr:hover td {
        background: #f8fafc;
    }
    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(225, 48, 108, 0.25);
    }

    /* Modal Overlay Styles */
    .modal-overlay, .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .modal-overlay.active, .modal-backdrop.active {
        display: flex;
    }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 24px;
        position: relative;
        color: #0f172a;
    }
</style>

<div class="header-actions">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px">
            <span>🎓 Student Directory &amp; Profiles</span>
            <?php if(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() && !auth()->user()->hasPermission('students', 'edit')): ?>
                <span class="badge" style="font-size:11px;padding:3.5px 10px;border-radius:8px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-weight:700">👁️ Read Only</span>
            <?php endif; ?>
        </h1>
        <div style="font-size:12px;color:#64748b;margin-top:4px;font-weight:500">
            Browse enrolled students, view academic profiles, and manage class placements.
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px">
        <div class="stat-pill">
            <span>👥</span>
            <span>Enrolled: <?php echo e($totalStudents); ?></span>
        </div>

        <?php if(auth()->check() && (auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('students', 'edit'))): ?>
            <?php if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin()): ?>
            <button type="button" onclick="openBulkImportModal()" class="btn btn-ghost btn-sm" style="border-color:#3b82f6;color:#2563eb;background:#eff6ff">
                <span>📥 Import Students</span>
            </button>
            <?php endif; ?>

            <a href="<?php echo e(route($routePrefix . 'create')); ?>" class="btn btn-primary btn-sm">
                <span>➕ New Student Admission</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<form method="GET" action="<?php echo e(route($routePrefix . 'index')); ?>" class="filter-bar">
    <div style="flex:1;min-width:200px">
        <input type="text" name="search" class="filter-input" style="width:100%"
            value="<?php echo e(request('search')); ?>" placeholder="🔍 Search student, roll #, father name, email...">
    </div>

    <div style="min-width:200px">
        <select name="class_section_id" class="filter-input" style="width:100%">
            <option value="">All Classes &amp; Sections</option>
            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sec->id); ?>" <?php echo e(request('class_section_id') == $sec->id ? 'selected' : ''); ?>>
                        <?php echo e($class->name); ?> — Sec <?php echo e($sec->section_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>

    <?php if(request('search') || request('class_section_id')): ?>
        <a href="<?php echo e(route($routePrefix . 'index')); ?>" class="btn btn-danger btn-sm">Reset</a>
    <?php endif; ?>
</form>

<div class="card" style="padding:0;overflow:hidden;background:#ffffff;border:1px solid rgba(226,232,240,0.85);box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
    <table class="student-table">
        <thead>
            <tr>
                <th style="width:28%">Student Profile</th>
                <th style="width:24%">Guardian &amp; Contact</th>
                <th style="width:16%">Class &amp; Section</th>
                <th style="width:16%;text-align:center">Attendance %</th>
                <th style="width:16%;text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px">
                            <?php if($student->passport_picture_path): ?>
                                <img src="<?php echo e(asset('storage/' . $student->passport_picture_path)); ?>" class="avatar-circle" style="object-fit:cover" alt="<?php echo e($student->full_name); ?>">
                            <?php else: ?>
                                <div class="avatar-circle"><?php echo e(strtoupper(substr($student->first_name, 0, 1))); ?></div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight:800;color:#0f172a;font-size:14px;display:flex;align-items:center;gap:6px">
                                    <span><?php echo e($student->full_name); ?></span>
                                    <span class="badge badge-purple" style="font-family:monospace;font-size:10px;padding:2px 6px"><?php echo e($student->roll_number); ?></span>
                                </div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500"><?php echo e($student->email); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:700;color:#1e293b;font-size:13px"><?php echo e($student->father_guardian_name ?? 'N/A'); ?></div>
                        <div style="font-size:12px;color:#059669;margin-top:2px;font-weight:600">
                            <?php
                                $p = $student->guardian_phone ?? '';
                                $flag = '📞';
                                if (str_starts_with($p, '+92') || str_starts_with($p, '03')) $flag = '🇵🇰';
                                elseif (str_starts_with($p, '+1')) $flag = '🇺🇸';
                                elseif (str_starts_with($p, '+44')) $flag = '🇬🇧';
                                elseif (str_starts_with($p, '+971')) $flag = '🇦🇪';
                                elseif (str_starts_with($p, '+966')) $flag = '🇸🇦';
                                elseif (str_starts_with($p, '+91')) $flag = '🇮🇳';
                                elseif (str_starts_with($p, '+974')) $flag = '🇶🇦';
                                elseif (str_starts_with($p, '+965')) $flag = '🇰🇼';
                                elseif (str_starts_with($p, '+968')) $flag = '🇴🇲';
                                elseif (str_starts_with($p, '+61')) $flag = '🇦🇺';
                            ?>
                            <span><?php echo e($flag); ?> <?php echo e($student->guardian_phone ?? 'N/A'); ?></span>
                        </div>
                        <?php if($student->father_guardian_cnic): ?>
                            <div style="font-size:11px;color:#64748b;margin-top:2px;font-family:monospace">
                                🪪 CNIC: <?php echo e($student->father_guardian_cnic); ?>

                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            <?php if($student->classSection && $student->classSection->instituteClass): ?>
                                <span class="badge badge-blue">
                                    <?php echo e($student->classSection->instituteClass->name); ?> <?php echo e($student->classSection->section_name); ?>

                                </span>
                            <?php else: ?>
                                <span class="badge badge-green"><?php echo e($student->enrolled_program ?? 'General'); ?></span>
                            <?php endif; ?>

                            <?php if($student->blood_group): ?>
                                <span class="badge badge-yellow" style="font-size:10px"><?php echo e($student->blood_group); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Annual Pass / Fail Result Status -->
                        <div style="margin-top:6px;display:flex;align-items:center;gap:4px">
                            <span style="font-size:11px;color:#64748b;font-weight:700">Result:</span>
                            <?php if(auth()->check() && (auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('students', 'edit'))): ?>
                                <form method="POST" action="<?php echo e(route('principal.students.result-status', $student)); ?>" style="display:inline-block">
                                    <?php echo csrf_field(); ?>
                                    <select name="annual_result_status" onchange="this.form.submit()" title="Set Annual Result Status for Session Promotion" style="background:#ffffff;border:1px solid <?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? '#fca5a5' : '#a7f3d0'); ?>;color:<?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? '#dc2626' : '#059669'); ?>;font-size:11px;font-weight:800;padding:3px 8px;border-radius:8px;cursor:pointer">
                                        <option value="passed" <?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'passed' ? 'selected' : ''); ?>>✅ PASSED</option>
                                        <option value="failed" <?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? 'selected' : ''); ?>>❌ FAILED (REPEAT CLASS)</option>
                                        <option value="pending" <?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'pending' ? 'selected' : ''); ?>>⏳ PENDING</option>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span style="font-size:11px;font-weight:800;color:<?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? '#dc2626' : '#059669'); ?>;background:<?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? '#fef2f2' : '#ecfdf5'); ?>;padding:2px 8px;border-radius:6px;border:1px solid <?php echo e(strtolower($student->annual_result_status ?? 'passed') === 'failed' ? '#fecaca' : '#a7f3d0'); ?>">
                                    <?php echo e(strtoupper($student->annual_result_status ?? 'PASSED')); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="text-align:center">
                        <?php
                            $pct = $student->attendance_percentage ?? 100;
                            $pctColor = $pct >= 75 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626');
                            $pctBg = $pct >= 75 ? '#ecfdf5' : ($pct >= 50 ? '#fffbeb' : '#fef2f2');
                            $pctBorder = $pct >= 75 ? '#a7f3d0' : ($pct >= 50 ? '#fde68a' : '#fecaca');
                        ?>
                        <div style="display:flex;flex-direction:column;align-items:center">
                            <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:800;background:<?php echo e($pctBg); ?>;color:<?php echo e($pctColor); ?>;border:1px solid <?php echo e($pctBorder); ?>">
                                📊 <?php echo e($pct); ?>%
                            </span>
                            <div style="font-size:11px;color:#64748b;margin-top:3px;font-weight:500">
                                <?php echo e($student->attendance_present_sessions ?? 0); ?>/<?php echo e($student->attendance_total_sessions ?? 0); ?> sessions
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right">
                        <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:wrap">
                            <a href="<?php echo e(route($routePrefix . 'show', $student)); ?>" class="btn btn-ghost btn-sm" style="padding:6px 12px;font-size:12px;font-weight:700;color:#0284c7;border-color:#bae6fd;background:#f0f9ff" title="View Full Student Details, Documents & Address">
                                👁️ View Details
                            </a>
                            <?php if(auth()->check() && (auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('students', 'edit'))): ?>
                                <?php if($student->user_id): ?>
                                    <button type="button" 
                                            onclick="openDirectStudentResetModal(<?php echo e($student->user_id); ?>, '<?php echo e(addslashes($student->full_name)); ?>', '<?php echo e(addslashes($student->email)); ?>')"
                                            class="btn btn-ghost btn-sm" 
                                            style="padding:6px 10px;font-size:12px;border-color:#fde68a;color:#d97706;background:#fffbeb">
                                        🔑 Reset Password
                                    </button>
                                <?php endif; ?>
                                <button type="button" 
                                        onclick="openShiftModal(<?php echo e($student->id); ?>, '<?php echo e(addslashes($student->full_name)); ?>', '<?php echo e($student->roll_number); ?>', '<?php echo e($student->classSection ? $student->classSection->id : ''); ?>', '<?php echo e($student->classSection && $student->classSection->instituteClass ? addslashes($student->classSection->instituteClass->name . ' - Sec ' . $student->classSection->section_name) : 'Not assigned'); ?>')"
                                        class="btn btn-ghost btn-sm" 
                                        style="padding:6px 10px;font-size:12px;border-color:#fecaca;color:#dc2626;background:#fef2f2">
                                    ⇄ Shift / Unenroll
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:48px;color:#64748b">
                        <div style="font-size:32px;margin-bottom:8px">🎓</div>
                        <div style="font-size:16px;font-weight:800;color:#0f172a">No Students Registered Yet</div>
                        <div style="font-size:13px;margin-top:4px;margin-bottom:16px;color:#64748b">Get started by registering students into your academic classes.</div>
                        <a href="<?php echo e(route($routePrefix . 'create')); ?>" class="btn btn-primary">
                            ➕ Register First Student
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($students->hasPages()): ?>
        <div style="padding:14px 20px;border-top:1px solid rgba(255,255,255,0.05)">
            <?php echo e($students->links()); ?>

        </div>
    <?php endif; ?>
</div>

<!-- Modal: Shift Class or Unenroll Student -->
<div id="shiftUnenrollModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                ⇄ Shift Class or Unenroll Student
            </h3>
            <button type="button" onclick="closeShiftModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1;padding:0 4px">&times;</button>
        </div>

        <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:14px 16px;margin-bottom:20px">
            <div style="font-size:15px;font-weight:800;color:#0f172a" id="modalStudentName">Student Name</div>
            <div style="font-size:12.5px;color:#64748b;margin-top:4px;font-weight:600">
                Roll No: <span id="modalStudentRoll" style="color:#059669;font-weight:800">STD-XXXX</span> | 
                Current: <span id="modalCurrentClass" style="color:#2563eb;font-weight:800">Class</span>
            </div>
        </div>

        <!-- Option A: Shift to another Class & Section -->
        <form id="shiftForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                    🏫 Select New Class &amp; Section
                </label>
                <select name="class_section_id" id="modalClassSelect" style="width:100%;padding:11px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px;font-weight:600;outline:none;box-sizing:border-box" required>
                    <option value="">-- Choose Target Class &amp; Section --</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sec->id); ?>">
                                <?php echo e($class->name); ?> — Section <?php echo e($sec->section_name); ?> (Capacity: <?php echo e($sec->enrolled_students); ?>/<?php echo e($sec->capacity); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div style="display:flex;gap:10px;margin-top:16px">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:11px 16px;font-weight:800">
                    🚀 Shift Student to Selected Class
                </button>
            </div>
        </form>

        <div style="display:flex;align-items:center;gap:12px;margin:20px 0;color:#64748b;font-size:11px;text-transform:uppercase;font-weight:800">
            <div style="flex:1;height:1px;background:#e2e8f0"></div>
            <span>OR UNENROLL ENTIRELY</span>
            <div style="flex:1;height:1px;background:#e2e8f0"></div>
        </div>

        <!-- Option B: Completely Unenroll / Delete -->
        <form id="unenrollForm" method="POST" action="" onsubmit="return confirm('⚠️ ARE YOU SURE?\nThis will permanently unenroll this student and remove their profile and login account.')">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;padding:11px 16px;font-weight:800">
                🗑️ Completely Unenroll &amp; Remove Student
            </button>
        </form>
    </div>
</div>

<!-- Direct Student Password Reset Modal -->
<div id="directStudentResetModal" class="modal-overlay">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px">
                <span>🔑</span>
                <span>Direct Student Password Reset</span>
            </h3>
            <button type="button" onclick="closeDirectStudentResetModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1;padding:0 4px">&times;</button>
        </div>

        <form id="directStudentResetForm" method="POST" action="" style="display:flex;flex-direction:column;gap:16px">
            <?php echo csrf_field(); ?>
            <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 16px">
                <div style="font-size:15px;font-weight:800;color:#92400e" id="directStudentResetName">Student Name</div>
                <div style="font-size:13px;font-weight:600;color:#b45309;margin-top:3px" id="directStudentResetEmail">student@email.com</div>
            </div>

            <div>
                <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">New Password</label>
                <input type="password" name="new_password" required placeholder="Enter new strong password" style="width:100%;padding:11px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px;font-weight:600;outline:none;box-sizing:border-box">
                <div style="font-size:11.5px;color:#64748b;font-weight:500;margin-top:5px">Must contain uppercase, lowercase, number, and special character.</div>
            </div>

            <div>
                <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" required placeholder="Confirm new password" style="width:100%;padding:11px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px;font-weight:600;outline:none;box-sizing:border-box">
            </div>

            <div style="display:flex;gap:12px;margin-top:8px">
                <button type="button" onclick="closeDirectStudentResetModal()" style="flex:1;padding:11px 16px;font-size:13px;font-weight:700;color:#475569;background:#f1f5f9;border:1.5px solid #cbd5e1;border-radius:10px;cursor:pointer;transition:all 0.15s ease">Cancel</button>
                <button type="submit" style="flex:1;padding:11px 16px;font-size:13px;font-weight:800;color:#ffffff;background:linear-gradient(135deg, #d97706, #b45309);border:none;border-radius:10px;cursor:pointer;box-shadow:0 4px 12px rgba(217,119,6,0.3)">⚡ Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDirectStudentResetModal(userId, name, email) {
        document.getElementById('directStudentResetName').textContent = name;
        document.getElementById('directStudentResetEmail').textContent = email || '';
        const prefix = window.location.pathname.startsWith('/teacher') ? 'teacher' : 'principal';
        document.getElementById('directStudentResetForm').action = `/${prefix}/users/${userId}/direct-reset-password`;
        document.getElementById('directStudentResetModal').classList.add('active');
    }

    function closeDirectStudentResetModal() {
        document.getElementById('directStudentResetModal').classList.remove('active');
    }

    function openShiftModal(studentId, studentName, rollNumber, currentSectionId, currentClassName) {
        document.getElementById('modalStudentName').innerText = studentName;
        document.getElementById('modalStudentRoll').innerText = rollNumber;
        document.getElementById('modalCurrentClass').innerText = currentClassName;

        const updateUrl = "<?php echo e(url('/principal/students')); ?>/" + studentId;
        const deleteUrl = "<?php echo e(url('/principal/students')); ?>/" + studentId;

        document.getElementById('shiftForm').action = updateUrl;
        document.getElementById('unenrollForm').action = deleteUrl;

        const classSelect = document.getElementById('modalClassSelect');
        if (currentSectionId) {
            classSelect.value = currentSectionId;
        } else {
            classSelect.value = '';
        }

        document.getElementById('shiftUnenrollModal').classList.add('active');
    }

    function closeShiftModal() {
        document.getElementById('shiftUnenrollModal').classList.remove('active');
    }

    // Close modal on click outside box
    document.getElementById('shiftUnenrollModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeShiftModal();
        }
    });

    document.getElementById('directStudentResetModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDirectStudentResetModal();
        }
    });

    function openBulkImportModal() {
        const modal = document.getElementById('bulkImportModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
        }
    }
    function closeBulkImportModal() {
        const modal = document.getElementById('bulkImportModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
    }
    window.openBulkImportModal = openBulkImportModal;
    window.closeBulkImportModal = closeBulkImportModal;
    document.getElementById('bulkImportModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeBulkImportModal();
    });
</script>


<div id="bulkImportModal" class="modal-backdrop">
    <div class="modal-box" style="max-width:520px;border-radius:18px;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px">
                    📥
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">Import Students</h3>
                    <p style="font-size:11.5px;color:#64748b;margin-top:2px">Upload CSV or Excel roster from your previous system.</p>
                </div>
            </div>
            <button type="button" onclick="closeBulkImportModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b">&times;</button>
        </div>

        <form method="POST" action="<?php echo e(route('principal.bulk-import.students')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:14px;margin-bottom:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span style="font-size:12px;font-weight:700;color:#0f172a">📁 Sample CSV Template</span>
                    <a href="<?php echo e(route('principal.bulk-import.sample', 'students')); ?>" class="btn btn-ghost btn-sm" style="font-size:11px;color:#0284c7;padding:4px 10px">
                        ⬇️ Download Sample CSV
                    </a>
                </div>
                <p style="font-size:11px;color:#64748b;margin:0">
                    Expected columns: <code>Full Name</code>, <code>Roll Number</code>, <code>Gender</code>, <code>Guardian Name</code>, <code>Phone</code>, <code>Email</code>, <code>Class</code>, <code>Section</code>. Missing fields will stay empty or auto-fill defaults.
                </p>
            </div>

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px">Fallback Placement (Optional)</label>
                <select name="fallback_class_section_id" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px">
                    <option value="">Do not assign default section if unmapped in CSV</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sec->id); ?>">Default to: <?php echo e($class->name); ?> — <?php echo e($sec->section_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px">Select CSV / Excel File</label>
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required style="width:100%;padding:8px;border:1px dashed #3b82f6;background:#eff6ff;border-radius:10px;font-size:12px">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeBulkImportModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">⚡ Start Bulk Import</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\students\index.blade.php ENDPATH**/ ?>
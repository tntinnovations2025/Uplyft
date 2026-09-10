<?php
    $routePrefix = 'principal.students.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.students.';
    }
?>

<?php $__env->startSection('title', $student->full_name . ' — Student Profile'); ?>
<?php $__env->startSection('breadcrumb', 'Student Profile'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .profile-header {
        background: #ffffff;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 24px;
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 800;
        box-shadow: 0 4px 16px rgba(225, 48, 108, 0.25);
    }
    .grid-card {
        background: #ffffff;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
    }
    @media (min-width: 768px) {
        .detail-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .detail-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    .detail-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
</style>

<div class="profile-header">
    <?php if($student->passport_picture_path): ?>
        <img src="<?php echo e(Storage::url($student->passport_picture_path)); ?>" class="profile-avatar" style="object-fit:cover" alt="<?php echo e($student->full_name); ?>">
    <?php else: ?>
        <div class="profile-avatar"><?php echo e(strtoupper(substr($student->first_name, 0, 1))); ?></div>
    <?php endif; ?>

    <div style="flex:1">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.5px">
                <?php echo e($student->full_name); ?>

            </h1>
            <span class="badge badge-purple" style="font-family:monospace"><?php echo e($student->roll_number); ?></span>
        </div>
        <div style="font-size:13px;color:#64748b;font-weight:500">
            Enrolled in <?php echo e($student->institute->name); ?> &bull; <?php echo e($student->email); ?>

        </div>
    </div>

    <div>
        <?php if(auth()->check() && auth()->user()->hasPermission('profile_edit', 'edit')): ?>
            <a href="<?php echo e(route($routePrefix . 'edit', $student)); ?>" class="btn btn-primary mb-2">
                <span><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'pencil','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pencil','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Edit Profile
            </a>
            <br>
        <?php endif; ?>
        <a href="<?php echo e(route($routePrefix . 'index')); ?>" class="btn btn-ghost">
            &larr; Return to Roster
        </a>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>👤</span> <span>Personal &amp; Guardian Details</span>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="detail-item">
            <div class="detail-label">Father / Guardian Name</div>
            <div class="detail-value" style="color:#059669"><?php echo e($student->father_guardian_name ?? 'N/A'); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Guardian Phone Number</div>
            <div class="detail-value" style="color:#0284c7"><?php echo e($student->guardian_phone ?? 'N/A'); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Student Phone Number</div>
            <div class="detail-value"><?php echo e($student->phone ?? 'N/A'); ?></div>
        </div>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="detail-item">
            <div class="detail-label">Blood Group</div>
            <div class="detail-value">
                <?php if($student->blood_group): ?>
                    <span class="badge badge-yellow"><?php echo e($student->blood_group); ?></span>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Assigned Class &amp; Section</div>
            <div class="detail-value">
                <?php if($student->classSection && $student->classSection->instituteClass): ?>
                    <?php echo e($student->classSection->instituteClass->name); ?> <?php echo e($student->classSection->section_name); ?>

                <?php else: ?>
                    <?php echo e($student->enrolled_program ?? 'General'); ?>

                <?php endif; ?>
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Date of Birth</div>
            <div class="detail-value"><?php echo e($student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A'); ?></div>
        </div>
    </div>

    <div class="detail-grid detail-grid-3">
        <div class="detail-item">
            <div class="detail-label">Father / Guardian CNIC</div>
            <div class="detail-value" style="font-family:monospace;color:#9333ea"><?php echo e($student->father_guardian_cnic ?? ($student->guardian_cnic ?? 'N/A')); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Student B-Form / CNIC Number</div>
            <div class="detail-value" style="font-family:monospace;color:#0284c7"><?php echo e($student->b_form_or_father_cnic ?? 'N/A'); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Residential Address</div>
            <div class="detail-value" style="font-size:13px;color:#334155"><?php echo e($student->address ?? 'N/A'); ?></div>
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>📑</span> <span>Uploaded Registration &amp; Clearance Documents</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:14px">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🖼️</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">Passport Size Photograph</div>
                    <div style="font-size:11px;color:#64748b">Official Student Photo ID</div>
                </div>
            </div>
            <?php if($student->passport_picture_path): ?>
                <a href="<?php echo e(Storage::url($student->passport_picture_path)); ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#059669;border-color:#a7f3d0;background:#ecfdf5">
                    👁️ View Photo
                </a>
            <?php else: ?>
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            <?php endif; ?>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🪪</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">B-Form / CNIC Document</div>
                    <div style="font-size:11px;color:#64748b">Student / Father CNIC Proof</div>
                </div>
            </div>
            <?php if($student->b_form_or_father_cnic && (str_contains($student->b_form_or_father_cnic, '/') || str_contains($student->b_form_or_father_cnic, '.'))): ?>
                <a href="<?php echo e(Storage::url($student->b_form_or_father_cnic)); ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                    📄 View File
                </a>
            <?php elseif($student->b_form_or_father_cnic): ?>
                <span style="font-size:11px;color:#0284c7;font-family:monospace;font-weight:600"><?php echo e($student->b_form_or_father_cnic); ?></span>
            <?php else: ?>
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            <?php endif; ?>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🛡️</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">Guardian CNIC Document</div>
                    <div style="font-size:11px;color:#64748b">Guardian Legal Proof</div>
                </div>
            </div>
            <?php if($student->guardian_cnic && (str_contains($student->guardian_cnic, '/') || str_contains($student->guardian_cnic, '.'))): ?>
                <a href="<?php echo e(Storage::url($student->guardian_cnic)); ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#9333ea;border-color:#f5d0fe;background:#fdf4ff">
                    📄 View File
                </a>
            <?php elseif($student->guardian_cnic): ?>
                <span style="font-size:11px;color:#9333ea;font-family:monospace;font-weight:600"><?php echo e($student->guardian_cnic); ?></span>
            <?php else: ?>
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <span>📊</span> <span>Attendance Performance &amp; Analytics</span>
        </div>
        <?php
            $pct = $student->attendance_percentage ?? 100;
            $pctColor = $pct >= 75 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626');
            $pctBg = $pct >= 75 ? '#ecfdf5' : ($pct >= 50 ? '#fffbeb' : '#fef2f2');
            $pctBorder = $pct >= 75 ? '#a7f3d0' : ($pct >= 50 ? '#fde68a' : '#fecaca');
        ?>
        <span style="padding:4px 14px;border-radius:20px;font-size:13px;font-weight:800;background:<?php echo e($pctBg); ?>;color:<?php echo e($pctColor); ?>;border:1px solid <?php echo e($pctBorder); ?>">
            Overall: <?php echo e($pct); ?>%
        </span>
    </div>

    <div class="detail-grid detail-grid-4" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Total Sessions</div>
            <div class="detail-value" style="font-size:20px"><?php echo e($student->attendance_total_sessions ?? 0); ?></div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Present Count</div>
            <div class="detail-value" style="font-size:20px;color:#059669"><?php echo e($student->attendance_present_sessions ?? 0); ?></div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Absent Count</div>
            <div class="detail-value" style="font-size:20px;color:#dc2626"><?php echo e($student->attendance_absent_sessions ?? 0); ?></div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Leave Count</div>
            <div class="detail-value" style="font-size:20px;color:#d97706"><?php echo e($student->attendance_leave_sessions ?? 0); ?></div>
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>📄</span> <span>Fee Invoices &amp; Ledger</span>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $student->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:8px">
            <div>
                <div style="font-weight:700;color:#0f172a;font-size:14px">Invoice #<?php echo e($invoice->id); ?> &bull; PKR <?php echo e(number_format($invoice->amount_pkr, 2)); ?></div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Status: <?php echo e(strtoupper($invoice->status)); ?> &bull; Due: <?php echo e($invoice->due_date); ?></div>
            </div>
            <?php if($invoice->pdf_path): ?>
                <a href="<?php echo e(Storage::url($invoice->pdf_path)); ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                    📄 Download PDF
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="font-size:13px;color:#64748b;padding:12px 0">No invoices generated yet for this student.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\students\show.blade.php ENDPATH**/ ?>
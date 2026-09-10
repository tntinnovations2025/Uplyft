<?php
    $routePrefix = 'principal.directory.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.directory.';
    }
?>

<?php $__env->startSection('title', 'Student Profile: ' . $student->full_name); ?>
<?php $__env->startSection('breadcrumb', 'Student Profile'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .profile-header-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .hero-avatar {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
    }
    @media(min-width: 768px) {
        .detail-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 4px;
    }
    .info-val {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }
</style>

<div class="profile-header-card">
    <div style="display:flex;align-items:center;gap:20px">
        <?php if($student->passport_picture_path): ?>
            <img src="<?php echo e(Storage::url($student->passport_picture_path)); ?>" class="hero-avatar" alt="<?php echo e($student->full_name); ?>">
        <?php else: ?>
            <div class="hero-avatar">🎓</div>
        <?php endif; ?>

        <div>
            <div style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
                <?php echo e($student->full_name); ?>

            </div>
            <div style="font-size:13px;color:#059669;font-weight:700;margin-top:4px;display:flex;gap:12px;align-items:center">
                <span class="badge badge-purple">Class Roll No: <?php echo e($student->roll_number); ?></span>
                <span>📚 <?php echo e($student->classSection?->instituteClass?->name ?? 'Unassigned'); ?> — Section <?php echo e($student->classSection?->section_name ?? $student->classSection?->name ?? 'N/A'); ?></span>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px">
        <?php if(auth()->check() && auth()->user()->hasPermission('profile_edit', 'edit')): ?>
            <?php
                $studentPrefix = auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()
                    ? auth()->user()->getStaffUrlPrefix() . '.students.'
                    : 'principal.students.';
            ?>
            <a href="<?php echo e(route($studentPrefix . 'edit', $student)); ?>" class="btn btn-primary">
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
        <?php endif; ?>
        <a href="<?php echo e(route($routePrefix . 'index')); ?>" class="btn btn-ghost">
            &larr; Back to Directory
        </a>
    </div>
</div>

<!-- STUDENT DETAILS SECTIONS -->
<div class="card mb-6">
    <div class="card-header">
        <div class="card-title">👤 1. Personal &amp; Identification Details</div>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="info-box">
            <div class="info-label">Full Name</div>
            <div class="info-val"><?php echo e($student->full_name); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Email Address</div>
            <div class="info-val"><?php echo e($student->email); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Phone Number</div>
            <div class="info-val"><?php echo e($student->phone ?? 'N/A'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Date of Birth</div>
            <div class="info-val"><?php echo e($student->date_of_birth ? $student->date_of_birth->format('d M, Y') : 'N/A'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Blood Group</div>
            <div class="info-val"><?php echo e($student->blood_group ?? 'N/A'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Student B-Form / CNIC</div>
            <div class="info-val" style="color:#059669"><?php echo e($student->student_bform_cnic ?? 'N/A'); ?></div>
        </div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <div class="card-title" style="color:#d97706">👨‍👧‍👦 2. Father &amp; Guardian Details</div>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="info-box">
            <div class="info-label">Father / Guardian Name</div>
            <div class="info-val"><?php echo e($student->father_guardian_name ?? 'N/A'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Guardian Phone</div>
            <div class="info-val"><?php echo e($student->guardian_phone ?? 'N/A'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Father / Guardian CNIC</div>
            <div class="info-val" style="color:#d97706"><?php echo e($student->father_guardian_cnic ?? 'N/A'); ?></div>
        </div>
    </div>

    <div class="info-box">
        <div class="info-label">Residential Address</div>
        <div class="info-val"><?php echo e($student->address ?? 'N/A'); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title" style="color:#9333ea">💳 3. Financial Ledger &amp; Issued Fee Vouchers</div>
    </div>

    <div class="detail-grid detail-grid-2 mb-6">
        <div class="info-box">
            <div class="info-label">Monthly Base Tuition Fee</div>
            <div class="info-val">PKR <?php echo e(number_format($student->base_fee ?? 0)); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">FBR Tax Filer Status</div>
            <div class="info-val">
                <?php if($student->guardian_tax_status === 'filer'): ?>
                    <span class="badge badge-green">Filer (Discounted Tax Rate)</span>
                <?php else: ?>
                    <span class="badge badge-yellow">Non-Filer (Standard Tax Rate)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="font-family:'Outfit',sans-serif;font-weight:800;color:#0f172a;margin-bottom:12px">Issued Fee Invoices</div>
    <?php if($student->invoices->isEmpty()): ?>
        <div style="padding:20px;text-align:center;color:#64748b;background:#f8fafc;border-radius:10px;border:1px dashed #cbd5e1">
            No fee invoices issued yet for this student.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice Title</th>
                    <th>Fee Period</th>
                    <th>Amount (PKR)</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $student->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong style="color:#0f172a"><?php echo e($inv->title); ?></strong></td>
                        <td><?php echo e($inv->fee_month); ?></td>
                        <td style="color:#059669;font-weight:800">PKR <?php echo e(number_format($inv->amount_pkr)); ?></td>
                        <td>
                            <?php if($inv->status === 'paid'): ?>
                                <span class="badge badge-green">PAID</span>
                            <?php else: ?>
                                <span class="badge badge-yellow">UNPAID</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($inv->due_date ? $inv->due_date->format('d M, Y') : 'N/A'); ?></td>
                        <td>
                            <?php if($inv->pdf_path): ?>
                                <a href="<?php echo e(Storage::url($inv->pdf_path)); ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                                    📥 PDF Voucher
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\directory\student-show.blade.php ENDPATH**/ ?>
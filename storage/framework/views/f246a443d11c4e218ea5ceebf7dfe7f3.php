

<?php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }
?>

<?php $__env->startSection('title', 'Password Reset Requests'); ?>
<?php $__env->startSection('page-title', 'Password Reset Requests'); ?>
<?php $__env->startSection('page-subtitle', 'Review and resolve password reset requests from campus students and faculty.'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);color:#10b981;padding:12px 16px;border-radius:10px;margin-bottom:20px">
        <span>✅ <?php echo e(session('success')); ?></span>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.4);color:#ef4444;padding:12px 16px;border-radius:10px;margin-bottom:20px">
        <span>⚠️ <?php echo e(session('error')); ?></span>
    </div>
<?php endif; ?>

<div class="card" style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
        <div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:700;color:#0f172a;margin:0">
                🔑 Password Reset Requests
            </h2>
            <span style="font-size:12px;color:#64748b">
                Review, approve, or reject account reset requests submitted by students and faculty.
            </span>
        </div>
        <span class="badge" style="background:rgba(0,206,209,0.15);border:1px solid rgba(0,206,209,0.3);color:#00ced1;padding:6px 12px;border-radius:20px;font-weight:700;font-size:11px">
            <?php echo e($resetRequests->total()); ?> TOTAL REQUESTS
        </span>
    </div>

    <div style="overflow-x:auto;border-radius:12px;border:1px solid var(--border)">
        <table class="horiz-matrix-table" style="width:100%">
            <thead>
                <tr>
                    <th style="text-align:left;padding-left:14px">USER / IDENTIFIER</th>
                    <th style="text-align:left">ROLE</th>
                    <th style="text-align:left">STATUS</th>
                    <th style="text-align:left">REQUESTED TIME</th>
                    <th style="text-align:right;padding-right:14px">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $resetRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="<?php echo e($request->isPending() ? 'background: rgba(245, 158, 11, 0.08);' : ''); ?>">
                        <td style="padding:14px">
                            <div style="font-weight: 700; color: #fff;"><?php echo e($request->user->name); ?></div>
                            <div style="font-size: 11px; color: #38bdf8;"><?php echo e($request->user->identifier ?? $request->user->email); ?></div>
                        </td>
                        <td style="padding:14px">
                            <span class="badge" style="background:<?php echo e($request->user->role === 'teacher' ? 'rgba(56,189,248,0.15)' : 'rgba(16,185,129,0.15)'); ?>;border:1px solid <?php echo e($request->user->role === 'teacher' ? 'rgba(56,189,248,0.3)' : 'rgba(16,185,129,0.3)'); ?>;color:<?php echo e($request->user->role === 'teacher' ? '#38bdf8' : '#10b981'); ?>;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                <?php echo e(strtoupper($request->user->role)); ?>

                            </span>
                        </td>
                        <td style="padding:14px">
                            <?php if($request->isPending()): ?>
                                <span class="badge" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#f59e0b;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ⚡ PENDING RESET
                                </span>
                            <?php elseif($request->status === 'completed'): ?>
                                <span class="badge" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ✅ COMPLETED
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ❌ DENIED
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px; padding:14px">
                            <?php echo e($request->created_at->diffForHumans()); ?>

                        </td>
                        <td style="text-align: right; padding-right:14px">
                            <?php if($request->isPending()): ?>
                                <a href="<?php echo e(route($routePrefix . 'password-resets.show', $request)); ?>" class="btn-primary" style="padding:6px 14px;font-size:12px;border-radius:8px">
                                    Process Request &rarr;
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route($routePrefix . 'password-resets.show', $request)); ?>" style="color:#94a3b8;font-size:12px">
                                    View Details
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 36px;">
                            🔑 No pending password reset requests in queue.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <?php echo e($resetRequests->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\password-resets\index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Emergency Reset Queue'); ?>
<?php $__env->startSection('page-title', 'Emergency Password Reset Queue'); ?>
<?php $__env->startSection('page-subtitle', 'Authorize and process pending password reset requests from Institute Principals'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <span>✅ <?php echo e(session('success')); ?></span>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error">
        <span>⚠️ <?php echo e(session('error')); ?></span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🔑 Pending Principal Password Reset Requests</div>
            <div class="card-subtitle">Review, approve, or deny reset requests transmitted by Institute Principals</div>
        </div>
        <span class="badge badge-purple" style="font-size:11px;font-weight:700"><?php echo e($resetRequests->total()); ?> TOTAL REQUESTS</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>USER / IDENTIFIER</th>
                <th>ROLE</th>
                <th>INSTITUTE</th>
                <th>STATUS</th>
                <th>REQUESTED TIME</th>
                <th style="text-align: right;">ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $resetRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr style="<?php echo e($request->isPending() ? 'background: #fffbeb;' : ''); ?>">
                    <td>
                        <div style="font-weight: 800; color: #0f172a; font-size: 14.5px;"><?php echo e($request->user->name); ?></div>
                        <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-top: 2px;"><?php echo e($request->user->identifier ?? $request->user->email); ?></div>
                    </td>
                    <td>
                        <span class="badge <?php echo e($request->user->role === 'principal' ? 'badge-purple' : 'badge-cyan'); ?>">
                            <?php echo e(strtoupper($request->user->role)); ?>

                        </span>
                    </td>
                    <td>
                        <div style="color: #334155; font-size: 13.5px; font-weight: 600;"><?php echo e($request->institute->name ?? '—'); ?></div>
                    </td>
                    <td>
                        <?php if($request->isPending()): ?>
                            <span class="badge badge-amber">⚡ PENDING APPROVAL</span>
                        <?php elseif($request->status === 'completed'): ?>
                            <span class="badge badge-green">✅ COMPLETED</span>
                        <?php else: ?>
                            <span class="badge badge-rose">❌ DENIED</span>
                        <?php endif; ?>
                    </td>
                    <td style="color: #64748b; font-size: 13px; font-weight: 600;">
                        <?php echo e($request->created_at->diffForHumans()); ?>

                    </td>
                    <td style="text-align: right;">
                        <?php if($request->isPending()): ?>
                            <a href="<?php echo e(route('global-admin.password-resets.show', $request)); ?>" class="btn btn-primary btn-sm">
                                <span>Process Request</span>
                                <span>&rarr;</span>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('global-admin.password-resets.show', $request)); ?>" class="btn btn-secondary btn-sm">
                                <span>View Log</span>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 36px; font-weight: 500;">
                        🔑 No emergency password reset requests currently in queue.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <?php echo e($resetRequests->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\password-resets\index.blade.php ENDPATH**/ ?>
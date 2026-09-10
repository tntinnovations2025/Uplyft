<?php $__env->startSection('title', 'Process Password Reset'); ?>
<?php $__env->startSection('page-title', 'Authorize & Reset Principal Password'); ?>
<?php $__env->startSection('page-subtitle', 'Execute password reset or deny request for user credential recovery'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 800px; margin: 0 auto;">

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

    <!-- Details Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">👤 Reset Request Details</div>
            <span class="badge <?php echo e($notification->isPending() ? 'badge-amber' : 'badge-purple'); ?>">
                <?php echo e(strtoupper($notification->status)); ?>

            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; font-size: 13px;">
            <div>
                <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Account Name</div>
                <div style="color: #0f172a; font-weight: 800; font-size: 15px; margin-top: 4px;"><?php echo e($notification->user->name); ?></div>
            </div>
            <div>
                <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Email / Account Identifier</div>
                <div style="color: #0284c7; font-weight: 700; font-size: 14px; margin-top: 4px;"><?php echo e($notification->user->identifier ?? $notification->user->email); ?></div>
            </div>
            <div>
                <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">System Role</div>
                <div style="margin-top: 4px;"><span class="badge badge-purple"><?php echo e(strtoupper($notification->user->role)); ?></span></div>
            </div>
            <div>
                <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Institute</div>
                <div style="color: #0f172a; font-weight: 700; margin-top: 4px;"><?php echo e($notification->institute->name ?? '—'); ?></div>
            </div>
            <div>
                <div style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Request Timestamp</div>
                <div style="color: #0f172a; font-weight: 700; margin-top: 4px;"><?php echo e($notification->created_at->format('M d, Y h:i A')); ?></div>
            </div>
        </div>
    </div>

    <?php if($notification->isPending()): ?>
        <!-- Set New Password Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div>
                    <div class="card-title">🔑 Issue New Principal Password</div>
                    <div class="card-subtitle">Set temporary or new security password for <?php echo e($notification->user->name); ?></div>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('global-admin.password-resets.execute', $notification)); ?>">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="new_password">New Security Password</label>
                    <input id="new_password" type="password" name="new_password" required placeholder="Enter a new strong password (e.g. Secure@Pass123)" />
                    <p style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Must include uppercase, lowercase, number, and special character.
                    </p>
                    <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span style="color: var(--danger); font-size: 12px; display: block; margin-top: 4px;"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Security Password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" required placeholder="Re-type new security password..." />
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <a href="<?php echo e(route('global-admin.password-resets.index')); ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span>Authorize &amp; Reset Password</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Deny Request Card -->
        <div class="card" style="border-color: #fecaca; background: #fff;">
            <div class="card-header" style="border-bottom-color: #fee2e2;">
                <div class="card-title" style="color: var(--danger);">❌ Deny Reset Request</div>
            </div>

            <form method="POST" action="<?php echo e(route('global-admin.password-resets.deny', $notification)); ?>">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="notes">Denial Reason / Administrative Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Enter reason for denying this request (optional)..."><?php echo e(old('notes')); ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-danger">
                        Deny Request
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; color: #64748b; font-weight: 500;">
            This password reset request has been <strong><?php echo e(strtoupper($notification->status)); ?></strong>.
            <?php if($notification->processedBy): ?>
                <div style="font-size: 12px; margin-top: 6px; color: #0f172a;">
                    Processed by <strong><?php echo e($notification->processedBy->name); ?></strong> on <?php echo e($notification->processed_at->format('M d, Y h:i A')); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\password-resets\show.blade.php ENDPATH**/ ?>
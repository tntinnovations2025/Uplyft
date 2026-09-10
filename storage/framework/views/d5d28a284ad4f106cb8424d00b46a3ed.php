

<?php $__env->startSection('title', 'Account Security & Credentials'); ?>
<?php $__env->startSection('breadcrumb', 'Security Settings'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 800px; margin: 0 auto;">

    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 800; color: #0f172a;">
                🔒 Account Security &amp; Credentials
            </h1>
            <p style="font-size: 13px; color: #64748b; margin-top: 4px;">
                Update your account password and security credentials.
            </p>
        </div>
        <span class="badge badge-green">INSTITUTE PRINCIPAL ACCESS</span>
    </div>

    <!-- Alert status -->
    <?php if(session('status') === 'password-updated'): ?>
        <div class="alert alert-success">
            <span>✅ Security Password successfully updated!</span>
        </div>
    <?php endif; ?>

    <?php if($errors->updatePassword->any()): ?>
        <div class="alert alert-error">
            <span>❌ Password update failed. Please review errors below.</span>
        </div>
    <?php endif; ?>

    <!-- Password Change Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🔑 Change Password (Direct Verification)</div>
            <span style="font-size: 11px; color: #6ee7b7; font-weight: 700;">AUTHENTICATED ACCESS</span>
        </div>

        <form method="POST" action="<?php echo e(route('password.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('put'); ?>

            <!-- Current Password -->
            <div class="form-group">
                <label for="current_password">Existing / Current Security Password</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" placeholder="Enter your current password..." />
                <?php if($errors->updatePassword->has('current_password')): ?>
                    <span style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;">
                        <?php echo e($errors->updatePassword->first('current_password')); ?>

                    </span>
                <?php endif; ?>
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label for="password">New Security Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Enter new strong password..." />
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    Must be at least 8 characters and contain uppercase, lowercase, numbers, and special characters.
                </p>
                <?php if($errors->updatePassword->has('password')): ?>
                    <span style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;">
                        <?php echo e($errors->updatePassword->first('password')); ?>

                    </span>
                <?php endif; ?>
            </div>

            <!-- Confirm New Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm New Security Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Re-type new security password..." />
                <?php if($errors->updatePassword->has('password_confirmation')): ?>
                    <span style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;">
                        <?php echo e($errors->updatePassword->first('password_confirmation')); ?>

                    </span>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border);">
                <span style="font-size: 12px; color: var(--text-muted);">
                    🛡️ Password changes take effect immediately across all sessions.
                </span>
                <button type="submit" class="btn btn-primary">
                    <span>Update Executive Password</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Banner for Forgotten Passwords -->
    <div class="card" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.05);">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
            <span style="font-size: 24px;">💡</span>
            <div>
                <div style="font-weight: 700; color: var(--warning); font-size: 14px; margin-bottom: 4px;">
                    Forgot your current password?
                </div>
                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                    If you cannot remember your existing password, log out and click <strong>"Forgot Password?"</strong> on the Login page. A reset notification will be dispatched to the Global Super Administrator.
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\security\edit.blade.php ENDPATH**/ ?>
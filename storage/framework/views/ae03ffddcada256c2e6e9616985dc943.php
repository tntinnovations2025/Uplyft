<?php $__env->startSection('title', 'My Profile Settings'); ?>
<?php $__env->startSection('page-title', 'My Account Profile & Security'); ?>
<?php $__env->startSection('page-subtitle', 'Personal information, password change and account security'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 880px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <?php if(auth()->check() && auth()->user()->isGlobalAdmin()): ?>
        <!-- Card: UPLYFT Master Platform Logo & Global Branding -->
        <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
            <?php echo $__env->make('profile.partials.update-platform-logo-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>

    <!-- Card 1: Profile Information -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <!-- Card 2: Account Security & Email -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        <?php echo $__env->make('profile.partials.update-account-security-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <!-- Card 3: Password Update -->
    <div class="card" style="padding: 28px 32px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);">
        <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->user()->isGlobalAdmin() ? 'global-admin.layouts.app' : (auth()->user()->isPrincipal() ? 'principal.layouts.app' : 'layouts.app'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\profile\edit.blade.php ENDPATH**/ ?>
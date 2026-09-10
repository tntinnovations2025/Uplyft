<?php $__env->startSection('title', 'Issue Principal Account'); ?>
<?php $__env->startSection('breadcrumb', 'Issue Principal'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 680px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👤 Issue Principal Account</div>
                <div class="card-subtitle">Generate primary login credentials and bind to an Institute</div>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('global-admin.accounts.principals.store')); ?>">
            <?php echo csrf_field(); ?>

            <!-- Name -->
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. Dr. Ahmed Khan" required autofocus />
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="e.g. principal@institute.edu.pk" required />
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Identifier (Optional for Principal) -->
            <div class="form-group">
                <label for="identifier">Employee ID / Custom Identifier (Optional)</label>
                <input id="identifier" type="text" name="identifier" value="<?php echo e(old('identifier')); ?>" placeholder="e.g. PRIN-001, ADM#101" />
                <?php $__errorArgs = ['identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Institute -->
            <div class="form-group">
                <label for="institute_id">Assign to Institute *</label>
                <select id="institute_id" name="institute_id" required>
                    <option value="">-- Select Institute --</option>
                    <?php $__currentLoopData = $institutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($institute->id); ?>" <?php echo e(old('institute_id') == $institute->id ? 'selected' : ''); ?>>
                            🏫 <?php echo e($institute->name); ?> (<?php echo e($institute->city ?? 'Main Campus'); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['institute_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password *</label>
                <input id="password" type="password" name="password" required placeholder="Enter strong password (min 8 characters)..." />
                <p style="font-size:11px;color:#64748b;margin-top:4px">Must contain uppercase, lowercase, number, and special character.</p>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password *</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Re-type password to confirm..." />
                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:28px">
                <a href="<?php echo e(route('global-admin.accounts.principals.index')); ?>" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>Create Principal Account</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\accounts\create-principal.blade.php ENDPATH**/ ?>
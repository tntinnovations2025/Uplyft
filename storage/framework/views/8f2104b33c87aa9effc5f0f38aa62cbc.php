
<?php $__env->startSection('breadcrumb', 'Edit: ' . $systemClass->name); ?>
<?php $__env->startSection('title', 'Edit ' . $systemClass->name); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1 class="page-title">Edit Class: <?php echo e($systemClass->name); ?></h1>
    <a href="<?php echo e(route('global-admin.system-classes.index')); ?>" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="<?php echo e(route('global-admin.system-classes.update', $systemClass)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="form-group">
            <label for="sc_name">Class / Program Name *</label>
            <input id="sc_name" type="text" name="name" value="<?php echo e(old('name', $systemClass->name)); ?>" required />
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="form-group">
            <label for="sc_code">Short Code *</label>
            <input id="sc_code" type="text" name="short_code" value="<?php echo e(old('short_code', $systemClass->short_code)); ?>" required />
            <?php $__errorArgs = ['short_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="form-group">
            <label for="sc_type">Education Type *</label>
            <select id="sc_type" name="education_type" required>
                <?php $__currentLoopData = $educationTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e(old('education_type', $systemClass->education_type) === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sc_sort">Sort Order</label>
            <input id="sc_sort" type="number" name="sort_order" value="<?php echo e(old('sort_order', $systemClass->sort_order)); ?>" min="0" max="255" />
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1" style="width:auto;accent-color:var(--accent)"
                    <?php echo e(old('is_active', $systemClass->is_active) ? 'checked' : ''); ?>>
                &nbsp; Active
            </label>
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">💾 Save</button>
            <a href="<?php echo e(route('global-admin.system-classes.index')); ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\system-classes\edit.blade.php ENDPATH**/ ?>
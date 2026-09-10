
<?php $__env->startSection('breadcrumb', 'Add System Class'); ?>
<?php $__env->startSection('title', 'Add System Class'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1 class="page-title">Add System Class / Program</h1>
    <a href="<?php echo e(route('global-admin.system-classes.index')); ?>" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="<?php echo e(route('global-admin.system-classes.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="sc_name">Class / Program Name *</label>
            <input id="sc_name" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. Grade 10, ACCA - FA1, 1st Year" required />
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
            <label for="sc_code">Short Code * (unique identifier)</label>
            <input id="sc_code" type="text" name="short_code" value="<?php echo e(old('short_code')); ?>" placeholder="e.g. G10, ACCA-FA1" required />
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
                <option value="<?php echo e($val); ?>" <?php echo e(old('education_type') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sc_sort">Display Sort Order</label>
            <input id="sc_sort" type="number" name="sort_order" value="<?php echo e(old('sort_order', 0)); ?>" min="0" max="255" />
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">✅ Add Class</button>
            <a href="<?php echo e(route('global-admin.system-classes.index')); ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\system-classes\create.blade.php ENDPATH**/ ?>
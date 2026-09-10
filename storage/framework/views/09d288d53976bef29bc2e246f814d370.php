<?php $__env->startSection('breadcrumb', 'System Classes'); ?>
<?php $__env->startSection('title', 'System Classes'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">System Classes &amp; Programs</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Configure global class templates, short codes, and educational stream mappings.
        </p>
    </div>
    <a href="<?php echo e(route('global-admin.system-classes.create')); ?>" class="btn btn-primary">➕ Add Class</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Class Name</th>
                <th>Short Code</th>
                <th>Education Type</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr style="<?php echo e($cls->trashed() ? 'opacity:.45;' : ''); ?>">
                <td style="color:#64748b;font-weight:600"><?php echo e($cls->id); ?></td>
                <td style="font-weight:800;color:#0f172a;font-size:14.5px"><?php echo e($cls->name); ?></td>
                <td><code class="badge badge-cyan"><?php echo e($cls->short_code); ?></code></td>
                <td style="color:#64748b;font-size:13px;font-weight:600"><?php echo e($educationTypeLabels[$cls->education_type] ?? $cls->education_type); ?></td>
                <td style="color:#64748b;font-weight:600"><?php echo e($cls->sort_order); ?></td>
                <td>
                    <?php if($cls->trashed()): ?>
                        <span class="badge badge-red">Deleted</span>
                    <?php elseif($cls->is_active): ?>
                        <span class="badge badge-green">Active</span>
                    <?php else: ?>
                        <span class="badge badge-red">Inactive</span>
                    <?php endif; ?>
                </td>
                <td style="display:flex;gap:6px">
                    <?php if(!$cls->trashed()): ?>
                        <a href="<?php echo e(route('global-admin.system-classes.edit', $cls)); ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <form method="POST" action="<?php echo e(route('global-admin.system-classes.destroy', $cls)); ?>"
                              onsubmit="return confirm('Deactivate this class?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    <?php else: ?>
                        <span style="color:#64748b;font-size:13px;font-weight:500">Deleted</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b;padding:40px;font-weight:500">
                    No classes defined yet. <a href="<?php echo e(route('global-admin.system-classes.create')); ?>" style="color:#0284c7;font-weight:700">Add one →</a>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\system-classes\index.blade.php ENDPATH**/ ?>
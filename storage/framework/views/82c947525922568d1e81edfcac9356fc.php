

<?php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }
?>

<?php $__env->startSection('title', 'Section Allocation'); ?>
<?php $__env->startSection('breadcrumb', 'Section Allocation'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Section Allocation</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Partition classes or professional modules into manageable student subsections (e.g., 10-A, 10-B, FA1-Sec 1).
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Class Sections Roster -->
    <div style="display:flex;flex-direction:column;gap:20px">
        <?php if($classes->count() > 0): ?>
            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card" style="margin-bottom:0">
                <div class="card-header">
                    <div class="card-title">Class: <?php echo e($class->custom_name); ?></div>
                    <span class="badge badge-purple"><?php echo e($class->sections->count()); ?> Sections</span>
                </div>

                <?php if($class->sections->count() > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Student Capacity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($section->section_name); ?></strong></td>
                            <td><?php echo e($section->capacity); ?> Seats</td>
                            <td>
                                <form method="POST" action="<?php echo e(route($routePrefix . 'sections.destroy', $section)); ?>" onsubmit="return confirm('Remove section?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color:var(--text-muted);font-size:13px">No sections created for this class yet.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
        <div class="card">
            <p style="color:var(--text-muted);font-size:14px">Please provision classes first in order to allocate sections.</p>
            <a href="<?php echo e(route($routePrefix . 'classes-subjects.index')); ?>" class="btn btn-primary btn-sm" style="margin-top:12px">Provision Classes &rarr;</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Create Section Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">➕ Create New Section</div>
            </div>

            <form method="POST" action="<?php echo e(route($routePrefix . 'sections.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="institute_class_id">Target Class / Module *</label>
                    <select id="institute_class_id" name="institute_class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->custom_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['institute_class_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="section_name">Section Name *</label>
                    <input id="section_name" type="text" name="section_name" placeholder="e.g. 10-A, Section 1" required value="<?php echo e(old('section_name')); ?>">
                    <?php $__errorArgs = ['section_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="capacity">Student Capacity *</label>
                    <input id="capacity" type="number" name="capacity" value="40" min="1" max="200" required>
                    <?php $__errorArgs = ['capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Create Section</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\sections\index.blade.php ENDPATH**/ ?>
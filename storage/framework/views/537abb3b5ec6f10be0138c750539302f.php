<?php $__env->startSection('title', 'Teachers Directory'); ?>
<?php $__env->startSection('page-header', 'Teacher Directory'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <div class="liquid-glass-card p-6 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200/90 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 font-display">Onboarded Teachers</h2>
            <p class="text-xs text-slate-500 font-medium">Total Faculty: <span class="badge badge-pink text-xs font-bold"><?php echo e($teachers->count()); ?></span></p>
        </div>
        <a href="<?php echo e(route('teachers.onboarding')); ?>" class="btn-primary py-2.5 px-5 text-sm font-extrabold shadow-md inline-flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Onboard New Teacher</span>
        </a>
    </div>

    <div class="liquid-glass-card p-0 overflow-hidden border border-slate-200/90 shadow-2xs">
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-700 font-extrabold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Employee ID</th>
                        <th class="py-3.5 px-6">Teacher Name</th>
                        <th class="py-3.5 px-6">Specialization</th>
                        <th class="py-3.5 px-6">Experience</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100 bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-6 font-bold font-mono text-pink-700"><?php echo e($teacher->employee_id); ?></td>
                            <td class="py-3.5 px-6 font-extrabold text-slate-900"><?php echo e($teacher->first_name); ?> <?php echo e($teacher->last_name); ?></td>
                            <td class="py-3.5 px-6 font-semibold text-slate-600"><?php echo e($teacher->specialization_subjects ?? 'N/A'); ?></td>
                            <td class="py-3.5 px-6 font-medium text-slate-500"><?php echo e($teacher->years_of_experience ? $teacher->years_of_experience . ' Years' : 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400 text-sm font-medium">No teachers onboarded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\admin\teachers_directory.blade.php ENDPATH**/ ?>
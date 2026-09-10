<?php $__env->startSection('title', 'Course Catalog & Subjects'); ?>
<?php $__env->startSection('page-header', 'Course Catalog'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2 font-display">
                <i class="fa-solid fa-book-bookmark text-pink-600"></i>
                <span>Enrolled Subjects</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Class: <strong class="text-pink-700 font-bold"><?php echo e($classSection?->instituteClass?->custom_name ?? 'Not Assigned'); ?></strong> 
                | Section: <strong class="text-pink-700 font-bold"><?php echo e($classSection?->section_name ?? 'N/A'); ?></strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-pink text-xs font-bold">
                <i class="fa-solid fa-layer-group"></i> <?php echo e($subjects->count()); ?> Active <?php echo e(Str::plural('Subject', $subjects->count())); ?>

            </span>
        </div>
    </div>

    <?php if($subjects->isEmpty()): ?>
        <div class="liquid-glass-card p-12 text-center border border-dashed border-slate-300 shadow-2xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800 font-display">No Enrolled Subjects Found</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto font-medium">
                You are currently not enrolled in any active class section subjects for this term. Please contact your campus administration.
            </p>
        </div>
    <?php else: ?>
        <!-- Course Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $teacherAssignment = $subject->teacherAssignments->first();
                    $teacherName = $teacherAssignment?->teacher?->name ?? 'Faculty Unassigned';
                ?>
                <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group border border-slate-200/90 hover:border-pink-300 shadow-2xs hover:shadow-md transition-all duration-300 hover:-translate-y-1 bg-white">
                    <div class="flex items-start justify-between relative z-10">
                        <span class="badge badge-pink font-mono text-[10px] font-extrabold uppercase tracking-wider">
                            <?php echo e($subject->subject_code ?? 'SUB-'.str_pad($subject->id, 3, '0', STR_PAD_LEFT)); ?>

                        </span>
                        <?php if($subject->credit_hours): ?>
                            <span class="text-[11px] text-slate-500 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-clock text-pink-600"></i><?php echo e($subject->credit_hours); ?> Cr. Hrs
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="relative z-10">
                        <h3 class="font-extrabold text-slate-900 text-lg group-hover:text-pink-600 transition-colors font-display">
                            <?php echo e($subject->subject_name); ?>

                        </h3>
                        <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mt-1.5">
                            <i class="fa-solid fa-user-tie text-slate-400 text-[10px]"></i>
                            <span><?php echo e($teacherName); ?></span>
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs text-slate-500 font-medium border-t border-slate-100 pt-3 mt-auto relative z-10">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-file-pdf text-pink-600"></i>
                            <span><strong class="text-slate-800"><?php echo e($subject->materials_count ?? $subject->materials->count()); ?></strong> Books / Notes</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 relative z-10">
                        <a href="<?php echo e(route('lms.chatbot.index', ['subject_id' => $subject->id])); ?>" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-pink-50 text-slate-700 hover:text-pink-700 text-xs font-bold text-center transition flex items-center justify-center gap-1.5 border border-slate-200/80">
                            <i class="fa-solid fa-robot text-pink-600"></i> AI Tutor
                        </a>
                        <a href="<?php echo e(route('lms.practice-test.index', ['subject_id' => $subject->id])); ?>" class="py-2 px-3 rounded-xl btn-primary text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="fa-solid fa-vial"></i> Practice
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\student\courses.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Exam Datesheets'); ?>
<?php $__env->startSection('page-header', 'Exam Datesheet'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2 font-display">
                <i class="fa-solid fa-calendar-days text-pink-600"></i>
                <span>Official Class Examination Datesheet</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Class: <strong class="text-pink-700 font-bold"><?php echo e($classSection?->instituteClass?->custom_name ?? 'Not Assigned'); ?></strong> 
                | Section: <strong class="text-pink-700 font-bold"><?php echo e($classSection?->section_name ?? 'N/A'); ?></strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-slate-700 hover:text-slate-900 border border-slate-300 shadow-2xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print / Save PDF
            </button>
            <span class="badge badge-pink text-xs font-bold">
                <i class="fa-solid fa-graduation-cap"></i> Term Datesheet Schedule
            </span>
        </div>
    </div>

    <?php if($examSchedules->isEmpty()): ?>
        <div class="liquid-glass-card p-12 text-center border border-dashed border-slate-300 shadow-2xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800 font-display">No Exam Datesheet Published Yet</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto font-medium">
                No formal examination datesheet (Midterms / Final Term) has been published for 
                <strong class="text-slate-700"><?php echo e($classSection?->instituteClass?->custom_name); ?> - <?php echo e($classSection?->section_name); ?></strong> by the administration yet.
            </p>
        </div>
    <?php else: ?>
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2 font-display">
                    <i class="fa-solid fa-scroll text-pink-600"></i>
                    <span>Scheduled Term Examinations</span>
                </h3>
                <span class="badge badge-pink text-xs font-bold">
                    <?php echo e($examSchedules->count()); ?> Exam Papers Scheduled
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase text-[11px] font-extrabold">
                            <th class="py-3 px-4">Date &amp; Day</th>
                            <th class="py-3 px-4">Timing &amp; Duration</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Exam Title</th>
                            <th class="py-3 px-4">Exam Type</th>
                            <th class="py-3 px-4 text-right">Total Marks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        <?php $__currentLoopData = $examSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $startDate = $exam->start_time ? $exam->start_time->format('D, M d, Y') : '—';
                                $startTime = $exam->start_time ? $exam->start_time->format('h:i A') : '—';
                                $endTime = $exam->end_time ? $exam->end_time->format('h:i A') : '—';
                                $typeBadge = match($exam->type) {
                                    'midterm' => 'badge-purple',
                                    'final' => 'badge-pink',
                                    default => 'badge-amber',
                                };
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar-check text-pink-600"></i>
                                        <span><?php echo e($startDate); ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-indigo-700 font-mono">⏱️ <?php echo e($startTime); ?> - <?php echo e($endTime); ?></div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5"><?php echo e($exam->duration_minutes ?: 60); ?> Mins</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900">📘 <?php echo e($exam->subject->subject_name ?? 'Subject'); ?></div>
                                    <div class="text-[11px] text-slate-500 font-medium"><?php echo e($exam->subject->subject_code ?? ''); ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?php echo e($exam->title); ?></div>
                                    <?php if($exam->instructions): ?>
                                        <div class="text-[11px] text-slate-500 mt-0.5 max-w-xs truncate font-medium"><?php echo e($exam->instructions); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="badge <?php echo e($typeBadge); ?> text-[11px] font-extrabold uppercase">
                                        <?php echo e(strtoupper($exam->type)); ?>

                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold font-mono text-emerald-700 text-sm">
                                    🎯 <?php echo e($exam->total_marks); ?> pts
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\student\datesheet.blade.php ENDPATH**/ ?>
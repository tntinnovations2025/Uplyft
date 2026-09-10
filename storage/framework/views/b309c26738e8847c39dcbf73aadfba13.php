<?php $__env->startSection('title', 'Attendance Records'); ?>
<?php $__env->startSection('page-header', 'Attendance Records'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="liquid-glass-card p-6 flex flex-col items-center justify-center gap-4 border border-slate-200/90 shadow-2xs">
            <h3 class="text-slate-500 text-xs font-extrabold uppercase tracking-wider w-full text-center">Overall Attendance</h3>
            
            <div class="relative w-32 h-32 rounded-full flex items-center justify-center shadow-inner" style="background: conic-gradient(#10b981 0% <?php echo e($percentage); ?>%, #e2e8f0 <?php echo e($percentage); ?>% 100%);">
                <div class="absolute inset-0 m-3.5 bg-white rounded-full flex items-center justify-center flex-col shadow-2xs">
                    <span class="text-2xl font-extrabold text-slate-900 font-display"><?php echo e($percentage); ?>%</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-600 font-medium">Total Sessions: <strong class="text-slate-900"><?php echo e($totalDays); ?></strong> | Attended: <strong class="text-emerald-700"><?php echo e($presentDays); ?></strong></p>
        </div>

        <!-- Detailed Daily Logs -->
        <div class="liquid-glass-card p-6 md:col-span-2 border border-slate-200/90 shadow-2xs">
            <h3 class="text-lg font-extrabold text-slate-900 mb-4 font-display flex items-center gap-2">
                <i class="fa-solid fa-clipboard-user text-pink-600"></i> Daily Attendance Logs
            </h3>
            <div class="overflow-y-auto max-h-[300px] pr-2 space-y-2.5 custom-scrollbar">
                <?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $st = strtolower($log->status);
                    ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/90 hover:bg-pink-50/30 transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-regular fa-calendar text-pink-600"></i>
                            <span class="text-xs font-bold text-slate-800"><?php echo e(\Carbon\Carbon::parse($log->date)->format('M d, Y')); ?></span>
                        </div>
                        <div>
                            <?php if($st === 'present'): ?>
                                <span class="badge badge-emerald text-xs font-bold">Present</span>
                            <?php elseif($st === 'absent'): ?>
                                <span class="badge badge-rose text-xs font-bold">Absent</span>
                            <?php elseif($st === 'late'): ?>
                                <span class="badge badge-amber text-xs font-bold">Late</span>
                            <?php else: ?>
                                <span class="badge badge-slate text-xs font-bold">Leave</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-8 text-slate-500 text-xs font-medium">
                        No attendance records found for this term.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subject-Wise Attendance Breakdown -->
    <?php if(isset($subjectWiseAttendance) && $subjectWiseAttendance->isNotEmpty()): ?>
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs">
            <h3 class="text-lg font-extrabold text-slate-900 mb-4 flex items-center gap-2 font-display">
                <i class="fa-solid fa-book text-pink-600"></i> Subject-Wise Attendance Percentage
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $subjectWiseAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subAtt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $subPct = $subAtt['percentage'];
                        $badgeClass = $subPct >= 75 ? 'badge-emerald' : ($subPct >= 50 ? 'badge-amber' : 'badge-rose');
                        $barColor = $subPct >= 75 ? '#10b981' : ($subPct >= 50 ? '#f59e0b' : '#f43f5e');
                    ?>
                    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-pink-700 font-bold uppercase tracking-wider font-mono"><?php echo e($subAtt['subject_code']); ?></div>
                            <div class="text-sm font-extrabold text-slate-900 mt-0.5"><?php echo e($subAtt['subject_name']); ?></div>
                            <div class="text-xs text-slate-500 font-medium mt-1">
                                <?php echo e($subAtt['present_sessions']); ?> / <?php echo e($subAtt['total_sessions']); ?> Sessions
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge <?php echo e($badgeClass); ?> text-xs font-extrabold">
                                <?php echo e($subPct); ?>%
                            </span>
                            <div class="w-16 h-1.5 bg-slate-200 rounded-full mt-2 overflow-hidden">
                                <div class="h-full rounded-full" style="width: <?php echo e($subPct); ?>%; background-color: <?php echo e($barColor); ?>;"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\student\attendance.blade.php ENDPATH**/ ?>
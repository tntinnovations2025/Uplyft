<?php $__env->startSection('title', 'Fee Management'); ?>
<?php $__env->startSection('page-header', 'Invoice Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <div class="liquid-glass-card p-6 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200/90 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 font-display">Student Invoices</h2>
            <p class="text-xs text-slate-500 font-medium">Track and manage fee payments across campus.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="p-4 rounded-xl alert-success text-xs font-bold flex items-center gap-2 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600"></i> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="liquid-glass-card p-0 overflow-hidden border border-slate-200/90 shadow-2xs">
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-700 font-extrabold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Invoice ID</th>
                        <th class="py-3.5 px-6">Student Name</th>
                        <th class="py-3.5 px-6 text-right">Amount (PKR)</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100 bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-6 font-bold font-mono text-pink-700">#<?php echo e(str_pad($invoice->id, 5, '0', STR_PAD_LEFT)); ?></td>
                            <td class="py-3.5 px-6">
                                <div class="font-extrabold text-slate-900"><?php echo e($invoice->student->first_name ?? 'Unknown'); ?> <?php echo e($invoice->student->last_name ?? ''); ?></div>
                                <div class="text-[11px] font-mono font-bold text-slate-500"><?php echo e($invoice->student->roll_number ?? ''); ?></div>
                            </td>
                            <td class="py-3.5 px-6 text-right font-extrabold text-slate-900 font-mono"><?php echo e(number_format($invoice->amount_pkr, 2)); ?></td>
                            <td class="py-3.5 px-6 text-center">
                                <?php if($invoice->status === 'paid'): ?>
                                    <span class="badge badge-emerald text-xs font-bold">Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-rose text-xs font-bold">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <?php if($invoice->status === 'unpaid'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.invoices.mark-paid', $invoice->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs">
                                            Mark Paid
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-emerald-700 font-bold"><i class="fa-solid fa-check"></i> Cleared</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm font-medium">No invoices generated yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\admin\fee_management.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'My Fee Ledger'); ?>
<?php $__env->startSection('page-header', 'Fee Ledger'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 shadow-2xs glass-specular-top">
        <h2 class="text-xl font-extrabold text-slate-900 font-display">Fee Statement</h2>
        <p class="text-xs text-slate-500 font-medium mt-0.5">View your current outstanding balance and tax details.</p>
    </div>

    <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs">
        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-700 font-extrabold uppercase tracking-wider">
                        <th class="py-3.5 px-5">Description</th>
                        <th class="py-3.5 px-5 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="py-3.5 px-5 font-semibold text-slate-800">Base Tuition Fee</td>
                        <td class="py-3.5 px-5 text-right font-bold text-slate-900 font-mono"><?php echo e($currencySymbol ?? 'PKR'); ?> <?php echo e(number_format($baseFee, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-5 font-semibold text-slate-800">
                            Tax (<?php echo e($isFiler ? 'Filer - 0%' : 'Non-Filer - 5%'); ?>)
                            <span class="block text-[11px] text-slate-500 font-normal">Based on guardian's tax status</span>
                        </td>
                        <td class="py-3.5 px-5 text-right font-bold text-slate-900 font-mono"><?php echo e($currencySymbol ?? 'PKR'); ?> <?php echo e(number_format($taxAmount, 2)); ?></td>
                    </tr>
                    <tr class="bg-slate-50/80 font-extrabold text-slate-900 border-t-2 border-slate-200">
                        <td class="py-4 px-5 text-base font-display">Total Amount Payable</td>
                        <td class="py-4 px-5 text-right text-base text-pink-700 font-extrabold font-mono"><?php echo e($currencySymbol ?? 'PKR'); ?> <?php echo e(number_format($totalFee, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="#" class="btn-primary inline-flex items-center gap-2 py-3 px-6 text-sm font-extrabold shadow-md">
                <i class="fa-solid fa-download"></i>
                <span>Download Latest PDF Invoice</span>
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\student\fees.blade.php ENDPATH**/ ?>
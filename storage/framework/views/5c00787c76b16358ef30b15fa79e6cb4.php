<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary', // primary, instagram, neon-cyan, neon-violet, neon-magenta, neon-emerald, glass, ghost, danger, outline
    'size' => 'md', // xs, sm, md, lg, xl
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'shimmer' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'variant' => 'primary', // primary, instagram, neon-cyan, neon-violet, neon-magenta, neon-emerald, glass, ghost, danger, outline
    'size' => 'md', // xs, sm, md, lg, xl
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'shimmer' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Size classes
    $sizeClasses = match($size) {
        'xs' => 'px-2.5 py-1.5 text-xs font-semibold rounded-lg gap-1.5',
        'sm' => 'px-3.5 py-2 text-xs font-semibold rounded-xl gap-2',
        'md' => 'px-5 py-2.5 text-sm font-semibold rounded-xl gap-2.5',
        'lg' => 'px-6 py-3 text-base font-semibold rounded-xl gap-3',
        'xl' => 'px-8 py-4 text-lg font-bold rounded-2xl gap-3.5',
        default => 'px-5 py-2.5 text-sm font-semibold rounded-xl gap-2.5',
    };

    // Variant classes
    $variantClasses = match($variant) {
        'primary', 'instagram' => 'bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 text-white shadow-sm shadow-indigo-500/25 hover:shadow-md hover:shadow-indigo-500/30 hover:brightness-105 border border-white/20',
        'neon-cyan' => 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-sm shadow-sky-500/25 hover:brightness-105 border border-sky-400/30',
        'neon-violet' => 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-sm shadow-violet-500/25 hover:brightness-105 border border-violet-400/30',
        'neon-magenta' => 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-sm shadow-indigo-500/25 hover:brightness-105 border border-indigo-400/30',
        'neon-emerald', 'success' => 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm shadow-emerald-500/25 hover:brightness-105 border border-emerald-400/30',
        'glass', 'secondary' => 'bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 hover:border-slate-400 shadow-2xs hover:shadow-xs',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-transparent',
        'danger' => 'bg-gradient-to-r from-rose-600 to-rose-700 text-white shadow-sm shadow-rose-600/25 hover:shadow-md hover:shadow-rose-600/35 border border-rose-400/30 hover:brightness-105',
        'outline' => 'bg-transparent text-indigo-600 border border-indigo-500/40 hover:bg-indigo-50 hover:border-indigo-500 shadow-2xs',
        default => 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-sm shadow-indigo-500/25 hover:shadow-md hover:shadow-indigo-500/30 border border-white/20',
    };

    $baseClasses = "relative inline-flex items-center justify-center select-none cursor-pointer tracking-wide transition-all duration-200 active:scale-[0.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:opacity-50 disabled:pointer-events-none disabled:cursor-not-allowed overflow-hidden group font-bold";
?>

<?php if($href && !$disabled): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}"])); ?>>
        
        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/40 to-transparent pointer-events-none opacity-60 group-hover:opacity-100 transition-opacity"></div>

        <?php if($loading): ?>
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        <?php elseif($icon && $iconPosition === 'left'): ?>
            <i class="<?php echo e($icon); ?> transition-transform duration-200 group-hover:scale-110"></i>
        <?php endif; ?>

        <span class="relative z-10 font-medium"><?php echo e($slot); ?></span>

        <?php if(!$loading && $icon && $iconPosition === 'right'): ?>
            <i class="<?php echo e($icon); ?> transition-transform duration-200 group-hover:translate-x-0.5"></i>
        <?php endif; ?>
    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php if($disabled || $loading): ?> disabled <?php endif; ?> <?php echo e($attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}"])); ?>>
        
        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/40 to-transparent pointer-events-none opacity-60 group-hover:opacity-100 transition-opacity"></div>

        <?php if($loading): ?>
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        <?php elseif($icon && $iconPosition === 'left'): ?>
            <i class="<?php echo e($icon); ?> transition-transform duration-200 group-hover:scale-110"></i>
        <?php endif; ?>

        <span class="relative z-10 font-medium"><?php echo e($slot); ?></span>

        <?php if(!$loading && $icon && $iconPosition === 'right'): ?>
            <i class="<?php echo e($icon); ?> transition-transform duration-200 group-hover:translate-x-0.5"></i>
        <?php endif; ?>
    </button>
<?php endif; ?>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\components\glass-button.blade.php ENDPATH**/ ?>
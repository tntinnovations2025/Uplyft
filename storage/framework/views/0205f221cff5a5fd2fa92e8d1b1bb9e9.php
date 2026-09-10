<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'name' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
    'suffix' => null,
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'size' => 'md', // sm, md, lg
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
    'id' => null,
    'name' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'hint' => null,
    'icon' => null,
    'suffix' => null,
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'size' => 'md', // sm, md, lg
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inputId = $id ?? ($name ? $name . '_' . uniqid() : 'input_' . uniqid());

    // Input height & padding sizes
    $sizeClasses = match($size) {
        'sm' => 'py-2 text-xs',
        'lg' => 'py-3.5 text-base',
        default => 'py-2.5 text-sm',
    };

    // Padding depending on presence of prefix or suffix icons
    $leftPadding = $icon ? 'pl-11' : 'pl-4';
    $rightPadding = $suffix ? 'pr-11' : 'pr-4';

    // State classes (Bright Daylight Mode)
    $stateClasses = $error
        ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 text-rose-900 placeholder-rose-400 bg-rose-50/50'
        : 'border-slate-300 focus:border-pink-500 focus:ring-pink-500/20 text-slate-900 placeholder-slate-400 bg-white hover:border-slate-400 shadow-sm';
?>

<div class="w-full flex flex-col gap-1.5 text-left">
    
    <?php if($label): ?>
        <label for="<?php echo e($inputId); ?>" class="flex items-center justify-between text-xs font-bold text-slate-700 uppercase tracking-wider select-none">
            <span class="flex items-center gap-1">
                <?php echo e($label); ?>

                <?php if($required): ?>
                    <span class="text-rose-500 text-sm leading-none">*</span>
                <?php endif; ?>
            </span>
            <?php if($hint && !$error): ?>
                <span class="text-[11px] font-normal text-slate-400 lowercase"><?php echo e($hint); ?></span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    
    <div class="relative rounded-xl overflow-hidden group">
        
        <?php if($icon): ?>
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-pink-500 transition-colors">
                <i class="<?php echo e($icon); ?> text-sm"></i>
            </div>
        <?php endif; ?>

        
        <input
            id="<?php echo e($inputId); ?>"
            name="<?php echo e($name); ?>"
            type="<?php echo e($type); ?>"
            value="<?php echo e(old($name, $value)); ?>"
            placeholder="<?php echo e($placeholder); ?>"
            <?php if($disabled): ?> disabled <?php endif; ?>
            <?php if($readonly): ?> readonly <?php endif; ?>
            <?php if($required): ?> required <?php endif; ?>
            <?php echo e($attributes->merge([
                'class' => "w-full rounded-xl {$leftPadding} {$rightPadding} {$sizeClasses} {$stateClasses} outline-none transition-all duration-200 font-medium focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed"
            ])); ?>

        />

        
        <?php if($suffix): ?>
            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                <?php echo e($suffix); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <?php if($error): ?>
        <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-0.5 animate-fadeIn">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?php echo e($error); ?></span>
        </p>
    <?php endif; ?>
</div>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\components\glass-input.blade.php ENDPATH**/ ?>
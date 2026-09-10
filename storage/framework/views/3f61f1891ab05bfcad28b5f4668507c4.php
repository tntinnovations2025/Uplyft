



<?php
    $branding = $instituteBranding ?? (object)[
        'is_tenant'        => false,
        'name'             => config('app.name', 'UPLYFT'),
        'logo_url'         => null,
        'icon_url'         => null,
        'has_custom_logo'  => false,
        'has_custom_icon'  => false,
        'initial'          => 'U',
    ];

    // Determine contextual role subtitle
    $roleSubtitle = 'ACADEMIC LMS';
    $isGlobalAdmin = false;
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isGlobalAdmin()) {
            $isGlobalAdmin = true;
            $roleSubtitle = 'GLOBAL GOVERNANCE';
        } elseif ($user->isPrincipal()) {
            $roleSubtitle = 'EXECUTIVE PRINCIPAL SUITE';
        } elseif ($user->isTeacher()) {
            $roleSubtitle = $user->staff_role ? strtoupper($user->staff_role . ' WORKSPACE') : 'STAFF PORTAL';
        } elseif ($user->isStudent()) {
            $roleSubtitle = 'STUDENT WORKSPACE';
        }
    }
?>

<div class="brand-header-container p-3.5 border-b border-[#2A2C30] flex items-center gap-3 relative bg-[#17191C]">
    <?php if(!empty($branding->logo_url)): ?>
        
        <div class="brand-logo-img-wrapper flex-shrink-0 w-11 h-11 rounded-xl bg-[#2A2C30] border border-[#3E4249] p-1 flex items-center justify-center overflow-hidden transition-transform hover:scale-105">
            <img src="<?php echo e($branding->logo_url); ?>" 
                 alt="<?php echo e($branding->name); ?> Logo" 
                 class="w-full h-full object-contain rounded-lg"
                 onerror="this.style.display='none'; document.getElementById('brand-fallback-badge-<?php echo e($branding->initial); ?>').style.display='flex';" />
        </div>
        
        
        <div class="min-w-0 flex-1 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">
                    UPLYFT
                </span>
                <?php if($branding->is_tenant && $branding->name !== 'UPLYFT'): ?>
                    <span class="text-[#8A5A10] font-bold text-xs">—</span>
                    <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]" title="<?php echo e($branding->name); ?>">
                        <?php echo e($branding->name); ?>

                    </span>
                <?php endif; ?>
            </div>
            
        </div>

        
        <div id="brand-fallback-badge-<?php echo e($branding->initial); ?>" style="display:none;" class="items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-[#D48A2E] text-[#1A1200] flex items-center justify-center font-extrabold text-lg border border-[#F0B45D] overflow-hidden flex-shrink-0">
                <span class="font-display"><?php echo e($branding->initial); ?></span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                    <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">UPLYFT</span>
                    <?php if($branding->is_tenant && $branding->name !== 'UPLYFT'): ?>
                        <span class="text-[#8A5A10] font-bold text-xs">—</span>
                        <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]"><?php echo e($branding->name); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        
        <div class="w-11 h-11 rounded-xl bg-[#D48A2E] text-[#1A1200] flex items-center justify-center font-extrabold text-lg border border-[#F0B45D] relative overflow-hidden flex-shrink-0">
            <span class="relative z-10 font-display"><?php echo e($branding->initial ?? 'U'); ?></span>
        </div>
        <div class="min-w-0 flex-1 flex flex-col justify-center">
            <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                <span class="font-extrabold text-[#F9F8F5] tracking-tight text-[15px] font-display">
                    UPLYFT
                </span>
                <?php if($branding->is_tenant && $branding->name !== 'UPLYFT'): ?>
                    <span class="text-[#8A5A10] font-bold text-xs">—</span>
                    <span class="font-bold text-[#F0B45D] text-[13px] font-display truncate max-w-[130px]" title="<?php echo e($branding->name); ?>">
                        <?php echo e($branding->name); ?>

                    </span>
                <?php endif; ?>
            </div>
            
        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\partials\brand-header.blade.php ENDPATH**/ ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'class' => 'w-[19px] h-[19px]']));

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

foreach (array_filter((['name', 'class' => 'w-[19px] h-[19px]']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Normalize name by removing fa-*, fa-solid, fa-regular prefixes if passed
    $key = trim(preg_replace('/^(fa[a-z]?\s+|fa-solid\s+|fa-regular\s+|fa-)/', '', $name));
    $key = str_replace('fa-', '', $key);
?>

<svg viewBox="0 0 24 24" <?php echo e($attributes->merge(['class' => $class])); ?> stroke="currentColor" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
<?php switch($key):
    
    case ('chart-pie'): ?>
        <circle cx="12" cy="12" r="9"/><path d="M12 3 V12 L18 8"/>
        <?php break; ?>

    <?php case ('cubes'): ?>
        <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
        <?php break; ?>

    <?php case ('magnifying-glass'): ?>
    <?php case ('search'): ?>
        <circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.3" y1="15.3" x2="20.5" y2="20.5"/>
        <?php break; ?>

    <?php case ('arrow-right-from-bracket'): ?>
    <?php case ('right-from-bracket'): ?>
    <?php case ('sign-out'): ?>
        <path d="M9 4 H5 a2 2 0 0 0 -2 2 V18 a2 2 0 0 0 2 2 H9"/><path d="M11 12 H21 M21 12 L17 8 M21 12 L17 16"/>
        <?php break; ?>

    
    <?php case ('sitemap'): ?>
        <rect x="9" y="3" width="6" height="5" rx="1.5"/><rect x="3" y="16" width="6" height="5" rx="1.5"/><rect x="15" y="16" width="6" height="5" rx="1.5"/><path d="M12 8 V12 M6 12 H18 M6 12 V16 M18 12 V16"/>
        <?php break; ?>

    <?php case ('square-plus'): ?>
        <rect x="3.5" y="3.5" width="17" height="17" rx="5"/><path d="M12 8 V16 M8 12 H16"/>
        <?php break; ?>

    <?php case ('plus'): ?>
        <path d="M12 5 V19 M5 12 H19"/>
        <?php break; ?>

    <?php case ('building-columns'): ?>
        <path d="M3 10 L12 4 L21 10"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="3" y1="20" x2="21" y2="20"/><line x1="6" y1="10" x2="6" y2="18"/><line x1="10.3" y1="10" x2="10.3" y2="18"/><line x1="13.7" y1="10" x2="13.7" y2="18"/><line x1="18" y1="10" x2="18" y2="18"/>
        <?php break; ?>

    <?php case ('building'): ?>
        <rect x="5" y="4" width="14" height="17" rx="1.5"/><path d="M8 8 H10 M14 8 H16 M8 12 H10 M14 12 H16 M8 16 H10 M14 16 H16"/>
        <?php break; ?>

    <?php case ('calendar-check'): ?>
        <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9 H21"/><path d="M8 3 V6 M16 3 V6"/><path d="M8.5 14.5 L11 17 L16 12"/>
        <?php break; ?>

    
    <?php case ('user-shield'): ?>
        <circle cx="10" cy="8" r="3.5"/><path d="M4 20 C4 15.3 6.7 13 10 13 C10.9 13 11.7 13.15 12.5 13.5"/><path d="M16.5 12.3 L19.5 13.3 V16.2 C19.5 18.4 18.2 19.7 16.5 20.3 C14.8 19.7 13.5 18.4 13.5 16.2 V13.3 Z"/>
        <?php break; ?>

    <?php case ('key'): ?>
        <circle cx="7.5" cy="12" r="4"/><line x1="11.5" y1="12" x2="21" y2="12"/><line x1="17" y1="12" x2="17" y2="16"/><line x1="21" y1="12" x2="21" y2="16"/>
        <?php break; ?>

    <?php case ('user-gear'): ?>
        <circle cx="10" cy="8" r="3.5"/><path d="M4 20 C4 15.3 6.7 13 10 13 C10.9 13 11.7 13.15 12.5 13.5"/><circle cx="17" cy="17" r="2.5"/><path d="M17 12.5 V13.7 M17 20.3 V21.5 M12.5 17 H13.7 M20.3 17 H21.5 M13.9 13.9 L14.7 14.7 M19.3 19.3 L20.1 20.1 M13.9 20.1 L14.7 19.3 M19.3 14.7 L20.1 13.9"/>
        <?php break; ?>

    <?php case ('shield-halved'): ?>
        <path d="M12 3 L19 6 V12 C19 17 16 20 12 21 C8 20 5 17 5 12 V6 Z"/><line x1="12" y1="3" x2="12" y2="21"/>
        <?php break; ?>

    <?php case ('user-plus'): ?>
        <circle cx="9" cy="8" r="3.5"/><path d="M3 20 C3 15.3 5.7 13 9 13 C10 13 10.9 13.2 11.7 13.6"/><path d="M17 8 V14 M14 11 H20"/>
        <?php break; ?>

    <?php case ('users'): ?>
        <circle cx="8" cy="8" r="3"/><path d="M3 20 C3 15.8 5.2 13.7 8 13.7 C9 13.7 9.9 13.9 10.7 14.3"/><circle cx="16" cy="9" r="2.6"/><path d="M12.5 20 C12.8 16.3 14.5 14.5 16.7 14.5 C18.9 14.5 20.6 16.3 20.9 20"/>
        <?php break; ?>

    <?php case ('user-graduate'): ?>
        <circle cx="12" cy="10" r="3.2"/><path d="M6.5 8.5 L12 6 L17.5 8.5 L12 11 Z"/><line x1="17.5" y1="8.5" x2="17.5" y2="12"/><path d="M6 20 C6 16.1 8.7 14 12 14 C15.3 14 18 16.1 18 20"/>
        <?php break; ?>

    <?php case ('chalkboard-user'): ?>
        <rect x="3" y="4" width="18" height="12" rx="1.5"/><circle cx="9" cy="9" r="2"/><path d="M6 14 C6 11.5 7.5 10.5 9 10.5 C10.5 10.5 12 11.5 12 14"/><path d="M9 16 L9 20 M15 16 L15 20"/>
        <?php break; ?>

    
    <?php case ('book-bookmark'): ?>
        <path d="M3 6 C6 5 9 5.3 12 7 C15 5.3 18 5 21 6 V18 C18 17 15 16.7 12 18.4 C9 16.7 6 17 3 18 Z"/><line x1="12" y1="7" x2="12" y2="18.4"/><rect x="10.5" y="3" width="3" height="5"/>
        <?php break; ?>

    <?php case ('clipboard-user'): ?>
        <rect x="5" y="4" width="14" height="17" rx="2"/><rect x="9" y="2" width="6" height="3" rx="1"/><circle cx="12" cy="11" r="2.3"/><path d="M8.5 17 C8.5 14.2 10 13 12 13 C14 13 15.5 14.2 15.5 17"/>
        <?php break; ?>

    <?php case ('clipboard-check'): ?>
        <rect x="5" y="4" width="14" height="17" rx="2"/><rect x="9" y="2" width="6" height="3" rx="1"/><path d="M8.5 12.5 L11 15 L15.5 9.5"/>
        <?php break; ?>

    <?php case ('brain'): ?>
        <path d="M8 5 C5.5 5 4 7 4.5 9.3 C3 10.3 3.2 13 5 14 C4.7 16.3 6.8 18 9 17.5 C9.5 19 12 19.3 13 17.8 C15 18.3 17 16.8 16.7 14.7 C18.7 13.7 18.7 10.7 16.8 9.6 C17.3 7.2 15.3 5 12.8 5.3 C11.8 4 9.3 4 8 5 Z"/><path d="M8.5 8 C9.5 9 9.5 11 8.5 12 M12 7.5 V16.5 M15 9 C14 10 14 12 15 13"/>
        <?php break; ?>

    <?php case ('robot'): ?>
        <rect x="5" y="7" width="14" height="12" rx="3"/><circle cx="9.5" cy="13" r="1.3" style="fill:currentColor"/><circle cx="14.5" cy="13" r="1.3" style="fill:currentColor"/><path d="M9 17 H15"/><path d="M12 7 V4"/><circle cx="12" cy="3" r="1"/><path d="M3 11 V15 M21 11 V15"/>
        <?php break; ?>

    <?php case ('bolt'): ?>
        <path d="M13 3 L6 13 H11 L10 21 L18 10 H13 Z"/>
        <?php break; ?>

    <?php case ('file-pen'): ?>
        <path d="M6 3 H14 L18 7 V21 H6 Z"/><path d="M14 3 V7 H18"/><path d="M9 18 L9.5 15.5 L16 9 L18 11 L11.5 17.5 Z"/>
        <?php break; ?>

    <?php case ('square-poll-vertical'): ?>
        <rect x="3.5" y="3.5" width="17" height="17" rx="3"/><line x1="8" y1="14" x2="8" y2="17"/><line x1="12" y1="10" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/>
        <?php break; ?>

    <?php case ('calendar-week'): ?>
        <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9 H21"/><path d="M8 3 V6 M16 3 V6"/><rect x="5" y="12" width="14" height="3" rx="1"/>
        <?php break; ?>

    <?php case ('file-lines'): ?>
        <path d="M6 3 H14 L18 7 V21 H6 Z"/><path d="M14 3 V7 H18"/><path d="M8.5 12 H15.5 M8.5 15 H15.5 M8.5 18 H13"/>
        <?php break; ?>

    <?php case ('scale-balanced'): ?>
        <path d="M12 3 V21 M6 21 H18 M5 8 H19"/><path d="M5 8 L3 12.5 A2.5 2.5 0 0 0 7.5 12.5 Z"/><path d="M19 8 L17 12.5 A2.5 2.5 0 0 0 21.5 12.5 Z"/>
        <?php break; ?>

    <?php case ('sliders'): ?>
        <line x1="6" y1="4" x2="6" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/><line x1="18" y1="4" x2="18" y2="20"/><circle cx="6" cy="9" r="2"/><circle cx="12" cy="15" r="2"/><circle cx="18" cy="7" r="2"/>
        <?php break; ?>

    <?php case ('file-signature'): ?>
        <path d="M3 5 H21 M3 9 H21 M3 13 H21 M3 17 H21"/>
        <?php break; ?>

    
    <?php case ('calendar-days'): ?>
        <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9 H21"/><path d="M8 3 V6 M16 3 V6"/><path d="M7 13 H9 M11 13 H13 M15 13 H17 M7 17 H9 M11 17 H13 M15 17 H17"/>
        <?php break; ?>

    <?php case ('layer-group'): ?>
        <path d="M12 3 L21 8 L12 13 L3 8 Z"/><path d="M3 12 L12 17 L21 12"/><path d="M3 16 L12 21 L21 16"/>
        <?php break; ?>

    <?php case ('receipt'): ?>
        <path d="M6 3 H18 V21 L16 19.5 L14 21 L12 19.5 L10 21 L8 19.5 L6 21 Z"/><path d="M9 8 H15 M9 12 H15 M9 16 H13"/>
        <?php break; ?>

    <?php case ('file-invoice-dollar'): ?>
        <path d="M6 3 H14 L18 7 V21 H6 Z"/><path d="M14 3 V7 H18"/><path d="M12 10.5 V17.5 M14 11.5 C14 10.7 13.1 10 12 10 C10.9 10 10 10.7 10 11.5 C10 13.3 14 12.7 14 14.5 C14 15.3 13.1 16 12 16 C10.9 16 10 15.3 10 14.5"/>
        <?php break; ?>

    <?php case ('award'): ?>
        <circle cx="12" cy="9" r="5"/><circle cx="12" cy="9" r="1.8"/><path d="M9 13.5 L7 21 L12 18.5 L17 21 L15 13.5"/>
        <?php break; ?>

    <?php case ('door-open'): ?>
        <rect x="5" y="4" width="14" height="17" rx="1.5"/><path d="M9 21 V13 H15 V21"/><circle cx="7" cy="12" r="0.6" style="fill:currentColor"/>
        <?php break; ?>

    <?php case ('triangle-exclamation'): ?>
    <?php case ('alert-triangle'): ?>
    <?php case ('warning'): ?>
        <path d="M10.29 3.86 L1.82 18 a2 2 0 0 0 1.71 3 H20.47 a2 2 0 0 0 1.71 -3 L13.71 3.86 a2 2 0 0 0 -3.42 0 Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        <?php break; ?>

    
    <?php case ('pause'): ?>
        <rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/>
        <?php break; ?>

    <?php case ('play'): ?>
        <polygon points="6,4 20,12 6,20"/>
        <?php break; ?>

    <?php case ('pen-to-square'): ?>
    <?php case ('edit'): ?>
    <?php case ('pen'): ?>
    <?php case ('pencil'): ?>
        <path d="M11 4 H4 a2 2 0 0 0 -2 2 v14 a2 2 0 0 0 2 2 h14 a2 2 0 0 0 2 -2 v-7"/><path d="M18.5 2.5 a2.121 2.121 0 0 1 3 3 L12 15 l-4 1 1-4 9.5-9.5z"/>
        <?php break; ?>

    <?php case ('trash'): ?>
    <?php case ('trash-can'): ?>
    <?php case ('delete'): ?>
        <polyline points="3 6 5 6 21 6"/><path d="M19 6 v14 a2 2 0 0 1 -2 2 H7 a2 2 0 0 1 -2 -2 V6 M8 6 V4 a2 2 0 0 1 2 -2 h4 a2 2 0 0 1 2 2 v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
        <?php break; ?>

    <?php case ('arrow-trend-up'): ?>
    <?php case ('trend-up'): ?>
    <?php case ('chart-line'): ?>
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
        <?php break; ?>

    <?php case ('circle-info'): ?>
    <?php case ('info'): ?>
    <?php case ('info-circle'): ?>
        <circle cx="12" cy="12" r="9"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        <?php break; ?>

    <?php case ('check'): ?>
    <?php case ('check-circle'): ?>
        <polyline points="20 6 9 17 4 12"/>
        <?php break; ?>

    <?php case ('xmark'): ?>
    <?php case ('close'): ?>
    <?php case ('times'): ?>
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        <?php break; ?>

    <?php case ('envelope'): ?>
    <?php case ('mail'): ?>
        <path d="M4 4 h16 c1.1 0 2 .9 2 2 v12 c0 1.1 -.9 2 -2 2 H4 c-1.1 0 -2 -.9 -2 -2 V6 c0 -1.1 .9 -2 2 -2 z"/><polyline points="22,6 12,13 2,6"/>
        <?php break; ?>

    <?php case ('phone'): ?>
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
        <?php break; ?>

    <?php case ('chevron-right'): ?>
    <?php case ('angle-right'): ?>
        <polyline points="9 18 15 12 9 6"/>
        <?php break; ?>

    <?php case ('chevron-left'): ?>
    <?php case ('angle-left'): ?>
        <polyline points="15 18 9 12 15 6"/>
        <?php break; ?>

    <?php case ('chevron-down'): ?>
    <?php case ('angle-down'): ?>
        <polyline points="6 9 12 15 18 9"/>
        <?php break; ?>

    <?php case ('arrow-left'): ?>
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 5 5 12 12 19"/>
        <?php break; ?>

    <?php case ('save'): ?>
    <?php case ('floppy'): ?>
        <path d="M19 21 H5 a2 2 0 0 1 -2 -2 V5 a2 2 0 0 1 2 -2 h11 l5 5 v11 a2 2 0 0 1 -2 2 z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/><line x1="9" y1="21" x2="9" y2="17"/><line x1="14" y1="21" x2="14" y2="15"/>
        <?php break; ?>

    <?php case ('arrow-right'): ?>
        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        <?php break; ?>

    <?php case ('crown'): ?>
        <path d="M4 18 L3 8 L8 12 L12 5 L16 12 L21 8 L20 18 Z"/><line x1="4" y1="21" x2="20" y2="21"/>
        <?php break; ?>

    <?php case ('hourglass-half'): ?>
    <?php case ('hourglass'): ?>
        <path d="M5 3 H19 M5 21 H19 M6 3 V7 L10 11 V13 L6 17 V21 M18 3 V7 L14 11 V13 L18 17 V21"/><line x1="9" y1="16" x2="15" y2="16"/>
        <?php break; ?>

    <?php case ('credit-card'): ?>
        <rect x="3" y="5" width="18" height="14" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="7" y1="15" x2="11" y2="15"/>
        <?php break; ?>

    <?php case ('id-card'): ?>
        <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M6 16 C6 14.5 7.5 13.8 9 13.8 C10.5 13.8 12 14.5 12 16"/><line x1="14" y1="9" x2="18" y2="9"/><line x1="14" y1="13" x2="18" y2="13"/>
        <?php break; ?>

    <?php case ('school'): ?>
        <path d="M12 3 L2 8 L12 13 L22 8 Z"/><path d="M6 10.5 V18 C6 18 8 20 12 20 C16 20 18 18 18 18 V10.5"/><line x1="22" y1="8" x2="22" y2="15"/>
        <?php break; ?>

    <?php case ('graduation-cap'): ?>
        <path d="M12 4 L2 9 L12 14 L22 9 Z"/><path d="M6 11.5 V17 C6 17 8 19 12 19 C16 19 18 17 18 17 V11.5"/><line x1="22" y1="9" x2="22" y2="16"/>
        <?php break; ?>

    <?php case ('book-open'): ?>
    <?php case ('book'): ?>
        <path d="M3 6 C6 5 9 5.3 12 7 C15 5.3 18 5 21 6 V19 C18 18 15 17.7 12 19.4 C9 17.7 6 18 3 19 Z"/><line x1="12" y1="7" x2="12" y2="19.4"/>
        <?php break; ?>

    <?php case ('flask'): ?>
        <path d="M10 3 H14 M12 3 V8 L6 19 C5 21 6.5 22 8 22 H16 C17.5 22 19 21 18 19 L12 8"/><line x1="8" y1="15" x2="16" y2="15"/>
        <?php break; ?>

    <?php case ('shapes'): ?>
        <polygon points="12,3 17,11 7,11"/><rect x="3" y="14" width="7" height="7" rx="1"/><circle cx="17.5" cy="17.5" r="3.5"/>
        <?php break; ?>

    <?php case ('laptop-code'): ?>
    <?php case ('laptop'): ?>
        <rect x="5" y="4" width="14" height="11" rx="1.5"/><path d="M2 19 H22 L19 15 H5 Z"/><polyline points="10 8 8 9.5 10 11"/><polyline points="14 8 16 9.5 14 11"/>
        <?php break; ?>

    <?php default: ?>
        
        <circle cx="12" cy="12" r="4"/>
<?php endswitch; ?>
</svg>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\components\icon.blade.php ENDPATH**/ ?>
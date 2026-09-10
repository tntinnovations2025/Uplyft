<?php $__env->startSection('breadcrumb', 'Onboard Campus'); ?>
<?php $__env->startSection('title', 'Onboard New Campus — ' . $organization->name); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            Onboard New Campus Profile
        </h1>
        <p style="color:#64748b;font-size:13.5px;margin-top:4px;font-weight:500">
            Register a new campus under <strong><?php echo e($organization->name); ?></strong> (Quota: <?php echo e($organization->campus_usage_text); ?>).
        </p>
    </div>
    <a href="<?php echo e(route('principal.dashboard')); ?>" class="btn btn-secondary">
        ← Back to Dashboard
    </a>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="<?php echo e(route('principal.organization.campuses.store')); ?>">
        <?php echo csrf_field(); ?>

        <div style="margin-bottom:20px;padding:16px;background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:14px;display:flex;align-items:center;gap:12px">
            <div style="width:38px;height:38px;border-radius:10px;background:#4f46e5;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                🏢
            </div>
            <div>
                <div style="font-size:13px;font-weight:800;color:#312e81"><?php echo e(\Illuminate\Support\Str::endsWith(trim($organization->name), 'Network', true) ? $organization->name : $organization->name . ' Network'); ?></div>
                <div style="font-size:11.5px;color:#4338ca;font-weight:600">
                    Allowed Quota: <strong><?php echo e($organization->max_campuses); ?> Campuses</strong> &bull; Currently Registered: <strong><?php echo e($organization->campus_count); ?> Campuses</strong>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="name">Campus Name *</label>
            <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. Apex Academy — Model Town Campus" required />
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
                <label for="city">City / Location</label>
                <input id="city" type="text" name="city" value="<?php echo e(old('city')); ?>" placeholder="e.g. Lahore" />
            </div>

            <div class="form-group">
                <label for="contact_phone">Campus Contact Phone</label>
                <input id="contact_phone" type="text" name="contact_phone" value="<?php echo e(old('contact_phone')); ?>" placeholder="+92 300 1234567" />
            </div>
        </div>

        <div class="form-group">
            <label for="contact_email">Campus Official Email</label>
            <input id="contact_email" type="email" name="contact_email" value="<?php echo e(old('contact_email', auth()->user()->email)); ?>" placeholder="campus2@organization.com" />
        </div>

        <div class="form-group" style="margin-top:22px;margin-bottom:24px">
            
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
                <div>
                    <label style="display:flex;align-items:center;gap:7px;font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px">
                        <i class="fa-solid fa-graduation-cap" style="color:#4f46e5;font-size:14px"></i>
                        Education Systems Offered
                        <span style="font-size:11px;font-weight:700;color:#4f46e5;background:#eef2ff;border:1px solid #c7d2fe;padding:1px 7px;border-radius:9999px;text-transform:none;letter-spacing:0">
                            Multi-Select
                        </span>
                    </label>
                    <p style="font-size:12px;color:#64748b;margin:0;font-weight:500">
                        Select one or more systems offered at this campus:
                    </p>
                </div>
                
                
                <div style="display:flex;align-items:center;gap:10px">
                    <?php
                        $selectedSystems = old() ? old('education_systems', []) : ['matric'];
                    ?>
                    <span id="education-count-badge" class="badge badge-ig" style="font-size:11.5px;font-weight:700;padding:3px 9px;border-radius:6px">
                        <?php echo e(count($selectedSystems)); ?> Selected
                    </span>
                    <button type="button" onclick="toggleAllEducationSystems(true)" style="background:transparent;border:none;color:#4f46e5;font-size:12px;font-weight:700;cursor:pointer;padding:2px 4px;text-decoration:underline">
                        Select All
                    </button>
                    <span style="color:#cbd5e1;font-size:12px">&bull;</span>
                    <button type="button" onclick="toggleAllEducationSystems(false)" style="background:transparent;border:none;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;padding:2px 4px;text-decoration:underline">
                        Clear
                    </button>
                </div>
            </div>

            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <?php $__currentLoopData = $educationSystemLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isSelected = in_array($key, $selectedSystems);
                    ?>
                    <label class="edu-card" 
                           id="card-edu-<?php echo e($key); ?>" 
                           style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:12px;cursor:pointer;position:relative;user-select:none;transition:all 0.18s ease;border:1.5px solid <?php echo e($isSelected ? '#4f46e5' : '#e2e8f0'); ?>;background:<?php echo e($isSelected ? '#f5f7ff' : '#ffffff'); ?>;box-shadow:<?php echo e($isSelected ? '0 3px 10px rgba(79,70,229,0.12)' : '0 1px 2px rgba(15,23,42,0.03)'); ?>">
                        
                        <div style="display:flex;align-items:center;gap:10px;padding-right:8px">
                            <span style="font-size:13px;font-weight:700;color:<?php echo e($isSelected ? '#312e81' : '#0f172a'); ?>;line-height:1.35;text-transform:none;letter-spacing:normal">
                                <?php echo e($label); ?>

                            </span>
                        </div>

                        
                        <div class="custom-checkbox-indicator" 
                             style="width:20px;height:20px;border-radius:6px;border:2px solid <?php echo e($isSelected ? '#4f46e5' : '#cbd5e1'); ?>;background:<?php echo e($isSelected ? '#4f46e5' : '#ffffff'); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.18s ease">
                            <svg class="check-svg" viewBox="0 0 16 16" fill="none" style="width:12px;height:12px;display:<?php echo e($isSelected ? 'block' : 'none'); ?>">
                                <path d="M3.5 8.5L6.5 11.5L12.5 4.5" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        
                        <input type="checkbox"
                               name="education_systems[]"
                               value="<?php echo e($key); ?>"
                               <?php echo e($isSelected ? 'checked' : ''); ?>

                               class="edu-system-input"
                               onchange="onEduCheckboxChange(this)"
                               style="position:absolute;opacity:0;pointer-events:none;width:0;height:0;margin:0" />
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php $__errorArgs = ['education_systems'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p style="color:var(--danger);font-size:12px;margin-top:6px;font-weight:600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <style>
            .edu-card:hover {
                border-color: #818cf8 !important;
                background: #f8fafc !important;
                transform: translateY(-1px);
            }
            .edu-card:has(.edu-system-input:checked) {
                border-color: #4f46e5 !important;
                background: #f5f7ff !important;
                box-shadow: 0 3px 10px rgba(79, 70, 229, 0.12) !important;
            }
            .edu-card:has(.edu-system-input:checked) .custom-checkbox-indicator {
                border-color: #4f46e5 !important;
                background: #4f46e5 !important;
            }
            .edu-card:has(.edu-system-input:checked) .check-svg {
                display: block !important;
            }
            .edu-card:has(.edu-system-input:checked) span {
                color: #312e81 !important;
            }
        </style>

        <script>
            function onEduCheckboxChange(checkbox) {
                const card = checkbox.closest('.edu-card');
                if (!card) return;
                const indicator = card.querySelector('.custom-checkbox-indicator');
                const svg = card.querySelector('.check-svg');
                const labelText = card.querySelector('span');

                if (checkbox.checked) {
                    card.style.borderColor = '#4f46e5';
                    card.style.background = '#f5f7ff';
                    card.style.boxShadow = '0 3px 10px rgba(79,70,229,0.12)';
                    if (indicator) {
                        indicator.style.borderColor = '#4f46e5';
                        indicator.style.background = '#4f46e5';
                    }
                    if (svg) svg.style.display = 'block';
                    if (labelText) labelText.style.color = '#312e81';
                } else {
                    card.style.borderColor = '#e2e8f0';
                    card.style.background = '#ffffff';
                    card.style.boxShadow = '0 1px 2px rgba(15,23,42,0.03)';
                    if (indicator) {
                        indicator.style.borderColor = '#cbd5e1';
                        indicator.style.background = '#ffffff';
                    }
                    if (svg) svg.style.display = 'none';
                    if (labelText) labelText.style.color = '#0f172a';
                }

                syncEducationCountBadge();
            }

            function toggleAllEducationSystems(select) {
                const checkboxes = document.querySelectorAll('.edu-system-input');
                checkboxes.forEach(cb => {
                    cb.checked = select;
                    onEduCheckboxChange(cb);
                });
            }

            function syncEducationCountBadge() {
                const checkedCount = document.querySelectorAll('.edu-system-input:checked').length;
                const badge = document.getElementById('education-count-badge');
                if (badge) {
                    badge.textContent = checkedCount + ' Selected';
                    if (checkedCount > 0) {
                        badge.style.background = '#eef2ff';
                        badge.style.color = '#4338ca';
                        badge.style.borderColor = '#c7d2fe';
                    } else {
                        badge.style.background = '#f1f5f9';
                        badge.style.color = '#64748b';
                        badge.style.borderColor = '#cbd5e1';
                    }
                }
            }
        </script>

        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle"></i> Onboard &amp; Activate Campus Profile
            </button>
            <a href="<?php echo e(route('principal.dashboard')); ?>" class="btn btn-secondary">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\organization\campuses\create.blade.php ENDPATH**/ ?>
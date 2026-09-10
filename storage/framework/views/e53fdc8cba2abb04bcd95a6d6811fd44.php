<?php $__env->startSection('breadcrumb', 'Register Organization'); ?>
<?php $__env->startSection('title', 'Register New Organization Network'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Register New Organization Network</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Establish a new multi-campus organization framework and assign its campus quota limits.
        </p>
    </div>
    <a href="<?php echo e(route('global-admin.organizations.index')); ?>" class="btn btn-secondary">
        ← Back to Organizations
    </a>
</div>

<div class="card" style="max-width:720px">
    <form method="POST" action="<?php echo e(route('global-admin.organizations.store')); ?>">
        <?php echo csrf_field(); ?>

        
        <div style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(79,70,229,0.25)">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Organization Network Info</h3>
                    <p style="font-size:12px;color:#64748b">Basic organization identification &amp; campus quota capacity.</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
                <div class="form-group" style="margin-bottom:0">
                    <label for="name">Organization Network Name *</label>
                    <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. Superior Group of Colleges" required />
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label for="max_campuses">Allowed Campuses Limit *</label>
                    <input id="max_campuses" type="number" name="max_campuses" min="1" max="50" value="<?php echo e(old('max_campuses', 3)); ?>" required />
                    <?php $__errorArgs = ['max_campuses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div style="margin-bottom:24px;padding:20px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:16px">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:38px;height:38px;border-radius:10px;background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:18px">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Organization Owner / Principal Account</h3>
                    <p style="font-size:12px;color:#64748b">The owner can switch between registered campuses under this organization.</p>
                </div>
            </div>

            <div class="form-group">
                <label style="font-size:11.5px;font-weight:800;color:#475569">Owner Assignment Option</label>
                <div style="display:flex;gap:16px;margin-top:6px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="existing" id="owner_mode_existing" <?php echo e(old('owner_mode', 'existing') === 'existing' ? 'checked' : ''); ?> onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Assign Existing Principal
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="new" id="owner_mode_new" <?php echo e(old('owner_mode') === 'new' ? 'checked' : ''); ?> onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Create New Principal Account
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="none" id="owner_mode_none" <?php echo e(old('owner_mode') === 'none' ? 'checked' : ''); ?> onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Assign Later (Unassigned)
                    </label>
                </div>
            </div>

            
            <div id="owner-existing-box" style="display:block">
                <div class="form-group" style="margin-bottom:0">
                    <label for="owner_user_id">Select Existing Principal *</label>
                    <select id="owner_user_id" name="owner_user_id">
                        <option value="">— Select Principal —</option>
                        <?php $__currentLoopData = $principals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e(old('owner_user_id') == $p->id ? 'selected' : ''); ?>>
                                <?php echo e($p->name); ?> (<?php echo e($p->email); ?>) <?php if($p->institute): ?>— Campus: <?php echo e($p->institute->name); ?><?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            
            <div id="owner-new-box" style="display:none;padding:14px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label for="new_owner_name">Owner Full Name *</label>
                        <input id="new_owner_name" type="text" name="new_owner_name" value="<?php echo e(old('new_owner_name')); ?>" placeholder="e.g. Dr. Salman Chaudhry" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_email">Owner Login Email *</label>
                        <input id="new_owner_email" type="email" name="new_owner_email" value="<?php echo e(old('new_owner_email')); ?>" placeholder="owner@organization.com" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_password">Password *</label>
                        <input id="new_owner_password" type="password" name="new_owner_password" placeholder="Min 8 characters" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_password_confirmation">Confirm Password *</label>
                        <input id="new_owner_password_confirmation" type="password" name="new_owner_password_confirmation" placeholder="Re-type password" />
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleOwnerModeUI() {
                const mode = document.querySelector('input[name="owner_mode"]:checked')?.value || 'existing';
                document.getElementById('owner-existing-box').style.display = mode === 'existing' ? 'block' : 'none';
                document.getElementById('owner-new-box').style.display = mode === 'new' ? 'block' : 'none';
            }
        </script>

        <div style="display:flex;gap:12px;margin-top:28px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Register Organization Network</button>
            <a href="<?php echo e(route('global-admin.organizations.index')); ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\organizations\create.blade.php ENDPATH**/ ?>
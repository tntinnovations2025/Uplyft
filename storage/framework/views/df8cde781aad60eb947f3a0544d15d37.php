<?php $__env->startSection('breadcrumb', 'Edit: ' . $organization->name); ?>
<?php $__env->startSection('title', 'Edit Organization Network'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Edit Organization Network</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Update organization details, campus capacity limit quota, and designated owner principal.
        </p>
    </div>
    <a href="<?php echo e(route('global-admin.organizations.index')); ?>" class="btn btn-secondary">
        ← Back to Organizations
    </a>
</div>

<div class="card" style="max-width:720px">
    <form method="POST" action="<?php echo e(route('global-admin.organizations.update', $organization)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        
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
                    <input id="name" type="text" name="name" value="<?php echo e(old('name', $organization->name)); ?>" required />
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
                    <input id="max_campuses" type="number" name="max_campuses" min="<?php echo e(max(1, $organization->campus_count)); ?>" max="50" value="<?php echo e(old('max_campuses', $organization->max_campuses)); ?>" required />
                    <span style="font-size:11px;color:#64748b">Current Usage: <?php echo e($organization->campus_usage_text); ?></span>
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
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Designated Organization Owner</h3>
                    <p style="font-size:12px;color:#64748b">The owner can switch between registered campuses under this organization.</p>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label for="owner_user_id">Select Principal Owner</label>
                <select id="owner_user_id" name="owner_user_id">
                    <option value="">— Unassigned Owner —</option>
                    <?php $__currentLoopData = $principals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php echo e(old('owner_user_id', $organization->owner_user_id) == $p->id ? 'selected' : ''); ?>>
                            <?php echo e($p->name); ?> (<?php echo e($p->email); ?>) <?php if($p->institute): ?>— Campus: <?php echo e($p->institute->name); ?><?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px">
            <h3 style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:800;color:#0f172a;margin-bottom:10px">
                🏫 Member Campuses (<?php echo e($organization->campus_count); ?>)
            </h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <?php $__empty_1 = true; $__currentLoopData = $organization->institutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $camp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;font-weight:700">
                        <span>🏫 <?php echo e($camp->name); ?></span>
                        <a href="<?php echo e(route('global-admin.institutes.edit', $camp)); ?>" style="color:#4f46e5;font-size:11.5px;text-decoration:underline">Edit</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span style="font-size:12.5px;color:#94a3b8">No campuses attached to this organization network yet.</span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:28px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Organization Changes</button>
            <a href="<?php echo e(route('global-admin.organizations.index')); ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\organizations\edit.blade.php ENDPATH**/ ?>
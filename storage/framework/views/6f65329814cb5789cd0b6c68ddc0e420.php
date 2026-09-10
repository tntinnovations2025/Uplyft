<?php $__env->startSection('title', 'Role Authority Governance Center — UPLYFT'); ?>
<?php $__env->startSection('breadcrumb', 'Default Authorities & Role Governance'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .auth-hero-banner {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        padding: 24px 28px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .role-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 22px;
    }

    .role-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
        position: relative;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
    }

    .role-card:hover {
        transform: translateY(-2px);
        border-color: #c7d2fe;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.08);
    }

    .role-card.custom-role-card {
        border-color: #f5d0fe;
    }
    .role-card.custom-role-card:hover {
        border-color: #9333ea;
        box-shadow: 0 10px 25px rgba(147, 51, 234, 0.1);
    }

    .role-avatar-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        color: #ffffff;
    }

    .coverage-progress-bar {
        width: 100%;
        height: 6px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin: 10px 0 14px;
    }

    .coverage-progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease;
    }

    .authority-pill-badge {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 8px;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .view-toggle-btn {
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-toggle-btn.active {
        background: #eef2ff;
        color: #4338ca;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.12);
    }

    /* Permission Matrix Table */
    .matrix-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 16px;
        overflow: hidden;
    }

    .matrix-table th {
        background: #f8fafc;
        padding: 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }

    .matrix-table td {
        padding: 14px 16px;
        font-size: 13px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
        color: #0f172a;
    }

    .matrix-table tr:hover td {
        background: #f8fafc;
    }

    /* Viewport-Centered Modal Backdrops */
    .authority-modal-backdrop {
        display: none;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        z-index: 9999999 !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        box-sizing: border-box !important;
    }

    .authority-modal-dialog {
        max-width: 680px !important;
        width: 92% !important;
        max-height: 88vh !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 20px !important;
        box-shadow: 0 25px 60px -10px rgba(15, 23, 42, 0.35) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        margin: 0 auto !important;
        animation: modalFadeIn 0.2s ease-out !important;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<div style="display:flex;flex-direction:column;gap:20px">

    
    <div class="auth-hero-banner">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px">
            <div style="display:flex;align-items:center;gap:18px">
                <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg, #4f46e5, #6366f1);display:flex;align-items:center;justify-content:center;font-size:28px;box-shadow:0 6px 18px rgba(79,70,229,0.25);color:#fff">
                    🛡️
                </div>
                <div>
                    <h1 style="font-family:'Outfit',sans-serif;font-size:23px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.5px">
                        Role Authority Governance Center
                    </h1>
                    <p style="font-size:13px;color:#64748b;margin-top:4px;max-width:640px;line-height:1.5;font-weight:500">
                        Configure default <strong>View Only</strong> and <strong>Edit</strong> permissions for staff roles. View Only restricts staff to read-only access (no editing or deleting), while Edit grants full modification rights.
                    </p>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px">
                <button type="button" class="btn btn-primary" onclick="openModal('createCustomRoleModal')" style="padding:11px 22px;border-radius:12px;font-weight:700">
                    ✨ Create Custom Role
                </button>
            </div>
        </div>

        
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px 18px">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase">CONFIGURED ROLES</div>
                <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin-top:4px">
                    <?php echo e(count($roleAuthorities)); ?> Roles Defined
                </div>
            </div>

            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:14px;padding:14px 18px">
                <div style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase">TOTAL SYSTEM MODULES</div>
                <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#059669;margin-top:4px">
                    <?php echo e(count($allAuthorityModules)); ?> Modules Guarded
                </div>
            </div>

            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:14px 18px">
                <div style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase">ROLE SYNC ENGINE</div>
                <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0284c7;margin-top:4px">
                    ⚡ Auto-Sync Active
                </div>
            </div>
        </div>
    </div>

    
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">
            Role Permission Matrix &amp; Profiles
        </h3>

        <div style="display:flex;align-items:center;gap:8px">
            <button type="button" class="view-toggle-btn active" id="btn-view-cards" onclick="switchGovernanceView('cards')">
                🎴 Executive Cards
            </button>
            <button type="button" class="view-toggle-btn" id="btn-view-matrix" onclick="switchGovernanceView('matrix')">
                📊 Permissions Matrix Table
            </button>
        </div>
    </div>

    
    <div id="view-governance-cards" class="role-card-grid">
        <?php $__currentLoopData = $roleAuthorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $authRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $perms = $authRecord->permissions ?? [];
                $activeCount = count(array_filter($perms));
                $totalModules = count($allAuthorityModules);
                $percentage = $totalModules > 0 ? round(($activeCount / $totalModules) * 100) : 0;
                
                $roleIcon = match(strtolower($authRecord->role_slug)) {
                    'teacher' => '🎓',
                    'administration' => '🏛️',
                    'accountant' => '💰',
                    'coordinator' => '📚',
                    default => '🛠️',
                };
                
                $iconBg = match(strtolower($authRecord->role_slug)) {
                    'teacher' => 'linear-gradient(135deg, #059669, #10b981)',
                    'administration' => 'linear-gradient(135deg, #4f46e5, #6366f1)',
                    'accountant' => 'linear-gradient(135deg, #d97706, #f59e0b)',
                    'coordinator' => 'linear-gradient(135deg, #0284c7, #38bdf8)',
                    default => 'linear-gradient(135deg, #7c3aed, #a855f7)',
                };
            ?>

            <div class="role-card <?php echo e($authRecord->is_custom ? 'custom-role-card' : ''); ?>">
                <div>
                    
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
                        <div style="display:flex;align-items:center;gap:14px">
                            <div class="role-avatar-icon" style="background:<?php echo e($iconBg); ?>">
                                <?php echo e($roleIcon); ?>

                            </div>
                            <div>
                                <h3 style="font-family:'Outfit',sans-serif;font-size:17.5px;font-weight:800;color:#0f172a;margin:0">
                                    <?php echo e($authRecord->role_name); ?>

                                </h3>
                                <span style="font-size:10px;font-weight:800;letter-spacing:0.6px;color:<?php echo e($authRecord->is_custom ? '#7c3aed' : '#059669'); ?>">
                                    <?php echo e($authRecord->is_custom ? 'CUSTOM ROLE PRESET' : 'SYSTEM DEFAULT ROLE'); ?>

                                </span>
                            </div>
                        </div>
                    </div>

                    <p style="font-size:12.5px;color:#64748b;margin-bottom:14px;line-height:1.5;min-height:38px;font-weight:500">
                        <?php echo e($authRecord->description ?? 'Default permissions for accounts assigned to '.$authRecord->role_name.'.'); ?>

                    </p>

                    
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;min-height:60px">
                        <?php 
                            $displayed = 0;
                            $activeCount = 0;
                            foreach($allAuthorityModules as $mKey => $mMeta) {
                                if (!empty($perms[$mKey.'_view']) || !empty($perms[$mKey])) $activeCount++;
                                if (!empty($perms[$mKey.'_edit']) || ($perms[$mKey] ?? null) === true) $activeCount++;
                            }
                        ?>
                        <?php $__currentLoopData = $allAuthorityModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modKey => $modMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $hasV = !empty($perms[$modKey.'_view']) || (!empty($perms[$modKey]) && $perms[$modKey] !== false);
                                $hasE = !empty($perms[$modKey.'_edit']) || (($perms[$modKey] ?? null) === true);
                            ?>
                            <?php if($hasV || $hasE): ?>
                                <?php if($displayed < 4): ?>
                                    <span class="authority-pill-badge" style="background:<?php echo e($hasE ? '#eef2ff' : '#f0fdf4'); ?>;color:<?php echo e($hasE ? '#4338ca' : '#047857'); ?>;border-color:<?php echo e($hasE ? '#c7d2fe' : '#a7f3d0'); ?>">
                                        <?php echo e($hasE ? '✏️ Edit' : '👁️ View Only'); ?> — <?php echo e($modMeta['label']); ?>

                                    </span>
                                    <?php $displayed++; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(($activeCount - $displayed) > 0): ?>
                            <span class="authority-pill-badge" style="background:#fdf4ff;color:#9333ea;border-color:#f5d0fe">
                                +<?php echo e($activeCount - $displayed); ?> More Action(s)
                            </span>
                        <?php endif; ?>

                        <?php if($activeCount === 0): ?>
                            <span style="font-size:11px;color:#94a3b8">No default authorities enabled.</span>
                        <?php endif; ?>
                    </div>
                </div>

                
                <button type="button" class="btn btn-ghost" onclick="openModal('editAuthorityModal_<?php echo e($authRecord->id); ?>')" style="width:100%;color:#0f172a;font-weight:700;font-size:13px;padding:11px;border-radius:12px;border:1px solid #cbd5e1">
                    ⚙️ Configure Role Authorities (View &amp; Edit)
                </button>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div id="view-governance-matrix" style="display:none">
        <div style="overflow-x:auto;width:100%">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="width:280px">System Module</th>
                        <th>Category</th>
                        <?php $__currentLoopData = $roleAuthorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $authRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th style="text-align:center"><?php echo e($authRecord->role_name); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $allAuthorityModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modKey => $modMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="font-weight:700;color:#0f172a">
                                <?php echo e($modMeta['label']); ?>

                            </td>
                            <td>
                                <span class="badge badge-purple" style="font-size:10px"><?php echo e($modMeta['category']); ?></span>
                            </td>
                            <?php $__currentLoopData = $roleAuthorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $authRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pMap = $authRecord->permissions ?? [];
                                    $hasV = !empty($pMap[$modKey.'_view']) || !empty($pMap[$modKey]);
                                    $hasE = !empty($pMap[$modKey.'_edit']) || (($pMap[$modKey] ?? null) === true);
                                ?>
                                <td style="text-align:center">
                                    <?php if($hasE): ?>
                                        <span style="font-size:11.5px;font-weight:800;color:#4338ca;background:#eef2ff;padding:4px 10px;border-radius:20px;border:1px solid #c7d2fe">
                                            ✏️ Edit Access
                                        </span>
                                    <?php elseif($hasV): ?>
                                        <span style="font-size:11.5px;font-weight:800;color:#047857;background:#f0fdf4;padding:4px 10px;border-radius:20px;border:1px solid #a7f3d0">
                                            👁️ View Only
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size:12px;color:#94a3b8;font-weight:600">
                                            ✕ Revoked
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

</div>



<?php $__currentLoopData = $roleAuthorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $authRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $perms = $authRecord->permissions ?? [];
        $roleIcon = match(strtolower($authRecord->role_slug)) {
            'teacher' => '🎓',
            'administration' => '🏛️',
            'accountant' => '💰',
            'coordinator' => '📚',
            default => '🛠️',
        };
        $iconBg = match(strtolower($authRecord->role_slug)) {
            'teacher' => 'linear-gradient(135deg, #059669, #10b981)',
            'administration' => 'linear-gradient(135deg, #4f46e5, #6366f1)',
            'accountant' => 'linear-gradient(135deg, #d97706, #f59e0b)',
            'coordinator' => 'linear-gradient(135deg, #0284c7, #38bdf8)',
            default => 'linear-gradient(135deg, #7c3aed, #a855f7)',
        };
    ?>

    
    <div id="editAuthorityModal_<?php echo e($authRecord->id); ?>" class="authority-modal-backdrop">
        <div class="authority-modal-dialog">
            
            
            <div style="padding:18px 24px;border-bottom:1px solid #cbd5e1;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
                <div style="display:flex;align-items:center;gap:12px">
                    <div class="role-avatar-icon" style="background:<?php echo e($iconBg); ?>;width:38px;height:38px;font-size:18px">
                        <?php echo e($roleIcon); ?>

                    </div>
                    <div>
                        <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">
                            Configure Authorities: <?php echo e($authRecord->role_name); ?>

                        </h3>
                        <div style="font-size:11.5px;color:#64748b;font-weight:600">Assign granular View Only &amp; Edit permissions for <?php echo e($authRecord->role_name); ?></div>
                    </div>
                </div>
                <button type="button" onclick="closeModal('editAuthorityModal_<?php echo e($authRecord->id); ?>')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer">✕</button>
            </div>

            
            <form method="POST" action="<?php echo e(route('principal.staff.authorities.store')); ?>" style="display:flex;flex-direction:column;flex:1;overflow:hidden">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="role_slug" value="<?php echo e($authRecord->role_slug); ?>">
                <input type="hidden" name="is_custom" value="<?php echo e($authRecord->is_custom ? 1 : 0); ?>">

                
                <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:18px">
                    
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div>
                            <label style="display:block;font-size:10.5px;font-weight:800;color:#334155;letter-spacing:0.5px;margin-bottom:5px;text-transform:uppercase">Role Name *</label>
                            <input type="text" name="role_name" value="<?php echo e($authRecord->role_name); ?>" required style="width:100%;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px;font-weight:700">
                        </div>
                        <div>
                            <label style="display:block;font-size:10.5px;font-weight:800;color:#334155;letter-spacing:0.5px;margin-bottom:5px;text-transform:uppercase">Role Description</label>
                            <input type="text" name="description" value="<?php echo e($authRecord->description); ?>" placeholder="Short summary of role duties" style="width:100%;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px;font-weight:500">
                        </div>
                    </div>

                    
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                            <h4 style="font-family:'Outfit',sans-serif;font-size:14.5px;font-weight:800;color:#0f172a;margin:0">
                                Module Authority Permissions (View &amp; Edit)
                            </h4>
                            <div style="font-size:11px;color:#4f46e5;font-weight:700">👁️ View Only = Read-Only | ✏️ Edit = Can Change</div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr;gap:10px">
                            <?php $__currentLoopData = $allAuthorityModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modKey => $modMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $hasV = !empty($perms[$modKey.'_view']) || !empty($perms[$modKey]);
                                    $hasE = !empty($perms[$modKey.'_edit']) || (($perms[$modKey] ?? null) === true);
                                ?>
                                <div style="display:flex;align-items:center;justify-content:space-between;background:<?php echo e(($hasV || $hasE) ? '#f8fafc' : '#ffffff'); ?>;border:1.5px solid <?php echo e(($hasV || $hasE) ? '#c7d2fe' : '#e2e8f0'); ?>;border-radius:12px;padding:10px 14px;transition:all 0.2s">
                                    <div>
                                        <div style="font-size:13px;font-weight:800;color:#0f172a"><?php echo e($modMeta['label']); ?></div>
                                        <div style="font-size:10.5px;color:#64748b;font-weight:600;margin-top:2px;text-transform:uppercase"><?php echo e($modMeta['category']); ?></div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:14px;background:#ffffff;padding:6px 14px;border-radius:10px;border:1px solid #cbd5e1">
                                        <!-- View Only Checkbox -->
                                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0" title="View Only: Read-only access, cannot change or delete any text/data">
                                            <input type="checkbox" 
                                                   name="permissions[<?php echo e($modKey); ?>_view]" 
                                                   id="auth_<?php echo e($authRecord->id); ?>_<?php echo e($modKey); ?>_view"
                                                   value="1" 
                                                   <?php echo e($hasV ? 'checked' : ''); ?> 
                                                   onchange="syncAuthDualToggle('<?php echo e($authRecord->id); ?>', '<?php echo e($modKey); ?>', 'view')"
                                                   style="width:17px;height:17px;accent-color:#059669;cursor:pointer">
                                            <span style="font-size:12px;font-weight:800;color:#047857">👁️ View</span>
                                        </label>

                                        <div style="width:1px;height:16px;background:#cbd5e1"></div>

                                        <!-- Edit Checkbox -->
                                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0" title="Edit Access: Can create, update, modify and edit data">
                                            <input type="checkbox" 
                                                   name="permissions[<?php echo e($modKey); ?>_edit]" 
                                                   id="auth_<?php echo e($authRecord->id); ?>_<?php echo e($modKey); ?>_edit"
                                                   value="1" 
                                                   <?php echo e($hasE ? 'checked' : ''); ?> 
                                                   onchange="syncAuthDualToggle('<?php echo e($authRecord->id); ?>', '<?php echo e($modKey); ?>', 'edit')"
                                                   style="width:17px;height:17px;accent-color:#4f46e5;cursor:pointer">
                                            <span style="font-size:12px;font-weight:800;color:#4338ca">✏️ Edit</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div style="background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:12px;padding:14px">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0">
                            <input type="checkbox" name="sync_to_existing" value="1" checked style="width:18px;height:18px;accent-color:#059669;cursor:pointer">
                            <span style="font-size:12.5px;font-weight:700;color:#0f172a">
                                ⚡ Sync &amp; Apply updated View/Edit authorities to all active accounts with role "<?php echo e($authRecord->role_name); ?>" immediately
                            </span>
                        </label>
                    </div>

                </div>

                
                <div style="padding:14px 24px;border-top:1px solid #cbd5e1;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0;background:#f8fafc">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('editAuthorityModal_<?php echo e($authRecord->id); ?>')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-weight:700">
                        💾 Save Role Authorities
                    </button>
                </div>
            </form>

        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div id="createCustomRoleModal" class="authority-modal-backdrop">
    <div class="authority-modal-dialog">
        
        <div style="padding:18px 24px;border-bottom:1px solid #cbd5e1;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">🛠️</span>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">
                        Create Custom Role Authority
                    </h3>
                    <div style="font-size:11.5px;color:#64748b;font-weight:600">Define a new role title and assign default View Only &amp; Edit permissions</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('createCustomRoleModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer">✕</button>
        </div>

        <form method="POST" action="<?php echo e(route('principal.staff.authorities.store')); ?>" style="display:flex;flex-direction:column;flex:1;overflow:hidden">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="is_custom" value="1">

            <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:18px">
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div>
                        <label style="display:block;font-size:10.5px;font-weight:800;color:#334155;letter-spacing:0.5px;margin-bottom:5px;text-transform:uppercase">Custom Role Name *</label>
                        <input type="text" name="role_name" placeholder="e.g. Librarian, Lab Assistant" required style="width:100%;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px;font-weight:700">
                    </div>
                    <div>
                        <label style="display:block;font-size:10.5px;font-weight:800;color:#334155;letter-spacing:0.5px;margin-bottom:5px;text-transform:uppercase">Role Description</label>
                        <input type="text" name="description" placeholder="Brief description of duties and authorities" style="width:100%;padding:10px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px;font-weight:500">
                    </div>
                </div>

                <div>
                    <h4 style="font-family:'Outfit',sans-serif;font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 10px 0">
                        Default Authorities for New Custom Role (View &amp; Edit)
                    </h4>
                    <div style="display:grid;grid-template-columns:1fr;gap:10px">
                        <?php $__currentLoopData = $allAuthorityModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modKey => $modMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:12px;padding:10px 14px">
                                <div>
                                    <div style="font-size:12.5px;font-weight:800;color:#0f172a"><?php echo e($modMeta['label']); ?></div>
                                    <div style="font-size:10.5px;color:#64748b;font-weight:600;margin-top:2px;text-transform:uppercase"><?php echo e($modMeta['category']); ?></div>
                                </div>

                                <div style="display:flex;align-items:center;gap:14px;background:#ffffff;padding:6px 14px;border-radius:10px;border:1px solid #cbd5e1">
                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo e($modKey); ?>_view]" 
                                               id="auth_new_<?php echo e($modKey); ?>_view"
                                               value="1" 
                                               onchange="syncAuthDualToggle('new', '<?php echo e($modKey); ?>', 'view')"
                                               style="width:17px;height:17px;accent-color:#059669;cursor:pointer">
                                        <span style="font-size:12px;font-weight:800;color:#047857">👁️ View</span>
                                    </label>

                                    <div style="width:1px;height:16px;background:#cbd5e1"></div>

                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin:0">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo e($modKey); ?>_edit]" 
                                               id="auth_new_<?php echo e($modKey); ?>_edit"
                                               value="1" 
                                               onchange="syncAuthDualToggle('new', '<?php echo e($modKey); ?>', 'edit')"
                                               style="width:17px;height:17px;accent-color:#4f46e5;cursor:pointer">
                                        <span style="font-size:12px;font-weight:800;color:#4338ca">✏️ Edit</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

            </div>

            <div style="padding:14px 24px;border-top:1px solid #cbd5e1;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0;background:#f8fafc">
                <button type="button" class="btn btn-ghost" onclick="closeModal('createCustomRoleModal')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-weight:700">
                    ✨ Create Custom Role
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function switchGovernanceView(viewMode) {
        const cardsDiv = document.getElementById('view-governance-cards');
        const matrixDiv = document.getElementById('view-governance-matrix');
        const btnCards = document.getElementById('btn-view-cards');
        const btnMatrix = document.getElementById('btn-view-matrix');

        if (viewMode === 'cards') {
            cardsDiv.style.display = 'grid';
            matrixDiv.style.display = 'none';
            btnCards.classList.add('active');
            btnMatrix.classList.remove('active');
        } else {
            cardsDiv.style.display = 'none';
            matrixDiv.style.display = 'block';
            btnCards.classList.remove('active');
            btnMatrix.classList.add('active');
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            document.body.appendChild(modal);
            modal.style.display = 'flex';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function syncAuthDualToggle(authId, modKey, actionType) {
        const viewChk = document.getElementById(`auth_${authId}_${modKey}_view`);
        const editChk = document.getElementById(`auth_${authId}_${modKey}_edit`);
        if (!viewChk || !editChk) return;

        if (actionType === 'edit' && editChk.checked) {
            viewChk.checked = true;
        } else if (actionType === 'view' && !viewChk.checked) {
            editChk.checked = false;
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\staff\authorities.blade.php ENDPATH**/ ?>
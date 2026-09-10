<?php $__env->startSection('breadcrumb', 'Register Institute'); ?>
<?php $__env->startSection('title', 'Register New Institute'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Register New Institute</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Provision a new institutional tenant database, subscription tier, and primary administrator.
        </p>
    </div>
    <a href="<?php echo e(route('global-admin.institutes.index')); ?>" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:820px">
        
        <div style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:16px">
            <label style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;display:block">
                🏫 Registration Type &amp; Multi-Campus Architecture *
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <label id="lbl-type-single" style="display:flex;align-items:flex-start;gap:12px;background:#ffffff;border:2px solid #4f46e5;border-radius:14px;padding:16px;cursor:pointer;transition:all 0.2s">
                    <input type="radio" name="registration_type" value="single" id="reg_type_single" <?php echo e(old('registration_type', 'single') === 'single' ? 'checked' : ''); ?> onchange="toggleRegTypeUI()" style="width:18px!important;height:18px!important;min-width:18px;margin-top:3px!important;margin-right:0!important;margin-bottom:0!important;margin-left:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none">
                    <div>
                        <div style="font-size:14px;font-weight:800;color:#0f172a">Single Standalone Institute</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">Single school campus operating independently.</div>
                    </div>
                </label>

                <label id="lbl-type-org" style="display:flex;align-items:flex-start;gap:12px;background:#ffffff;border:2px solid #cbd5e1;border-radius:14px;padding:16px;cursor:pointer;transition:all 0.2s">
                    <input type="radio" name="registration_type" value="organization" id="reg_type_org" <?php echo e(old('registration_type') === 'organization' ? 'checked' : ''); ?> onchange="toggleRegTypeUI()" style="width:18px!important;height:18px!important;min-width:18px;margin-top:3px!important;margin-right:0!important;margin-bottom:0!important;margin-left:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none">
                    <div>
                        <div style="font-size:14px;font-weight:800;color:#0f172a">Register as an Organization Network</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">Centralized multi-campus setup allowing owner to switch profiles across campuses.</div>
                    </div>
                </label>
            </div>

            
            <div id="org-details-panel" style="display:<?php echo e(old('registration_type') === 'organization' ? 'block' : 'none'); ?>;margin-top:16px;padding:16px;background:#ffffff;border:1px solid #c7d2fe;border-radius:12px">
                <div style="font-size:13px;font-weight:800;color:#3730a3;margin-bottom:12px">🏢 Organization Network Configuration</div>
                
                <div style="display:flex;gap:16px;margin-bottom:14px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="organization_mode" value="new" id="org_mode_new" <?php echo e(old('organization_mode', 'new') === 'new' ? 'checked' : ''); ?> onchange="toggleOrgModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none">
                        Create New Organization Network
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="organization_mode" value="existing" id="org_mode_existing" <?php echo e(old('organization_mode') === 'existing' ? 'checked' : ''); ?> onchange="toggleOrgModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none">
                        Add to Existing Organization
                    </label>
                </div>

                
                <div id="org-new-box" style="margin-bottom:0">
                    <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px">
                        <div class="form-group" style="margin-bottom:0">
                            <label for="organization_name">Organization / Chain Name *</label>
                            <input id="organization_name" type="text" name="organization_name" value="<?php echo e(old('organization_name')); ?>" placeholder="e.g. Beaconhouse Educational Group, City Schools Network" />
                        </div>

                        <div class="form-group" style="margin-bottom:0">
                            <label for="max_campuses_new">Allowed Campuses Limit *</label>
                            <input id="max_campuses_new" type="number" name="max_campuses" min="1" max="50" value="<?php echo e(old('max_campuses', 3)); ?>" placeholder="e.g. 3" />
                            <span style="font-size:10.5px;color:#64748b">Max campuses owner can switch between.</span>
                        </div>
                    </div>
                </div>

                
                <div id="org-existing-box" style="display:none;margin-bottom:0">
                    <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px">
                        <div class="form-group" style="margin-bottom:0">
                            <label for="organization_id">Select Existing Organization *</label>
                            <select id="organization_id" name="organization_id" onchange="onExistingOrgSelected()">
                                <option value="">— Select Organization —</option>
                                <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($org->id); ?>" 
                                            data-owner-name="<?php echo e($org->owner ? $org->owner->name : ''); ?>" 
                                            data-owner-email="<?php echo e($org->owner ? $org->owner->email : ''); ?>"
                                            data-max-campuses="<?php echo e($org->max_campuses ?? 3); ?>"
                                            data-used-campuses="<?php echo e($org->campus_count); ?>"
                                            data-can-add="<?php echo e($org->canAddMoreCampuses() ? '1' : '0'); ?>"
                                            <?php echo e(old('organization_id') == $org->id ? 'selected' : ''); ?>>
                                        <?php echo e($org->name); ?> (<?php echo e($org->campus_usage_text); ?>) <?php if($org->owner): ?>— Owner: <?php echo e($org->owner->name); ?><?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0">
                            <label for="max_campuses_edit">Update Limit Quota</label>
                            <input id="max_campuses_edit" type="number" min="1" max="50" placeholder="Quota limit" onchange="syncMaxCampusesVal(this.value)" />
                        </div>
                    </div>

                    
                    <div id="org-quota-full-warning" style="display:none;margin-top:12px;padding:12px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;color:#991b1b;font-size:12.5px;font-weight:700">
                        ⚠️ <span id="quota-warning-text">This organization has reached its maximum campus limit. Please increase the limit quota above to allow registering another campus.</span>
                    </div>

                    <div id="existing-owner-notice" style="display:none;margin-top:12px;padding:10px 14px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px">
                        <label style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:700;color:#1e1b4b;cursor:pointer">
                            <input type="checkbox" name="assign_existing_owner" id="assign_existing_owner" value="1" onchange="toggleExistingOwnerLogin()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none">
                            Use existing Organization Principal/Owner (<span id="existing-owner-name-span"></span>) for this new campus
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function syncMaxCampusesVal(val) {
                const newInp = document.getElementById('max_campuses_new');
                if (newInp) newInp.value = val;
            }

            function toggleRegTypeUI() {
                const isOrg = document.getElementById('reg_type_org').checked;
                document.getElementById('org-details-panel').style.display = isOrg ? 'block' : 'none';
                document.getElementById('lbl-type-single').style.borderColor = isOrg ? '#cbd5e1' : '#4f46e5';
                document.getElementById('lbl-type-org').style.borderColor = isOrg ? '#4f46e5' : '#cbd5e1';
                toggleOrgModeUI();
            }

            function toggleOrgModeUI() {
                const isExisting = document.getElementById('org_mode_existing').checked;
                document.getElementById('org-new-box').style.display = isExisting ? 'none' : 'block';
                document.getElementById('org-existing-box').style.display = isExisting ? 'block' : 'none';
                if (isExisting) {
                    onExistingOrgSelected();
                } else {
                    resetPrincipalLoginFields();
                }
            }

            function onExistingOrgSelected() {
                const select = document.getElementById('organization_id');
                const selectedOpt = select.options[select.selectedIndex];
                const ownerName = selectedOpt ? selectedOpt.getAttribute('data-owner-name') : '';
                const maxC = selectedOpt ? selectedOpt.getAttribute('data-max-campuses') : '3';
                const usedC = selectedOpt ? selectedOpt.getAttribute('data-used-campuses') : '0';
                const canAdd = selectedOpt ? selectedOpt.getAttribute('data-can-add') : '1';

                const editQuotaInput = document.getElementById('max_campuses_edit');
                if (editQuotaInput && selectedOpt && selectedOpt.value) {
                    editQuotaInput.value = maxC;
                    syncMaxCampusesVal(maxC);
                }

                const warningBox = document.getElementById('org-quota-full-warning');
                if (selectedOpt && selectedOpt.value && canAdd === '0') {
                    document.getElementById('quota-warning-text').textContent = `Organization limit reached (${usedC} / ${maxC} Campuses). Increase limit above to proceed.`;
                    warningBox.style.display = 'block';
                } else if (warningBox) {
                    warningBox.style.display = 'none';
                }

                const notice = document.getElementById('existing-owner-notice');

                if (ownerName) {
                    document.getElementById('existing-owner-name-span').textContent = ownerName;
                    notice.style.display = 'block';
                } else {
                    notice.style.display = 'none';
                    resetPrincipalLoginFields();
                }
            }

            function toggleExistingOwnerLogin() {
                const isAssignExisting = document.getElementById('assign_existing_owner').checked;
                const reqInputs = ['principal_name', 'principal_email', 'principal_password', 'principal_password_confirmation'];
                
                reqInputs.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.required = !isAssignExisting;
                        el.disabled = isAssignExisting;
                        if (isAssignExisting) el.value = '';
                    }
                });
            }

            function resetPrincipalLoginFields() {
                const reqInputs = ['principal_name', 'principal_email', 'principal_password', 'principal_password_confirmation'];
                reqInputs.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.required = true;
                        el.disabled = false;
                    }
                });
                const chk = document.getElementById('assign_existing_owner');
                if (chk) chk.checked = false;
            }
        </script>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group">
                <label for="name">Campus / Institute Name *</label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>"
                       placeholder="e.g. Superior College - Gulberg Campus" required />
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="subscription_tier">Subscription Plan *</label>
                <select id="subscription_tier" name="subscription_tier" required>
                    <option value="">— Select Plan —</option>
                    <option value="basic"    <?php echo e(old('subscription_tier') === 'basic'    ? 'selected' : ''); ?>>Basic</option>
                    <option value="standard" <?php echo e(old('subscription_tier') === 'standard' ? 'selected' : ''); ?>>Standard</option>
                    <option value="premium"  <?php echo e(old('subscription_tier') === 'premium'  ? 'selected' : ''); ?>>Premium</option>
                </select>
                <?php $__errorArgs = ['subscription_tier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input id="contact_email" type="email" name="contact_email"
                       value="<?php echo e(old('contact_email')); ?>" placeholder="admin@school.edu.pk" />
                <?php $__errorArgs = ['contact_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input id="contact_phone" type="text" name="contact_phone"
                       value="<?php echo e(old('contact_phone')); ?>" placeholder="+92 300 1234567" />
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" type="text" name="city"
                       value="<?php echo e(old('city')); ?>" placeholder="Lahore" />
            </div>

            <div class="form-group">
                <label for="subscription_starts_at">Subscription Start</label>
                <input id="subscription_starts_at" type="date" name="subscription_starts_at"
                       value="<?php echo e(old('subscription_starts_at')); ?>" />
            </div>

            <div class="form-group">
                <label for="subscription_expires_at">Subscription Expiry</label>
                <input id="subscription_expires_at" type="date" name="subscription_expires_at"
                       value="<?php echo e(old('subscription_expires_at')); ?>" />
            </div>
        </div>

        
        <div style="margin-top:16px;margin-bottom:24px;padding:22px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:16px;box-shadow:0 2px 8px rgba(15,23,42,0.04)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 4px 12px rgba(79,70,229,0.25)">
                        🎨
                    </div>
                    <div>
                        <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Institute Brand Logo &amp; Circular Emblem</h3>
                        <p style="font-size:12px;color:#64748b;margin-top:1px">Upload and crop the official logo into a circular emblem for all present and future portal headers and spinning loaders.</p>
                    </div>
                </div>
            </div>

            
            <div id="brand-logo-dropzone" 
                 style="border:2px dashed #818cf8;border-radius:14px;padding:24px 20px;text-align:center;background:#f5f7ff;cursor:pointer;transition:all .2s;position:relative"
                 onclick="document.getElementById('brand_file_input').click()"
                 ondragover="event.preventDefault(); this.style.borderColor='#4f46e5'; this.style.background='#eef2ff';"
                 ondragleave="this.style.borderColor='#818cf8'; this.style.background='#f5f7ff';"
                 ondrop="handleLogoDrop(event)">
                
                <input id="brand_file_input" type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none" onchange="handleFileSelect(this)" />
                <input type="hidden" name="cropped_logo" id="cropped_logo_input" value="" />

                <div id="dropzone-idle-state">
                    <div style="width:48px;height:48px;border-radius:50%;background:#ffffff;border:1px solid #cbd5e1;display:inline-flex;align-items:center;justify-content:center;font-size:22px;color:#4f46e5;margin-bottom:8px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div style="font-size:13.5px;font-weight:800;color:#1e1b4b">Click or Drop Image to Upload Full Logo</div>
                    <div style="font-size:11.5px;color:#64748b;margin-top:3px">Supports SVG, PNG, JPG, WEBP &bull; Full original image saved as-is without forced cropping</div>
                </div>

                
                <div id="dropzone-applied-state" style="display:none;align-items:center;justify-content:center;gap:16px">
                    <div style="width:68px;height:68px;border-radius:12px;background:#ffffff;border:2px solid #4f46e5;padding:3px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(79,70,229,0.25);overflow:hidden;flex-shrink:0">
                        <img id="applied-crop-preview" src="#" alt="Selected Logo Preview" style="width:100%;height:100%;object-fit:contain;border-radius:8px" />
                    </div>
                    <div style="text-align:left">
                        <div style="display:flex;align-items:center;gap:6px">
                            <span id="applied-crop-status-title" style="font-size:13px;font-weight:800;color:#047857">✓ Full Original Image Selected</span>
                            <span style="font-size:11px;background:#ecfdf5;color:#047857;padding:1px 6px;border-radius:4px;font-weight:700">Ready to Save</span>
                        </div>
                        <div id="applied-crop-filename" style="font-size:11.5px;color:#64748b;margin-top:2px"></div>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:6px">
                            <button type="button" onclick="event.stopPropagation(); openCropModalAgain();" style="font-size:11.5px;font-weight:700;color:#4f46e5;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                                ✂️ Optional: Crop &amp; Zoom Tool
                            </button>
                            <span style="color:#cbd5e1">&bull;</span>
                            <button type="button" onclick="event.stopPropagation(); keepFullOriginalImage();" style="font-size:11.5px;font-weight:700;color:#059669;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                                🖼️ Keep Full Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div id="circularCropModal" class="apple-liquid-glass-overlay" style="display:none;">
            <div class="apple-liquid-glass-card" style="max-width:540px;width:92%;padding:26px 30px;">
                
                
                <div style="position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.9),transparent)"></div>

                
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:14px;margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid rgba(199,210,254,0.6);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                            <i class="fa-solid fa-crop-simple"></i>
                        </div>
                        <div>
                            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.3px">Crop / Adjust Logo (Optional)</h3>
                            <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">Drag to position &bull; Use slider or buttons to Zoom In / Out</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCropModal()" class="apple-liquid-close-btn">✕</button>
                </div>

                
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0f172a;border-radius:16px;padding:12px;position:relative;user-select:none;overflow:hidden;box-shadow:inset 0 2px 6px rgba(0,0,0,0.3)">
                    <canvas id="cropperCanvas" width="340" height="340" style="border-radius:12px;cursor:grab;touch-action:none;background:#1e293b"></canvas>
                    
                    
                    <div style="position:absolute;bottom:18px;left:50%;transform:translateX(-50%);background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);color:#e2e8f0;font-size:10.5px;font-weight:700;padding:4px 12px;border-radius:9999px;pointer-events:none;border:1px solid rgba(255,255,255,0.1)">
                        🖱️ Drag to pan &bull; 📜 Scroll to zoom
                    </div>
                </div>

                
                <div style="margin-top:16px;padding:12px 16px;background:rgba(248,250,252,0.7);backdrop-filter:blur(8px);border-radius:14px;border:1px solid rgba(226,232,240,0.8)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <span style="font-size:12px;font-weight:800;color:#334155;letter-spacing:0.3px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-magnifying-glass"></i> ZOOM CONTROLS
                        </span>
                        <span id="zoomPercentLabel" style="font-size:12px;font-weight:800;color:#4f46e5">100%</span>
                    </div>
                    
                    <div style="display:flex;align-items:center;gap:10px">
                        <button type="button" onclick="adjustZoom(-0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;font-size:13px;cursor:pointer">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input id="zoomSlider" type="range" min="0.2" max="3.5" step="0.02" value="1.0" oninput="setZoom(this.value)" style="flex:1;accent-color:#4f46e5;cursor:pointer" />
                        <button type="button" onclick="adjustZoom(0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;font-size:13px;cursor:pointer">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <button type="button" onclick="resetCropTransform()" title="Center & Fit" style="padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:700;font-size:12px;cursor:pointer">
                            🎯 Fit Image
                        </button>
                        <button type="button" onclick="rotateCropImage()" title="Rotate 90°" style="padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:700;font-size:12px;cursor:pointer">
                            🔄 Rotate
                        </button>
                    </div>
                </div>

                
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;border-top:1px solid rgba(226,232,240,0.7);padding-top:16px">
                    <button type="button" onclick="keepFullOriginalImage()" class="apple-liquid-btn-cancel" style="font-size:13px">
                        🖼️ Keep Full Image (No Crop)
                    </button>
                    <button type="button" onclick="applyCircularCrop()" class="apple-liquid-btn-primary" style="font-size:13px">
                        <span>✨ Apply Circular Crop</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // ── Full Image Preservation & Optional Canvas Cropper Engine ──────
            let cropImg = null;
            let rawDataUrl = '';
            let cropScale = 1.0;
            let cropPosX = 0;
            let cropPosY = 0;
            let cropRotation = 0;
            let isDragging = false;
            let startDragX = 0;
            let startDragY = 0;
            let originalFilename = '';

            const canvas = document.getElementById('cropperCanvas');
            const ctx = canvas.getContext('2d');
            const circleRadius = 135; // 270px diameter circle viewport

            function handleFileSelect(input) {
                if (input.files && input.files[0]) {
                    processUploadedFile(input.files[0]);
                }
            }

            function handleLogoDrop(e) {
                e.preventDefault();
                e.currentTarget.style.borderColor = '#818cf8';
                e.currentTarget.style.background = '#f5f7ff';
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    const file = e.dataTransfer.files[0];
                    document.getElementById('brand_file_input').files = e.dataTransfer.files;
                    processUploadedFile(file);
                }
            }

            function processUploadedFile(file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size exceeds 2MB limit. Please choose a smaller image.');
                    return;
                }
                originalFilename = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    rawDataUrl = e.target.result;
                    cropImg = new Image();
                    cropImg.onload = function() {
                        keepFullOriginalImage();
                    };
                    cropImg.src = rawDataUrl;
                };
                reader.readAsDataURL(file);
            }

            function keepFullOriginalImage() {
                document.getElementById('cropped_logo_input').value = '';
                
                document.getElementById('applied-crop-preview').src = rawDataUrl || (cropImg ? cropImg.src : '');
                document.getElementById('applied-crop-preview').style.borderRadius = '8px';
                document.getElementById('applied-crop-filename').textContent = originalFilename || 'full_original_image';
                document.getElementById('applied-crop-status-title').textContent = '✓ Full Original Image Selected (No Crop)';
                document.getElementById('applied-crop-status-title').style.color = '#047857';
                
                document.getElementById('dropzone-idle-state').style.display = 'none';
                document.getElementById('dropzone-applied-state').style.display = 'flex';

                closeCropModal();
            }

            function openCropModalAgain() {
                if (cropImg) {
                    document.getElementById('circularCropModal').style.display = 'flex';
                    renderCropper();
                } else {
                    document.getElementById('brand_file_input').click();
                }
            }

            function closeCropModal() {
                document.getElementById('circularCropModal').style.display = 'none';
            }

            function resetCropTransform() {
                if (!cropImg) return;
                cropRotation = 0;
                cropPosX = canvas.width / 2;
                cropPosY = canvas.height / 2;
                const maxDim = Math.max(cropImg.width, cropImg.height);
                cropScale = (circleRadius * 2 * 0.92) / maxDim;
                document.getElementById('zoomSlider').value = 1.0;
                document.getElementById('zoomPercentLabel').textContent = '100%';
                renderCropper();
            }

            function setZoom(val) {
                cropScale = parseFloat(val);
                document.getElementById('zoomPercentLabel').textContent = Math.round(cropScale * 100) + '%';
                renderCropper();
            }

            function adjustZoom(delta) {
                const slider = document.getElementById('zoomSlider');
                let newVal = Math.min(3.5, Math.max(0.2, parseFloat(slider.value) + delta));
                slider.value = newVal;
                setZoom(newVal);
            }

            function rotateCropImage() {
                cropRotation = (cropRotation + 90) % 360;
                renderCropper();
            }

            function renderCropper() {
                if (!cropImg) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                ctx.save();
                ctx.translate(cropPosX, cropPosY);
                ctx.rotate((cropRotation * Math.PI) / 180);
                ctx.scale(cropScale, cropScale);
                ctx.drawImage(cropImg, -cropImg.width / 2, -cropImg.height / 2);
                ctx.restore();

                const cx = canvas.width / 2;
                const cy = canvas.height / 2;

                ctx.save();
                ctx.fillStyle = 'rgba(15, 23, 42, 0.68)';
                ctx.beginPath();
                ctx.rect(0, 0, canvas.width, canvas.height);
                ctx.arc(cx, cy, circleRadius, 0, Math.PI * 2, true);
                ctx.fill();

                ctx.lineWidth = 3;
                ctx.strokeStyle = '#6366f1';
                ctx.shadowColor = 'rgba(99, 102, 241, 0.6)';
                ctx.shadowBlur = 10;
                ctx.beginPath();
                ctx.arc(cx, cy, circleRadius, 0, Math.PI * 2, false);
                ctx.stroke();

                ctx.lineWidth = 1;
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.4)';
                ctx.setLineDash([4, 4]);
                ctx.beginPath();
                ctx.arc(cx, cy, circleRadius - 4, 0, Math.PI * 2, false);
                ctx.stroke();
                ctx.restore();
            }

            canvas.addEventListener('mousedown', function(e) {
                isDragging = true;
                startDragX = e.clientX - cropPosX;
                startDragY = e.clientY - cropPosY;
                canvas.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', function(e) {
                if (isDragging) {
                    cropPosX = e.clientX - startDragX;
                    cropPosY = e.clientY - startDragY;
                    renderCropper();
                }
            });

            window.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    canvas.style.cursor = 'grab';
                }
            });

            canvas.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                adjustZoom(delta);
            }, { passive: false });

            function applyCircularCrop() {
                if (!cropImg) return;

                const exportSize = 512;
                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = exportSize;
                exportCanvas.height = exportSize;
                const eCtx = exportCanvas.getContext('2d');

                const scaleFactor = exportSize / (circleRadius * 2);

                eCtx.beginPath();
                eCtx.arc(exportSize / 2, exportSize / 2, exportSize / 2, 0, Math.PI * 2, true);
                eCtx.closePath();
                eCtx.clip();

                eCtx.fillStyle = '#ffffff';
                eCtx.fill();

                const relX = (cropPosX - canvas.width / 2) * scaleFactor;
                const relY = (cropPosY - canvas.height / 2) * scaleFactor;
                const relScale = cropScale * scaleFactor;

                eCtx.save();
                eCtx.translate(exportSize / 2 + relX, exportSize / 2 + relY);
                eCtx.rotate((cropRotation * Math.PI) / 180);
                eCtx.scale(relScale, relScale);
                eCtx.drawImage(cropImg, -cropImg.width / 2, -cropImg.height / 2);
                eCtx.restore();

                const croppedDataUrl = exportCanvas.toDataURL('image/png', 0.95);

                document.getElementById('cropped_logo_input').value = croppedDataUrl;
                document.getElementById('applied-crop-preview').src = croppedDataUrl;
                document.getElementById('applied-crop-filename').textContent = originalFilename || 'circular_cropped_logo.png';
                document.getElementById('dropzone-idle-state').style.display = 'none';
                document.getElementById('dropzone-applied-state').style.display = 'flex';

                closeCropModal();
            }
        </script>

        
        <div class="form-group" style="margin-top:8px">
            <label style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:12px;display:block">
                🏫 Education System *
                <span style="font-weight:400;color:#64748b;font-size:12px">
                    — Select all that apply to this institute
                </span>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <?php $__currentLoopData = $educationSystemLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label style="
                    display:flex;align-items:center;gap:12px;
                    background:#f8fafc;
                    border:1px solid #cbd5e1;
                    border-radius:10px;
                    padding:14px 16px;
                    cursor:pointer;
                    transition:border-color .15s;
                    <?php echo e(in_array($value, old('education_systems', [])) ? 'border-color:#e1306c;background:#fdf2f8' : ''); ?>

                ">
                    <input type="checkbox"
                           name="education_systems[]"
                           value="<?php echo e($value); ?>"
                           <?php echo e(in_array($value, old('education_systems', [])) ? 'checked' : ''); ?>

                           style="width:18px;height:18px;accent-color:#e1306c;flex-shrink:0"
                           onchange="this.closest('label').style.borderColor = this.checked ? '#e1306c' : '#cbd5e1';
                                     this.closest('label').style.background = this.checked ? '#fdf2f8' : '#f8fafc'">
                    <span style="font-size:13.5px;font-weight:700;color:#0f172a"><?php echo e($label); ?></span>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php $__errorArgs = ['education_systems'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:8px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div style="margin-top:28px;padding:24px;background:#fdf2f8;border:1.5px solid #fbcfe8;border-radius:14px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                <span style="font-size:24px">🔑</span>
                <div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a;display:block">
                        Create Master Login for Institute *
                    </div>
                    <span style="font-weight:500;color:#64748b;font-size:12.5px">
                        This creates the Principal account. Login credentials will be emailed automatically.
                    </span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div class="form-group" style="grid-column:1/-1">
                    <label for="principal_name">Principal Full Name *</label>
                    <input id="principal_name" type="text" name="principal_name"
                           value="<?php echo e(old('principal_name')); ?>"
                           placeholder="e.g. Mr. Ahmed Khan" required />
                    <?php $__errorArgs = ['principal_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="principal_email">Principal Email *</label>
                    <input id="principal_email" type="email" name="principal_email"
                           value="<?php echo e(old('principal_email')); ?>"
                           placeholder="principal@school.edu.pk" required />
                    <span style="font-size:11px;color:#64748b">This email will be used for login and credential delivery.</span>
                    <?php $__errorArgs = ['principal_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="principal_password">Login Password *</label>
                    <input id="principal_password" type="password" name="principal_password"
                           placeholder="Min 8 chars: Aa1@..." required />
                    <span style="font-size:11px;color:#64748b">Uppercase + lowercase + number + special char required.</span>
                    <?php $__errorArgs = ['principal_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group" style="grid-column:1;">
                    <label for="principal_password_confirmation">Confirm Password *</label>
                    <input id="principal_password_confirmation" type="password"
                           name="principal_password_confirmation"
                           placeholder="Re-type password" required />
                </div>

                <div class="form-group">
                    <label for="principal_identifier">Employee ID <span style="color:#64748b">(Optional)</span></label>
                    <input id="principal_identifier" type="text" name="principal_identifier"
                           value="<?php echo e(old('principal_identifier')); ?>"
                           placeholder="e.g. PRIN-001, ADM#101" />
                    <?php $__errorArgs = ['principal_identifier'];
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

        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn btn-primary">✅ Register Institute & Create Master Login</button>
            <a href="<?php echo e(route('global-admin.institutes.index')); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\institutes\create.blade.php ENDPATH**/ ?>
<?php $__env->startSection('breadcrumb', 'Edit: ' . $institute->name); ?>
<?php $__env->startSection('title', 'Edit ' . $institute->name); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Edit Institute</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Update institutional settings, subscription plan, contact coordinates, and education streams.
        </p>
    </div>
    <a href="<?php echo e(route('global-admin.institutes.show', $institute)); ?>" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:820px">
    <form method="POST" action="<?php echo e(route('global-admin.institutes.update', $institute)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group">
                <label for="name">Institute Name *</label>
                <input id="name" type="text" name="name"
                       value="<?php echo e(old('name', $institute->name)); ?>" required />
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
                    <?php $__currentLoopData = ['basic','standard','premium']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($tier); ?>"
                        <?php echo e(old('subscription_tier', $institute->subscription_tier) === $tier ? 'selected' : ''); ?>>
                        <?php echo e(ucfirst($tier)); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input id="contact_email" type="email" name="contact_email"
                       value="<?php echo e(old('contact_email', $institute->contact_email)); ?>" />
            </div>

            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input id="contact_phone" type="text" name="contact_phone"
                       value="<?php echo e(old('contact_phone', $institute->contact_phone)); ?>" />
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" type="text" name="city"
                       value="<?php echo e(old('city', $institute->city)); ?>" />
            </div>

            <div class="form-group">
                <label for="subscription_starts_at">Subscription Start</label>
                <input id="subscription_starts_at" type="date" name="subscription_starts_at"
                       value="<?php echo e(old('subscription_starts_at', $institute->subscription_starts_at?->format('Y-m-d'))); ?>" />
            </div>

            <div class="form-group">
                <label for="subscription_expires_at">Subscription Expiry</label>
                <input id="subscription_expires_at" type="date" name="subscription_expires_at"
                       value="<?php echo e(old('subscription_expires_at', $institute->subscription_expires_at?->format('Y-m-d'))); ?>" />
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

            
            <?php if($institute->logo_path): ?>
                <div id="current-logo-card" style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;margin-bottom:14px">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:48px;height:48px;border-radius:50%;background:#ffffff;border:2px solid #6366f1;padding:2px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.06);overflow:hidden;flex-shrink:0">
                            <img src="<?php echo e(asset('storage/'.$institute->logo_path)); ?>" alt="Current Logo" style="width:100%;height:100%;object-fit:contain;border-radius:50%" />
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:800;color:#0f172a">Current Active Logo</div>
                            <div style="font-size:11px;color:#64748b">Live across all portal headers &amp; spinning loaders</div>
                        </div>
                    </div>
                    <label style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#e11d48;cursor:pointer;margin:0;background:#fff1f2;padding:8px 14px;border-radius:10px;border:1.5px solid #fecdd3;transition:all 0.2s ease;user-select:none">
                        <input type="checkbox" name="remove_logo" value="1" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#e11d48;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none" />
                        <span style="display:inline-flex;align-items:center;gap:4px">🗑️ Remove Logo</span>
                    </label>
                </div>
            <?php endif; ?>

            
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
                    <div style="font-size:13.5px;font-weight:800;color:#1e1b4b"><?php echo e($institute->logo_path ? 'Click or Drop to Replace Institute Logo' : 'Click or Drop Image to Upload Full Logo'); ?></div>
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
                        // Directly preserve full original image (no forced cropping down)
                        keepFullOriginalImage();
                    };
                    cropImg.src = rawDataUrl;
                };
                reader.readAsDataURL(file);
            }

            function keepFullOriginalImage() {
                // Clear cropped base64 so server saves the exact full original file as uploaded
                document.getElementById('cropped_logo_input').value = '';
                
                // Update UI preview card with the full original image
                document.getElementById('applied-crop-preview').src = rawDataUrl || (cropImg ? cropImg.src : '');
                document.getElementById('applied-crop-preview').style.borderRadius = '8px';
                document.getElementById('applied-crop-filename').textContent = originalFilename || 'full_original_image';
                document.getElementById('applied-crop-status-title').textContent = '✓ Full Original Image Selected (No Crop)';
                document.getElementById('applied-crop-status-title').style.color = '#047857';
                
                document.getElementById('dropzone-idle-state').style.display = 'none';
                document.getElementById('dropzone-applied-state').style.display = 'flex';

                const currentLogoCard = document.getElementById('current-logo-card');
                if (currentLogoCard) {
                    currentLogoCard.style.opacity = '0.5';
                }

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
                // Fit image nicely into circular viewport
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

                // 1. Draw Image with Transformations
                ctx.save();
                ctx.translate(cropPosX, cropPosY);
                ctx.rotate((cropRotation * Math.PI) / 180);
                ctx.scale(cropScale, cropScale);
                ctx.drawImage(cropImg, -cropImg.width / 2, -cropImg.height / 2);
                ctx.restore();

                // 2. Draw Darkened Overlay outside Circle
                const cx = canvas.width / 2;
                const cy = canvas.height / 2;

                ctx.save();
                ctx.fillStyle = 'rgba(15, 23, 42, 0.68)';
                ctx.beginPath();
                ctx.rect(0, 0, canvas.width, canvas.height);
                ctx.arc(cx, cy, circleRadius, 0, Math.PI * 2, true);
                ctx.fill();

                // 3. Draw Specular Glowing Circular Guide Ring
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#6366f1';
                ctx.shadowColor = 'rgba(99, 102, 241, 0.6)';
                ctx.shadowBlur = 10;
                ctx.beginPath();
                ctx.arc(cx, cy, circleRadius, 0, Math.PI * 2, false);
                ctx.stroke();

                // Dashed inner framing guide
                ctx.lineWidth = 1;
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.4)';
                ctx.setLineDash([4, 4]);
                ctx.beginPath();
                ctx.arc(cx, cy, circleRadius - 4, 0, Math.PI * 2, false);
                ctx.stroke();
                ctx.restore();
            }

            // Canvas drag & pan interactions
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

            // Wheel zoom
            canvas.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                adjustZoom(delta);
            }, { passive: false });

            // Apply and Export High-Resolution Circular PNG
            function applyCircularCrop() {
                if (!cropImg) return;

                // Create high-res 512x512 export canvas
                const exportSize = 512;
                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = exportSize;
                exportCanvas.height = exportSize;
                const eCtx = exportCanvas.getContext('2d');

                const scaleFactor = exportSize / (circleRadius * 2);

                // Clip to circle with anti-aliasing
                eCtx.beginPath();
                eCtx.arc(exportSize / 2, exportSize / 2, exportSize / 2, 0, Math.PI * 2, true);
                eCtx.closePath();
                eCtx.clip();

                // Clean white/transparent background
                eCtx.fillStyle = '#ffffff';
                eCtx.fill();

                // Calculate image position relative to center of circle
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

                // Set hidden input value for form submission
                document.getElementById('cropped_logo_input').value = croppedDataUrl;

                // Update UI preview card
                document.getElementById('applied-crop-preview').src = croppedDataUrl;
                document.getElementById('applied-crop-filename').textContent = originalFilename || 'circular_cropped_logo.png';
                document.getElementById('dropzone-idle-state').style.display = 'none';
                document.getElementById('dropzone-applied-state').style.display = 'flex';

                // If current logo was displayed, hide remove check or update it
                const currentLogoCard = document.getElementById('current-logo-card');
                if (currentLogoCard) {
                    currentLogoCard.style.opacity = '0.5';
                }

                closeCropModal();
            }
        </script>

        
        <div style="margin-top:24px;margin-bottom:24px;padding:22px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:16px;box-shadow:0 2px 10px rgba(15,23,42,0.03)">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:17px;box-shadow:0 2px 6px rgba(79,70,229,0.12)">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">Organization Network &amp; Multi-Campus Architecture</h3>
                    <p style="font-size:12px;color:#64748b;margin-top:1px">Link this campus to an Organization network or convert it into a multi-campus owner profile.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="organization_mode_edit" style="font-size:11.5px;font-weight:800;color:#475569">Organization Status</label>
                <select id="organization_mode_edit" name="organization_assignment_type" onchange="toggleEditOrgUI()" style="width:100%">
                    <option value="none" <?php echo e(!$institute->organization_id ? 'selected' : ''); ?>>Single Standalone Institute (No Organization)</option>
                    <option value="existing" <?php echo e($institute->organization_id ? 'selected' : ''); ?>>Attach to Existing Organization</option>
                    <option value="new">Convert &amp; Create New Organization Network</option>
                </select>
            </div>

            
            <div id="edit-org-existing-box" style="display:<?php echo e($institute->organization_id ? 'block' : 'none'); ?>;margin-top:14px">
                <label for="edit_organization_id">Select Organization Network *</label>
                <select id="edit_organization_id" name="organization_id" style="width:100%">
                    <option value="">— Select Organization —</option>
                    <?php $__currentLoopData = $organizations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($org->id); ?>" <?php echo e($institute->organization_id == $org->id ? 'selected' : ''); ?>>
                            <?php echo e($org->name); ?> (<?php echo e($org->campus_usage_text); ?>) <?php if($org->owner): ?>— Owner: <?php echo e($org->owner->name); ?><?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div id="edit-org-new-box" style="display:none;margin-top:14px">
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px">
                    <div class="form-group" style="margin-bottom:0">
                        <label for="edit_organization_name">New Organization Name *</label>
                        <input id="edit_organization_name" type="text" name="new_organization_name" value="<?php echo e(old('new_organization_name', $institute->name . ' Group')); ?>" placeholder="e.g. Crescent Educational Network" />
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label for="edit_max_campuses">Allowed Campuses Limit *</label>
                        <input id="edit_max_campuses" type="number" name="new_max_campuses" min="1" max="50" value="3" />
                    </div>
                </div>
                <p style="font-size:11.5px;color:#4f46e5;margin-top:8px;font-weight:600">
                    ✨ Converting this institute will create a new Organization Network. This campus will become Campus #1, and its primary principal will be designated as the Organization Owner with multi-campus profile switching.
                </p>
            </div>
        </div>

        <script>
            function toggleEditOrgUI() {
                const val = document.getElementById('organization_mode_edit').value;
                document.getElementById('edit-org-existing-box').style.display = val === 'existing' ? 'block' : 'none';
                document.getElementById('edit-org-new-box').style.display = val === 'new' ? 'block' : 'none';
            }
        </script>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1"
                       style="width:auto;accent-color:#e1306c"
                    <?php echo e(old('is_active', $institute->is_active) ? 'checked' : ''); ?>>
                <span style="font-size:13.5px;font-weight:700;color:#0f172a">Institute is Active</span>
            </label>
        </div>

        
        <div class="form-group">
            <label style="font-size:12px;font-weight:800;color:#475569;margin-bottom:12px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-graduation-cap" style="color:#4f46e5"></i> EDUCATION SYSTEM
                <span style="font-weight:500;color:#64748b;font-size:11.5px;text-transform:none">
                    — Select all that apply
                </span>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <?php $currentSystems = old('education_systems', $institute->education_systems ?? []); ?>
                <?php $__currentLoopData = $educationSystemLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label style="
                    display:flex;align-items:center;gap:12px;
                    background:#f8fafc;
                    border:1.5px solid <?php echo e(in_array($value, $currentSystems) ? '#6366f1' : '#cbd5e1'); ?>;
                    border-radius:12px;padding:14px 16px;cursor:pointer;
                    background:<?php echo e(in_array($value, $currentSystems) ? '#eef2ff' : '#ffffff'); ?>;
                    transition:all .18s ease;
                ">
                    <input type="checkbox"
                           name="education_systems[]"
                           value="<?php echo e($value); ?>"
                           <?php echo e(in_array($value, $currentSystems) ? 'checked' : ''); ?>

                           style="width:18px!important;height:18px!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer"
                           onchange="this.closest('label').style.borderColor = this.checked ? '#6366f1' : '#cbd5e1';
                                     this.closest('label').style.background = this.checked ? '#eef2ff' : '#ffffff'">
                    <span style="font-size:13.5px;font-weight:700;color:#0f172a"><?php echo e($label); ?></span>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:28px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            <a href="<?php echo e(route('global-admin.institutes.show', $institute)); ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('global-admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\institutes\edit.blade.php ENDPATH**/ ?>
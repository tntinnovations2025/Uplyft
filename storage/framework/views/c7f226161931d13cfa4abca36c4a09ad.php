<?php
    $currentPlatformLogo = \App\Models\PlatformSetting::get('platform_logo_path');
?>

<section>
    <header style="margin-bottom: 22px;">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:42px;height:42px;border-radius:12px;background:#eef2ff;color:#4f46e5;border:1px solid #e0e7ff;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                ⚡
            </div>
            <div>
                <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.3px">
                    UPLYFT Master Platform Logo &amp; Global Branding
                </h2>
                <p style="font-size:13px;color:#64748b;margin-top:3px;font-weight:500">
                    Configure the global UPLYFT platform logo displayed across the Global Admin governance tower and master loaders. Individual campus logo updates will never overwrite this.
                </p>
            </div>
        </div>
    </header>

    <?php if(session('success')): ?>
        <div style="margin-bottom:18px;padding:12px 18px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#047857;font-size:13px;font-weight:700;display:flex;align-items:center;gap:8px">
            <span>✨</span>
            <span><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(route('profile.platform-logo.update')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        
        <?php if($currentPlatformLogo): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px 20px;margin-bottom:18px">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:54px;height:54px;border-radius:12px;background:#ffffff;border:1.5px solid #cbd5e1;padding:3px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;flex-shrink:0">
                        <img src="<?php echo e(asset('storage/'.$currentPlatformLogo)); ?>" alt="UPLYFT Master Logo" style="width:100%;height:100%;object-fit:contain;border-radius:8px" />
                    </div>
                    <div>
                        <div style="font-size:14.5px;font-weight:800;color:#0f172a">Active UPLYFT Master Logo</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">Active across Global Admin Command Tower and master initial login loaders</div>
                    </div>
                </div>
                <label style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#dc2626;cursor:pointer;margin:0;background:#fef2f2;border:1.5px solid #fecaca;padding:8px 16px;border-radius:10px;transition:all .2s;user-select:none">
                    <input type="checkbox" name="remove_logo" value="1" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#dc2626;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none" />
                    <span style="display:inline-flex;align-items:center;gap:4px">🔄 Restore Default</span>
                </label>
            </div>
        <?php endif; ?>

        
        <div id="platform-logo-dropzone" 
             style="border:2px dashed #cbd5e1;border-radius:14px;padding:26px 20px;text-align:center;background:#f8fafc;cursor:pointer;transition:all .2s;position:relative"
             onclick="document.getElementById('platform_file_input').click()"
             ondragover="event.preventDefault(); this.style.borderColor='#4f46e5'; this.style.background='#f5f7ff';"
             ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';"
             ondrop="handlePlatformLogoDrop(event)">
            
            <input id="platform_file_input" type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none" onchange="handlePlatformFileSelect(this)" />
            <input type="hidden" name="cropped_logo" id="platform_cropped_logo_input" value="" />
            
            <div id="platform-dropzone-idle">
                <div style="width:48px;height:48px;border-radius:50%;background:#ffffff;border:1px solid #e2e8f0;display:inline-flex;align-items:center;justify-content:center;font-size:22px;color:#4f46e5;margin-bottom:8px;box-shadow:0 2px 8px rgba(79,70,229,0.1)">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div style="font-size:14px;font-weight:800;color:#0f172a"><?php echo e($currentPlatformLogo ? 'Click or Drop Image to Replace UPLYFT Master Logo' : 'Click or Drop Image to Set UPLYFT Master Logo'); ?></div>
                <div style="font-size:12px;color:#64748b;margin-top:3px">Supports SVG, PNG, JPG, WEBP &bull; Full original image saved without forced cropping</div>
            </div>

            
            <div id="platform-dropzone-applied" style="display:none;align-items:center;justify-content:center;gap:16px">
                <div style="width:64px;height:64px;border-radius:12px;background:#ffffff;border:2px solid #4f46e5;padding:3px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(79,70,229,0.2);overflow:hidden;flex-shrink:0">
                    <img id="platform-applied-crop-preview" src="#" alt="Selected Logo Preview" style="width:100%;height:100%;object-fit:contain;border-radius:8px" />
                </div>
                <div style="text-align:left">
                    <div style="display:flex;align-items:center;gap:6px">
                        <span id="platform-applied-status-title" style="font-size:13.5px;font-weight:800;color:#047857">✓ Full Original Image Selected</span>
                        <span style="font-size:11px;background:#ecfdf5;color:#047857;padding:1px 6px;border-radius:4px;font-weight:700">Ready to Save</span>
                    </div>
                    <div id="platform-applied-crop-filename" style="font-size:12px;color:#64748b;margin-top:2px"></div>
                    <div style="display:flex;align-items:center;gap:12px;margin-top:6px">
                        <button type="button" onclick="event.stopPropagation(); openPlatformCropModal();" style="font-size:11.5px;font-weight:700;color:#4f46e5;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                            ✂️ Optional: Crop Tool
                        </button>
                        <span style="color:#cbd5e1">&bull;</span>
                        <button type="button" onclick="event.stopPropagation(); keepFullPlatformLogo();" style="font-size:11.5px;font-weight:700;color:#047857;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                            🖼️ Keep Full Image
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;align-items:center;justify-content:flex-end">
            <button type="submit" style="padding:10px 24px;border-radius:10px;border:none;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;font-size:13.5px;font-weight:800;cursor:pointer;box-shadow:0 4px 14px rgba(79,70,229,0.25)">
                💾 Save Master Platform Logo
            </button>
        </div>
    </form>
</section>


<div id="platformCropModal" class="apple-liquid-glass-overlay" style="display:none;">
    <div class="apple-liquid-glass-card" style="max-width:540px;width:92%;padding:26px 30px;">
        
        
        <div style="position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.9),transparent)"></div>

        
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:14px;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid rgba(199,210,254,0.6);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                    ⚡
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.3px">Crop / Adjust Master Logo (Optional)</h3>
                    <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">Drag to position &bull; Use slider or buttons to Zoom In / Out</p>
                </div>
            </div>
            <button type="button" onclick="closePlatformCropModal()" class="apple-liquid-close-btn">✕</button>
        </div>

        
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0f172a;border-radius:16px;padding:12px;position:relative;user-select:none;overflow:hidden;box-shadow:inset 0 2px 6px rgba(0,0,0,0.3)">
            <canvas id="platformCropperCanvas" width="340" height="340" style="border-radius:12px;cursor:grab;touch-action:none;background:#1e293b"></canvas>
            <div style="position:absolute;bottom:18px;left:50%;transform:translateX(-50%);background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);color:#e2e8f0;font-size:10.5px;font-weight:700;padding:4px 12px;border-radius:9999px;pointer-events:none;border:1px solid rgba(255,255,255,0.1)">
                🖱️ Drag to pan &bull; 📜 Scroll to zoom
            </div>
        </div>

        
        <div style="margin-top:16px;padding:12px 16px;background:rgba(248,250,252,0.7);backdrop-filter:blur(8px);border-radius:14px;border:1px solid rgba(226,232,240,0.8)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12px;font-weight:800;color:#334155;letter-spacing:0.3px">ZOOM CONTROLS</span>
                <span id="platformZoomLabel" style="font-size:12px;font-weight:800;color:#4f46e5">100%</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <button type="button" onclick="adjustPlatformZoom(-0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;cursor:pointer">-</button>
                <input id="platformZoomSlider" type="range" min="0.2" max="3.5" step="0.02" value="1.0" oninput="setPlatformZoom(this.value)" style="flex:1;accent-color:#4f46e5;cursor:pointer" />
                <button type="button" onclick="adjustPlatformZoom(0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;cursor:pointer">+</button>
                <button type="button" onclick="resetPlatformCropTransform()" style="padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-size:12px;font-weight:700;cursor:pointer">🎯 Fit</button>
            </div>
        </div>

        
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;border-top:1px solid rgba(226,232,240,0.7);padding-top:16px">
            <button type="button" onclick="keepFullPlatformLogo()" class="apple-liquid-btn-cancel" style="font-size:13px">
                🖼️ Keep Full Image (No Crop)
            </button>
            <button type="button" onclick="applyPlatformCircularCrop()" class="apple-liquid-btn-primary" style="font-size:13px">
                <span>✨</span> Apply Circular Crop
            </button>
        </div>
    </div>
</div>

<script>
    let pCropImg = null;
    let pRawDataUrl = '';
    let pCropScale = 1.0;
    let pCropPosX = 0;
    let pCropPosY = 0;
    let pIsDragging = false;
    let pStartDragX = 0;
    let pStartDragY = 0;
    let pOriginalFilename = '';

    const pCanvas = document.getElementById('platformCropperCanvas');
    const pCtx = pCanvas.getContext('2d');
    const pCircleRadius = 135;

    function handlePlatformFileSelect(input) {
        if (input.files && input.files[0]) {
            processUploadedPlatformFile(input.files[0]);
        }
    }

    function handlePlatformLogoDrop(e) {
        e.preventDefault();
        e.currentTarget.style.borderColor = '#6366f1';
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            const file = e.dataTransfer.files[0];
            document.getElementById('platform_file_input').files = e.dataTransfer.files;
            processUploadedPlatformFile(file);
        }
    }

    function processUploadedPlatformFile(file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit.');
            return;
        }
        pOriginalFilename = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            pRawDataUrl = e.target.result;
            pCropImg = new Image();
            pCropImg.onload = function() {
                keepFullPlatformLogo();
            };
            pCropImg.src = pRawDataUrl;
        };
        reader.readAsDataURL(file);
    }

    function keepFullPlatformLogo() {
        document.getElementById('platform_cropped_logo_input').value = '';
        
        document.getElementById('platform-applied-crop-preview').src = pRawDataUrl || (pCropImg ? pCropImg.src : '');
        document.getElementById('platform-applied-crop-filename').textContent = pOriginalFilename || 'full_master_logo';
        document.getElementById('platform-applied-status-title').textContent = '✓ Full Original Image Selected';
        
        document.getElementById('platform-dropzone-idle').style.display = 'none';
        document.getElementById('platform-dropzone-applied').style.display = 'flex';

        closePlatformCropModal();
    }

    function openPlatformCropModal() {
        if (pCropImg) {
            document.getElementById('platformCropModal').style.display = 'flex';
            resetPlatformCropTransform();
        } else {
            document.getElementById('platform_file_input').click();
        }
    }

    function closePlatformCropModal() {
        document.getElementById('platformCropModal').style.display = 'none';
    }

    function resetPlatformCropTransform() {
        if (!pCropImg) return;
        pCropRotation = 0;
        pCropPosX = pCanvas.width / 2;
        pCropPosY = pCanvas.height / 2;
        const maxDim = Math.max(pCropImg.width, pCropImg.height);
        pCropScale = (pCircleRadius * 2 * 0.92) / maxDim;
        document.getElementById('platformZoomSlider').value = 1.0;
        document.getElementById('platformZoomLabel').textContent = '100%';
        renderPlatformCropper();
    }

    function setPlatformZoom(val) {
        pCropScale = parseFloat(val);
        document.getElementById('platformZoomLabel').textContent = Math.round(pCropScale * 100) + '%';
        renderPlatformCropper();
    }

    function adjustPlatformZoom(delta) {
        const slider = document.getElementById('platformZoomSlider');
        let newVal = Math.min(3.5, Math.max(0.2, parseFloat(slider.value) + delta));
        slider.value = newVal;
        setPlatformZoom(newVal);
    }

    function renderPlatformCropper() {
        if (!pCropImg) return;
        pCtx.clearRect(0, 0, pCanvas.width, pCanvas.height);

        pCtx.save();
        pCtx.translate(pCropPosX, pCropPosY);
        pCtx.scale(pCropScale, pCropScale);
        pCtx.drawImage(pCropImg, -pCropImg.width / 2, -pCropImg.height / 2);
        pCtx.restore();

        const cx = pCanvas.width / 2;
        const cy = pCanvas.height / 2;

        pCtx.save();
        pCtx.fillStyle = 'rgba(15, 23, 42, 0.72)';
        pCtx.beginPath();
        pCtx.rect(0, 0, pCanvas.width, pCanvas.height);
        pCtx.arc(cx, cy, pCircleRadius, 0, Math.PI * 2, true);
        pCtx.fill();

        pCtx.lineWidth = 3;
        pCtx.strokeStyle = '#6366f1';
        pCtx.shadowColor = 'rgba(99, 102, 241, 0.6)';
        pCtx.shadowBlur = 10;
        pCtx.beginPath();
        pCtx.arc(cx, cy, pCircleRadius, 0, Math.PI * 2, false);
        pCtx.stroke();
        pCtx.restore();
    }

    pCanvas.addEventListener('mousedown', function(e) {
        pIsDragging = true;
        pStartDragX = e.clientX - pCropPosX;
        pStartDragY = e.clientY - pCropPosY;
        pCanvas.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', function(e) {
        if (pIsDragging) {
            pCropPosX = e.clientX - pStartDragX;
            pCropPosY = e.clientY - pStartDragY;
            renderPlatformCropper();
        }
    });

    window.addEventListener('mouseup', function() {
        if (pIsDragging) {
            pIsDragging = false;
            pCanvas.style.cursor = 'grab';
        }
    });

    pCanvas.addEventListener('wheel', function(e) {
        e.preventDefault();
        const delta = e.deltaY < 0 ? 0.08 : -0.08;
        adjustPlatformZoom(delta);
    }, { passive: false });

    function applyPlatformCircularCrop() {
        if (!pCropImg) return;

        const exportSize = 512;
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = exportSize;
        exportCanvas.height = exportSize;
        const eCtx = exportCanvas.getContext('2d');

        const scaleFactor = exportSize / (pCircleRadius * 2);

        eCtx.beginPath();
        eCtx.arc(exportSize / 2, exportSize / 2, exportSize / 2, 0, Math.PI * 2, true);
        eCtx.closePath();
        eCtx.clip();

        eCtx.fillStyle = '#ffffff';
        eCtx.fill();

        const relX = (pCropPosX - pCanvas.width / 2) * scaleFactor;
        const relY = (pCropPosY - pCanvas.height / 2) * scaleFactor;
        const relScale = pCropScale * scaleFactor;

        eCtx.save();
        eCtx.translate(exportSize / 2 + relX, exportSize / 2 + relY);
        eCtx.scale(relScale, relScale);
        eCtx.drawImage(pCropImg, -pCropImg.width / 2, -pCropImg.height / 2);
        eCtx.restore();

        const croppedDataUrl = exportCanvas.toDataURL('image/png', 0.95);

        document.getElementById('platform_cropped_logo_input').value = croppedDataUrl;
        document.getElementById('platform-applied-crop-preview').src = croppedDataUrl;
        document.getElementById('platform-applied-crop-filename').textContent = pOriginalFilename || 'uplyft_master_logo.png';
        document.getElementById('platform-dropzone-idle').style.display = 'none';
        document.getElementById('platform-dropzone-applied').style.display = 'flex';

        closePlatformCropModal();
    }
</script>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\profile\partials\update-platform-logo-form.blade.php ENDPATH**/ ?>
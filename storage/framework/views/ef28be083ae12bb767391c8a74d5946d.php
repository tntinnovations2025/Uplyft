

<style>
    /* Center Screen Warning Modal Animation */
    .warning-modal-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 9999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .warning-modal-card {
        max-width: 440px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
        background: #ffffff;
        border: 2px solid #f59e0b;
        border-radius: 20px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: 0 20px 50px -10px rgba(245, 158, 11, 0.3), 0 0 30px rgba(245, 158, 11, 0.15);
        animation: warningPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        margin: auto;
    }

    @keyframes warningPop {
        0% { opacity: 0; transform: scale(0.85) translateY(15px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Bottom Line Toast Banners (Green Success & Red Error) */
    .toast-bottom-banner {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999999;
        min-width: 340px;
        max-width: 90vw;
        border-radius: 16px;
        padding: 14px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.3);
        animation: slideUpToast 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        overflow: hidden;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    .toast-bottom-banner.toast-success {
        background: #064e3b;
        color: #ffffff;
        border: 1.5px solid #10b981;
        border-bottom: 4px solid #10b981;
        box-shadow: 0 16px 40px -6px rgba(16, 185, 129, 0.4);
    }

    .toast-bottom-banner.toast-error {
        background: #4c0519;
        color: #ffffff;
        border: 1.5px solid #f43f5e;
        border-bottom: 4px solid #f43f5e;
        box-shadow: 0 16px 40px -6px rgba(244, 63, 94, 0.4);
    }

    .toast-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        animation: toastProgress 3s linear forwards;
    }

    .toast-success .toast-progress-bar {
        background: #34d399;
    }

    .toast-error .toast-progress-bar {
        background: #fb7185;
    }

    @keyframes slideUpToast {
        0% { opacity: 0; transform: translate(-50%, 30px); }
        100% { opacity: 1; transform: translate(-50%, 0); }
    }

    @keyframes slideDownToast {
        0% { opacity: 1; transform: translate(-50%, 0); }
        100% { opacity: 1; transform: translate(-50%, 40px); }
    }

    @keyframes toastProgress {
        0% { width: 100%; }
        100% { width: 0%; }
    }
    @keyframes greenBlinkBorder {
        0%, 100% {
            border-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.75), 0 4px 15px rgba(16, 185, 129, 0.3);
            transform: scale(1);
        }
        50% {
            border-color: #34d399;
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.4), 0 0 25px rgba(52, 211, 153, 0.7);
            transform: scale(1.02);
        }
    }

    .btn-take-me-there {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        width: auto !important;
        max-width: fit-content !important;
        margin: 0 auto !important;
        padding: 8px 22px !important;
        background: linear-gradient(135deg, #059669, #10b981) !important;
        color: #ffffff !important;
        font-family: 'Outfit', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        letter-spacing: 0.2px !important;
        border: 2px solid #10b981 !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        animation: greenBlinkBorder 1.2s infinite ease-in-out !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        box-sizing: border-box !important;
    }

    .btn-take-me-there:hover {
        background: linear-gradient(135deg, #047857, #059669) !important;
        color: #ffffff !important;
        transform: translateY(-1px) scale(1.02) !important;
        box-shadow: 0 0 25px rgba(16, 185, 129, 0.7) !important;
    }
</style>

<?php
    $hasWarning = session('warning') || (!empty($errors) && method_exists($errors, 'any') && $errors->any());
    $warningMsg = session('warning') ?? '';
    if (is_array($warningMsg)) {
        $warningMsg = implode(' ', $warningMsg);
    }
    $warningUrl = session('warning_url') ?? session('action_url') ?? null;
    
    if (!$warningUrl && $warningMsg) {
        $lowerMsg = strtolower($warningMsg);
        if (str_contains($lowerMsg, 'academic term') || str_contains($lowerMsg, 'active term') || str_contains($lowerMsg, 'session')) {
            $warningUrl = route('principal.academic-terms.index');
        } elseif (str_contains($lowerMsg, 'timetable') || str_contains($lowerMsg, 'schedule')) {
            $warningUrl = route('principal.timetables.index');
        } elseif (str_contains($lowerMsg, 'student') || str_contains($lowerMsg, 'admission')) {
            $warningUrl = route('principal.students.index');
        } elseif (str_contains($lowerMsg, 'staff') || str_contains($lowerMsg, 'faculty') || str_contains($lowerMsg, 'teacher') || str_contains($lowerMsg, 'rights') || str_contains($lowerMsg, 'role')) {
            $warningUrl = route('principal.staff.index');
        } elseif (str_contains($lowerMsg, 'class') || str_contains($lowerMsg, 'subject')) {
            $warningUrl = route('principal.classes-subjects.index');
        } elseif (str_contains($lowerMsg, 'invoice') || str_contains($lowerMsg, 'fee') || str_contains($lowerMsg, 'billing') || str_contains($lowerMsg, 'ledger')) {
            $warningUrl = route('principal.invoices.index');
        } elseif (str_contains($lowerMsg, 'room')) {
            $warningUrl = route('principal.rooms.index');
        }
    }

    if (!$warningUrl && $hasWarning) {
        $warningUrl = route('principal.dashboard');
    }
?>


<div id="centerWarningModal" class="warning-modal-backdrop" style="display: <?php echo e($hasWarning ? 'flex' : 'none'); ?>;">
    <div class="warning-modal-card">
        <div style="width:48px;height:48px;margin:0 auto 12px;background:#fef3c7;border:2px solid #f59e0b;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;color:#d97706;box-shadow:0 0 16px rgba(245,158,11,0.25)">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 id="centerWarningTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0 0 10px 0;letter-spacing:-0.2px">
            Attention &amp; Important Notice
        </h3>
        <div id="centerWarningMessage" style="font-size:13px;color:#475569;line-height:1.55;margin-bottom:20px;font-weight:600">
            <?php if(session('warning')): ?>
                <?php
                    $rawWarn = session('warning');
                    $parts = preg_split('/<br\s*\/?>|\n/i', $rawWarn);
                    $parts = array_filter(array_map('trim', $parts));
                ?>
                <?php if(count($parts) > 1): ?>
                    <?php $headerText = array_shift($parts); ?>
                    <div style="font-size:13px;font-weight:800;color:#0f172a;margin-bottom:12px;text-align:left;background:#fef3c7;padding:9px 12px;border-radius:10px;border:1px solid #fde68a;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-bolt text-amber-600"></i> <?php echo e($headerText); ?>

                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;max-height:260px;overflow-y:auto;text-align:left;padding-right:4px">
                        <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!empty($part)): ?>
                                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:9px 12px;font-size:12px;color:#92400e;font-weight:600;display:flex;align-items:flex-start;gap:8px">
                                    <span style="font-size:13px;flex-shrink:0;margin-top:1px;color:#d97706"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                    <div style="flex:1"><?php echo e($part); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div style="font-size:13px;color:#475569;line-height:1.55;font-weight:600">
                        <?php echo e($rawWarn); ?>

                    </div>
                <?php endif; ?>
            <?php elseif(!empty($errors) && method_exists($errors, 'any') && $errors->any()): ?>
                <ul style="list-style:none;padding:0;margin:0;text-align:left;display:flex;flex-direction:column;gap:6px">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li style="display:flex;align-items:center;gap:6px;color:#991b1b;font-size:12.5px">
                            <span>•</span> <?php echo e($error); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                Please review the action details before continuing.
            <?php endif; ?>
        </div>
        
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;width:100%;margin:0 auto">
            
            <a href="<?php echo e($warningUrl ?: route('principal.academic-terms.index')); ?>" id="centerWarningActionBtn" class="btn-take-me-there" style="<?php echo e($warningUrl ? 'display:inline-flex;' : 'display:none;'); ?>;margin:0 auto;align-items:center;gap:6px">
                <i class="fa-solid fa-location-arrow"></i> <span>Take me there</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
            
            
            <button type="button" onclick="closeCenterWarningModal()" style="display:inline-flex;align-items:center;justify-content:center;width:auto;min-width:160px;margin:0 auto;padding:7px 18px;background:#f8fafc;color:#64748b;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;transition:all 0.2s">
                Understood &amp; Dismiss
            </button>
        </div>
    </div>
</div>


<?php if(session('success')): ?>
    <div id="bottomSuccessToast" class="toast-bottom-banner toast-success">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:28px;height:28px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;color:#6ee7b7">Action Executed Successfully</div>
                <div style="font-size:13px;font-weight:700;margin-top:2px"><?php echo e(session('success')); ?></div>
            </div>
        </div>
        <button type="button" onclick="closeToastBanner('bottomSuccessToast')" style="background:none;border:none;color:#a7f3d0;font-size:14px;cursor:pointer;padding:4px"><i class="fa-solid fa-xmark"></i></button>
        <div class="toast-progress-bar"></div>
    </div>
<?php endif; ?>


<?php if(session('error')): ?>
    <div id="bottomErrorToast" class="toast-bottom-banner toast-error">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:28px;height:28px;border-radius:50%;background:#f43f5e;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;color:#fda4af">Execution Failed / Error</div>
                <div style="font-size:13px;font-weight:700;margin-top:2px"><?php echo e(session('error')); ?></div>
            </div>
        </div>
        <button type="button" onclick="closeToastBanner('bottomErrorToast')" style="background:none;border:none;color:#fecdd3;font-size:14px;cursor:pointer;padding:4px"><i class="fa-solid fa-xmark"></i></button>
        <div class="toast-progress-bar"></div>
    </div>
<?php endif; ?>


<script>
    function closeCenterWarningModal() {
        const modal = document.getElementById('centerWarningModal');
        if (modal) modal.style.display = 'none';
    }

    function showWarningModal(title, message, targetUrl, buttonText) {
        const modal = document.getElementById('centerWarningModal');
        const titleEl = document.getElementById('centerWarningTitle');
        const msgEl = document.getElementById('centerWarningMessage');
        const actionBtn = document.getElementById('centerWarningActionBtn');

        if (modal && titleEl && msgEl) {
            titleEl.innerText = title || 'Attention & Important Notice';
            
            if (typeof message === 'string' && (message.includes('<br>') || message.includes('\n'))) {
                const parts = message.split(/<br\s*\/?>|\n/i).map(p => p.trim()).filter(Boolean);
                let html = '';
                if (parts.length > 0) {
                    const header = parts.shift();
                    html += `<div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:14px;text-align:left;background:#fef3c7;padding:10px 14px;border-radius:10px;border:1px solid #fde68a;display:flex;align-items:center;gap:6px"><i class="fa-solid fa-bolt text-amber-600"></i> ${header}</div>`;
                    if (parts.length > 0) {
                        html += `<div style="display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;text-align:left;padding-right:4px">`;
                        parts.forEach(part => {
                            html += `
                                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 14px;font-size:12.5px;color:#92400e;font-weight:600;display:flex;align-items:flex-start;gap:10px">
                                    <span style="font-size:13px;flex-shrink:0;margin-top:1px;color:#d97706"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                    <div style="flex:1">${part}</div>
                                </div>
                            `;
                        });
                        html += `</div>`;
                    }
                }
                msgEl.innerHTML = html;
            } else {
                msgEl.innerHTML = message || '';
            }

            let url = targetUrl;
            if (!url && typeof message === 'string') {
                const lower = message.toLowerCase();
                if (lower.includes('academic term') || lower.includes('active term') || lower.includes('session')) {
                    url = '/principal/academic-terms';
                } else if (lower.includes('timetable') || lower.includes('schedule')) {
                    url = '/principal/timetables';
                } else if (lower.includes('student') || lower.includes('admission')) {
                    url = '/principal/students';
                } else if (lower.includes('staff') || lower.includes('faculty') || lower.includes('teacher') || lower.includes('rights') || lower.includes('role')) {
                    url = '/principal/staff';
                } else if (lower.includes('class') || lower.includes('subject')) {
                    url = '/principal/classes-subjects';
                } else if (lower.includes('fee') || lower.includes('invoice') || lower.includes('billing')) {
                    url = '/principal/invoices';
                } else if (lower.includes('room')) {
                    url = '/principal/rooms';
                } else {
                    url = '/principal/dashboard';
                }
            }

            if (actionBtn) {
                actionBtn.href = url || '/principal/dashboard';
                actionBtn.style.display = 'inline-flex';
                actionBtn.style.margin = '0 auto';
                actionBtn.style.alignItems = 'center';
                actionBtn.style.gap = '6px';
                const label = buttonText || 'Take me there';
                actionBtn.innerHTML = `<i class="fa-solid fa-location-arrow"></i> <span>${label}</span> <i class="fa-solid fa-arrow-right"></i>`;
            }
            
            document.body.appendChild(modal);
            modal.style.display = 'flex';
        }
    }

    function closeToastBanner(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.style.animation = 'slideDownToast 0.3s ease-in forwards';
            setTimeout(() => { toast.remove(); }, 300);
        }
    }

    function showSuccessToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-bottom-banner toast-success';
        toast.innerHTML = `
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:28px;height:28px;border-radius:50%;background:#10b981;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0"><i class="fa-solid fa-check"></i></div>
                <div>
                    <div style="font-size:11px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;color:#6ee7b7">Action Executed Successfully</div>
                    <div style="font-size:13px;font-weight:700;margin-top:2px">${message}</div>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#a7f3d0;font-size:14px;cursor:pointer;padding:4px"><i class="fa-solid fa-xmark"></i></button>
            <div class="toast-progress-bar"></div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.animation = 'slideDownToast 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }

    function showErrorToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-bottom-banner toast-error';
        toast.innerHTML = `
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:28px;height:28px;border-radius:50%;background:#f43f5e;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0"><i class="fa-solid fa-xmark"></i></div>
                <div>
                    <div style="font-size:11px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;color:#fda4af">Execution Failed / Error</div>
                    <div style="font-size:13px;font-weight:700;margin-top:2px">${message}</div>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#fecdd3;font-size:14px;cursor:pointer;padding:4px"><i class="fa-solid fa-xmark"></i></button>
            <div class="toast-progress-bar"></div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.animation = 'slideDownToast 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }

    // Auto dismiss server-rendered bottom toast banners & prevent re-appearance on Back/Forward navigation (bfcache)
    function initToastBanners() {
        ['bottomSuccessToast', 'bottomErrorToast'].forEach(id => {
            const toast = document.getElementById(id);
            if (toast) {
                setTimeout(() => {
                    closeToastBanner(id);
                }, 3000);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initToastBanners();
    });

    // Handle browser Back/Forward button cache restoration (bfcache)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            ['bottomSuccessToast', 'bottomErrorToast'].forEach(id => {
                const toast = document.getElementById(id);
                if (toast) {
                    toast.remove();
                }
            });
        }
    });
</script>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views/partials/alert-notifications.blade.php ENDPATH**/ ?>
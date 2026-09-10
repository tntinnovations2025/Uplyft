
<?php
    $platformLogoPath = \App\Models\PlatformSetting::get('platform_logo_path');
    $uplyftMasterLogoUrl = null;
    if ($platformLogoPath && file_exists(public_path('storage/' . $platformLogoPath))) {
        $uplyftMasterLogoUrl = asset('storage/' . $platformLogoPath);
    } elseif (file_exists(public_path('images/uplyft-logo.png'))) {
        $uplyftMasterLogoUrl = asset('images/uplyft-logo.png');
    } elseif ($platformLogoPath) {
        $uplyftMasterLogoUrl = asset('storage/' . $platformLogoPath);
    } else {
        $uplyftMasterLogoUrl = asset('images/uplyft-logo.png');
    }

    $branding = $instituteBranding ?? null;

    $isGlobalAdmin = (
        (isset($branding) && isset($branding->is_tenant) && !$branding->is_tenant) ||
        (auth()->check() && auth()->user()->isGlobalAdmin()) ||
        request()->is('global-admin*') ||
        request()->routeIs('global-admin.*') ||
        (request()->getPort() == 8000 && !request()->routeIs('principal.*') && !request()->routeIs('student.*') && !request()->routeIs('teacher.*'))
    );

    // Resolve dynamic logo for current tenant vs Global Admin platform:
    $spinnerLogoUrl = null;
    if ($isGlobalAdmin) {
        $spinnerLogoUrl = $uplyftMasterLogoUrl;
        $spinnerInitial = 'U';
        $spinnerAltText = 'UPLYFT Global Platform';
    } elseif ($branding && !empty($branding->logo_url)) {
        $spinnerLogoUrl = $branding->logo_url;
        $spinnerInitial = $branding->initial ?? 'U';
        $spinnerAltText = ($branding->name ?? 'Platform') . ' Logo';
    } elseif ($branding && !empty($branding->icon_url)) {
        $spinnerLogoUrl = $branding->icon_url;
        $spinnerInitial = $branding->initial ?? 'U';
        $spinnerAltText = ($branding->name ?? 'Platform') . ' Logo';
    } else {
        $spinnerLogoUrl = $uplyftMasterLogoUrl;
        $spinnerInitial = 'U';
        $spinnerAltText = 'UPLYFT Platform';
    }

    $isLoginPage = request()->routeIs('login')
                || request()->routeIs('principal.login')
                || request()->routeIs('global-admin.login')
                || request()->routeIs('*.login')
                || request()->is('login*')
                || request()->is('*/login*')
                || !auth()->check();
?>

<?php if($isLoginPage): ?>
<div id="uplyft-global-loader" class="uplyft-loader-screen" aria-hidden="true" role="status" aria-label="Loading Portal Session">
    
    
    <div class="uplyft-loader-light-bg">
        <div class="uplyft-mesh-light-1"></div>
        <div class="uplyft-mesh-light-2"></div>
        <div class="uplyft-light-aurora-glow"></div>
    </div>

    
    <div class="uplyft-loader-content">
        
        
        <div class="uplyft-coin-stage">
            
            
            <div class="uplyft-orbit-ring uplyft-orbit-ring-primary"></div>
            <div class="uplyft-orbit-ring uplyft-orbit-ring-secondary"></div>

            
            <div class="uplyft-coin-shadow"></div>

            
            <div class="uplyft-logo-ion-aura"></div>
            <div class="uplyft-logo-pulse-halo"></div>

            
            <div class="uplyft-logo-frame uplyft-coin-spinning">
                
                <div class="uplyft-coin-specular-sweep"></div>

                <?php if(!empty($spinnerLogoUrl)): ?>
                    <img src="<?php echo e($spinnerLogoUrl); ?>" 
                         alt="<?php echo e($spinnerAltText); ?>" 
                         class="uplyft-logo-img"
                         loading="eager"
                         onerror="this.style.display='none'; document.getElementById('uplyft-loader-fallback-badge').style.display='flex';" />
                <?php endif; ?>

                
                <div id="uplyft-loader-fallback-badge" 
                     class="uplyft-logo-fallback" 
                     style="<?php echo e((!empty($spinnerLogoUrl)) ? 'display:none;' : 'display:flex;'); ?>">
                    <span class="uplyft-fallback-letter"><?php echo e($spinnerInitial); ?></span>
                </div>
            </div>
        </div>

        
        <div class="uplyft-loader-under-logo-group">
            
            
            <div class="uplyft-attribution-badge-center">
                <span class="uplyft-attribution-spark">✨</span>
                <span class="uplyft-attribution-powered">POWERED BY</span>
                <span class="uplyft-attribution-tnt">TNT INNOVATIONS</span>
            </div>

            
            <div class="uplyft-loader-bar-wrap">
                <div class="uplyft-loader-bar-track">
                    <div class="uplyft-loader-bar-progress">
                        <div class="uplyft-loader-laser-spark"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* ========================================================================= */
    /* UI/UX PRO MAX PLAIN WHITE & GLOSSY BLUE 3D COIN LOADER                    */
    /* ========================================================================= */

    /* Full Page Screen Overlay - Pure Plain White */
    .uplyft-loader-screen {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        backdrop-filter: blur(32px);
        -webkit-backdrop-filter: blur(32px);
        transition: opacity 0.55s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.55s cubic-bezier(0.16, 1, 0.3, 1), transform 0.55s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: auto;
        overflow: hidden;
    }

    .uplyft-loader-screen.uplyft-loader-dismissed {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: scale(1.05);
    }

    /* Soft Atmospheric Glow on White */
    .uplyft-loader-light-bg {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .uplyft-mesh-light-1 {
        position: absolute;
        top: -15%;
        left: -10%;
        width: 65vw;
        height: 65vh;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, rgba(14, 165, 233, 0.06) 50%, transparent 70%);
        filter: blur(70px);
        animation: meshLightFloat1 12s ease-in-out infinite alternate;
    }

    .uplyft-mesh-light-2 {
        position: absolute;
        bottom: -15%;
        right: -10%;
        width: 65vw;
        height: 65vh;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.16) 0%, rgba(59, 130, 246, 0.05) 50%, transparent 70%);
        filter: blur(70px);
        animation: meshLightFloat2 15s ease-in-out infinite alternate;
    }

    @keyframes meshLightFloat1 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(50px, 35px) scale(1.12); }
    }

    @keyframes meshLightFloat2 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-45px, -30px) scale(1.15); }
    }

    .uplyft-light-aurora-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(250, 204, 21, 0.14) 0%, rgba(56, 189, 248, 0.20) 35%, rgba(99, 102, 241, 0.08) 65%, transparent 75%);
        filter: blur(60px);
        animation: lightAuroraBreathing 3s ease-in-out infinite alternate;
    }

    @keyframes lightAuroraBreathing {
        0% {
            transform: translate(-50%, -50%) scale(0.92);
            opacity: 0.8;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.22);
            opacity: 1;
        }
    }

    /* Center Content Container */
    .uplyft-loader-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 30px;
        position: relative;
        z-index: 10;
    }

    /* ── 3-Stage Cinematic Choreography ──────────────────────────────────────── */
    @keyframes cinematicCoinStage {
        0% {
            transform: scale3d(0.12, 0.12, 0.12) translateZ(-500px);
            opacity: 0;
            filter: blur(8px);
        }
        18% {
            transform: scale3d(1.18, 1.18, 1.18) translateZ(80px);
            opacity: 1;
            filter: blur(0px);
        }
        25% {
            transform: scale3d(1.0, 1.0, 1.0) translateZ(0px);
            opacity: 1;
            filter: blur(0px);
        }
        78% {
            transform: scale3d(1.0, 1.0, 1.0) translateZ(0px);
            opacity: 1;
            filter: blur(0px);
        }
        92% {
            transform: scale3d(0.28, 0.28, 0.28) translateZ(-520px);
            opacity: 0.2;
            filter: blur(4px);
        }
        100% {
            transform: scale3d(0.06, 0.06, 0.06) translateZ(-800px);
            opacity: 0;
            filter: blur(10px);
        }
    }

    .uplyft-coin-stage {
        position: relative;
        width: 160px;
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        perspective: 1200px;
        perspective-origin: center center;
        transform-style: preserve-3d;
        animation: cinematicCoinStage 3.0s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Celestial Orbit Energy Rings */
    .uplyft-orbit-ring {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .uplyft-orbit-ring-primary {
        width: 196px;
        height: 196px;
        border: 1.5px dashed rgba(234, 179, 8, 0.65);
        box-shadow: 0 0 16px rgba(250, 204, 21, 0.4);
        transform: rotateX(68deg) rotateY(15deg);
        animation: orbitSpinPrimary 6s linear infinite;
    }

    .uplyft-orbit-ring-secondary {
        width: 228px;
        height: 228px;
        border: 1.5px solid rgba(2, 132, 199, 0.55);
        box-shadow: 0 0 24px rgba(56, 189, 248, 0.45);
        transform: rotateX(72deg) rotateY(-25deg);
        animation: orbitSpinSecondary 8s linear infinite reverse;
    }

    @keyframes orbitSpinPrimary {
        from { transform: rotateX(68deg) rotateY(15deg) rotateZ(0deg); }
        to { transform: rotateX(68deg) rotateY(15deg) rotateZ(360deg); }
    }

    @keyframes orbitSpinSecondary {
        from { transform: rotateX(72deg) rotateY(-25deg) rotateZ(0deg); }
        to { transform: rotateX(72deg) rotateY(-25deg) rotateZ(360deg); }
    }

    /* Floor Drop Shadow */
    .uplyft-coin-shadow {
        position: absolute;
        bottom: -16px;
        width: 110px;
        height: 28px;
        border-radius: 50%;
        background: radial-gradient(ellipse at center, rgba(30, 58, 138, 0.20) 0%, rgba(2, 132, 199, 0.08) 40%, transparent 75%);
        filter: blur(5px);
        transform: rotateX(78deg);
        animation: shadowGleamPulse 1.1s ease-in-out infinite alternate;
        pointer-events: none;
    }

    @keyframes shadowGleamPulse {
        0% {
            transform: rotateX(78deg) scaleX(0.75);
            opacity: 0.7;
        }
        100% {
            transform: rotateX(78deg) scaleX(1.15);
            opacity: 1;
        }
    }

    /* Ion Aura & Halos */
    .uplyft-logo-ion-aura {
        position: absolute;
        inset: -16px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(250, 204, 21, 0.35) 0%, rgba(56, 189, 248, 0.28) 45%, transparent 70%);
        filter: blur(16px);
        animation: ionAuraGlow 2.5s ease-in-out infinite alternate;
        pointer-events: none;
    }

    @keyframes ionAuraGlow {
        0% { transform: scale(0.92); opacity: 0.65; }
        100% { transform: scale(1.2); opacity: 1; }
    }

    .uplyft-logo-pulse-halo {
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 1.5px solid rgba(250, 204, 21, 0.5);
        box-shadow: 0 0 24px rgba(250, 204, 21, 0.45);
        animation: haloPulseGleam 2.2s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes haloPulseGleam {
        0%, 100% {
            transform: scale(0.96);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.95;
        }
    }

    /* Standing Straight Upright 360° 3D Coin Spin (Counter-Clockwise) */
    @keyframes uprightCoinSpin360 {
        0% {
            transform: rotateY(0deg);
        }
        100% {
            transform: rotateY(-360deg);
        }
    }

    /* 24k Polished Gold Coin Frame (Masterpiece Light Styling) */
    .uplyft-logo-frame.uplyft-coin-spinning {
        width: 126px;
        height: 126px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #facc15;
        box-shadow: 
            0 0 0 1px #ca8a04,
            0 0 0 2.5px rgba(254, 240, 138, 0.8),
            0 0 28px rgba(250, 204, 21, 0.5),
            0 14px 35px -6px rgba(2, 132, 199, 0.25),
            inset 0 0 8px rgba(254, 240, 138, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 7px;
        position: relative;
        transform-style: preserve-3d;
        backface-visibility: visible;
        animation: uprightCoinSpin360 1.1s linear infinite;
    }

    /* Specular Reflection Gleam Sweep */
    .uplyft-coin-specular-sweep {
        position: absolute;
        inset: -100%;
        background: linear-gradient(
            115deg,
            transparent 0%,
            transparent 40%,
            rgba(255, 255, 255, 0.7) 50%,
            rgba(254, 240, 138, 0.5) 55%,
            transparent 65%,
            transparent 100%
        );
        animation: specularSweep 2.2s ease-in-out infinite;
        pointer-events: none;
        z-index: 5;
    }

    @keyframes specularSweep {
        0% { transform: translateX(60%) translateY(-60%); }
        100% { transform: translateX(-60%) translateY(60%); }
    }

    .uplyft-logo-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
        background: #ffffff;
        display: block;
        backface-visibility: visible;
        position: relative;
        z-index: 2;
    }

    .uplyft-logo-fallback {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #4f46e5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        box-shadow: inset 0 2px 8px rgba(255, 255, 255, 0.4);
        position: relative;
        z-index: 2;
    }

    .uplyft-fallback-letter {
        font-family: 'Outfit', sans-serif;
        font-size: 52px;
        font-weight: 900;
        color: #ffffff;
        line-height: 1;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }

    /* Synced Group Under Logo */
    @keyframes underLogoFadeSync {
        0% {
            opacity: 0;
            transform: translateY(16px);
        }
        20% {
            opacity: 1;
            transform: translateY(0);
        }
        78% {
            opacity: 1;
            transform: translateY(0);
        }
        95%, 100% {
            opacity: 0;
            transform: translateY(-8px);
        }
    }

    .uplyft-loader-under-logo-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        animation: underLogoFadeSync 3.0s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Centered 'POWERED BY TNT INNOVATIONS' Premium Attribution Badge */
    .uplyft-attribution-badge-center {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 16px;
        border-radius: 9999px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 
            0 4px 14px rgba(15, 23, 42, 0.06),
            0 1px 3px rgba(15, 23, 42, 0.03);
        font-family: 'Outfit', -apple-system, sans-serif;
        user-select: none;
        transition: all 0.2s ease;
    }

    .uplyft-attribution-spark {
        font-size: 11px;
        line-height: 1;
        display: inline-block;
        animation: sparkPulse 2s ease-in-out infinite alternate;
    }

    @keyframes sparkPulse {
        0% { transform: scale(0.9) rotate(0deg); opacity: 0.8; }
        100% { transform: scale(1.15) rotate(15deg); opacity: 1; }
    }

    .uplyft-attribution-powered {
        font-size: 9.5px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 1.6px;
        text-transform: uppercase;
    }

    .uplyft-attribution-tnt {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: #0f172a;
        background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Laser Precision Progress Bar - Glossy Blue Track */
    .uplyft-loader-bar-wrap {
        width: 250px;
        height: 5px;
        margin-top: 16px;
        position: relative;
    }

    .uplyft-loader-bar-track {
        width: 100%;
        height: 100%;
        border-radius: 9999px;
        background: rgba(2, 132, 199, 0.12);
        border: 1px solid rgba(2, 132, 199, 0.25);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        position: relative;
    }

    .uplyft-loader-bar-progress {
        width: 0%;
        height: 100%;
        background: linear-gradient(180deg, rgba(255,255,255,0.4) 0%, transparent 50%), linear-gradient(90deg, #0284c7 0%, #38bdf8 50%, #2563eb 100%);
        border-radius: 9999px;
        position: absolute;
        top: 0;
        left: 0;
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.9), 0 0 20px rgba(2, 132, 199, 0.6);
        animation: barFillExact 3s cubic-bezier(0.2, 0, 0.2, 1) forwards;
    }

    .uplyft-loader-laser-spark {
        position: absolute;
        top: -3px;
        right: -2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 0 10px #ffffff, 0 0 18px #38bdf8, 0 0 25px #0284c7;
    }

    @keyframes barFillExact {
        0% { width: 0%; }
        100% { width: 100%; }
    }
</style>

<script>
    (function() {
        const loader = document.getElementById('uplyft-global-loader');
        if (!loader) return;

        // Guarantee loader is visible and on top on open or refresh
        loader.style.display = 'flex';
        loader.style.opacity = '1';
        loader.style.visibility = 'visible';
        loader.classList.remove('uplyft-loader-dismissed');

        const exactDisplayDuration = 3000; // EXACT 3.0 SECONDS (3000ms)

        function dismissLoader() {
            setTimeout(function() {
                loader.classList.add('uplyft-loader-dismissed');
                setTimeout(function() {
                    loader.style.display = 'none';
                }, 550);
            }, exactDisplayDuration);
        }

        dismissLoader();

        // Trigger loader immediately whenever a login / authentication form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                if (form.querySelector('input[type="password"]')) {
                    form.addEventListener('submit', function() {
                        loader.style.display = 'flex';
                        loader.classList.remove('uplyft-loader-dismissed');
                        loader.style.opacity = '1';
                        loader.style.visibility = 'visible';
                    });
                }
            });
        });
    })();
</script>
<?php endif; ?>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views/partials/institute-loader.blade.php ENDPATH**/ ?>
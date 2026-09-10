<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <style>
        .portal-header-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 10px;
        }

        .portal-mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            color: #d48806;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 11px;
            line-height: 1.45;
            color: #475569;
        }

        .input-group { margin-bottom: 9px; }
        .input-label { display: block; font-size: 9.5px; font-weight: 800; color: #475569; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 10px; font-size: 12px; pointer-events: none; opacity: 0.75; }

        .custom-input {
            width: 100%;
            height: 32px;
            padding: 0 8px 0 28px !important;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #0f172a;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            outline: none;
            transition: all 0.18s ease;
        }

        .custom-input:focus {
            border-color: #d97706;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.18);
        }

        .custom-input::placeholder {
            color: #94a3b8;
            font-size: 11px;
        }

        .login-submit-btn {
            width: 100%;
            height: 36px;
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-top: 10px;
        }

        .login-submit-btn:hover {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(217, 119, 6, 0.38);
        }

        .other-portals {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: center;
            font-size: 11px;
        }

        .portal-link {
            color: #0284c7;
            text-decoration: none;
            font-weight: 700;
        }

        .portal-link:hover {
            text-decoration: underline;
        }
    </style>

    <!-- Header Portal Badge -->
    <div class="portal-header-group">
        <div class="portal-mode-badge">
            <span>🔑</span>
            <span>PASSWORD RESET REQUEST</span>
        </div>
    </div>

    <div class="info-card">
        Forgot your password? Enter your <strong>Email</strong> or <strong>Account ID</strong>. Submitting dispatches a request to your <strong>Institute Admin</strong> to issue a password reset.
    </div>

    <!-- Session Status -->
    <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-2','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-2','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.email')); ?>">
        <?php echo csrf_field(); ?>

        <!-- Email or Identifier -->
        <div class="input-group">
            <label for="credential" class="input-label">Email Address or Account ID</label>
            <div class="input-wrapper">
                <input id="credential" class="custom-input" type="text" name="credential" value="<?php echo e(old('credential')); ?>" required autofocus
                    placeholder="e.g. teacher@apex.edu.pk, TCH-APEX-01" />
                <span class="input-icon">✉️</span>
            </div>
            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('credential'),'class' => 'mt-1 text-xs text-red-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('credential')),'class' => 'mt-1 text-xs text-red-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
        </div>

        <button type="submit" class="login-submit-btn">
            <span>Send Reset Request</span>
            <span>&rarr;</span>
        </button>

        <div class="other-portals">
            <a href="<?php echo e(route('login')); ?>" class="portal-link">&larr; Back to Login</a>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>
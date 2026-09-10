<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Change OTP</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0b0f19; margin: 0; padding: 0; color: #f8fafc; }
        .container { max-width: 580px; margin: 40px auto; background: #111827; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .header { background: linear-gradient(135deg, #4f46e5, #9333ea); padding: 32px 28px; color: #fff; text-align: center; }
        .header h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
        .header p { margin: 0; font-size: 13px; opacity: .85; }
        .body { padding: 32px 28px; color: #cbd5e1; }
        .body p { font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .otp-box { background: rgba(99,102,241,0.1); border: 2px dashed #6366f1; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .otp-code { font-family: monospace, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #38bdf8; }
        .otp-expiry { font-size: 12px; color: #94a3b8; margin-top: 8px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Change OTP</h1>
            <p><?php echo e($user->institute?->name ?? 'UPLYFT'); ?> — Account Security</p>
        </div>
        <div class="body">
            <p>Hello <strong><?php echo e($user->name); ?></strong>,</p>
            <p>A request was made to change the password for your account (<strong><?php echo e($user->email); ?></strong>). Use the One-Time Passcode below to complete the change.</p>

            <div class="otp-box">
                <div style="font-size:12px;color:#a5b4fc;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:6px">Your One-Time Passcode (OTP)</div>
                <div class="otp-code"><?php echo e($otp); ?></div>
                <div class="otp-expiry">⏱️ Valid for 2 minutes only</div>
            </div>

            <p style="font-size:12px;color:#94a3b8">If you did not request this, please ignore this email and contact your Principal / Institute Administration.</p>
        </div>
        <div class="footer">
            &copy; <?php echo e(date('Y')); ?> UPLYFT Multi-Tenant School Platform &bull; Automated Security Dispatch
        </div>
    </div>
</body>
</html><?php /**PATH D:\UPLYFT\uplifyt\resources\views\emails\password-change-otp.blade.php ENDPATH**/ ?>
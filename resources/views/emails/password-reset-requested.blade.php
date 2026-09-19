<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset OTP & Security Notice</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0b0f19; margin: 0; padding: 0; color: #f8fafc; }
        .container { max-width: 580px; margin: 40px auto; background: #111827; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .header { background: linear-gradient(135deg, #4f46e5, #9333ea); padding: 32px 28px; color: #fff; text-align: center; }
        .header h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
        .header p { margin: 0; font-size: 13px; opacity: .85; }
        .body { padding: 32px 28px; color: #cbd5e1; }
        .body p { font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .otp-box { background: rgba(99,102,241,0.1); border: 2px dashed #6366f1; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .otp-code { font-family: monospace, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #E8CEAA; }
        .otp-expiry { font-size: 12px; color: #94a3b8; margin-top: 8px; }
        .warning-card { background: rgba(239,68,68,0.12); border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin: 24px 0; }
        .warning-card p { margin: 0; font-size: 13px; color: #fca5a5; line-height: 1.5; }
        .btn-cancel { display: inline-block; background: #ef4444; color: #ffffff !important; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; margin-top: 10px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset OTP</h1>
            <p>Security Request Notice — UPLYFT Governance</p>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>A password reset request was recently submitted for your account (<strong>{{ $user->email ?? $user->identifier }}</strong>).</p>

            <div class="otp-box">
                <div style="font-size:12px;color:#a5b4fc;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:6px">Your One-Time Passcode (OTP)</div>
                <div class="otp-code">{{ $notification->otp }}</div>
                <div class="otp-expiry">⏱️ Valid for 30 minutes (Expires: {{ $notification->otp_expires_at ? $notification->otp_expires_at->format('h:i A') : '30 mins' }})</div>
            </div>

            <p>You can use this OTP or wait for your Administrator / Principal to complete the reset procedure.</p>

            <div class="warning-card">
                <p>⚠️ <strong>Was this NOT you?</strong></p>
                <p>If you did not request a password reset, someone else may be trying to access your account. Click the button below immediately to void this request and secure your account.</p>
                <div style="text-align:center;margin-top:12px">
                    <a href="{{ url('/password/cancel/' . $notification->cancellation_token) }}" class="btn-cancel">🛑 Cancel Password Reset Request</a>
                </div>
            </div>

            <p style="font-size:12px;color:#94a3b8">If the button above does not work, copy and paste this link into your browser:<br>
            <a href="{{ url('/password/cancel/' . $notification->cancellation_token) }}" style="color:#E8CEAA">{{ url('/password/cancel/' . $notification->cancellation_token) }}</a></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UPLYFT Multi-Tenant School Platform &bull; Automated Security Dispatch
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Successful</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0b0f19; margin: 0; padding: 0; color: #f8fafc; }
        .container { max-width: 580px; margin: 40px auto; background: #111827; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .header { background: linear-gradient(135deg, #10b981, #06b6d4); padding: 32px 28px; color: #fff; text-align: center; }
        .header h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
        .header p { margin: 0; font-size: 13px; opacity: .9; }
        .body { padding: 32px 28px; color: #cbd5e1; }
        .body p { font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .credential-card { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 12px; padding: 20px; margin: 24px 0; }
        .credential-card table { width: 100%; border-collapse: collapse; }
        .credential-card td { padding: 8px 0; font-size: 14px; }
        .credential-card td:first-child { color: #94a3b8; width: 140px; }
        .credential-card td:last-child { font-weight: 700; color: #ffffff; }
        .cta { display: inline-block; background: #10b981; color: #ffffff !important; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 12px; }
        .info-card { background: rgba(245,158,11,0.12); border-left: 4px solid #f59e0b; padding: 14px; border-radius: 8px; margin: 20px 0; font-size: 13px; color: #fef08a; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; border-top: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Password Reset Complete</h1>
            <p>Your account security credentials have been updated</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            <p>Your password for the UPLYFT platform has been successfully reset
            @if($processedBy)
                by Administrator <strong>{{ $processedBy->name }}</strong>.
            @else
                via OTP verification.
            @endif
            </p>

            <div class="credential-card">
                <table>
                    <tr><td>Account Name</td><td>{{ $user->name }}</td></tr>
                    @if($user->email)
                    <tr><td>Email Address</td><td>{{ $user->email }}</td></tr>
                    @endif
                    @if($user->identifier)
                    <tr><td>Account Identifier</td><td>{{ $user->identifier }}</td></tr>
                    @endif
                    <tr><td>New Password</td><td style="font-family:monospace;font-size:16px;color:#E8CEAA">{{ $newPassword }}</td></tr>
                </table>
            </div>

            <div class="info-card">
                🔒 <strong>Security Recommendation:</strong> For maximum safety, please log in immediately using your new password and change it to your personal preference under Profile Settings.
            </div>

            @php
                $loginUrl = match($user->role) {
                    'global_admin' => url('/global-admin/login'),
                    'principal'    => url('/principal/login'),
                    default        => url('/login'),
                };
            @endphp

            <div style="text-align:center;margin-top:24px">
                <a href="{{ $loginUrl }}" class="cta">🔐 Log In To Your Portal</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UPLYFT Multi-Tenant Governance Platform
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f7f8fc; margin: 0; padding: 0; }
        .container { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #6c63ff, #00ced1); padding: 32px 28px; color: #fff; }
        .header h1 { margin: 0 0 6px; font-size: 22px; }
        .header p { margin: 0; font-size: 14px; opacity: .85; }
        .body { padding: 28px; color: #333; }
        .body p { font-size: 15px; line-height: 1.6; }
        .credential-box { background: #f7f8fc; border: 1.5px solid #e0e3ea; border-radius: 10px; padding: 20px; margin: 20px 0; }
        .credential-box table { width: 100%; border-collapse: collapse; }
        .credential-box td { padding: 8px 0; font-size: 14px; }
        .credential-box td:first-child { color: #888; width: 120px; }
        .credential-box td:last-child { font-weight: 600; color: #333; }
        .cta { display: inline-block; background: #6c63ff; color: #fff !important; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 12px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
        .warning { background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 16px; border-radius: 6px; margin: 16px 0; font-size: 13px; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Welcome to UPLYFT</h1>
            <p>Your institute "{{ $institute->name }}" has been registered.</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $principal->name }}</strong>,</p>
            <p>Your Principal master login has been created for <strong>{{ $institute->name }}</strong>. Below are your login credentials:</p>

            <div class="credential-box">
                <table>
                    <tr><td>Login URL</td><td>{{ config('app.url') }}/login</td></tr>
                    <tr><td>Email</td><td>{{ $principal->email }}</td></tr>
                    @if($principal->identifier)
                    <tr><td>Employee ID</td><td>{{ $principal->identifier }}</td></tr>
                    @endif
                    <tr><td>Password</td><td>{{ $plainPassword }}</td></tr>
                </table>
            </div>

            <div class="warning">
                ⚠️ <strong>Important:</strong> Please change your password immediately after your first login. Do not share these credentials with anyone.
            </div>

            <p>You can log in with either your email or Employee ID.</p>

            <a href="{{ config('app.url') }}/login" class="cta">🔐 Log In Now</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UPLYFT — Multi-Institute LMS Platform
        </div>
    </div>
</body>
</html>

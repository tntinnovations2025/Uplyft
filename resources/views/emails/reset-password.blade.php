<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Your Password</title>
</head>
<body style="margin:0;padding:0;background-color:#F3F4F6;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;">

    {{-- Outer wrapper — centres the card on any screen size --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#F3F4F6;padding:40px 20px;">
        <tr>
            <td align="center">

                {{-- Card --}}
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #E5E7EB;">

                    {{-- Brand Header --}}
                    <tr>
                        <td style="background-color:#0E0E11;padding:28px 32px;text-align:center;">
                            {{-- Inline logo fallback — text-based for maximum compatibility --}}
                            <table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
                                <tr>
                                    <td style="width:44px;height:44px;background-color:#D48A2E;border-radius:12px;text-align:center;vertical-align:middle;">
                                        <span style="display:inline-block;line-height:44px;color:#1A1200;font-size:20px;font-weight:800;font-family:'Segoe UI',Tahoma,sans-serif;">U</span>
                                    </td>
                                    <td style="padding-left:14px;vertical-align:middle;">
                                        <span style="color:#FFFFFF;font-size:22px;font-weight:800;letter-spacing:-0.5px;font-family:'Segoe UI',Tahoma,sans-serif;">{{ $appName }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 32px 20px;">

                            {{-- Greeting --}}
                            <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#1B1A17;line-height:1.4;">
                                Hi {{ $user->name }},
                            </p>

                            {{-- Instruction --}}
                            <p style="margin:0 0 24px;font-size:14px;color:#4B5563;line-height:1.7;">
                                We received a request to reset the password for your
                                <strong>{{ $appName }}</strong> account. Click the button
                                below to choose a new password.
                            </p>

                            {{-- CTA Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding:0 0 28px;">
                                        <a href="{{ $resetUrl }}"
                                           style="display:inline-block;
                                                  background-color:#D48A2E;
                                                  color:#FFFFFF;
                                                  text-decoration:none;
                                                  font-size:15px;
                                                  font-weight:700;
                                                  padding:14px 36px;
                                                  border-radius:10px;
                                                  letter-spacing:0.3px;
                                                  font-family:'Segoe UI',Tahoma,sans-serif;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Expiry Notice --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 24px;background-color:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <p style="margin:0;font-size:13px;color:#92400E;line-height:1.5;">
                                            <strong>⏰ This link expires in {{ $expiresIn }} minutes.</strong>
                                            If you did not request this reset, you can safely ignore this email —
                                            no changes will be made to your account.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Security Notice --}}
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 0;background-color:#F3F4F6;border:1px solid #E5E7EB;border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <p style="margin:0;font-size:12px;color:#6B7280;line-height:1.6;">
                                            <strong>🔒 Security tip:</strong> Never share this link with anyone.
                                            {{ $appName }} staff will never ask for your password or reset token.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <hr style="border:none;border-top:1px solid #E5E7EB;margin:0;" />
                        </td>
                    </tr>

                    {{-- Fallback Raw URL --}}
                    <tr>
                        <td style="padding:20px 32px 24px;">
                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.5px;">
                                Can't click the button?
                            </p>
                            <p style="margin:0;font-size:12px;color:#6B7280;line-height:1.6;word-break:break-all;">
                                Copy and paste this URL into your browser:<br />
                                <a href="{{ $resetUrl }}" style="color:#D48A2E;text-decoration:underline;word-break:break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#F9FAFB;padding:20px 32px;text-align:center;border-top:1px solid #E5E7EB;">
                            <p style="margin:0 0 4px;font-size:12px;color:#9CA3AF;">
                                This email was sent by {{ $appName }}.
                            </p>
                            <p style="margin:0;font-size:12px;color:#9CA3AF;">
                                If you didn't request a password reset, no action is required.
                            </p>
                        </td>
                    </tr>

                </table>
                {{-- End Card --}}

            </td>
        </tr>
    </table>

</body>
</html>

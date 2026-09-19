@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }
@endphp

@section('title', 'Process Password Reset')
@section('page-title', 'Authorize & Reset User Password')
@section('page-subtitle', 'Execute password reset or deny request for student/teacher account')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">

    @if (session('success'))
        <div class="alert alert-success" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);color:#10b981;padding:12px 16px;border-radius:10px;margin-bottom:20px">
            <span>✅ {{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.4);color:#ef4444;padding:12px 16px;border-radius:10px;margin-bottom:20px">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    <!-- Request Details Card -->
    <div class="card" style="margin-bottom: 24px; padding: 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:12px">
            <h2 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:#fff;margin:0">👤 Reset Request Details</h2>
            <span class="badge" style="background:{{ $notification->isPending() ? 'rgba(212,138,46,0.15)' : 'rgba(46,110,66,0.15)' }};border:1px solid {{ $notification->isPending() ? 'rgba(212,138,46,0.3)' : 'rgba(46,110,66,0.3)' }};color:{{ $notification->isPending() ? '#D48A2E' : '#2E6E42' }};padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700">
                {{ strtoupper($notification->status) }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; font-size: 13px;">
            <div>
                <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700;">Account Name</div>
                <div style="color: #fff; font-weight: 700; font-size: 15px; margin-top: 4px;">{{ $notification->user->name }}</div>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700;">Email / Account Identifier</div>
                <div style="color: #E8CEAA; font-weight: 700; font-size: 14px; margin-top: 4px;">{{ $notification->user->identifier ?? $notification->user->email }}</div>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700;">System Role</div>
                <div style="margin-top: 4px;">
                    <span class="badge" style="background:rgba(56,189,248,0.15);border:1px solid rgba(56,189,248,0.3);color:#E8CEAA;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                        {{ strtoupper($notification->user->role) }}
                    </span>
                </div>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700;">Request Timestamp</div>
                <div style="color: #fff; margin-top: 4px;">{{ $notification->created_at->format('M d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    @if($notification->isPending())
        <!-- Set New Password Card -->
        <div class="card" style="margin-bottom: 24px; padding: 24px;">
            <div style="margin-bottom:18px;border-bottom:1px solid var(--border);padding-bottom:12px">
                <h2 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:#fff;margin:0">🔑 Issue New Account Password</h2>
                <span style="font-size:12px;color:var(--text-muted)">Set temporary or new security password for {{ $notification->user->name }}</span>
            </div>

            <form method="POST" action="{{ route($routePrefix . 'password-resets.execute', $notification) }}">
                @csrf

                <div class="form-group" style="margin-bottom:16px">
                    <label for="new_password" style="display:block;font-size:12px;font-weight:700;color:#cbd5e1;margin-bottom:6px">New Security Password</label>
                    <input id="new_password" type="password" name="new_password" required placeholder="Enter a new strong password (e.g. Secure@Pass123)" style="width:100%;padding:10px 14px;background:rgba(15,23,42,0.8);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:14px" />
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Must include uppercase, lowercase, number, and special character (min 8 chars).
                    </p>
                    @error('new_password')
                        <span style="color: #ef4444; font-size: 12px; display: block; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom:20px">
                    <label for="new_password_confirmation" style="display:block;font-size:12px;font-weight:700;color:#cbd5e1;margin-bottom:6px">Confirm New Security Password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" required placeholder="Re-type new password..." style="width:100%;padding:10px 14px;background:rgba(15,23,42,0.8);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:14px" />
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route($routePrefix . 'password-resets.index') }}" class="btn-secondary" style="padding:8px 16px;border-radius:8px;text-decoration:none;color:#94a3b8;font-size:13px">Cancel</a>
                    <button type="submit" class="btn-primary" style="padding:10px 20px;font-size:13px;border-radius:8px;font-weight:700;cursor:pointer">
                        Authorize &amp; Reset Password &rarr;
                    </button>
                </div>
            </form>
        </div>

        <!-- Deny Request Card -->
        <div class="card" style="padding: 24px; border-color: rgba(239, 68, 68, 0.3);">
            <div style="margin-bottom:14px;border-bottom:1px solid rgba(239,68,68,0.2);padding-bottom:10px">
                <h2 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:#ef4444;margin:0">❌ Deny Reset Request</h2>
            </div>

            <form method="POST" action="{{ route($routePrefix . 'password-resets.deny', $notification) }}">
                @csrf

                <div class="form-group" style="margin-bottom:16px">
                    <label for="notes" style="display:block;font-size:12px;font-weight:700;color:#cbd5e1;margin-bottom:6px">Denial Reason / Administrative Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Enter reason for denying this request (optional)..." style="width:100%;padding:10px 14px;background:rgba(15,23,42,0.8);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:13px">{{ old('notes') }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444; padding:8px 16px; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer">
                        Deny Request
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="card" style="padding: 24px; text-align: center; color: var(--text-muted);">
            This password reset request has been <strong>{{ strtoupper($notification->status) }}</strong>.
            @if($notification->processedBy)
                <div style="font-size: 12px; margin-top: 6px;">
                    Processed by <strong>{{ $notification->processedBy->name }}</strong> on {{ $notification->processed_at->format('M d, Y h:i A') }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

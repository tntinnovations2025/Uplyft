@extends('global-admin.layouts.app')
@section('breadcrumb', $institute->name)
@section('title', $institute->name . ' — Profile')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div style="display:flex;align-items:center;gap:16px">
        @if($institute->logo_path)
            <img src="{{ asset('storage/'.$institute->logo_path) }}" alt="Logo"
                 style="height:60px;max-width:140px;object-fit:contain;border-radius:12px;background:#f8fafc;padding:6px;border:1px solid #e2e8f0">
        @elseif($institute->icon_path)
            <img src="{{ asset('storage/'.$institute->icon_path) }}" alt="Icon"
                 style="width:60px;height:60px;object-fit:cover;border-radius:50%;background:#ffffff;padding:2px;border:2px solid #6366f1">
        @else
            <div style="width:60px;height:60px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:26px">🏫</div>
        @endif
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">{{ $institute->name }}</h1>
            <div style="color:#64748b;font-size:13px;font-weight:600;margin-top:2px">{{ $institute->slug }} &mdash; {{ $institute->city }}</div>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('global-admin.institutes.edit', $institute) }}" class="btn btn-secondary">✏️ Edit</a>
        <a href="{{ route('global-admin.institutes.toggles.edit', $institute) }}" class="btn btn-primary">🔧 Manage Toggles</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Institute Details</div>
            <span class="badge {{ $institute->subscription_tier === 'premium' ? 'badge-purple' : ($institute->subscription_tier === 'standard' ? 'badge-yellow' : 'badge-cyan') }}">
                {{ ucfirst($institute->subscription_tier) }} Plan
            </span>
        </div>
        <table>
            <tr>
                <td style="color:#64748b;font-weight:700;width:140px">Status</td>
                <td><span class="badge {{ $institute->is_active ? 'badge-green' : 'badge-red' }}">{{ $institute->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            <tr><td style="color:#64748b;font-weight:700">Email</td><td style="color:#0f172a;font-weight:600">{{ $institute->contact_email ?? '—' }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">Phone</td><td style="color:#0f172a;font-weight:600">{{ $institute->contact_phone ?? '—' }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">City</td><td style="color:#0f172a;font-weight:600">{{ $institute->city ?? '—' }}, {{ $institute->country }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">Sub. Starts</td><td style="color:#0f172a;font-weight:600">{{ $institute->subscription_starts_at?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">Sub. Expires</td><td style="color:#0f172a;font-weight:600">{{ $institute->subscription_expires_at?->format('d M Y') ?? 'Perpetual' }}</td></tr>
            <tr>
                <td style="color:#64748b;font-weight:700">Tenant DB</td>
                <td><code style="font-size:12px;color:#D48A2E;background:#FBF3E8;padding:2px 6px;border-radius:4px;border:1px solid #E8CEAA">{{ $institute->tenant_db_name }}</code></td>
            </tr>
            <tr><td style="color:#64748b;font-weight:700">Registered</td><td style="color:#0f172a;font-weight:600">{{ $institute->created_at->format('d M Y') }}</td></tr>
        </table>

        <div style="margin-top:20px;display:flex;gap:10px">
            <form method="POST" action="{{ route('global-admin.institutes.toggles.apply-tier', $institute) }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm"
                    onclick="return confirm('Apply {{ ucfirst($institute->subscription_tier) }} tier defaults to all toggles?')">
                    ⚡ Apply Tier Defaults
                </button>
            </form>
            <form method="POST" action="{{ route('global-admin.institutes.destroy', $institute) }}"
                  onsubmit="return confirm('Deactivate this institute?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
            </form>
        </div>
    </div>

    <!-- Feature Toggles Summary -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Feature Toggles</div>
            <a href="{{ route('global-admin.institutes.toggles.edit', $institute) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
        @if($institute->featureToggles)
            <div style="display:flex;flex-direction:column;gap:8px;max-height:360px;overflow-y:auto">
                @foreach($featureLabels as $key => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                    <span style="font-size:12.5px;font-weight:700;color:#0f172a">{{ $label }}</span>
                    <span class="badge {{ $institute->featureToggles->$key ? 'badge-green' : 'badge-red' }}" style="font-size:10px">
                        {{ $institute->featureToggles->$key ? 'ON' : 'OFF' }}
                    </span>
                </div>
                @endforeach
            </div>
        @else
            <p style="color:#64748b;font-size:14px">No toggle configuration found.</p>
        @endif
    </div>

</div>

<!-- Master Login (Principal) Card -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title">🔑 Master Login (Principal)</div>
        @php $principal = $institute->principals->first(); @endphp
        @if($principal)
            <div style="display:flex;align-items:center;gap:8px">
                <button type="button" 
                        onclick="openPasswordModal('{{ $principal->id }}', '{{ addslashes($principal->name) }}', '{{ addslashes($principal->email) }}')"
                        class="btn btn-secondary btn-sm"
                        style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;color:#4f46e5;border:1px solid #c7d2fe;background:#eef2ff">
                    🔑 Override Password
                </button>
                <form method="POST" action="{{ route('global-admin.accounts.principals.destroy', $principal) }}"
                      onsubmit="return confirm('WARNING: Are you sure you want to permanently delete credentials for {{ addslashes($principal->name) }}? The Principal portal for {{ addslashes($institute->name) }} will no longer be linked to any database account.');"
                      style="display:inline;margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;color:#fff"
                            title="Permanently delete and unlink principal credentials">
                        🗑️ Delete / Unlink Credentials
                    </button>
                </form>
            </div>
        @endif
    </div>
    @if($principal)
        <table>
            <tr><td style="color:#64748b;font-weight:700;width:140px">Name</td><td style="color:#0f172a;font-weight:800">{{ $principal->name }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">Email</td><td><code style="font-size:13px;color:#D48A2E">{{ $principal->email }}</code></td></tr>
            @if($principal->identifier)
            <tr><td style="color:#64748b;font-weight:700">Employee ID</td><td><code style="font-size:13px;color:#9333ea">{{ $principal->identifier }}</code></td></tr>
            @endif
            <tr><td style="color:#64748b;font-weight:700">Created</td><td style="color:#0f172a;font-weight:600">{{ $principal->created_at->format('d M Y, h:i A') }}</td></tr>
            <tr><td style="color:#64748b;font-weight:700">Status</td>
                <td><span class="badge badge-green">Active</span></td></tr>
        </table>
    @else
        <p style="color:#64748b;font-size:14px;padding:4px 0">
            No principal account created yet.
            <a href="{{ route('global-admin.accounts.principals.create') }}?institute_id={{ $institute->id }}" style="color:#e1306c;font-weight:700">Create one →</a>
        </p>
    @endif
</div>

<!-- Education System Types -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title">🏫 Education System</div>
        <a href="{{ route('global-admin.institutes.edit', $institute) }}" class="btn btn-secondary btn-sm">Edit</a>
    </div>
    @if(!empty($institute->education_systems))
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach($institute->education_systems as $sys)
                <span class="badge badge-purple" style="font-size:13px;padding:8px 16px">
                    {{ \App\Models\Institute::$educationSystemLabels[$sys] ?? $sys }}
                </span>
            @endforeach
        </div>
    @else
        <p style="color:#64748b;font-size:14px">No education system type selected yet.</p>
    @endif
</div>

{{-- ── Global Admin Principal Password Change Modal (Apple Liquid Glass) ── --}}
<div id="changePasswordModal" class="apple-liquid-glass-overlay" style="display:none;">
    <div class="apple-liquid-glass-card" style="max-width:490px;width:92%;padding:28px 32px;">
        
        {{-- Modal Top Specular Highlight --}}
        <div style="position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.9),transparent)"></div>

        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:16px;margin-bottom:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid rgba(199,210,254,0.6);display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                    🔑
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.4px">Set New Principal Password</h3>
                    <p id="modalPrincipalSubtitle" style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500"></p>
                </div>
            </div>
            <button type="button" onclick="closePasswordModal()" class="apple-liquid-close-btn">✕</button>
        </div>

        <form id="changePasswordForm" method="POST" action="">
            @csrf
            
            <div style="display:flex;flex-direction:column;gap:16px">
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <label for="new_password" style="font-size:12px;font-weight:800;color:#334155;letter-spacing:0.3px">NEW PASSWORD *</label>
                        <button type="button" onclick="generateRandomPass()" style="font-size:11.5px;font-weight:800;color:#4f46e5;background:rgba(238,242,255,0.7);border:1px solid #c7d2fe;padding:2px 8px;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                            🎲 Auto-Generate
                        </button>
                    </div>
                    <input type="text" id="new_password" name="password" required minlength="6" class="apple-liquid-input" placeholder="e.g. PrincipalPass2026!#" style="width:100%;font-family:monospace;font-size:14px;font-weight:600" />
                </div>

                <div>
                    <label for="new_password_confirmation" style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:6px;letter-spacing:0.3px">CONFIRM NEW PASSWORD *</label>
                    <input type="text" id="new_password_confirmation" name="password_confirmation" required minlength="6" class="apple-liquid-input" placeholder="Re-type password" style="width:100%;font-family:monospace;font-size:14px;font-weight:600" />
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:24px;border-top:1px solid rgba(226,232,240,0.7);padding-top:18px">
                <button type="button" onclick="closePasswordModal()" class="apple-liquid-btn-cancel">
                    Cancel
                </button>
                <button type="submit" class="apple-liquid-btn-primary">
                    <span>💾</span> Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPasswordModal(id, name, email) {
        const form = document.getElementById('changePasswordForm');
        form.action = `/global-admin/accounts/principals/${id}/change-password`;
        document.getElementById('modalPrincipalSubtitle').textContent = `${name} (${email})`;
        document.getElementById('new_password').value = '';
        document.getElementById('new_password_confirmation').value = '';
        document.getElementById('changePasswordModal').style.display = 'flex';
    }

    function closePasswordModal() {
        document.getElementById('changePasswordModal').style.display = 'none';
    }

    function generateRandomPass() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
        let pass = '';
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('new_password').value = pass;
        document.getElementById('new_password_confirmation').value = pass;
    }
</script>
@endsection

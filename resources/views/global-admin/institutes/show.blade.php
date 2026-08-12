@extends('global-admin.layouts.app')
@section('breadcrumb', $institute->name)
@section('title', $institute->name . ' — Profile')

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:16px">
        @if($institute->logo_path)
            <img src="{{ asset('storage/'.$institute->logo_path) }}" alt="Logo"
                 style="width:60px;height:60px;object-fit:contain;border-radius:8px;background:var(--surface2);padding:6px">
        @else
            <div style="width:60px;height:60px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:24px">🏫</div>
        @endif
        <div>
            <h1 class="page-title">{{ $institute->name }}</h1>
            <div style="color:var(--text-muted);font-size:13px">{{ $institute->slug }} &mdash; {{ $institute->city }}</div>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('global-admin.institutes.edit', $institute) }}" class="btn btn-ghost">✏️ Edit</a>
        <a href="{{ route('global-admin.institutes.toggles.edit', $institute) }}" class="btn btn-primary">🔧 Manage Toggles</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Institute Details</div>
            <span class="badge {{ $institute->subscription_tier === 'premium' ? 'badge-purple' : ($institute->subscription_tier === 'standard' ? 'badge-yellow' : 'badge-blue') }}">
                {{ ucfirst($institute->subscription_tier) }} Plan
            </span>
        </div>
        <table>
            <tr><td style="color:var(--text-muted);width:140px">Status</td>
                <td><span class="badge {{ $institute->is_active ? 'badge-green' : 'badge-red' }}">{{ $institute->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
            <tr><td style="color:var(--text-muted)">Email</td><td>{{ $institute->contact_email ?? '—' }}</td></tr>
            <tr><td style="color:var(--text-muted)">Phone</td><td>{{ $institute->contact_phone ?? '—' }}</td></tr>
            <tr><td style="color:var(--text-muted)">City</td><td>{{ $institute->city ?? '—' }}, {{ $institute->country }}</td></tr>
            <tr><td style="color:var(--text-muted)">Sub. Starts</td><td>{{ $institute->subscription_starts_at?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td style="color:var(--text-muted)">Sub. Expires</td><td>{{ $institute->subscription_expires_at?->format('d M Y') ?? 'Perpetual' }}</td></tr>
            <tr><td style="color:var(--text-muted)">Tenant DB</td>
                <td><code style="font-size:12px;color:var(--accent2)">{{ $institute->tenant_db_name }}</code></td></tr>
            <tr><td style="color:var(--text-muted)">Registered</td><td>{{ $institute->created_at->format('d M Y') }}</td></tr>
        </table>

        <div style="margin-top:20px;display:flex;gap:10px">
            <form method="POST" action="{{ route('global-admin.institutes.toggles.apply-tier', $institute) }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm"
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
            <a href="{{ route('global-admin.institutes.toggles.edit', $institute) }}" class="btn btn-ghost btn-sm">Edit</a>
        </div>
        @if($institute->featureToggles)
            @foreach($featureLabels as $key => $label)
            <div class="toggle-row">
                <span class="toggle-label">{{ $label }}</span>
                <span class="badge {{ $institute->featureToggles->$key ? 'badge-green' : 'badge-red' }}">
                    {{ $institute->featureToggles->$key ? 'ON' : 'OFF' }}
                </span>
            </div>
            @endforeach
        @else
            <p style="color:var(--text-muted);font-size:14px">No toggle configuration found.</p>
        @endif
    </div>

</div>

<!-- Master Login (Principal) Card -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title">🔑 Master Login (Principal)</div>
    </div>
    @php $principal = $institute->principals->first(); @endphp
    @if($principal)
        <table>
            <tr><td style="color:var(--text-muted);width:140px">Name</td><td>{{ $principal->name }}</td></tr>
            <tr><td style="color:var(--text-muted)">Email</td><td><code style="font-size:13px">{{ $principal->email }}</code></td></tr>
            @if($principal->identifier)
            <tr><td style="color:var(--text-muted)">Employee ID</td><td><code style="font-size:13px">{{ $principal->identifier }}</code></td></tr>
            @endif
            <tr><td style="color:var(--text-muted)">Created</td><td>{{ $principal->created_at->format('d M Y, h:i A') }}</td></tr>
            <tr><td style="color:var(--text-muted)">Status</td>
                <td><span class="badge badge-green">Active</span></td></tr>
        </table>
    @else
        <p style="color:var(--text-muted);font-size:14px;padding:4px 0">
            No principal account created yet.
            <a href="{{ route('global-admin.accounts.principals.create') }}?institute_id={{ $institute->id }}" style="color:var(--accent)">Create one →</a>
        </p>
    @endif
</div>

<!-- Education System Types -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title">🏫 Education System</div>
        <a href="{{ route('global-admin.institutes.edit', $institute) }}" class="btn btn-ghost btn-sm">Edit</a>
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
        <p style="color:var(--text-muted);font-size:14px">No education system type selected yet.</p>
    @endif
</div>
@endsection

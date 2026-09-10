@extends('global-admin.layouts.app')
@section('breadcrumb', 'Edit: ' . $organization->name)
@section('title', 'Edit Organization Network')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Edit Organization Network</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Update organization details, campus capacity limit quota, and designated owner principal.
        </p>
    </div>
    <a href="{{ route('global-admin.organizations.index') }}" class="btn btn-secondary">
        ← Back to Organizations
    </a>
</div>

<div class="card" style="max-width:720px">
    <form method="POST" action="{{ route('global-admin.organizations.update', $organization) }}">
        @csrf @method('PUT')

        {{-- Organization Details --}}
        <div style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(79,70,229,0.25)">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Organization Network Info</h3>
                    <p style="font-size:12px;color:#64748b">Basic organization identification &amp; campus quota capacity.</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
                <div class="form-group" style="margin-bottom:0">
                    <label for="name">Organization Network Name *</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $organization->name) }}" required />
                    @error('name')<p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label for="max_campuses">Allowed Campuses Limit *</label>
                    <input id="max_campuses" type="number" name="max_campuses" min="{{ max(1, $organization->campus_count) }}" max="50" value="{{ old('max_campuses', $organization->max_campuses) }}" required />
                    <span style="font-size:11px;color:#64748b">Current Usage: {{ $organization->campus_usage_text }}</span>
                    @error('max_campuses')<p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Organization Owner Selection --}}
        <div style="margin-bottom:24px;padding:20px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:16px">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:38px;height:38px;border-radius:10px;background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:18px">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Designated Organization Owner</h3>
                    <p style="font-size:12px;color:#64748b">The owner can switch between registered campuses under this organization.</p>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label for="owner_user_id">Select Principal Owner</label>
                <select id="owner_user_id" name="owner_user_id">
                    <option value="">— Unassigned Owner —</option>
                    @foreach($principals as $p)
                        <option value="{{ $p->id }}" {{ old('owner_user_id', $organization->owner_user_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->email }}) @if($p->institute)— Campus: {{ $p->institute->name }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Linked Campuses Table --}}
        <div style="margin-bottom:24px;padding:20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px">
            <h3 style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:800;color:#0f172a;margin-bottom:10px">
                🏫 Member Campuses ({{ $organization->campus_count }})
            </h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @forelse($organization->institutes as $camp)
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;font-weight:700">
                        <span>🏫 {{ $camp->name }}</span>
                        <a href="{{ route('global-admin.institutes.edit', $camp) }}" style="color:#4f46e5;font-size:11.5px;text-decoration:underline">Edit</a>
                    </div>
                @empty
                    <span style="font-size:12.5px;color:#94a3b8">No campuses attached to this organization network yet.</span>
                @endforelse
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:28px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Organization Changes</button>
            <a href="{{ route('global-admin.organizations.index') }}" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </div>
    </form>
</div>
@endsection

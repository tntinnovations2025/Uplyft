@extends('global-admin.layouts.app')
@section('breadcrumb', 'Register Organization')
@section('title', 'Register New Organization Network')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Register New Organization Network</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Establish a new multi-campus organization framework and assign its campus quota limits.
        </p>
    </div>
    <a href="{{ route('global-admin.organizations.index') }}" class="btn btn-secondary">
        ← Back to Organizations
    </a>
</div>

<div class="card" style="max-width:720px">
    <form method="POST" action="{{ route('global-admin.organizations.store') }}">
        @csrf

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
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Superior Group of Colleges" required />
                    @error('name')<p class="form-error" style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label for="max_campuses">Allowed Campuses Limit *</label>
                    <input id="max_campuses" type="number" name="max_campuses" min="1" max="50" value="{{ old('max_campuses', 3) }}" required />
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
                    <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a">Organization Owner / Principal Account</h3>
                    <p style="font-size:12px;color:#64748b">The owner can switch between registered campuses under this organization.</p>
                </div>
            </div>

            <div class="form-group">
                <label style="font-size:11.5px;font-weight:800;color:#475569">Owner Assignment Option</label>
                <div style="display:flex;gap:16px;margin-top:6px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="existing" id="owner_mode_existing" {{ old('owner_mode', 'existing') === 'existing' ? 'checked' : '' }} onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Assign Existing Principal
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="new" id="owner_mode_new" {{ old('owner_mode') === 'new' ? 'checked' : '' }} onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Create New Principal Account
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0f172a;cursor:pointer">
                        <input type="radio" name="owner_mode" value="none" id="owner_mode_none" {{ old('owner_mode') === 'none' ? 'checked' : '' }} onchange="toggleOwnerModeUI()" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#4f46e5;flex-shrink:0;cursor:pointer">
                        Assign Later (Unassigned)
                    </label>
                </div>
            </div>

            {{-- Existing Owner Select --}}
            <div id="owner-existing-box" style="display:block">
                <div class="form-group" style="margin-bottom:0">
                    <label for="owner_user_id">Select Existing Principal *</label>
                    <select id="owner_user_id" name="owner_user_id">
                        <option value="">— Select Principal —</option>
                        @foreach($principals as $p)
                            <option value="{{ $p->id }}" {{ old('owner_user_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->email }}) @if($p->institute)— Campus: {{ $p->institute->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- New Owner Inputs --}}
            <div id="owner-new-box" style="display:none;padding:14px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label for="new_owner_name">Owner Full Name *</label>
                        <input id="new_owner_name" type="text" name="new_owner_name" value="{{ old('new_owner_name') }}" placeholder="e.g. Dr. Salman Chaudhry" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_email">Owner Login Email *</label>
                        <input id="new_owner_email" type="email" name="new_owner_email" value="{{ old('new_owner_email') }}" placeholder="owner@organization.com" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_password">Password *</label>
                        <input id="new_owner_password" type="password" name="new_owner_password" placeholder="Min 8 characters" />
                    </div>
                    <div class="form-group">
                        <label for="new_owner_password_confirmation">Confirm Password *</label>
                        <input id="new_owner_password_confirmation" type="password" name="new_owner_password_confirmation" placeholder="Re-type password" />
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleOwnerModeUI() {
                const mode = document.querySelector('input[name="owner_mode"]:checked')?.value || 'existing';
                document.getElementById('owner-existing-box').style.display = mode === 'existing' ? 'block' : 'none';
                document.getElementById('owner-new-box').style.display = mode === 'new' ? 'block' : 'none';
            }
        </script>

        <div style="display:flex;gap:12px;margin-top:28px">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Register Organization Network</button>
            <a href="{{ route('global-admin.organizations.index') }}" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
        </div>
    </form>
</div>
@endsection

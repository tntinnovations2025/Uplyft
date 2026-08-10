@extends('global-admin.layouts.app')
@section('breadcrumb', 'Edit: ' . $institute->name)
@section('title', 'Edit ' . $institute->name)

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Institute</h1>
    <a href="{{ route('global-admin.institutes.show', $institute) }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:820px">
    <form method="POST" action="{{ route('global-admin.institutes.update', $institute) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group">
                <label for="name">Institute Name *</label>
                <input id="name" type="text" name="name"
                       value="{{ old('name', $institute->name) }}" required />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="subscription_tier">Subscription Plan *</label>
                <select id="subscription_tier" name="subscription_tier" required>
                    @foreach(['basic','standard','premium'] as $tier)
                    <option value="{{ $tier }}"
                        {{ old('subscription_tier', $institute->subscription_tier) === $tier ? 'selected' : '' }}>
                        {{ ucfirst($tier) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input id="contact_email" type="email" name="contact_email"
                       value="{{ old('contact_email', $institute->contact_email) }}" />
            </div>

            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input id="contact_phone" type="text" name="contact_phone"
                       value="{{ old('contact_phone', $institute->contact_phone) }}" />
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" type="text" name="city"
                       value="{{ old('city', $institute->city) }}" />
            </div>

            <div class="form-group">
                <label for="logo">Replace Logo
                    <span style="color:var(--text-muted)">(leave blank to keep current)</span>
                </label>
                @if($institute->logo_path)
                <div style="margin-bottom:8px">
                    <img src="{{ asset('storage/'.$institute->logo_path) }}" alt="Current Logo"
                         style="height:48px;object-fit:contain;border-radius:6px;background:var(--surface2);padding:4px">
                </div>
                @endif
                <input id="logo" type="file" name="logo" accept="image/*" />
            </div>

            <div class="form-group">
                <label for="subscription_starts_at">Subscription Start</label>
                <input id="subscription_starts_at" type="date" name="subscription_starts_at"
                       value="{{ old('subscription_starts_at', $institute->subscription_starts_at?->format('Y-m-d')) }}" />
            </div>

            <div class="form-group">
                <label for="subscription_expires_at">Subscription Expiry</label>
                <input id="subscription_expires_at" type="date" name="subscription_expires_at"
                       value="{{ old('subscription_expires_at', $institute->subscription_expires_at?->format('Y-m-d')) }}" />
            </div>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1"
                       style="width:auto;accent-color:var(--accent)"
                    {{ old('is_active', $institute->is_active) ? 'checked' : '' }}>
                &nbsp; Institute is Active
            </label>
        </div>

        {{-- ── Education System Type ─────────────────────────────────── --}}
        <div class="form-group">
            <label style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:12px;display:block">
                🏫 Education System
                <span style="font-weight:400;color:var(--text-muted);font-size:12px">
                    — Select all that apply
                </span>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @php $currentSystems = old('education_systems', $institute->education_systems ?? []); @endphp
                @foreach($educationSystemLabels as $value => $label)
                <label style="
                    display:flex;align-items:center;gap:12px;
                    background:var(--surface2);
                    border:1px solid {{ in_array($value, $currentSystems) ? 'var(--accent)' : 'var(--border)' }};
                    border-radius:10px;padding:14px 16px;cursor:pointer;
                    background:{{ in_array($value, $currentSystems) ? 'rgba(108,99,255,.08)' : 'var(--surface2)' }};
                    transition:border-color .15s;
                ">
                    <input type="checkbox"
                           name="education_systems[]"
                           value="{{ $value }}"
                           {{ in_array($value, $currentSystems) ? 'checked' : '' }}
                           style="width:18px;height:18px;accent-color:var(--accent);flex-shrink:0"
                           onchange="this.closest('label').style.borderColor = this.checked ? 'var(--accent)' : 'var(--border)';
                                     this.closest('label').style.background = this.checked ? 'rgba(108,99,255,.08)' : 'var(--surface2)'">
                    <span style="font-size:14px;font-weight:500">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="{{ route('global-admin.institutes.show', $institute) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

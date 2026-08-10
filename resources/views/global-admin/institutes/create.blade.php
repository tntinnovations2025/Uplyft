@extends('global-admin.layouts.app')
@section('breadcrumb', 'Register Institute')
@section('title', 'Register New Institute')

@section('content')
<div class="page-header">
    <h1 class="page-title">Register New Institute</h1>
    <a href="{{ route('global-admin.institutes.index') }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:820px">
    <form method="POST" action="{{ route('global-admin.institutes.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="form-group">
                <label for="name">Institute Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Dar e Arqam School" required />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="subscription_tier">Subscription Plan *</label>
                <select id="subscription_tier" name="subscription_tier" required>
                    <option value="">— Select Plan —</option>
                    <option value="basic"    {{ old('subscription_tier') === 'basic'    ? 'selected' : '' }}>Basic</option>
                    <option value="standard" {{ old('subscription_tier') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="premium"  {{ old('subscription_tier') === 'premium'  ? 'selected' : '' }}>Premium</option>
                </select>
                @error('subscription_tier')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input id="contact_email" type="email" name="contact_email"
                       value="{{ old('contact_email') }}" placeholder="admin@school.edu.pk" />
                @error('contact_email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="contact_phone">Contact Phone</label>
                <input id="contact_phone" type="text" name="contact_phone"
                       value="{{ old('contact_phone') }}" placeholder="+92 300 1234567" />
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" type="text" name="city"
                       value="{{ old('city') }}" placeholder="Lahore" />
            </div>

            <div class="form-group">
                <label for="logo">Institute Logo <span style="color:var(--text-muted)">(PNG/JPG/SVG, max 2 MB)</span></label>
                <input id="logo" type="file" name="logo" accept="image/*" />
                @error('logo')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="subscription_starts_at">Subscription Start</label>
                <input id="subscription_starts_at" type="date" name="subscription_starts_at"
                       value="{{ old('subscription_starts_at') }}" />
            </div>

            <div class="form-group">
                <label for="subscription_expires_at">Subscription Expiry</label>
                <input id="subscription_expires_at" type="date" name="subscription_expires_at"
                       value="{{ old('subscription_expires_at') }}" />
            </div>
        </div>

        {{-- ── Education System Type ─────────────────────────────────── --}}
        <div class="form-group" style="margin-top:8px">
            <label style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:12px;display:block">
                🏫 Education System *
                <span style="font-weight:400;color:var(--text-muted);font-size:12px">
                    — Select all that apply to this institute
                </span>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @foreach($educationSystemLabels as $value => $label)
                <label style="
                    display:flex;align-items:center;gap:12px;
                    background:var(--surface2);
                    border:1px solid var(--border);
                    border-radius:10px;
                    padding:14px 16px;
                    cursor:pointer;
                    transition:border-color .15s;
                    {{ in_array($value, old('education_systems', [])) ? 'border-color:var(--accent);background:rgba(108,99,255,.08)' : '' }}
                " onmouseover="this.style.borderColor='var(--accent)'"
                   onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border)'">
                    <input type="checkbox"
                           name="education_systems[]"
                           value="{{ $value }}"
                           {{ in_array($value, old('education_systems', [])) ? 'checked' : '' }}
                           style="width:18px;height:18px;accent-color:var(--accent);flex-shrink:0"
                           onchange="this.closest('label').style.borderColor = this.checked ? 'var(--accent)' : 'var(--border)';
                                     this.closest('label').style.background = this.checked ? 'rgba(108,99,255,.08)' : 'var(--surface2)'">
                    <span style="font-size:14px;font-weight:500">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            @error('education_systems')<p class="form-error" style="margin-top:8px">{{ $message }}</p>@enderror
        </div>

        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn btn-primary">✅ Register Institute</button>
            <a href="{{ route('global-admin.institutes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

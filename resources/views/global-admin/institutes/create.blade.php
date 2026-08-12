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

        {{-- ── Master Login (Principal Account) ─────────────────────────── --}}
        <div style="margin-top:28px;padding:24px;background:linear-gradient(135deg, rgba(108,99,255,.06), rgba(0,206,209,.06));border:1.5px solid var(--accent);border-radius:12px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                <span style="font-size:22px">🔑</span>
                <div>
                    <label style="font-size:15px;font-weight:700;color:var(--text);display:block">
                        Create Master Login for Institute *
                    </label>
                    <span style="font-weight:400;color:var(--text-muted);font-size:12px">
                        This creates the Principal account. Login credentials will be emailed automatically.
                    </span>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div class="form-group" style="grid-column:1/-1">
                    <label for="principal_name">Principal Full Name *</label>
                    <input id="principal_name" type="text" name="principal_name"
                           value="{{ old('principal_name') }}"
                           placeholder="e.g. Mr. Ahmed Khan" required />
                    @error('principal_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="principal_email">Principal Email *</label>
                    <input id="principal_email" type="email" name="principal_email"
                           value="{{ old('principal_email') }}"
                           placeholder="principal@school.edu.pk" required />
                    <span style="font-size:11px;color:var(--text-muted)">This email will be used for login and credential delivery.</span>
                    @error('principal_email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="principal_password">Login Password *</label>
                    <input id="principal_password" type="password" name="principal_password"
                           placeholder="Min 8 chars: Aa1@..." required />
                    <span style="font-size:11px;color:var(--text-muted)">Uppercase + lowercase + number + special char required.</span>
                    @error('principal_password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group" style="grid-column:1;">
                    <label for="principal_password_confirmation">Confirm Password *</label>
                    <input id="principal_password_confirmation" type="password"
                           name="principal_password_confirmation"
                           placeholder="Re-type password" required />
                </div>

                <div class="form-group">
                    <label for="principal_identifier">Employee ID <span style="color:var(--text-muted)">(Optional)</span></label>
                    <input id="principal_identifier" type="text" name="principal_identifier"
                           value="{{ old('principal_identifier') }}"
                           placeholder="e.g. PRIN-001, ADM#101" />
                    @error('principal_identifier')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px">
            <button type="submit" class="btn btn-primary">✅ Register Institute & Create Master Login</button>
            <a href="{{ route('global-admin.institutes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

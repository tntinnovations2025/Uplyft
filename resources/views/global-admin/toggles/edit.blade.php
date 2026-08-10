@extends('global-admin.layouts.app')
@section('breadcrumb', 'Feature Toggles — ' . $institute->name)
@section('title', 'Feature Toggles')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">🔧 Feature Toggles</h1>
        <div style="color:var(--text-muted);font-size:14px;margin-top:4px">
            {{ $institute->name }} &mdash;
            <span class="badge {{ $institute->subscription_tier === 'premium' ? 'badge-purple' : ($institute->subscription_tier === 'standard' ? 'badge-yellow' : 'badge-blue') }}">
                {{ ucfirst($institute->subscription_tier) }} Plan
            </span>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <form method="POST" action="{{ route('global-admin.institutes.toggles.apply-tier', $institute) }}">
            @csrf
            <button type="submit" class="btn btn-ghost"
                onclick="return confirm('Apply {{ ucfirst($institute->subscription_tier) }} plan defaults? This will override current settings.')">
                ⚡ Apply Tier Defaults
            </button>
        </form>
        <a href="{{ route('global-admin.institutes.show', $institute) }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('global-admin.institutes.toggles.update', $institute) }}">
        @csrf @method('PUT')

        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px">
            Toggle individual platform modules on or off for this institute. Changes take effect immediately.
        </p>

        @foreach($featureKeys as $key)
        <div class="toggle-row">
            <div>
                <div class="toggle-label">{{ $featureLabels[$key] }}</div>
                <div style="font-size:12px;color:var(--text-muted)">Feature key: <code>{{ $key }}</code></div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="{{ $key }}" value="1"
                    {{ old($key, $toggles->$key ?? false) ? 'checked' : '' }}>
                <span class="toggle-track"></span>
            </label>
        </div>
        @endforeach

        <div style="margin-top:24px;display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">💾 Save Toggle Settings</button>
            <a href="{{ route('global-admin.institutes.show', $institute) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection

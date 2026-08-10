@extends('global-admin.layouts.app')
@section('breadcrumb', 'Dashboard')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Platform Overview</h1>
    <a href="{{ route('global-admin.institutes.create') }}" class="btn btn-primary">
        ➕ Register Institute
    </a>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Institutes</div>
        <div class="stat-value" style="color:var(--accent2)">{{ $stats['total_institutes'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active</div>
        <div class="stat-value" style="color:var(--success)">{{ $stats['active_institutes'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Basic Plan</div>
        <div class="stat-value" style="color:var(--text-muted)">{{ $stats['basic_plan'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Standard Plan</div>
        <div class="stat-value" style="color:var(--warning)">{{ $stats['standard_plan'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Premium Plan</div>
        <div class="stat-value" style="color:var(--accent2)">{{ $stats['premium_plan'] }}</div>
    </div>
</div>

<!-- Recent Institutes -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Recently Registered Institutes</div>
        <a href="{{ route('global-admin.institutes.index') }}" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Institute</th>
                <th>Tier</th>
                <th>Status</th>
                <th>Features Active</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentInstitutes as $inst)
            <tr>
                <td>
                    <div style="font-weight:600">{{ $inst->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $inst->slug }}</div>
                </td>
                <td>
                    <span class="badge {{ $inst->subscription_tier === 'premium' ? 'badge-purple' : ($inst->subscription_tier === 'standard' ? 'badge-yellow' : 'badge-blue') }}">
                        {{ ucfirst($inst->subscription_tier) }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $inst->is_active ? 'badge-green' : 'badge-red' }}">
                        {{ $inst->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    @if($inst->featureToggles)
                        {{ collect(\App\Models\InstituteFeatureToggle::$featureKeys)->filter(fn($k) => $inst->featureToggles->$k)->count() }}
                        / {{ count(\App\Models\InstituteFeatureToggle::$featureKeys) }}
                    @else
                        <span style="color:var(--text-muted)">—</span>
                    @endif
                </td>
                <td style="color:var(--text-muted);font-size:13px">
                    {{ $inst->created_at->diffForHumans() }}
                </td>
                <td>
                    <a href="{{ route('global-admin.institutes.show', $inst) }}" class="btn btn-ghost btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px">
                    No institutes registered yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

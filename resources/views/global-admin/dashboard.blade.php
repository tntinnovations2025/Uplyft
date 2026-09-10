@extends('global-admin.layouts.app')
@section('breadcrumb', 'Overview')
@section('title', 'Platform Overview')

@push('styles')
<style>
    /* ── INK & AMBER — COMMAND CENTER ──────────────────────────────────── */

    /* Hero */
    .hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .hero-title {
        font-family: 'Manrope', sans-serif;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.2px;
        color: var(--text-primary);
    }

    .hero-sub {
        color: var(--text-secondary);
        font-size: 14px;
        margin-top: 6px;
        font-weight: 500;
        line-height: 1.5;
    }

    .hero-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 13px;
        border-radius: var(--radius-pill);
        background: var(--success-bg);
        color: var(--success-text);
        font-size: 11px;
        font-weight: 600;
        margin-top: 12px;
    }

    .hero-status-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--success-text);
    }

    .hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn-outline-subtle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: var(--radius-btn);
        background: var(--card-surface);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease;
    }

    .btn-outline-subtle:hover {
        border-color: #CBC8BE;
        color: var(--text-primary);
    }

    .btn-amber {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-btn);
        background: var(--amber);
        border: none;
        color: var(--amber-on);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .btn-amber:hover {
        background: var(--amber-hover);
    }

    /* ── Stat Cards ────────────────────────────────────────────────────── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
        gap: 14px;
        margin-bottom: 32px;
    }

    .s-card {
        background: var(--card-surface);
        border-radius: var(--radius-card);
        padding: 22px;
    }

    .s-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .s-card-label {
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: var(--text-faint);
    }

    .s-card-icon {
        width: 34px; height: 34px;
        border-radius: var(--radius-chip);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Three icon chip tints — no more */
    .chip-amber   { background: var(--amber-tint-bg); color: var(--amber-tint-text); }
    .chip-success { background: var(--success-bg);     color: var(--success-text); }
    .chip-info    { background: var(--tertiary-bg);    color: var(--tertiary-text); }

    .s-card-value {
        font-family: 'Manrope', sans-serif;
        font-size: 29px;
        font-weight: 800;
        letter-spacing: -0.3px;
        color: var(--text-primary);
        line-height: 1;
    }

    .s-card-desc {
        font-size: 12px;
        color: var(--text-faint);
        font-weight: 500;
        margin-top: 8px;
    }

    /* ── Quick-access Cards ────────────────────────────────────────────── */
    .quick-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
        margin-bottom: 32px;
    }

    .q-card {
        background: var(--card-surface);
        border-radius: var(--radius-card);
        padding: 20px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: box-shadow 0.18s ease;
    }

    .q-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    .q-card-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .q-card-icon {
        width: 42px; height: 42px;
        border-radius: var(--radius-btn);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .q-card-title {
        font-family: 'Manrope', sans-serif;
        font-weight: 700;
        color: var(--text-primary);
        font-size: 14px;
    }

    .q-card-sub {
        font-size: 12px;
        color: var(--text-faint);
        margin-top: 2px;
        font-weight: 500;
    }

    .q-card-arrow {
        width: 28px; height: 28px;
        border-radius: 7px;
        background: var(--page-bg);
        color: var(--text-faint);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .q-card:hover .q-card-arrow {
        background: var(--amber);
        color: var(--amber-on);
    }

    /* ── Table Panel ───────────────────────────────────────────────────── */
    .tbl-panel {
        background: var(--card-surface);
        border-radius: var(--radius-card);
        overflow: hidden;
    }

    .tbl-panel-head {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border);
    }

    .tbl-panel-title {
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .tbl-panel-title i { color: var(--amber); font-size: 15px; }

    .tbl-panel-sub {
        font-size: 12px;
        color: var(--text-faint);
        margin-top: 3px;
        font-weight: 500;
    }

    .tbl-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: var(--radius-chip);
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color 0.15s ease, color 0.15s ease;
    }

    .tbl-link:hover {
        border-color: #CBC8BE;
        color: var(--text-primary);
    }

    .t {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .t th {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--text-faint);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 12px 24px;
        border-bottom: 1px solid var(--border);
        background: transparent;
    }

    .t td {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        font-size: 13px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .t tr:last-child td { border-bottom: none; }
    .t tr:hover td { background: rgba(0,0,0,0.012); }

    .inst-name {
        font-family: 'Manrope', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: var(--text-primary);
    }

    .inst-slug {
        font-size: 12px;
        color: var(--text-faint);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        margin-top: 2px;
        font-weight: 500;
    }

    .tier-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        font-size: 11px;
        font-weight: 600;
    }

    .tier-basic    { background: var(--tertiary-bg); color: var(--tertiary-text); }
    .tier-standard { background: var(--amber-tint-bg); color: var(--amber-tint-text); }
    .tier-premium  { background: var(--amber-tint-bg); color: var(--amber-tint-text); }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        font-size: 11px;
        font-weight: 600;
    }

    .status-active    { background: var(--success-bg); color: var(--success-text); }
    .status-suspended { background: var(--danger-bg); color: var(--danger-text); }

    .dot-sm {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .modules-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: var(--radius-pill);
        background: var(--amber-tint-bg);
        color: var(--amber-tint-text);
        font-size: 11px;
        font-weight: 600;
    }

    .act-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: var(--radius-chip);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.12s ease;
    }

    .act-neutral {
        background: var(--page-bg);
        border: 1px solid var(--border);
        color: var(--text-secondary);
    }
    .act-neutral:hover {
        background: #E5E4E0;
        color: var(--text-primary);
    }

    .act-primary {
        background: var(--amber);
        border: none;
        color: var(--amber-on);
    }
    .act-primary:hover {
        background: var(--amber-hover);
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
    }

    .empty-icon {
        font-size: 28px;
        color: var(--text-faint);
        margin-bottom: 10px;
    }

    .empty-title {
        font-family: 'Manrope', sans-serif;
        font-weight: 700;
        color: var(--text-secondary);
        font-size: 15px;
    }

    .empty-sub {
        font-size: 12.5px;
        margin-top: 4px;
        color: var(--text-faint);
    }
</style>
@endpush

@section('content')
<div>
    <!-- Hero -->
    <div class="hero">
        <div>
            <div class="hero-title">Welcome back, {{ auth()->user()->first_name }}</div>
            <p class="hero-sub">
                Multi-tenant infrastructure overview and campus governance.
            </p>
            <div class="hero-status">
                <span class="hero-status-dot"></span>
                All systems operational
            </div>
        </div>
        <div class="hero-actions">
            <a href="{{ route('global-admin.accounts.principals.create') }}" class="btn-outline-subtle">
                <x-icon name="user-shield" class="w-4 h-4" />
                <span>New principal</span>
            </a>
            <a href="{{ route('global-admin.institutes.create') }}" class="btn-amber">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Register new institute</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="s-card">
            <div class="s-card-top">
                <div class="s-card-label">Total Institutes</div>
                <div class="s-card-icon chip-amber">
                    <x-icon name="building-columns" class="w-4 h-4" />
                </div>
            </div>
            <div class="s-card-value">{{ $stats['total_institutes'] }}</div>
            <div class="s-card-desc">Registered multi-tenants</div>
        </div>

        <div class="s-card">
            <div class="s-card-top">
                <div class="s-card-label">Active Tenants</div>
                <div class="s-card-icon chip-success">
                    <x-icon name="check" class="w-4 h-4" />
                </div>
            </div>
            <div class="s-card-value">{{ $stats['active_institutes'] }}</div>
            <div class="s-card-desc">Operational &amp; serving users</div>
        </div>

        <div class="s-card">
            <div class="s-card-top">
                <div class="s-card-label">Basic Plan</div>
                <div class="s-card-icon chip-info">
                    <x-icon name="cubes" class="w-4 h-4" />
                </div>
            </div>
            <div class="s-card-value">{{ $stats['basic_plan'] }}</div>
            <div class="s-card-desc">Standard features tier</div>
        </div>

        <div class="s-card">
            <div class="s-card-top">
                <div class="s-card-label">Standard Plan</div>
                <div class="s-card-icon chip-amber">
                    <x-icon name="bolt" class="w-4 h-4" />
                </div>
            </div>
            <div class="s-card-value">{{ $stats['standard_plan'] }}</div>
            <div class="s-card-desc">Pro LMS &amp; timetable module</div>
        </div>

        <div class="s-card">
            <div class="s-card-top">
                <div class="s-card-label">Premium Plan</div>
                <div class="s-card-icon chip-amber">
                    <x-icon name="award" class="w-4 h-4" />
                </div>
            </div>
            <div class="s-card-value">{{ $stats['premium_plan'] }}</div>
            <div class="s-card-desc">Full suite enterprise</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-row">
        <a href="{{ route('global-admin.accounts.principals.index') }}" class="q-card">
            <div class="q-card-left">
                <div class="q-card-icon chip-amber">
                    <x-icon name="user-gear" class="w-5 h-5" />
                </div>
                <div>
                    <div class="q-card-title">Principal Directory</div>
                    <div class="q-card-sub">Manage institutional leaders &amp; credentials</div>
                </div>
            </div>
            <div class="q-card-arrow">
                <x-icon name="arrow-right" class="w-4 h-4 text-faint" />
            </div>
        </a>

        <a href="{{ route('global-admin.password-resets.index') }}" class="q-card">
            <div class="q-card-left">
                <div class="q-card-icon chip-info">
                    <x-icon name="key" class="w-5 h-5" />
                </div>
                <div>
                    <div class="q-card-title">Password Reset Requests</div>
                    <div class="q-card-sub">Review &amp; execute credential overrides</div>
                </div>
            </div>
            <div class="q-card-arrow">
                <x-icon name="arrow-right" class="w-4 h-4 text-faint" />
            </div>
        </a>
    </div>

    <!-- Institutes Table -->
    <div class="tbl-panel">
        <div class="tbl-panel-head">
            <div>
                <div class="tbl-panel-title">
                    <x-icon name="building-columns" class="w-4 h-4" />
                    Registered Institutes
                </div>
                <div class="tbl-panel-sub">Active multi-tenant instances across the platform</div>
            </div>
            <a href="{{ route('global-admin.institutes.index') }}" class="tbl-link">
                View all <x-icon name="arrow-right" class="w-3 h-3" />
            </a>
        </div>
        <div style="overflow-x:auto">
            <table class="t">
                <thead>
                    <tr>
                        <th>Institute &amp; Domain</th>
                        <th>Tier</th>
                        <th>Status</th>
                        <th>Active Modules</th>
                        <th>Created</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInstitutes as $inst)
                    <tr>
                        <td>
                            <div class="inst-name">{{ $inst->name }}</div>
                            <div class="inst-slug">
                                {{ $inst->slug }}.uplyft.app
                            </div>
                        </td>
                        <td>
                            <span class="tier-pill {{ $inst->subscription_tier === 'premium' ? 'tier-premium' : ($inst->subscription_tier === 'standard' ? 'tier-standard' : 'tier-basic') }}">
                                {{ ucfirst($inst->subscription_tier) }}
                            </span>
                        </td>
                        <td>
                            @if($inst->is_active)
                                <span class="status-chip status-active">
                                    <span class="dot-sm"></span>
                                    Active
                                </span>
                            @else
                                <span class="status-chip status-suspended">
                                    <x-icon name="xmark" class="w-2.5 h-2.5" />
                                    Suspended
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($inst->featureToggles)
                                @php
                                    $activeCount = collect(\App\Models\InstituteFeatureToggle::$featureKeys)->filter(fn($k) => $inst->featureToggles->$k)->count();
                                    $totalCount = count(\App\Models\InstituteFeatureToggle::$featureKeys);
                                @endphp
                                <span class="modules-pill">
                                    {{ $activeCount }}/{{ $totalCount }} enabled
                                </span>
                            @else
                                <span style="color:var(--text-faint);font-size:12px">Default</span>
                            @endif
                        </td>
                        <td style="color:var(--text-faint);font-size:12px;font-weight:500">
                            {{ $inst->created_at->diffForHumans() }}
                        </td>
                        <td style="text-align:right">
                            <div style="display:inline-flex;gap:6px">
                                <a href="{{ route('global-admin.institutes.toggles.edit', $inst) }}" class="act-btn act-neutral">
                                    <x-icon name="sliders" class="w-3 h-3" /> Toggles
                                </a>
                                <a href="{{ route('global-admin.institutes.show', $inst) }}" class="act-btn act-primary">
                                    Inspect <x-icon name="chevron-right" class="w-2.5 h-2.5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon"><x-icon name="building-columns" class="w-8 h-8" /></div>
                                <div class="empty-title">No institutes provisioned yet</div>
                                <div class="empty-sub">Click &quot;Register new institute&quot; to create your first tenant.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

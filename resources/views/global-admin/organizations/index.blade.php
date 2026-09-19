@extends('global-admin.layouts.app')
@section('breadcrumb', 'Organization Networks')
@section('title', 'Registered Organization Networks')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">Organization Networks</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Manage multi-campus organization networks, campus limit quotas, and designated organization owners.
        </p>
    </div>
    <a href="{{ route('global-admin.organizations.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus-circle"></i> Register New Organization
    </a>
</div>

{{-- Summary Stats Grid --}}
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div class="stat-label">Total Organizations</div>
            <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:16px">
                <i class="fa-solid fa-sitemap"></i>
            </div>
        </div>
        <div class="stat-value">{{ $organizations->count() }}</div>
    </div>

    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div class="stat-label">Multi-Campus Total</div>
            <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:16px">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>
        <div class="stat-value">{{ $organizations->sum('institutes_count') }}</div>
    </div>

    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div class="stat-label">Total Quota Allocated</div>
            <div style="width:36px;height:36px;border-radius:10px;background:#faf5ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:16px">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
        <div class="stat-value">{{ $organizations->sum('max_campuses') }}</div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <table class="data-table">
        <thead>
            <tr>
                <th>Organization Network</th>
                <th>Campuses &amp; Quota</th>
                <th>Organization Owner</th>
                <th>Member Campuses</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($organizations as $org)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#4338ca,#4f46e5);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;box-shadow:0 2px 8px rgba(79,70,229,0.25);flex-shrink:0">
                            🏢
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:6px">
                                <span>{{ $org->name }}</span>
                                @if($org->is_active)
                                    <span class="badge badge-green" style="font-size:9.5px;padding:1px 6px">🟢 Active</span>
                                @else
                                    <span class="badge badge-yellow" style="font-size:9.5px;padding:1px 6px" title="Services temporarily paused (e.g. pending payment resolution). All campus data is preserved.">⏸️ Paused</span>
                                @endif
                            </div>
                            <div style="font-size:11.5px;color:#64748b">Registered Network #{{ $org->id }}</div>
                        </div>
                    </div>
                </td>

                <td>
                    @php 
                        $used = $org->institutes_count;
                        $max = $org->max_campuses;
                        $isFull = $used >= $max;
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <span class="badge {{ $isFull ? 'badge-amber' : 'badge-emerald' }}" style="width:fit-content">
                            {{ $used }} / {{ $max }} Campuses {{ $isFull ? '(Quota Full)' : 'Used' }}
                        </span>
                        <div style="width:120px;height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:2px">
                            <div style="width:{{ min(100, round(($used/$max)*100)) }}%;height:100%;background:{{ $isFull ? '#f59e0b' : '#10b981' }};border-radius:999px"></div>
                        </div>
                    </div>
                </td>

                <td>
                    @if($org->owner)
                        <div style="display:flex;flex-direction:column;gap:2px">
                            <div style="font-size:13.5px;font-weight:800;color:#0f172a">
                                👔 {{ $org->owner->name }}
                            </div>
                            <div style="font-size:11.5px;color:#D48A2E;font-weight:600">
                                ✉️ {{ $org->owner->email }}
                            </div>
                            @if($org->owner->identifier)
                                <div style="font-size:10.5px;color:#7c3aed;font-weight:700;font-family:monospace">
                                    🆔 {{ $org->owner->identifier }}
                                </div>
                            @endif
                            <span style="font-size:10px;background:#fdf4ff;color:#c026d3;border:1px solid #f5d0fe;padding:1px 6px;border-radius:4px;font-weight:700;width:fit-content;margin-top:2px">
                                👑 Network Principal Owner
                            </span>
                        </div>
                    @else
                        <span style="font-size:12px;color:#94a3b8;font-style:italic">⚠️ Unassigned Network Owner</span>
                    @endif
                </td>

                <td>
                    <div style="display:flex;flex-direction:column;gap:6px;max-width:300px">
                        @forelse($org->institutes as $camp)
                            @php $campPrincipal = $camp->principals->first(); @endphp
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;display:flex;flex-direction:column;gap:2px">
                                <a href="{{ route('global-admin.institutes.show', $camp) }}" style="font-size:12px;font-weight:800;color:#4338ca;text-decoration:none">
                                    🏫 {{ $camp->name }}
                                </a>
                                @if($campPrincipal)
                                    <div style="font-size:11px;color:#334155;font-weight:600">
                                        👤 Principal: {{ $campPrincipal->name }} (<span style="color:#D48A2E">{{ $campPrincipal->email }}</span>)
                                    </div>
                                @else
                                    <div style="font-size:10.5px;color:#94a3b8;font-style:italic">
                                        ⚠️ No Principal linked
                                    </div>
                                @endif
                            </div>
                        @empty
                            <span style="font-size:12px;color:#94a3b8">No campuses onboarded yet</span>
                        @endforelse
                    </div>
                </td>

                <td style="text-align:right">
                    <div style="display:inline-flex;gap:6px;align-items:center">
                        <form method="POST" action="{{ route('global-admin.organizations.toggle-status', $org) }}" style="display:inline">
                            @csrf
                            @if($org->is_active)
                                <button type="submit" class="btn btn-sm btn-ghost" style="color:#b45309;border-color:#fde68a;background:#fffbeb;font-size:11.5px;padding:4px 8px" title="Pause Services: Temporarily pause portal access (e.g. payment issue). All member campus data remains 100% preserved.">
                                    ⏸️ Pause
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm btn-ghost" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-size:11.5px;padding:4px 8px" title="Resume Services: Instantly restore portal access for all member campuses after payment/conflict resolution.">
                                    ▶️ Resume
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('global-admin.organizations.edit', $org) }}" class="btn btn-secondary btn-sm" style="font-size:11.5px;padding:4px 8px">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>

                        <form method="POST" action="{{ route('global-admin.organizations.destroy', $org) }}" onsubmit="return confirm('⚠️ DANGER: Deleting this Organization Network will permanently delete ALL member campuses, data, and portals linked to it. This action cannot be undone. Are you absolutely sure?');" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-liquid-red btn-sm" style="font-size:11.5px;padding:4px 10px" title="Permanently Delete Organization & All Campuses">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;padding:40px;color:#64748b">
                    <div style="font-size:32px;margin-bottom:8px">🏢</div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a">No Organization Networks Registered</div>
                    <div style="font-size:12.5px;color:#64748b;margin-top:2px">Click "Register New Organization" above to create a multi-campus network.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

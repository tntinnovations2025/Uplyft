@extends('global-admin.layouts.app')
@section('breadcrumb', 'Campuses')
@section('title', 'Registered Campuses')

@section('content')

{{-- ── Custom Delete Confirmation Modal ───────────────────────────── --}}
<div id="deleteModal" style="
    display:none;
    position:fixed;inset:0;z-index:999999;
    background:rgba(15,23,42,0.6);
    backdrop-filter:blur(8px);
    -webkit-backdrop-filter:blur(8px);
    align-items:center;justify-content:center;
    padding:20px;
">
    <div style="
        background:#ffffff;
        border:1px solid #e2e8f0;
        border-radius:20px;
        padding:32px 36px;
        max-width:480px;width:100%;
        box-shadow:0 25px 60px -10px rgba(15,23,42,0.3);
        animation:modalPop .18s ease;
        text-align:center;
    ">
        <div style="font-size:44px;margin-bottom:12px">⚠️</div>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:6px;color:#0f172a">Permanently Delete Institute?</h2>
        <p style="color:#64748b;font-size:13.5px;margin-bottom:6px">You are about to delete:</p>
        <p id="modalInstituteName" style="color:#e11d48;font-size:16px;font-weight:800;margin-bottom:18px"></p>
        
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:22px;text-align:left;font-size:12.5px;color:#991b1b;line-height:1.6">
            <strong>Critical Warning:</strong> This will completely wipe:
            <ul style="margin:6px 0 0 18px;padding:0">
                <li>All linked Portals (Principal, Faculty, Student, Staff, LMS)</li>
                <li>All Student records, enrollment profiles, and marks</li>
                <li>All Teacher accounts, timetables, and salary records</li>
                <li>All Fee invoices, account heads, and transactions</li>
                <li>All Classes, subjects, and campus configurations</li>
            </ul>
            <span style="font-weight:800;display:block;margin-top:6px">This action is permanent and cannot be undone.</span>
        </div>

        <div style="display:flex;gap:10px;justify-content:center">
            <button type="button" id="modalCancelBtn" onclick="closeDeleteModal()" style="
                padding:9px 20px;border-radius:10px;border:1px solid #cbd5e1;
                background:#ffffff;color:#475569;font-size:13.5px;font-weight:700;
                cursor:pointer;
            ">
                Cancel
            </button>
            <button type="button" id="modalConfirmBtn" onclick="submitDeleteForm()" style="
                padding:9px 22px;border-radius:10px;border:none;
                background:linear-gradient(135deg,#ef4444,#dc2626);
                color:#fff;font-size:13.5px;font-weight:800;
                cursor:pointer;box-shadow:0 4px 14px rgba(239,68,68,0.35);
            ">
                Permanently Delete Everything
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { transform:scale(.94);opacity:0; }
    to   { transform:scale(1);opacity:1; }
}
.campuses-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}
.campuses-table th {
    padding: 10px 10px;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    border-bottom: 1.5px solid #e2e8f0;
    font-weight: 800;
    background: #f8fafc;
    white-space: nowrap;
}
.campuses-table td {
    padding: 10px 10px;
    border-bottom: 1px solid #f1f5f9;
    color: #0f172a;
    vertical-align: middle;
}
.campuses-table tr:hover td {
    background: #f8fafc;
}
.action-btn-group {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    flex-wrap: nowrap;
    justify-content: flex-end;
}
.act-btn {
    padding: 4.5px 8px;
    font-size: 11px;
    border-radius: 7px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 3.5px;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all .15s ease;
    white-space: nowrap;
    font-family: 'Inter', sans-serif;
}
.act-btn-ghost {
    background: #EAE8E3;
    border-color: #E1DFD7;
    color: #1B1A17;
}
.act-btn-ghost:hover {
    background: #E1DFD7;
    color: #1B1A17;
}
.act-btn-liquid-red {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 55%, #b91c1c 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(185, 28, 28, 0.7) !important;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.28) !important;
    font-weight: 700 !important;
    letter-spacing: 0.2px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.act-btn-liquid-red:hover {
    background: linear-gradient(135deg, #f87171 0%, #ef4444 50%, #dc2626 100%) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
}
.act-btn-liquid-red:active {
    transform: scale(0.97);
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.45) !important;
}
.act-btn-danger {
    background: #F6E4E1;
    border-color: rgba(162, 65, 44, 0.25);
    color: #A2412C;
}
.act-btn-danger:hover {
    background: #EED4CF;
}
.act-btn-warning {
    background: #F8E9D3;
    border-color: rgba(212, 138, 46, 0.3);
    color: #8A5A10;
}
.act-btn-warning:hover {
    background: #F1DEC0;
}
.act-btn-success {
    background: #E3EFE2;
    border-color: rgba(46, 110, 66, 0.25);
    color: #2E6E42;
}
.act-btn-success:hover {
    background: #D5E7D3;
}
</style>

{{-- ── Hidden delete form (single, reused for all rows) ──────────── --}}
<form id="deleteForm" method="POST" action="" style="display:none">
    @csrf
    @method('DELETE')
</form>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div>
        <h1 class="page-title" style="font-size:22px;font-weight:800;color:var(--text-primary);margin:0">Registered Campuses</h1>
        <p style="font-size:12.5px;color:var(--text-secondary);margin-top:2px">Manage campus portals, lifecycle status, feature toggles, and tenant isolation.</p>
    </div>
    <a href="{{ route('global-admin.institutes.create') }}" class="btn btn-primary" style="padding:8px 16px;font-size:12.5px">
        <x-icon name="plus" class="w-3.5 h-3.5" />
        <span>Onboard New Campus</span>
    </a>
</div>

{{-- Responsive, non-overflowing card table container --}}
<div class="card" style="padding:0;overflow:hidden;border-radius:14px;box-shadow:0 1px 3px rgba(15,23,42,0.04)">
    <table class="campuses-table">
        <thead>
            <tr>
                <th style="width:28px;text-align:center">#</th>
                <th>Institute &amp; Campus</th>
                <th style="width:20%">Executive Principal</th>
                <th style="width:16%">Plan &amp; Status</th>
                <th style="text-align:right;white-space:nowrap">Portal Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($institutes as $inst)
            <tr style="{{ $inst->trashed() ? 'opacity:.6;background:#fef2f2;' : (!$inst->is_active ? 'background:#fffbeb;' : '') }}">
                <td style="color:#64748b;font-weight:700;text-align:center;font-size:12px">{{ $inst->id }}</td>
                
                {{-- Column: Institute & Campus --}}
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        @if($inst->logo_path)
                            <img src="{{ asset('storage/'.$inst->logo_path) }}" alt="{{ $inst->name }}" style="height:34px;width:34px;object-fit:contain;border-radius:8px;background:#F9F8F5;padding:2px;border:1px solid #E1DFD7;flex-shrink:0" />
                        @elseif($inst->icon_path)
                            <img src="{{ asset('storage/'.$inst->icon_path) }}" alt="{{ $inst->name }}" style="height:34px;width:34px;object-fit:cover;border-radius:50%;background:#F9F8F5;padding:1px;border:2px solid #D48A2E;flex-shrink:0" />
                        @else
                            <div style="width:34px;height:34px;border-radius:8px;background:#D48A2E;color:#1A1200;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
                                {{ $inst->display_initial }}
                            </div>
                        @endif
                        <div style="min-width:0">
                            <div style="font-weight:700;color:var(--text-primary);font-size:13.5px;display:flex;align-items:center;gap:5px;flex-wrap:wrap">
                                <span>{{ $inst->name }}</span>
                                @if($inst->logo_path || $inst->icon_path)
                                    <span title="Custom Branding Active" style="font-size:9.5px;background:#E3EFE2;color:#2E6E42;padding:1px 5px;border-radius:4px;font-weight:700">Branded</span>
                                @endif
                            </div>
                            <div style="font-size:11.5px;color:var(--text-secondary);font-weight:500;margin-top:2px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <span>{{ $inst->city ?? 'Main Campus' }}</span>
                                @if($inst->organization)
                                    <a href="{{ route('global-admin.organizations.edit', $inst->organization) }}" style="font-size:10.5px;background:#F8E9D3;color:#8A5A10;padding:1px 6px;border-radius:5px;font-weight:600;text-decoration:none" title="Member of {{ $inst->organization->name }}">
                                        {{ $inst->organization->name }}
                                    </a>
                                @else
                                    <span style="font-size:10px;background:#EAE8E3;color:var(--text-secondary);padding:1px 5px;border-radius:5px;font-weight:600">
                                        Standalone
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Column: Executive Principal --}}
                <td>
                    @php $instPrincipal = $inst->principals->first(); @endphp
                    @if($instPrincipal)
                        <div style="display:flex;flex-direction:column;gap:1px">
                            <div style="font-weight:700;color:var(--text-primary);font-size:13px">
                                {{ $instPrincipal->name }}
                            </div>
                            <div style="font-size:11px;color:#8A5A10;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $instPrincipal->email }}">
                                {{ $instPrincipal->email }}
                            </div>
                            @if($instPrincipal->identifier)
                                <div style="font-size:10px;color:var(--text-faint);font-weight:600;font-family:monospace">
                                    ID: {{ $instPrincipal->identifier }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:2px">
                            <span style="font-size:10.5px;background:#F6E4E1;color:#A2412C;padding:1px 6px;border-radius:5px;font-weight:600;width:fit-content">
                                No Principal Linked
                            </span>
                            <a href="{{ route('global-admin.accounts.principals.create') }}?institute_id={{ $inst->id }}" style="font-size:10.5px;color:#8A5A10;font-weight:700;text-decoration:none">
                                + Create Principal
                            </a>
                        </div>
                    @endif
                </td>

                {{-- Column: Plan & Status (Combined Stack) --}}
                <td>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            <span class="badge {{ $inst->subscription_tier === 'premium' ? 'badge-amber' : ($inst->subscription_tier === 'standard' ? 'badge-amber' : 'badge-neutral') }}" style="font-size:10px;padding:2px 7px">
                                {{ ucfirst($inst->subscription_tier) }}
                            </span>
                            @if($inst->trashed())
                                <span class="badge badge-danger" style="font-size:10px;padding:2px 7px">Deleted</span>
                            @elseif($inst->is_active)
                                <span class="badge badge-success" style="font-size:10px;padding:2px 7px">Active</span>
                            @else
                                <span class="badge badge-amber" style="font-size:10px;padding:2px 7px" title="Services temporarily paused. All data preserved.">Paused</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-faint);font-weight:500;line-height:1.3">
                            <span>{{ $inst->subscription_expires_at?->format('d M Y') ?? 'Perpetual' }}</span>
                            @if($inst->featureToggles)
                                <span> &bull; {{ collect(\App\Models\InstituteFeatureToggle::$featureKeys)->filter(fn($k) => $inst->featureToggles->$k)->count() }} Modules ON</span>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Column: Portal Actions --}}
                <td style="text-align:right">
                    <div class="action-btn-group">
                        @if($inst->trashed())
                            {{-- Trashed actions: Restore & Permanent Purge --}}
                            <form method="POST" action="{{ route('global-admin.institutes.restore', $inst->id) }}" style="display:inline">
                                @csrf
                                <button class="act-btn act-btn-success" type="submit" title="Restore Institute and Reactivate Portals">
                                    <x-icon name="arrows-rotate" class="w-3 h-3" />
                                    <span>Restore</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('global-admin.institutes.purge', $inst->id) }}" onsubmit="return confirm('Permanently wipe {{ addslashes($inst->name) }} and ALL linked data, users, and portals? This cannot be undone.');" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="act-btn act-btn-liquid-red" type="submit" title="Permanently Wipe All Data">
                                    <span>Purge</span>
                                </button>
                            </form>
                        @else
                            {{-- Standard actions --}}
                            <a href="{{ route('global-admin.institutes.show', $inst) }}" class="act-btn act-btn-ghost" title="View details">
                                <x-icon name="magnifying-glass" class="w-3 h-3" />
                                <span>View</span>
                            </a>
                            <a href="{{ route('global-admin.institutes.edit', $inst) }}" class="act-btn act-btn-ghost" title="Edit details">
                                <x-icon name="pen-to-square" class="w-3 h-3" />
                                <span>Edit</span>
                            </a>
                            <a href="{{ route('global-admin.institutes.toggles.edit', $inst) }}" class="act-btn act-btn-ghost" title="Feature toggles">
                                <x-icon name="sliders" class="w-3 h-3" />
                                <span>Toggles</span>
                            </a>

                            {{-- Pause / Resume Services Portal Toggle --}}
                            <form method="POST" action="{{ route('global-admin.institutes.toggle-status', $inst) }}" style="display:inline">
                                @csrf
                                @if($inst->is_active)
                                    <button type="submit" class="act-btn act-btn-warning" title="Pause Services: Temporarily suspends portal access. All data is safely preserved.">
                                        <x-icon name="pause" class="w-3 h-3" />
                                        <span>Pause</span>
                                    </button>
                                @else
                                    <button type="submit" class="act-btn act-btn-success" title="Resume Services: Instantly restores portal access.">
                                        <x-icon name="play" class="w-3 h-3" />
                                        <span>Resume</span>
                                    </button>
                                @endif
                            </form>

                            {{-- Delete button triggers comprehensive purge modal --}}
                            <button
                                type="button"
                                class="act-btn act-btn-liquid-red"
                                title="Delete Institute & Purge all data"
                                onclick="openDeleteModal(
                                    '{{ route('global-admin.institutes.destroy', $inst) }}',
                                    '{{ addslashes($inst->name) }}'
                                )">
                                <span>Delete</span>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:var(--text-muted);padding:40px">
                    No institutes registered yet. <a href="{{ route('global-admin.institutes.create') }}" style="color:var(--accent2)">Register one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:14px 18px;border-top:1px solid #f1f5f9;background:#f8fafc">{{ $institutes->links() }}</div>
</div>

<script>
    const modal    = document.getElementById('deleteModal');
    const nameEl   = document.getElementById('modalInstituteName');
    const delForm  = document.getElementById('deleteForm');

    function openDeleteModal(actionUrl, instituteName) {
        delForm.action = actionUrl;
        nameEl.textContent = instituteName;
        modal.style.display = 'flex';
        modal.onclick = function(e) {
            if (e.target === modal) closeDeleteModal();
        };
    }

    function closeDeleteModal() {
        modal.style.display = 'none';
    }

    function submitDeleteForm() {
        const btn = document.getElementById('modalConfirmBtn');
        btn.textContent = 'Purging All Data & Portals…';
        btn.disabled = true;
        delForm.submit();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>

@endsection

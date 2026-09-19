@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Faculty & Staff Directory')
@section('breadcrumb', 'Faculty & Staff Roster')

@section('content')
<style>
    .roster-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .roster-card {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
    }

    .roster-table {
        width: 100%;
        border-collapse: collapse;
    }

    .roster-table th {
        background: #f8fafc;
        padding: 10px 14px;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }

    .roster-table td {
        padding: 10px 14px;
        font-size: 12.5px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }

    .roster-table tr:hover td {
        background: #fdf2f8;
    }

    .staff-avatar-circle {
        width: 35px;
        height: 35px;
        border-radius: 9px;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 14px;
        color: #fff;
        box-shadow: 0 2px 8px rgba(225, 48, 108, 0.2);
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 22px;
        vertical-align: middle;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: 0.25s ease;
        border-radius: 22px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: #ffffff;
        transition: 0.25s ease;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #fd1d1d, #e1306c);
    }
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    .rights-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .rights-badge.owner {
        background: #fdf4ff;
        color: #c026d3;
        border: 1px solid #f5d0fe;
    }

    /* ====== Category Tabs Styling (Bright Daylight Mode) ====== */
    .cat-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 12px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .cat-tab:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .cat-tab.active {
        background: #eef2ff;
        color: #4338ca;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.12);
    }
    .cat-pill {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .cat-tab.active .cat-pill {
        background: #4f46e5;
        color: #ffffff;
    }

    /* ====== Role Selection Modal Tiles ====== */
    .role-modal-tile {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #0f172a;
    }
    .role-modal-tile:hover {
        border-color: #4f46e5;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .role-modal-tile.selected {
        background: #eef2ff;
        border-color: #4f46e5;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.15);
    }
    .role-modal-tile .tile-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        transition: color 0.2s ease;
    }
    .role-modal-tile.selected .tile-title {
        color: #4338ca;
        font-weight: 800;
    }

    /* ====== Permissions Modal (Bright Daylight) ====== */
    .perm-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        z-index: 9999999 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        margin: 0 !important;
    }
    .perm-backdrop.show { display: flex !important; }

    .perm-modal {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 20px !important;
        width: 90% !important;
        max-width: 720px !important;
        max-height: 86vh !important;
        display: flex !important;
        flex-direction: column !important;
        box-shadow: 0 25px 60px -10px rgba(15, 23, 42, 0.35) !important;
        overflow: hidden !important;
        animation: permIn .22s ease !important;
        color: #0f172a !important;
        margin: 0 auto !important;
    }
    @keyframes permIn {
        from { opacity: 0; transform: translateY(12px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .perm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 26px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .perm-header h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .perm-header .perm-sub {
        font-size: 13px;
        color: #64748b;
        margin-top: 3px;
    }
    .perm-close {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .perm-close:hover {
        color: #0f172a;
        border-color: #94a3b8;
        background: #f1f5f9;
    }

    .perm-body {
        overflow-y: auto;
        padding: 22px 26px;
        flex: 1;
        background: #ffffff;
    }

    .perm-delegation {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 14px;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        margin-bottom: 20px;
    }
    .perm-delegation .perm-label { display: flex; align-items: center; gap: 12px; }
    .perm-delegation .perm-title { font-size: 15px; font-weight: 800; color: #1e1b4b; }
    .perm-delegation .perm-desc { font-size: 12.5px; color: #4338ca; margin-top: 2px; }
    .perm-delegation .perm-badge { font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; }

    .perm-group {
        margin-bottom: 18px;
    }
    .perm-group-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #4f46e5;
        margin: 16px 4px 10px;
    }
    
    /* ====== Permission Accordion Cards ====== */
    .perm-accordion-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .perm-accordion-card.open {
        border-color: #c7d2fe;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.08);
    }
    .perm-accordion-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s ease;
    }
    .perm-accordion-header:hover {
        background: #f1f5f9;
    }
    .perm-accordion-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }
    .perm-accordion-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 20px;
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .perm-accordion-caret {
        font-size: 11px;
        color: #64748b;
        transition: transform 0.2s ease;
    }
    .perm-accordion-card.open .perm-accordion-caret {
        transform: rotate(180deg);
        color: #4f46e5;
    }
    .perm-accordion-body {
        display: none;
        padding: 14px 18px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .perm-accordion-card.open .perm-accordion-body {
        display: block;
    }

    /* ====== Dual Toggle Pill Group ====== */
    .dual-toggle-box {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #f8fafc;
        padding: 6px 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .toggle-pill-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .toggle-pill-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .perm-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 10px;
        transition: border-color .2s ease, background .2s ease;
    }
    .perm-item:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }
    .perm-item.on {
        background: #f0fdf4;
        border-color: #a7f3d0;
    }
    .perm-item label { flex-shrink: 0; cursor: pointer; }

    .perm-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 26px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .perm-footer .perm-legend { font-size: 12px; color: #64748b; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
    .perm-footer .perm-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .perm-save { transition: all .2s ease; }
    .perm-save:disabled { opacity: .55; cursor: not-allowed; transform: none; }

    /* Toast */
    .perm-toast {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(-140%);
        z-index: 10001;
        padding: 12px 22px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.15);
        transition: transform .25s ease, opacity .25s ease;
        opacity: 0;
        pointer-events: none;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .perm-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }

    @media (max-width: 640px) {
        .perm-body { padding: 16px; }
        .perm-footer { flex-direction: column; align-items: stretch; }
    }
</style>

@php
    $canEditStaff = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('staff', 'edit');
@endphp

<div class="roster-header">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            👥 Faculty &amp; Staff Directory
        </h1>
        <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500">
            Manage faculty and staff accounts, designations, role authorities, and module permissions.
        </div>
    </div>

    <div style="display:flex;gap:10px;align-items:center">
        @if($canEditStaff)
            <a href="{{ route('principal.staff.authorities') }}" class="btn btn-ghost" style="border-color:#cbd5e1;color:#0f172a">
                🛡️ Permissions &amp; Role Access
            </a>
            <button type="button" id="btnOpenImportStaff" onclick="window.openImportStaffModal ? window.openImportStaffModal() : openImportStaffModal()" class="btn btn-ghost" style="border-color:#D48A2E;color:#D48A2E;background:#FBF3E8;cursor:pointer">
                📥 Import Staff
            </button>
            <a href="{{ route('principal.staff.create') }}" class="btn btn-primary">
                🚀 New Staff Onboarding
            </a>
        @else
            <div style="font-size:12px;font-weight:700;color:#D48A2E;background:#FBF3E8;padding:6px 14px;border-radius:10px;border:1px solid #E8CEAA;display:inline-flex;align-items:center;gap:6px">
                <span>👁️</span> <span>View Only Access Mode</span>
            </div>
        @endif
    </div>
</div>

<script>
    window.openImportStaffModal = function() {
        const modal = document.getElementById('importStaffModal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.add('active', 'show');
            modal.style.setProperty('display', 'flex', 'important');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeImportStaffModal = function() {
        const modal = document.getElementById('importStaffModal');
        if (modal) {
            modal.classList.remove('active', 'show');
            modal.style.setProperty('display', 'none', 'important');
            document.body.style.overflow = '';
        }
    };
</script>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:10px">
    <span style="font-size:18px">✓</span>
    <span style="font-weight:600">{{ session('success') }}</span>
</div>
@endif

@php
    $counts = [
        'all' => $staffMembers->count(),
        'teacher' => 0,
        'administration' => 0,
        'accountant' => 0,
        'coordinator' => 0,
        'other' => 0,
    ];
    $customCategories = [];

    foreach ($staffMembers as $s) {
        $rTitle = $s->staff_role ?? ucfirst($s->role);
        $roleRaw = strtolower($s->staff_role ?? $s->role);

        if ($s->role === 'principal' || str_contains($roleRaw, 'admin')) {
            $catKey = 'administration';
        } elseif (str_contains($roleRaw, 'teacher') || ($s->role === 'teacher' && empty($s->staff_role))) {
            $catKey = 'teacher';
        } elseif (str_contains($roleRaw, 'account')) {
            $catKey = 'accountant';
        } elseif (str_contains($roleRaw, 'coord')) {
            $catKey = 'coordinator';
        } else {
            $slug = \Illuminate\Support\Str::slug($rTitle);
            if (!empty($slug)) {
                $catKey = 'custom-' . $slug;
                $customCategories[$slug] = $rTitle;
            } else {
                $catKey = 'other';
            }
        }
        $counts[$catKey] = ($counts[$catKey] ?? 0) + 1;
    }
@endphp

<!-- STAFF CATEGORY FILTER TABS -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <button type="button" class="cat-tab active" onclick="filterStaffCategory('all', this)">
            <span>🌐</span> All Staff
            <span class="cat-pill" id="badge-all">{{ $counts['all'] }}</span>
        </button>
        <button type="button" class="cat-tab" onclick="filterStaffCategory('teacher', this)">
            <span>👨‍🏫</span> Teachers &amp; Faculty
            <span class="cat-pill" id="badge-teacher">{{ $counts['teacher'] }}</span>
        </button>
        <button type="button" class="cat-tab" onclick="filterStaffCategory('administration', this)">
            <span>💼</span> Administration
            <span class="cat-pill" id="badge-admin">{{ $counts['administration'] }}</span>
        </button>
        <button type="button" class="cat-tab" onclick="filterStaffCategory('accountant', this)">
            <span>💰</span> Accountants
            <span class="cat-pill" id="badge-accountant">{{ $counts['accountant'] }}</span>
        </button>
        <button type="button" class="cat-tab" onclick="filterStaffCategory('coordinator', this)">
            <span>📋</span> Coordinators
            <span class="cat-pill" id="badge-coordinator">{{ $counts['coordinator'] }}</span>
        </button>

        @foreach($customCategories as $cSlug => $cTitle)
            <button type="button" class="cat-tab" onclick="filterStaffCategory('custom-{{ $cSlug }}', this)">
                <span>⭐</span> {{ $cTitle }}
                <span class="cat-pill" id="badge-custom-{{ $cSlug }}">{{ $counts['custom-' . $cSlug] ?? 0 }}</span>
            </button>
        @endforeach

        <button type="button" class="cat-tab" id="tab-other" onclick="filterStaffCategory('other', this)" style="{{ ($counts['other'] > 0) ? '' : 'display:none' }}">
            <span>🧩</span> Other Staff
            <span class="cat-pill" id="badge-other">{{ $counts['other'] }}</span>
        </button>
    </div>

    <!-- Live Search Box -->
    <div style="position:relative;min-width:240px">
        <input type="text" id="staff-search-input" class="form-control" placeholder="🔍 Search by name, role, email..." oninput="onStaffSearch()" style="padding-left:14px;font-size:13px;height:38px;background:#ffffff;border-radius:10px;border:1px solid #cbd5e1;color:#0f172a">
    </div>
</div>

<div class="roster-card" style="overflow-x:hidden">
    <div style="width:100%">
        <table class="roster-table" style="width:100%">
        <thead>
            <tr>
                <th style="width:28%">Faculty / Staff Member</th>
                <th style="width:18%">Designation &amp; Contract</th>
                <th style="width:22%;text-align:center">Admin Rights</th>
                <th style="width:32%;text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody id="staff-table-body">
            @forelse($staffMembers as $staff)
            @php
                $perms = $staff->getEffectivePermissions();
                $roleTitle = $staff->staff_role ?? ucfirst($staff->role);
                if ($staff->employment_type) {
                    $roleTitle .= ' (' . ucfirst($staff->employment_type) . ')';
                }

                $roleRaw = strtolower($staff->staff_role ?? $staff->role);
                if ($staff->role === 'principal' || str_contains($roleRaw, 'admin')) {
                    $catKey = 'administration';
                    $avatarGradient = 'linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4)';
                    $badgeStyle = 'background:#fdf4ff;color:#c026d3;border:1px solid #f5d0fe';
                } elseif (str_contains($roleRaw, 'teacher') || ($staff->role === 'teacher' && empty($staff->staff_role))) {
                    $catKey = 'teacher';
                    $avatarGradient = 'linear-gradient(135deg, #10b981, #059669)';
                    $badgeStyle = 'background:#ecfdf5;color:#059669;border:1px solid #a7f3d0';
                } elseif (str_contains($roleRaw, 'account')) {
                    $catKey = 'accountant';
                    $avatarGradient = 'linear-gradient(135deg, #f59e0b, #d97706)';
                    $badgeStyle = 'background:#fffbeb;color:#d97706;border:1px solid #fde68a';
                } elseif (str_contains($roleRaw, 'coord')) {
                    $catKey = 'coordinator';
                    $avatarGradient = 'linear-gradient(135deg, #D48A2E, #C07A22)';
                    $badgeStyle = 'background:#FBF3E8;color:#D48A2E;border:1px solid #E8CEAA';
                } else {
                    $cSlug = \Illuminate\Support\Str::slug($staff->staff_role ?? 'other');
                    $catKey = !empty($cSlug) ? 'custom-' . $cSlug : 'other';
                    $avatarGradient = 'linear-gradient(135deg, #ec4899, #be185d)';
                    $badgeStyle = 'background:#fff1f2;color:#e11d48;border:1px solid #fecdd3';
                }
            @endphp
            <tr class="staff-row" data-staff-category="{{ $catKey }}" data-search-text="{{ strtolower($staff->name . ' ' . $staff->email . ' ' . ($staff->identifier ?? '') . ' ' . $roleTitle) }}">
                <td>
                    <div style="display:flex;align-items:center;gap:14px">
                        <div class="staff-avatar-circle" style="background:{{ $avatarGradient }};box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:700;color:#0f172a;font-size:14.5px">{{ $staff->name }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:1px">✉️ {{ $staff->email }}</div>
                            @if($staff->identifier)
                                <span style="display:inline-block;margin-top:3px;font-size:10.5px;color:#D48A2E;background:#f0fdf4;padding:2px 8px;border-radius:6px;border:1px solid #E8CEAA;font-family:monospace;font-weight:700">
                                    🆔 {{ $staff->identifier }}
                                </span>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size:11px;font-weight:800;padding:5px 12px;border-radius:20px;letter-spacing:0.4px;display:inline-flex;align-items:center;gap:6px;{{ $badgeStyle }}">
                        <span style="width:6px;height:6px;border-radius:50%;background:currentColor"></span>
                        {{ strtoupper($roleTitle) }}
                    </span>
                </td>

                <!-- Admin Rights Status -->
                <td style="text-align:center">
                    @if($staff->is_primary_principal || ($staff->role === 'principal' && $staff->created_by === null))
                        <span class="rights-badge owner" style="font-size:11.5px;padding:6px 14px;border-radius:20px;font-weight:800;background:#fef2f2;color:#991b1b;border:1px solid #fecaca">👑 Primary Principal</span>
                    @elseif($staff->role === 'principal')
                        <span class="rights-badge owner" style="font-size:11.5px;padding:6px 14px;border-radius:20px;font-weight:800;background:#fdf4ff;color:#c026d3;border:1px solid #f5d0fe">👑 Secondary Principal</span>
                    @else
                        <span class="rights-badge" data-delegate-badge="{{ $staff->id }}" style="font-size:11.5px;padding:6px 14px;border-radius:20px;font-weight:800;background:{{ $staff->is_delegated_admin ? '#eef2ff' : '#f8fafc' }};color:{{ $staff->is_delegated_admin ? '#4338ca' : '#475569' }};border:1px solid {{ $staff->is_delegated_admin ? '#c7d2fe' : '#cbd5e1' }}">
                            {{ $staff->is_delegated_admin ? '👑 Master Delegated Admin' : '🛡️ Standard Role Rights' }}
                        </span>
                    @endif
                </td>

                <td style="text-align:right">
                    @if($staff->is_primary_principal && !auth()->user()->isGlobalAdmin())
                        <span style="font-size:12px;font-weight:700;color:#64748b;background:#f1f5f9;padding:6px 12px;border-radius:8px;border:1px solid #e2e8f0;display:inline-flex;align-items:center;gap:6px">
                            🔒 Primary Account Protected
                        </span>
                    @elseif($staff->role !== 'global_admin')
                        @php
                            $tp = $staff->teacherProfile;
                            $roleOptionVal = match(true) {
                                str_contains($roleRaw, 'coord') => 'coordinator',
                                str_contains($roleRaw, 'account') => 'accountant',
                                str_contains($roleRaw, 'admin') || $staff->role === 'principal' => 'administration',
                                str_contains($roleRaw, 'teacher') || ($staff->role === 'teacher' && empty($staff->staff_role)) => 'teacher',
                                default => 'custom',
                            };

                            $editRoleData = [
                                'id' => $staff->id,
                                'name' => $staff->name,
                                'email' => $staff->email,
                                'identifier' => $staff->identifier ?? 'N/A',
                                'system_role' => $staff->role,
                                'staff_role' => $staff->staff_role ?? 'Teacher',
                                'role_option' => $roleOptionVal,
                                'custom_role_name' => $roleOptionVal === 'custom' ? ($staff->staff_role ?? '') : '',
                                'employment_type' => $staff->employment_type ?? 'permanent',
                                'qualification' => $tp->qualification ?? ($staff->staff_role ?? 'Faculty Member'),
                                'basic_salary_pkr' => $tp->basic_salary_pkr ?? '',
                                'update_url' => route('principal.staff.update-role', $staff),
                            ];

                            $staffDetailData = [
                                'id' => $staff->id,
                                'name' => $staff->name,
                                'email' => $staff->email,
                                'identifier' => $staff->identifier ?? 'N/A',
                                'role' => $roleTitle,
                                'employment_type' => $staff->employment_type ? ucfirst($staff->employment_type) : 'N/A',
                                'created_at' => $staff->created_at ? $staff->created_at->format('M d, Y') : 'N/A',
                                'phone' => $tp->phone ?? 'N/A',
                                'qualification' => $tp->qualification ?? ($staff->staff_role ?? 'Faculty Member'),
                                'years_of_experience' => $tp && $tp->years_of_experience !== null ? $tp->years_of_experience . ' Years' : 'N/A',
                                'basic_salary_pkr' => $tp && $tp->basic_salary_pkr ? 'PKR ' . number_format($tp->basic_salary_pkr, 2) : 'N/A',
                                'profile_picture' => $tp && $tp->profile_picture_path ? asset('storage/' . $tp->profile_picture_path) : null,
                                'matriculation_cert' => $tp && $tp->matriculation_cert ? asset('storage/' . $tp->matriculation_cert) : null,
                                'intermediate_cert' => $tp && $tp->intermediate_cert ? asset('storage/' . $tp->intermediate_cert) : null,
                                'bachelors_cert' => $tp && $tp->bachelors_cert ? asset('storage/' . $tp->bachelors_cert) : null,
                                'masters_cert' => $tp && $tp->masters_cert ? asset('storage/' . $tp->masters_cert) : null,
                                'phd_cert' => $tp && $tp->phd_cert ? asset('storage/' . $tp->phd_cert) : null,
                                'is_delegated_admin' => (bool)$staff->is_delegated_admin,
                                'permissions' => $perms,
                                'edit_data' => $editRoleData,
                            ];
                        @endphp
                        @php
                            $canEditStaff = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('staff', 'edit');
                        @endphp
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:wrap">
                            @if($canEditStaff)
                                {{-- Change Role, Designation & Contract --}}
                                <button type="button" class="btn" onclick='openEditRoleModal({{ json_encode($editRoleData, JSON_HEX_APOS | JSON_HEX_QUOT) }})' title="Change Designation, Role & Contract Type" style="padding:6px 11px;font-size:11.5px;font-weight:800;border-radius:10px;background:#FBF3E8;color:#D48A2E;border:1.5px solid #E8CEAA;cursor:pointer">
                                    ✏️ Edit Role
                                </button>
                            @endif

                            {{-- View Details & Documents --}}
                            <button type="button" class="btn" onclick='openStaffDetailModal({{ json_encode($staffDetailData, JSON_HEX_APOS | JSON_HEX_QUOT) }})' title="View Employee Details & Uploaded Documents" style="padding:6px 11px;font-size:11.5px;font-weight:800;border-radius:10px;background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;cursor:pointer">
                                👁️ Details
                            </button>

                            @if($canEditStaff)
                                {{-- Edit Rights & Access --}}
                                <button type="button" class="btn btn-rights" data-staff-id="{{ $staff->id }}" data-staff-perms='{{ json_encode($perms, JSON_HEX_APOS | JSON_HEX_QUOT) }}' data-staff-delegate="{{ $staff->is_delegated_admin ? 1 : 0 }}" onclick="openPermModal('{{ $staff->id }}', '{{ addslashes($staff->name) }}', this)" style="padding:6px 12px;font-size:11.5px;font-weight:800;border-radius:10px;background:#eef2ff;color:#4f46e5;border:1.5px solid #c7d2fe;cursor:pointer">
                                    ⚙️ Rights
                                </button>

                                {{-- Password Reset --}}
                                <button type="button" class="btn" onclick="openDirectResetModal({{ $staff->id }}, '{{ addslashes($staff->name) }}', '{{ addslashes($staff->email) }}')" title="Direct Password Reset" style="padding:6px 10px;font-size:11.5px;font-weight:800;background:#fffbeb;border:1.5px solid #fde68a;color:#d97706;border-radius:10px;cursor:pointer">
                                    🔑 Reset
                                </button>

                                {{-- Delete Button (Explicit Red Button with Delete Text) --}}
                                <form action="{{ route('principal.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('Delete staff account for {{ addslashes($staff->name) }}?')" style="display:inline-block;margin:0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" title="Delete Staff Account" style="padding:6px 12px;font-size:11.5px;font-weight:800;background:#fff1f2;border:1.5px solid #fecdd3;color:#e11d48;border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                        🗑️ Delete
                                    </button>
                                </form>
                            @else
                                <span style="font-size:11px;font-weight:700;color:#64748b;background:#f1f5f9;padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;display:inline-flex;align-items:center;gap:4px">
                                    👁️ View Only
                                </span>
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;padding:48px;color:#64748b">
                    <div style="font-size:32px;margin-bottom:8px">👥</div>
                    <div style="font-size:16px;font-weight:800;color:#0f172a">No Staff Members Registered</div>
                    <div style="font-size:13px;margin-top:4px;margin-bottom:16px;color:#64748b">Provision official faculty and administrative accounts for your institute.</div>
                    <a href="{{ route('principal.staff.create') }}" class="btn btn-primary" style="background:#4f46e5;color:#fff;border-radius:10px;font-weight:800">
                        🚀 Onboard First Staff Member
                    </a>
                </td>
            </tr>
            @endforelse

            <!-- Dynamic Category Empty State -->
            <tr id="empty-category-row" style="display:none">
                <td colspan="4" style="text-align:center;padding:36px;color:#64748b">
                    <div style="font-size:28px;margin-bottom:6px">📂</div>
                    <div style="font-size:15px;font-weight:800;color:#0f172a">No Employees Found in Selected Category</div>
                    <div style="font-size:12.5px;margin-top:4px;color:#64748b" id="empty-category-message">There are currently no staff members registered under this department.</div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<!-- ===== Permissions Modal ===== -->
<div id="perm-backdrop" class="perm-backdrop" aria-hidden="true">
    <div class="perm-modal" role="dialog" aria-modal="true" aria-labelledby="perm-title">
        <div class="perm-header">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;width:100%">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:44px;height:44px;border-radius:12px;background:#eef2ff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;font-size:22px;color:#4f46e5">
                        ⚙️
                    </div>
                    <div>
                        <h3 id="perm-title" style="font-family:'Outfit',sans-serif;font-size:19px;font-weight:800;color:#0f172a;margin:0">Portal Rights &amp; Access Control</h3>
                        <div class="perm-sub" id="perm-subtitle" style="font-size:13px;color:#64748b;margin-top:2px">Configure granular permissions for this staff account.</div>
                    </div>
                </div>
                <button type="button" class="perm-close" onclick="closePermModal()" aria-label="Close">&times;</button>
            </div>

            <!-- In-Modal Quick Search -->
            <div style="margin-top:16px;position:relative;width:100%">
                <input type="text" id="perm-modal-search" placeholder="Search assignable rights (e.g. Invoices, Attendance, RAG, LMS)..." onkeyup="filterPermModalItems()" style="width:100%;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;padding:10px 14px 10px 38px;color:#0f172a;font-size:13px;font-weight:600;outline:none;transition:border-color 0.2s">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b;font-size:14px">🔍</span>
            </div>
        </div>

        <div class="perm-body" id="perm-body">
            <div class="perm-delegation">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="font-size:28px">👑</div>
                    <div>
                        <div class="perm-title">Master Delegated Admin Rights</div>
                        <div class="perm-desc">Grant full administrative oversight &amp; management across all institute modules.</div>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="delegation-toggle" data-live-perm data-perm="__delegate">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div id="perm-groups"></div>
        </div>

        <div class="perm-footer">
            <div class="perm-legend">
                <span>👁️ <strong style="color:#D48A2E">View Only</strong></span>
                <span>✏️ <strong style="color:#7c3aed">Edit &amp; Manage</strong></span>
                <span>⚪ <strong style="color:#64748b">Revoked</strong></span>
            </div>
            <button type="button" class="btn btn-secondary" onclick="closePermModal()" style="padding:8px 20px;border-radius:10px">Close</button>
        </div>
    </div>
</div>

<!-- ===== Direct Password Reset Modal ===== -->
<div id="direct-reset-backdrop" class="perm-backdrop" aria-hidden="true">
    <div class="perm-modal" style="max-width: 500px;" role="dialog">
        <div class="perm-header">
            <div>
                <h3>🔑 Direct Administrative Password Reset</h3>
                <div class="perm-sub">Set a new security password for employee account without requiring a request.</div>
            </div>
            <button type="button" class="perm-close" onclick="closeDirectResetModal()">&times;</button>
        </div>

        <form id="direct-reset-form" method="POST" action="" style="padding: 24px; display: flex; flex-direction: column; gap: 16px; background:#ffffff">
            @csrf
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px;">
                <div style="font-size: 15px; font-weight: 800; color: #92400e;" id="direct-reset-user-name">Staff Name</div>
                <div style="font-size: 12.5px; color: #b45309; margin-top: 2px;" id="direct-reset-user-email">staff@email.com</div>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 800; color: #334155; text-transform: uppercase; margin-bottom: 6px;">New Password</label>
                <input type="password" name="new_password" required placeholder="Enter new strong password"
                    style="width: 100%; padding: 11px 14px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; color: #0f172a; font-size: 13.5px; font-weight:600; outline:none">
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Must contain uppercase, lowercase, number, and special character.</div>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 800; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" required placeholder="Confirm new password"
                    style="width: 100%; padding: 11px 14px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; color: #0f172a; font-size: 13.5px; font-weight:600; outline:none">
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeDirectResetModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #d97706, #f59e0b); border: none; font-weight:800">⚡ Execute Reset</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Staff Full Profile & Uploaded Documents Dossier Modal ===== -->
<div id="staffDetailModal" class="perm-backdrop" style="z-index:10000">
    <div class="perm-modal" style="max-width:840px">
        <div class="perm-header">
            <div style="display:flex;align-items:center;gap:16px">
                <div id="modalStaffAvatarCircle" style="width:52px;height:52px;border-radius:14px;background:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;box-shadow:0 4px 14px rgba(79,70,229,0.25);flex-shrink:0">
                    S
                </div>
                <div>
                    <h3 id="modalStaffName" style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;margin:0">
                        Staff Member Dossier
                    </h3>
                    <div style="display:flex;align-items:center;gap:10px;margin-top:4px;flex-wrap:wrap">
                        <span id="modalStaffRole" style="font-size:11px;font-weight:800;padding:3px 12px;border-radius:14px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe">
                            TEACHER
                        </span>
                        <span id="modalStaffEmail" style="font-size:13px;color:#64748b;font-weight:500">
                            staff@school.com
                        </span>
                        <span id="modalStaffId" style="font-size:11px;color:#C07A22;font-family:monospace;font-weight:700;background:#FBF3E8;padding:3px 10px;border-radius:8px;border:1px solid #E8CEAA">
                            EMP-XXXX
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" class="perm-close" onclick="closeStaffDetailModal()">&times;</button>
        </div>

        <div class="perm-body">
            <!-- Information Grid -->
            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;margin-bottom:24px">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">📞 Phone / Contact</div>
                    <div id="modalStaffPhone" style="font-size:14.5px;font-weight:700;color:#0f172a;margin-top:4px">N/A</div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">🎓 Qualification</div>
                    <div id="modalStaffQualification" style="font-size:14.5px;font-weight:700;color:#0f172a;margin-top:4px">N/A</div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">⌛ Teaching Experience</div>
                    <div id="modalStaffExperience" style="font-size:14.5px;font-weight:700;color:#d97706;margin-top:4px">N/A</div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">💼 Contract Type</div>
                    <div id="modalStaffEmployment" style="font-size:14.5px;font-weight:700;color:#D48A2E;margin-top:4px">Permanent</div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">💳 Monthly Basic Salary</div>
                    <div id="modalStaffSalary" style="font-size:14.5px;font-weight:700;color:#7c3aed;margin-top:4px">PKR 0.00</div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:0.5px">📅 Account Created</div>
                    <div id="modalStaffCreated" style="font-size:14.5px;font-weight:700;color:#334155;margin-top:4px">N/A</div>
                </div>
            </div>

            <!-- Uploaded Qualification & Identification Documents Section -->
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:18px;margin-bottom:20px">
                <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:10px">
                    <span>📑</span> <span>Uploaded Academic Degrees &amp; Identification Documents</span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" id="modalStaffDocsContainer">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- Active Rights & Access Summary -->
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:18px">
                <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin-bottom:12px;display:flex;align-items:center;gap:10px">
                    <span>🛡️</span> <span>Active Delegation &amp; Portal Permission Status</span>
                </div>
                <div id="modalStaffRightsSummary" style="display:flex;flex-wrap:wrap;gap:8px">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>

        <div class="perm-footer">
            <button type="button" id="dossierEditRoleBtn" class="btn btn-secondary" onclick="openEditRoleFromDossier()" style="font-weight:800;padding:9px 18px">
                ✏️ Edit Role &amp; Contract
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeStaffDetailModal()" style="padding:9px 22px">Close Dossier</button>
        </div>
    </div>
</div>

<!-- ===== Change Role, Designation & Contract Type Modal ===== -->
<div id="editRoleModal" class="perm-backdrop" style="z-index:10001">
    <div class="perm-modal" style="max-width:680px">
        <div class="perm-header">
            <div style="display:flex;align-items:center;gap:14px">
                <div id="editRoleAvatar" style="width:46px;height:46px;border-radius:12px;background:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#ffffff;box-shadow:0 4px 12px rgba(79,70,229,0.25);flex-shrink:0">
                    ✏️
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:19px;font-weight:800;color:#0f172a;margin:0">
                        Change Role, Designation &amp; Contract
                    </h3>
                    <div style="font-size:13px;color:#64748b;margin-top:2px">
                        Reassign staff department, shift between permanent / contractual, or update title.
                    </div>
                </div>
            </div>
            <button type="button" class="perm-close" onclick="closeEditRoleModal()">&times;</button>
        </div>

        <form id="editRoleForm" method="POST" action="" style="display:flex;flex-direction:column;max-height:calc(88vh - 80px);overflow-y:auto;background:#ffffff">
            @csrf
            @method('PUT')

            <div style="padding:22px 26px;display:flex;flex-direction:column;gap:18px">
                <!-- Staff Info Banner -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                    <div>
                        <div style="font-weight:800;font-size:15px;color:#0f172a" id="editRoleStaffName">Staff Name</div>
                        <div style="font-size:12.5px;color:#64748b;margin-top:2px" id="editRoleStaffEmail">staff@school.edu.pk</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span id="editRoleCurrentBadge" style="font-size:11px;font-weight:800;padding:4px 12px;border-radius:14px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe">
                            CURRENT ROLE
                        </span>
                        <span id="editRoleStaffId" style="font-size:11px;color:#334155;font-family:monospace;font-weight:700;background:#f1f5f9;padding:4px 10px;border-radius:8px;border:1px solid #cbd5e1">
                            EMP-XXXX
                        </span>
                    </div>
                </div>

                <!-- 0. System Access Level (Role) -->
                <div style="background:#FBF3E8;border:1.5px solid #E8CEAA;border-radius:12px;padding:14px 18px">
                    <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;color:#1e40af;letter-spacing:0.5px;margin-bottom:6px">
                        🔑 System Portal Access Role *
                    </label>
                    <select name="system_role" id="edit_system_role" style="background:#ffffff;border:1.5px solid #93c5fd;color:#0f172a;border-radius:8px;padding:10px 14px;font-size:13.5px;font-weight:700;width:100%;outline:none;cursor:pointer">
                        <option value="teacher">👨‍🏫 Faculty Teacher (Academic LMS Access)</option>
                        <option value="principal">👔 Executive Campus Principal (Principal Portal Access)</option>
                        <option value="student">🎓 Student (Student Workspace Access)</option>
                        <option value="staff">🛡️ Administrative Staff (Staff Operations Access)</option>
                    </select>
                </div>

                <!-- 1. Role Selection Grid -->
                <div style="width:100%">
                    <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;color:#334155;letter-spacing:0.5px;margin-bottom:8px">
                        Select Department / Role *
                    </label>
                    <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:12px;width:100%">
                        <label class="role-modal-tile" id="edit-tile-teacher" onclick="selectEditRole('teacher')">
                            <input type="radio" name="role_option" id="edit_role_teacher" value="teacher" style="display:none">
                            <div style="font-size:22px;margin-bottom:4px">👨‍🏫</div>
                            <div class="tile-title">Teacher</div>
                        </label>
                        <label class="role-modal-tile" id="edit-tile-administration" onclick="selectEditRole('administration')">
                            <input type="radio" name="role_option" id="edit_role_administration" value="administration" style="display:none">
                            <div style="font-size:22px;margin-bottom:4px">🏛️</div>
                            <div class="tile-title">Admin</div>
                        </label>
                        <label class="role-modal-tile" id="edit-tile-coordinator" onclick="selectEditRole('coordinator')">
                            <input type="radio" name="role_option" id="edit_role_coordinator" value="coordinator" style="display:none">
                            <div style="font-size:22px;margin-bottom:4px">📋</div>
                            <div class="tile-title">Coordinator</div>
                        </label>
                        <label class="role-modal-tile" id="edit-tile-accountant" onclick="selectEditRole('accountant')">
                            <input type="radio" name="role_option" id="edit_role_accountant" value="accountant" style="display:none">
                            <div style="font-size:22px;margin-bottom:4px">💳</div>
                            <div class="tile-title">Accountant</div>
                        </label>
                        <label class="role-modal-tile" id="edit-tile-custom" onclick="selectEditRole('custom')">
                            <input type="radio" name="role_option" id="edit_role_custom" value="custom" style="display:none">
                            <div style="font-size:22px;margin-bottom:4px">➕</div>
                            <div class="tile-title">+ Custom</div>
                        </label>
                    </div>
                </div>

                <!-- Custom Role Title Input (Conditional) -->
                <div id="editCustomRoleRow" style="display:none;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:14px">
                    <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;color:#166534;margin-bottom:6px">
                        ⭐ Custom Designation / Role Title *
                    </label>
                    <input type="text" name="custom_role_name" id="edit_custom_role_name" placeholder="e.g. Lab Assistant, Librarian, Vice Principal..." style="background:#ffffff;border:1.5px solid #86efac;color:#0f172a;border-radius:8px;padding:10px 14px;font-size:13.5px;font-weight:600;width:100%;outline:none">
                </div>

                <!-- 2. Employment Contract Type (Permanent vs Contractual) -->
                <div>
                    <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;color:#334155;letter-spacing:0.5px;margin-bottom:8px">
                        Employment Contract Status *
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <label id="edit-contract-permanent" style="background:#ffffff;border:2px solid #e2e8f0;border-radius:12px;padding:14px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;color:#0f172a;transition:all 0.2s">
                            <input type="radio" name="employment_type" value="permanent" id="edit_type_permanent" style="accent-color:#10b981;width:18px;height:18px">
                            <div>
                                <div style="font-weight:800;font-size:13.5px;color:#059669">🟢 Permanent Staff</div>
                                <div style="font-size:11.5px;color:#64748b">Regular, full-time employee</div>
                            </div>
                        </label>
                        <label id="edit-contract-contractual" style="background:#ffffff;border:2px solid #e2e8f0;border-radius:12px;padding:14px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;color:#0f172a;transition:all 0.2s">
                            <input type="radio" name="employment_type" value="contractual" id="edit_type_contractual" style="accent-color:#f59e0b;width:18px;height:18px">
                            <div>
                                <div style="font-weight:800;font-size:13.5px;color:#d97706">🟡 Contractual / Visiting</div>
                                <div style="font-size:11.5px;color:#64748b">Fixed-term / visiting contractor</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Optional Details (Qualification & Basic Salary) -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;color:#334155;margin-bottom:6px">
                            🎓 Qualification / Title
                        </label>
                        <input type="text" name="qualification" id="edit_qualification" placeholder="e.g. M.Sc. Computer Science" style="background:#ffffff;border:1.5px solid #cbd5e1;color:#0f172a;border-radius:10px;padding:10px 14px;font-size:13.5px;font-weight:600;width:100%;outline:none">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;text-transform:uppercase;color:#334155;margin-bottom:6px">
                            💳 Basic Salary (PKR)
                        </label>
                        <input type="number" name="basic_salary_pkr" id="edit_basic_salary_pkr" step="500" placeholder="e.g. 75000" style="background:#ffffff;border:1.5px solid #cbd5e1;color:#0f172a;border-radius:10px;padding:10px 14px;font-size:13.5px;font-weight:600;width:100%;outline:none">
                    </div>
                </div>

                <!-- Auto-Sync Default Permissions Toggle -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#0f172a">🔄 Auto-Apply Default Role Permissions</div>
                        <div style="font-size:11.5px;color:#64748b;margin-top:2px">Update portal rights and module access to match standard permissions of the selected role.</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sync_default_permissions" value="1" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="perm-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditRoleModal()" style="padding:9px 18px;border-radius:10px">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #4f46e5, #6366f1);border:none;padding:9px 24px;border-radius:10px;font-weight:800;box-shadow:0 4px 14px rgba(79,70,229,0.25)">
                    💾 Save Role &amp; Contract Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div class="perm-toast" id="perm-toast"></div>

<script>
    let currentDossierStaff = null;

    function openStaffDetailModal(staff) {
        currentDossierStaff = staff;
        document.getElementById('modalStaffName').textContent = staff.name;
        document.getElementById('modalStaffEmail').textContent = staff.email;
        document.getElementById('modalStaffId').textContent = 'ID: ' + staff.identifier;
        document.getElementById('modalStaffRole').textContent = staff.role.toUpperCase();
        document.getElementById('modalStaffPhone').textContent = staff.phone || 'N/A';
        document.getElementById('modalStaffQualification').textContent = staff.qualification || 'N/A';
        document.getElementById('modalStaffExperience').textContent = staff.years_of_experience || 'N/A';
        document.getElementById('modalStaffEmployment').textContent = staff.employment_type || 'N/A';
        document.getElementById('modalStaffSalary').textContent = staff.basic_salary_pkr || 'N/A';
        document.getElementById('modalStaffCreated').textContent = staff.created_at || 'N/A';

        const avatarCircle = document.getElementById('modalStaffAvatarCircle');
        if (staff.profile_picture) {
            avatarCircle.innerHTML = `<img src="${staff.profile_picture}" style="width:100%;height:100%;object-fit:cover;border-radius:16px" alt="${staff.name}">`;
        } else {
            avatarCircle.innerHTML = staff.name.charAt(0).toUpperCase();
        }

        // Render Uploaded Documents List
        const docsContainer = document.getElementById('modalStaffDocsContainer');
        docsContainer.innerHTML = '';

        const docSpecs = [
            { key: 'matriculation_cert', title: 'Matric / SSC Certificate', icon: '📄' },
            { key: 'intermediate_cert', title: 'Intermediate / FSc Certificate', icon: '📄' },
            { key: 'bachelors_cert', title: 'Bachelor Degree / Transcript', icon: '🎓' },
            { key: 'masters_cert', title: 'Master / M.Phil Certificate', icon: '🎓' },
            { key: 'phd_cert', title: 'PhD / Doctorate Certificate', icon: '📜' },
        ];

        docSpecs.forEach(doc => {
            const url = staff[doc.key];
            const item = document.createElement('div');
            item.style.cssText = 'background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px';

            if (url) {
                item.innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:20px">${doc.icon}</span>
                        <div>
                            <div style="font-weight:700;font-size:13px;color:#0f172a">${doc.title}</div>
                            <div style="font-size:11.5px;color:#059669;font-weight:600;margin-top:1px">✓ Verified Document Attached</div>
                        </div>
                    </div>
                    <a href="${url}" target="_blank" class="btn btn-secondary btn-sm" style="padding:4px 12px;font-size:11.5px;font-weight:700;color:#4f46e5;border-color:#c7d2fe">
                        👁️ View / Download
                    </a>
                `;
            } else {
                item.innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px;opacity:0.65">
                        <span style="font-size:20px">${doc.icon}</span>
                        <div>
                            <div style="font-weight:600;font-size:13px;color:#64748b">${doc.title}</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:1px">Not Uploaded During Onboarding</div>
                        </div>
                    </div>
                    <span style="font-size:11px;color:#64748b;background:#f1f5f9;border:1px solid #e2e8f0;padding:3px 8px;border-radius:6px;font-weight:600">None</span>
                `;
            }
            docsContainer.appendChild(item);
        });

        // Render Active Rights Badges
        const rightsContainer = document.getElementById('modalStaffRightsSummary');
        rightsContainer.innerHTML = '';

        if (staff.is_delegated_admin) {
            rightsContainer.innerHTML = `
                <span class="rights-badge" style="background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;font-size:12px;padding:6px 14px;border-radius:20px;font-weight:800">
                    👑 Master Delegated Principal Rights (Full System Access)
                </span>
            `;
        } else {
            const perms = staff.permissions || {};
            const keys = Object.keys(perms).filter(k => perms[k]);
            if (keys.length === 0) {
                rightsContainer.innerHTML = `<span style="font-size:12.5px;color:#64748b">Standard Role Rights assigned. No custom overrides.</span>`;
            } else {
                keys.forEach(k => {
                    const badge = document.createElement('span');
                    badge.style.cssText = 'background:#FBF3E8;color:#C07A22;border:1px solid #E8CEAA;font-size:11px;padding:4px 10px;border-radius:12px;font-weight:700';
                    badge.textContent = '⚡ ' + k.replace('_', ' ').toUpperCase();
                    rightsContainer.appendChild(badge);
                });
            }
        }

        const detailModal = document.getElementById('staffDetailModal');
        if (detailModal) {
            document.body.appendChild(detailModal);
            detailModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeStaffDetailModal() {
        const detailModal = document.getElementById('staffDetailModal');
        if (detailModal) {
            detailModal.classList.remove('show');
        }
        document.body.style.overflow = '';
    }

    function openDirectResetModal(userId, userName, userEmail) {
        document.getElementById('direct-reset-user-name').textContent = userName;
        document.getElementById('direct-reset-user-email').textContent = userEmail || '';
        const prefix = window.location.pathname.startsWith('/teacher') ? 'teacher' : 'principal';
        document.getElementById('direct-reset-form').action = `/${prefix}/users/${userId}/direct-reset-password`;
        const resetModal = document.getElementById('direct-reset-backdrop');
        if (resetModal) {
            document.body.appendChild(resetModal);
            resetModal.classList.add('show');
        }
    }

    function closeDirectResetModal() {
        document.getElementById('direct-reset-backdrop').classList.remove('show');
    }
    // ── Global Admin Active Features Sync ────────────────────────────
    window.INSTITUTE_ACTIVE_FEATURES = @json($activeFeatureKeys ?? []);

    // ── Modal state ──────────────────────────────────────────────────
    let permModalUserId = null;
    let permModalPerms = {};

    const PERM_GROUPS = [
        {
            title: 'Attendance, Classes & Sections',
            icon: '🏫',
            items: [
                { key: 'attendance', featureKey: 'attendance_system', label: 'Mark Student & Daily Attendance', desc: 'Access daily attendance registers & mark student presence.' },
                { key: 'classes', featureKey: 'classes_sections', label: 'Classes & Section Management', desc: 'Manage class sections, capacity & student assignments.' },
                { key: 'subjects', featureKey: 'subjects_catalog', label: 'Subject Catalog & Lab Requirements', desc: 'Define curriculum, subjects & lab prerequisites.' },
                { key: 'academics', featureKey: 'classes_sections', label: 'Academic Terms & Active Session', desc: 'Switch active session and view academic terms.' },
            ]
        },
        {
            title: 'Financials, Fees & Scholarships',
            icon: '💰',
            items: [
                { key: 'invoices', featureKey: 'fee_invoicing', label: 'Fee Vouchers & Payment Invoices', desc: 'View ledger / generate vouchers & mark paid.' },
                { key: 'accounts', featureKey: 'financial_accounts', label: 'Financial Ledger & Salary Accounts', desc: 'Manage general ledger, accounts heads & salary slips.' },
                { key: 'scholarships', featureKey: 'scholarships', label: 'Scholarship Policies & Percentages', desc: 'View policies or set student discount percentages.' },
            ]
        },
        {
            title: 'Students & Admissions',
            icon: '🎓',
            items: [
                { key: 'students', featureKey: 'registration_portals', label: 'Student Roster & Admission Records', desc: 'Browse student list or view dossiers.' },
                { key: 'student_registration', featureKey: 'registration_portals', label: 'Register New Student Admission', desc: 'Access full student registration portal.' },
            ]
        },
        {
            title: 'Timetables & Scheduling',
            icon: '🗓️',
            items: [
                { key: 'timetables', featureKey: 'timetable', label: 'Timetable Matrix Engine', desc: 'View weekly schedules or generate matrix slots.' },
                { key: 'faculty_hours', featureKey: 'faculty_hours', label: 'Faculty Work Hours & Availability', desc: 'Manage teacher daily working time windows.' },
                { key: 'allocations', featureKey: 'teacher_allocations', label: 'Subject-Teacher Allocations', desc: 'Map teachers to subject sections.' },
                { key: 'rooms', featureKey: 'rooms_facilities', label: 'Campus Rooms & Facilities', desc: 'View room list or manage campus facilities.' },
            ]
        },
        {
            title: 'LMS, AI Suite & Examination Reports',
            icon: '📚',
            items: [
                { key: 'lms_content', featureKey: 'lms_content', label: 'LMS Course Materials & Uploads', desc: 'Upload & manage syllabus PDFs, study guides & course assets.' },
                { key: 'ai_bot', featureKey: 'ai_bot', label: 'AI RAG Learning Assistant & Chatbot', desc: 'Access AI study assistant & interactive queries.' },
                { key: 'practice_tests', featureKey: 'practice_tests', label: 'AI Practice Test & Mock Generator', desc: 'Generate student practice tests and evaluate mock scores.' },
                { key: 'assessment_engine', featureKey: 'assessment_engine', label: 'Assessments, Quizzes & Auto-Grading', desc: 'Create, schedule, auto-grade and manage quizzes.' },
                { key: 'datesheet_manager', featureKey: 'datesheet_manager', label: 'Official Exam Datesheet Manager', desc: 'Schedule exam datesheets and timetable slots.' },
                { key: 'exam_reports', featureKey: 'exam_reports', label: 'Exam Reports & Marksheet Entries', desc: 'Enter student marks, generate marksheets and report cards.' },
                { key: 'grading_normalizer', featureKey: 'grading_normalizer', label: 'Grade Normalization & Term Reports', desc: 'Run end-of-term grade normalization engines.' },
            ]
        },
        {
            title: 'Faculty & Staff Governance',
            icon: '👥',
            items: [
                { key: 'staff', featureKey: 'staff_governance', label: 'Faculty & Staff Roster', desc: 'View staff profiles or onboard new employees.' },
                { key: 'staff_onboard', featureKey: 'staff_governance', label: 'Onboard Faculty / Staff', desc: 'Upload academic certificates & contracts.' },
            ]
        },
        {
            title: 'Directory & Official Records',
            icon: '🔍',
            items: [
                { key: 'directory', featureKey: 'master_directory', label: 'Master Directory Search & Salary Slips', desc: 'Search master records & manage salary slips.' },
            ]
        },
        {
            title: 'Security & Account Control',
            icon: '🔒',
            items: [
                { key: 'security', featureKey: 'security_management', label: 'Security Settings & Password Reset', desc: 'Access security settings and reset credentials.' },
            ]
        },
    ];

    function openPermModal(userId, userName, btn) {
        permModalUserId = userId;
        permModalPerms = parseStaffPerms(btn);
        const subtitle = document.getElementById('perm-subtitle');
        subtitle.textContent = userName ? `Configure View Only vs Edit rights for ${userName}.` : 'Configure View Only vs Edit rights for this employee.';

        // Delegate toggle state
        const delegateInput = document.getElementById('delegation-toggle');
        delegateInput.checked = (btn.dataset.staffDelegate === '1');

        // Build permission accordion groups
        const container = document.getElementById('perm-groups');
        container.innerHTML = '';

        PERM_GROUPS.forEach((group, idx) => {
            // Filter items based on what Global Admin has enabled for this institute
            const activeItems = group.items.filter(item => {
                if (!window.INSTITUTE_ACTIVE_FEATURES || window.INSTITUTE_ACTIVE_FEATURES.length === 0) return true;
                return !item.featureKey || window.INSTITUTE_ACTIVE_FEATURES.includes(item.featureKey);
            });

            if (activeItems.length === 0) return; // Skip category if all features in it are disabled by Global Admin!

            const card = document.createElement('div');
            card.className = 'perm-accordion-card' + (idx === 0 ? ' open' : '');

            // Header
            const header = document.createElement('div');
            header.className = 'perm-accordion-header';
            header.onclick = () => card.classList.toggle('open');

            const title = document.createElement('div');
            title.className = 'perm-accordion-title';
            title.innerHTML = `<span style="font-size:16px">${group.icon}</span> <span>${group.title}</span>`;

            const rightSide = document.createElement('div');
            rightSide.style.cssText = 'display:flex;align-items:center;gap:10px';

            let groupActiveCount = 0;
            activeItems.forEach(item => {
                if (permModalPerms[item.key + '_view'] || permModalPerms[item.key + '_edit'] || permModalPerms[item.key]) {
                    groupActiveCount++;
                }
            });

            const badge = document.createElement('span');
            badge.className = 'perm-accordion-badge';
            badge.textContent = `${groupActiveCount}/${activeItems.length} Active`;

            const caret = document.createElement('span');
            caret.className = 'perm-accordion-caret';
            caret.innerHTML = '▼';

            rightSide.appendChild(badge);
            rightSide.appendChild(caret);

            header.appendChild(title);
            header.appendChild(rightSide);

            // Body
            const body = document.createElement('div');
            body.className = 'perm-accordion-body';

            activeItems.forEach(item => {
                body.appendChild(buildPermRow(userId, item));
            });

            card.appendChild(header);
            card.appendChild(body);
            container.appendChild(card);
        });

        const pModal = document.getElementById('perm-backdrop');
        if (pModal) {
            document.body.appendChild(pModal);
            pModal.classList.add('show');
        }
        document.body.style.overflow = 'hidden';
    }

    function parseStaffPerms(btn) {
        try {
            return JSON.parse(btn.dataset.staffPerms || '{}');
        } catch (e) {
            return {};
        }
    }

    function buildPermRow(userId, item) {
        const row = document.createElement('div');
        row.className = 'perm-item';
        row.dataset.permKey = item.key;
        row.dataset.permUserId = userId;

        const info = document.createElement('div');
        info.style.cssText = 'display:flex;align-items:center;gap:12px;min-width:0';
        const txt = document.createElement('div');
        txt.style.cssText = 'min-width:0';
        const title = document.createElement('div');
        title.style.cssText = 'font-size:13.5px;font-weight:700;color:#0f172a';
        title.textContent = item.label;
        const desc = document.createElement('div');
        desc.style.cssText = 'font-size:12px;color:#64748b;margin-top:2px';
        desc.textContent = item.desc || '';
        txt.appendChild(title);
        txt.appendChild(desc);
        info.appendChild(txt);

        // Check states
        const isEditOn = !!(permModalPerms[item.key + '_edit'] || (permModalPerms[item.key] && permModalPerms[item.key] !== 'view'));
        const isViewOn = !!(permModalPerms[item.key + '_view'] || permModalPerms[item.key] || isEditOn);

        if (isViewOn || isEditOn) {
            row.classList.add('on');
        }

        // Dual Toggle Box
        const dualBox = document.createElement('div');
        dualBox.className = 'dual-toggle-box';

        // 1. View Only Toggle Pill
        const viewPill = document.createElement('div');
        viewPill.className = 'toggle-pill-group';
        const viewLabel = document.createElement('span');
        viewLabel.className = 'toggle-pill-label';
        viewLabel.textContent = '👁️ View';
        const viewToggle = document.createElement('label');
        viewToggle.className = 'toggle-switch';
        const viewInput = document.createElement('input');
        viewInput.type = 'checkbox';
        viewInput.checked = isViewOn;

        // 2. Edit / Manage Toggle Pill
        const editPill = document.createElement('div');
        editPill.className = 'toggle-pill-group';
        const editLabel = document.createElement('span');
        editLabel.className = 'toggle-pill-label';
        editLabel.textContent = '✏️ Edit';
        const editToggle = document.createElement('label');
        editToggle.className = 'toggle-switch';
        const editInput = document.createElement('input');
        editInput.type = 'checkbox';
        editInput.checked = isEditOn;

        // View input event listener
        viewInput.onchange = async () => {
            const newState = viewInput.checked;
            permModalPerms[item.key + '_view'] = newState ? 1 : 0;
            if (!newState) {
                editInput.checked = false;
                permModalPerms[item.key + '_edit'] = 0;
                delete permModalPerms[item.key];
                row.classList.remove('on');
                await applyToggle(userId, item.key + '_view', false);
                await applyToggle(userId, item.key + '_edit', false);
                await applyToggle(userId, item.key, false);
                showToast(`Revoked View & Edit rights for ${item.label}`);
            } else {
                row.classList.add('on');
                await applyToggle(userId, item.key + '_view', true);
                showToast(`Granted View Only right for ${item.label}`);
            }
        };

        // Edit input event listener
        editInput.onchange = async () => {
            const newState = editInput.checked;
            permModalPerms[item.key + '_edit'] = newState ? 1 : 0;
            if (newState) {
                viewInput.checked = true;
                permModalPerms[item.key + '_view'] = 1;
                permModalPerms[item.key] = 1;
                row.classList.add('on');
                await applyToggle(userId, item.key + '_edit', true);
                await applyToggle(userId, item.key + '_view', true);
                await applyToggle(userId, item.key, true);
                showToast(`Granted Full Edit & Manage rights for ${item.label}`);
            } else {
                delete permModalPerms[item.key];
                await applyToggle(userId, item.key + '_edit', false);
                showToast(`Downgraded ${item.label} to View Only access`);
            }
        };

        const viewSlider = document.createElement('span');
        viewSlider.className = 'toggle-slider';
        viewToggle.appendChild(viewInput);
        viewToggle.appendChild(viewSlider);
        viewPill.appendChild(viewLabel);
        viewPill.appendChild(viewToggle);

        const editSlider = document.createElement('span');
        editSlider.className = 'toggle-slider';
        editToggle.appendChild(editInput);
        editToggle.appendChild(editSlider);
        editPill.appendChild(editLabel);
        editPill.appendChild(editToggle);

        dualBox.appendChild(viewPill);
        dualBox.appendChild(editPill);

        row.appendChild(info);
        row.appendChild(dualBox);
        return row;
    }

    async function applyToggle(userId, key, state) {
        if (!userId) return;
        const body = new FormData();
        body.append('permission_key', key);
        body.append('state', state ? '1' : '0');

        const res = await fetch(`/principal/staff/${userId}/toggle-permission`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body,
        });
        if (!res.ok) throw new Error('Request failed');
        const data = await res.json();

        if (data.effective_permissions) {
            permModalPerms = Object.assign({}, permModalPerms, data.effective_permissions);
        }

        // Sync row DOM dataset
        const rightsBtn = document.querySelector(`.btn-rights[data-staff-id="${userId}"]`);
        if (rightsBtn) {
            rightsBtn.dataset.staffPerms = JSON.stringify(permModalPerms);
        }
        return data;
    }

    async function applyDelegation(userId, state) {
        const body = new FormData();
        body.append('state', state ? '1' : '0');
        const res = await fetch(`/principal/staff/${userId}/toggle-delegation`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body,
        });
        if (!res.ok) throw new Error('Request failed');

        // Sync row DOM dataset
        const rightsBtn = document.querySelector(`.btn-rights[data-staff-id="${userId}"]`);
        if (rightsBtn) {
            rightsBtn.dataset.staffDelegate = state ? '1' : '0';
        }
        return res.json();
    }

    function showToast(message, type) {
        const toast = document.getElementById('perm-toast');
        toast.innerHTML = `<span>${type === 'error' ? '⚠️' : '✅'}</span> ${message}`;
        toast.style.borderColor = type === 'error' ? 'rgba(244,63,94,0.5)' : 'rgba(212,138,46,0.4)';
        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 2600);
    }

    function closePermModal() {
        document.getElementById('perm-backdrop').classList.remove('show');
        document.body.style.overflow = '';
        permModalUserId = null;
        if (document.getElementById('perm-modal-search')) {
            document.getElementById('perm-modal-search').value = '';
        }
    }

    function filterPermModalItems() {
        const query = (document.getElementById('perm-modal-search')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.perm-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            if (query === '' || text.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });

        // Hide empty accordion groups during search
        document.querySelectorAll('.perm-accordion-card').forEach(card => {
            const visibleItems = card.querySelectorAll('.perm-item:not([style*="display: none"])');
            if (query !== '' && visibleItems.length === 0) {
                card.style.display = 'none';
            } else {
                card.style.display = 'block';
                if (query !== '') card.classList.add('open');
            }
        });
    }

    // ── Staff Category Filtering Engine ────────────────────────────────
    let currentStaffCategory = 'all';

    function filterStaffCategory(category, tabBtn) {
        currentStaffCategory = category;
        document.querySelectorAll('.cat-tab').forEach(btn => btn.classList.remove('active'));
        if (tabBtn) tabBtn.classList.add('active');
        applyStaffFilters();
    }

    function onStaffSearch() {
        applyStaffFilters();
    }

    function applyStaffFilters() {
        const query = (document.getElementById('staff-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.staff-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const cat = row.dataset.staffCategory;
            const searchText = row.dataset.searchText || '';

            const matchesCat = (currentStaffCategory === 'all' || cat === currentStaffCategory);
            const matchesSearch = (!query || searchText.includes(query));

            if (matchesCat && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyRow = document.getElementById('empty-category-row');
        if (emptyRow) {
            if (visibleCount === 0 && rows.length > 0) {
                emptyRow.style.display = '';
            } else {
                emptyRow.style.display = 'none';
            }
        }
    }

    function updateStaffCategoryBadges() {
        const rows = document.querySelectorAll('.staff-row');
        const counts = {};

        rows.forEach(row => {
            const cat = row.dataset.staffCategory;
            if (cat) {
                counts[cat] = (counts[cat] || 0) + 1;
            }
        });

        if (document.getElementById('badge-all')) document.getElementById('badge-all').textContent = rows.length;
        if (document.getElementById('badge-teacher')) document.getElementById('badge-teacher').textContent = counts['teacher'] || 0;
        if (document.getElementById('badge-admin')) document.getElementById('badge-admin').textContent = counts['administration'] || 0;
        if (document.getElementById('badge-accountant')) document.getElementById('badge-accountant').textContent = counts['accountant'] || 0;
        if (document.getElementById('badge-coordinator')) document.getElementById('badge-coordinator').textContent = counts['coordinator'] || 0;

        // Dynamic custom category badges
        Object.keys(counts).forEach(catKey => {
            if (catKey.startsWith('custom-')) {
                const el = document.getElementById('badge-' + catKey);
                if (el) el.textContent = counts[catKey];
            }
        });

        if (document.getElementById('badge-other')) document.getElementById('badge-other').textContent = counts['other'] || 0;

        const otherTab = document.getElementById('tab-other');
        if (otherTab) {
            otherTab.style.display = (counts['other'] && counts['other'] > 0) ? 'inline-flex' : 'none';
        }
    }

    function openEditRoleFromDossier() {
        if (currentDossierStaff && currentDossierStaff.edit_data) {
            closeStaffDetailModal();
            openEditRoleModal(currentDossierStaff.edit_data);
        }
    }

    function openEditRoleModal(data) {
        const modal = document.getElementById('editRoleModal');
        const form = document.getElementById('editRoleForm');
        if (!modal || !form) return;

        form.action = data.update_url;

        document.getElementById('editRoleStaffName').textContent = data.name || 'Staff Member';
        document.getElementById('editRoleStaffEmail').textContent = data.email || '';
        document.getElementById('editRoleStaffId').textContent = 'ID: ' + (data.identifier || 'N/A');
        
        const currentRoleDisplay = (data.staff_role || 'TEACHER').toUpperCase() + (data.employment_type ? ' (' + data.employment_type.toUpperCase() + ')' : '');
        document.getElementById('editRoleCurrentBadge').textContent = currentRoleDisplay;

        const avatar = document.getElementById('editRoleAvatar');
        if (avatar) {
            avatar.textContent = (data.name || 'S').charAt(0).toUpperCase();
        }

        const systemRoleSel = document.getElementById('edit_system_role');
        if (systemRoleSel) {
            systemRoleSel.value = data.system_role || 'teacher';
        }

        // Set qualification and salary
        const qualInput = document.getElementById('edit_qualification');
        if (qualInput) qualInput.value = data.qualification || '';

        const salaryInput = document.getElementById('edit_basic_salary_pkr');
        if (salaryInput) salaryInput.value = data.basic_salary_pkr || '';

        // Set employment type radio
        const empType = data.employment_type || 'permanent';
        const permRadio = document.getElementById('edit_type_permanent');
        const contractRadio = document.getElementById('edit_type_contractual');
        if (empType === 'contractual') {
            if (contractRadio) contractRadio.checked = true;
        } else {
            if (permRadio) permRadio.checked = true;
        }

        // Set role option
        selectEditRole(data.role_option || 'teacher', data.custom_role_name || data.staff_role);

        document.body.appendChild(modal);
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeEditRoleModal() {
        const modal = document.getElementById('editRoleModal');
        if (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    }

    function selectEditRole(roleSlug, customName = '') {
        // Unselect all tiles
        document.querySelectorAll('.role-modal-tile').forEach(t => t.classList.remove('selected'));

        // Select clicked tile
        const targetTile = document.getElementById('edit-tile-' + roleSlug) || document.getElementById('edit-tile-custom');
        if (targetTile) {
            targetTile.classList.add('selected');
            const radio = targetTile.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        const customRow = document.getElementById('editCustomRoleRow');
        const customInput = document.getElementById('edit_custom_role_name');
        if (roleSlug === 'custom') {
            if (customRow) customRow.style.display = 'block';
            if (customInput) {
                customInput.required = true;
                if (customName) customInput.value = customName;
                customInput.focus();
            }
        } else {
            if (customRow) customRow.style.display = 'none';
            if (customInput) {
                customInput.required = false;
            }
        }
    }

    // ── Event delegation handlers ────────────────────────────────────
    function initCategoryBadges() {
        updateStaffCategoryBadges();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCategoryBadges);
    } else {
        initCategoryBadges();
    }
    window.addEventListener('load', initCategoryBadges);

    document.addEventListener('DOMContentLoaded', () => {

        // Modal backdrop click closes
        const backdrop = document.getElementById('perm-backdrop');
        if (backdrop) {
            backdrop.addEventListener('mousedown', (e) => {
                if (e.target === backdrop) closePermModal();
            });
        }

        const staffDetailModal = document.getElementById('staffDetailModal');
        if (staffDetailModal) {
            staffDetailModal.addEventListener('mousedown', (e) => {
                if (e.target === staffDetailModal) closeStaffDetailModal();
            });
        }

        const editRoleModal = document.getElementById('editRoleModal');
        if (editRoleModal) {
            editRoleModal.addEventListener('mousedown', (e) => {
                if (e.target === editRoleModal) closeEditRoleModal();
            });
        }

        const directResetBackdrop = document.getElementById('direct-reset-backdrop');
        if (directResetBackdrop) {
            directResetBackdrop.addEventListener('mousedown', (e) => {
                if (e.target === directResetBackdrop) closeDirectResetModal();
            });
        }

        // Escape closes
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (editRoleModal && editRoleModal.classList.contains('show')) closeEditRoleModal();
                if (staffDetailModal && staffDetailModal.classList.contains('show')) closeStaffDetailModal();
                if (backdrop && backdrop.classList.contains('show')) closePermModal();
                if (directResetBackdrop && directResetBackdrop.classList.contains('show')) closeDirectResetModal();
            }
        });

        // Delegate toggle (Master Admin)
        const delegationToggle = document.getElementById('delegation-toggle');
        if (delegationToggle) {
            delegationToggle.addEventListener('change', async (e) => {
                const userId = permModalUserId;
                if (!userId) return;
                const state = e.target.checked;
                try {
                    const data = await applyDelegation(userId, state);
                    const badge = document.querySelector(`[data-delegate-badge="${userId}"]`);
                    if (badge) {
                        badge.textContent = state ? '👑 Master Delegated Admin' : '🛡️ Standard Role Rights';
                        badge.style.background = state ? '#eef2ff' : '#f8fafc';
                        badge.style.color = state ? '#4338ca' : '#475569';
                        badge.style.borderColor = state ? '#c7d2fe' : '#cbd5e1';
                    }
                    showToast(data.message || (state ? 'Master Admin rights granted.' : 'Master Admin rights revoked.'));
                } catch (err) {
                    e.target.checked = !state;
                    showToast('Failed to update Master Admin rights.', 'error');
                }
            });
        }

        // Modal permission toggles (delegated)
        const permGroups = document.getElementById('perm-groups');
        if (permGroups) {
            permGroups.addEventListener('change', async (e) => {
                const input = e.target;
                if (!input.matches('input[data-live-perm][data-perm]')) return;
                const userId = permModalUserId;
                if (!userId) return;
                const key = input.dataset.perm;
                const state = input.checked;
                const row = input.closest('.perm-item');

                // View-first dependency enforcement
                if (!state && key.endsWith('_view')) {
                    const editKey = key.slice(0, -5) + '_edit';
                    const editInput = document.querySelector(`input[data-perm="${editKey}"]`);
                    if (editInput && editInput.checked) {
                        editInput.checked = false;
                        const editRow = editInput.closest('.perm-item');
                        if (editRow) editRow.classList.remove('on');
                        permModalPerms[editKey] = false;
                        await applyToggle(userId, editKey, false);
                    }
                } else if (state && key.endsWith('_edit')) {
                    const viewKey = key.slice(0, -5) + '_view';
                    const viewInput = document.querySelector(`input[data-perm="${viewKey}"]`);
                    if (viewInput && !viewInput.checked) {
                        viewInput.checked = true;
                        const viewRow = viewInput.closest('.perm-item');
                        if (viewRow) viewRow.classList.add('on');
                        permModalPerms[viewKey] = true;
                        await applyToggle(userId, viewKey, true);
                    }
                }

                try {
                    const data = await applyToggle(userId, key, state);
                    if (row) row.classList.toggle('on', state);
                    permModalPerms[key] = state;
                    showToast(data.message || `${state ? 'Enabled' : 'Disabled'} ${key}.`);
                } catch (err) {
                    input.checked = !state;
                    if (row) row.classList.toggle('on', !state);
                    showToast('Failed to update permission.', 'error');
                }
            });
        }
    });

    // Expose functions to global window scope for inline onclick handlers
    window.openStaffDetailModal = openStaffDetailModal;
    window.closeStaffDetailModal = closeStaffDetailModal;
    window.openDirectResetModal = openDirectResetModal;
    window.closeDirectResetModal = closeDirectResetModal;
    window.openPermModal = openPermModal;
    window.closePermModal = closePermModal;
    window.openEditRoleModal = openEditRoleModal;
    window.closeEditRoleModal = closeEditRoleModal;
    window.selectEditRole = selectEditRole;
    window.filterStaffCategory = filterStaffCategory;
    window.onStaffSearch = onStaffSearch;
    window.filterPermModalItems = filterPermModalItems;
    window.openEditRoleFromDossier = openEditRoleFromDossier;
</script>

<style>
    .modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        z-index: 9999999 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        margin: 0 !important;
    }
    .modal-backdrop.active {
        display: flex !important;
    }
    .modal-box {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
        width: 100% !important;
        max-width: 520px !important;
        box-shadow: 0 25px 60px -10px rgba(15,23,42,0.35) !important;
        overflow: hidden !important;
        animation: modalIn .2s ease !important;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

{{-- ===== IMPORT STAFF MODAL ===== --}}
<div id="importStaffModal" class="modal-backdrop" style="position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100vw!important;height:100vh!important;background:rgba(15,23,42,0.65)!important;backdrop-filter:blur(8px)!important;-webkit-backdrop-filter:blur(8px)!important;z-index:99999999!important;display:none;align-items:center!important;justify-content:center!important;padding:20px!important;box-sizing:border-box!important;margin:0!important">
    <div class="modal-box" style="background:#ffffff!important;border:1px solid #e2e8f0!important;border-radius:18px!important;max-width:520px!important;width:100%!important;box-shadow:0 25px 60px -10px rgba(15,23,42,0.35)!important;overflow:hidden!important;position:relative!important;padding:0!important">
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #e2e8f0;background:#f8fafc">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:12px;background:#FBF3E8;display:flex;align-items:center;justify-content:center;font-size:20px;border:1px solid #E8CEAA">📥</div>
                <div>
                    <div style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">Import Staff</div>
                    <div style="font-size:11.5px;color:#64748b;margin-top:1px">Upload CSV or Excel to onboard faculty &amp; support staff in bulk.</div>
                </div>
            </div>
            <button type="button" onclick="closeImportStaffModal()" style="background:none;border:1px solid #e2e8f0;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px;color:#64748b;display:flex;align-items:center;justify-content:center">&times;</button>
        </div>

        {{-- Body --}}
        <div style="padding:22px">
            {{-- Info box --}}
            <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div style="font-size:12px;font-weight:700;color:#059669;margin-bottom:4px">📋 Expected Columns</div>
                    <div style="font-size:11px;color:#047857;line-height:1.8">
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Name</code>
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Email</code>
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Phone</code>
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Role</code>
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Qualification</code>
                        <code style="background:#fff;padding:1px 5px;border-radius:4px;border:1px solid #a7f3d0">Basic Salary</code>
                        <br>All fields optional — missing values auto-fill with defaults.
                    </div>
                </div>
                <a href="{{ route('principal.bulk-import.sample', 'staff') }}" style="flex-shrink:0;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9px;background:#fff;border:1.5px solid #a7f3d0;font-size:12px;font-weight:700;color:#059669;text-decoration:none;white-space:nowrap">⬇️ Sample CSV</a>
            </div>

            <form method="POST" action="{{ route('principal.bulk-import.staff') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:8px">SELECT CSV / EXCEL FILE</label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                        style="width:100%;padding:10px 14px;border:2px dashed #D48A2E;background:#FBF3E8;border-radius:12px;font-size:13px;cursor:pointer;box-sizing:border-box">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" class="btn btn-ghost" onclick="closeImportStaffModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">⚡ Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('importStaffModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    window.closeImportStaffModal();
                }
            });
        }
    })();
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
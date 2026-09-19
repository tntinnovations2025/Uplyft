@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.directory.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.directory.';
    }
@endphp

@section('title', 'Search Profile')
@section('breadcrumb', 'Search Profile')

@section('content')
<style>
    /* ── Compact & Professional Campus Directory System ── */
    .directory-search-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 18px;
    }

    .directory-search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .directory-search-input-wrapper input {
        width: 100%;
        padding: 9px 120px 9px 38px;
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        color: #0F172A;
        font-size: 13px;
        font-weight: 500;
        outline: none;
        transition: all 0.2s;
    }

    .directory-search-input-wrapper input:focus {
        background: #ffffff;
        border-color: #D48A2E;
        box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.15);
    }

    .directory-search-input-wrapper .search-icon {
        position: absolute;
        left: 12px;
        color: #94A3B8;
        pointer-events: none;
        display: flex;
        align-items: center;
    }

    .directory-search-btn {
        position: absolute;
        right: 4px;
        padding: 6px 16px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 6px;
        background: #17191C;
        color: #F9F8F5;
        border: 1px solid #2A2C30;
        cursor: pointer;
        transition: all 0.2s;
    }

    .directory-search-btn:hover {
        background: #D48A2E;
        color: #1A1200;
        border-color: #C07A22;
    }

    .directory-type-tabs {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .directory-tab-btn {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        color: #475569;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        transition: all 0.18s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .directory-tab-btn:hover {
        background: #E2E8F0;
        color: #0F172A;
    }

    .directory-tab-btn.active {
        background: #17191C;
        border-color: #17191C;
        color: #D48A2E;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    /* ── High Density 3-Column Responsive Grid ── */
    .records-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    @media (min-width: 768px) {
        .records-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .records-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* ── Compact & Clean Card Design ── */
    .profile-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .profile-card:hover {
        border-color: #CBD5E1;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }

    .card-top-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 9px;
        border-bottom: 1px solid #F1F5F9;
        margin-bottom: 8px;
    }

    .avatar-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        object-fit: cover;
        border: 1px solid #E2E8F0;
        flex-shrink: 0;
    }

    .card-title-area {
        flex: 1;
        min-width: 0;
    }

    .name-title {
        font-family: 'Manrope', sans-serif;
        font-size: 13px;
        font-weight: 800;
        color: #0F172A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.25;
    }

    .id-badge {
        display: inline-block;
        font-size: 10px;
        font-family: monospace;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
        margin-top: 2px;
    }

    .id-badge.faculty {
        background: #FBF3E8;
        color: #1D4ED8;
        border-color: #E8CEAA;
    }

    /* ── Compact Key-Value Details Rows ── */
    .card-meta-list {
        display: flex;
        flex-direction: column;
        gap: 4.5px;
        font-size: 11.5px;
        line-height: 1.35;
        color: #475569;
        margin-bottom: 10px;
        flex: 1;
    }

    .meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        overflow: hidden;
    }

    .meta-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748B;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .meta-val {
        font-weight: 600;
        color: #1E293B;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: right;
    }

    .card-footer-action {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-top: 8px;
        border-top: 1px dashed #E2E8F0;
    }

    .view-profile-link {
        font-size: 11px;
        font-weight: 700;
        color: #D48A2E;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 5px;
        background: #FBF3E8;
        border: 1px solid #E8CEAA;
        transition: all 0.15s;
    }

    .view-profile-link:hover {
        background: #D48A2E;
        color: #ffffff;
        border-color: #D48A2E;
    }

    .view-profile-link.faculty {
        color: #D48A2E;
        background: #FFFBEB;
        border-color: #FDE68A;
    }

    .view-profile-link.faculty:hover {
        background: #D48A2E;
        color: #ffffff;
        border-color: #D48A2E;
    }
</style>

<!-- ── COMPACT SEARCH CONTROLS ── -->
<div class="directory-search-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px">
        <div>
            <span style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#0F172A;letter-spacing:-0.3px">
                Search Profile
            </span>
            <span style="font-size:11.5px;color:#64748B;margin-left:8px;font-weight:500">
                Search students &amp; faculty by name, roll ID, employee ID, phone, or CNIC.
            </span>
        </div>
        <div style="font-size:11px;font-weight:700;color:#475569;background:#F8FAFC;padding:3px 8px;border-radius:6px;border:1px solid #E2E8F0">
            Total Results: {{ $students->count() + $teachers->count() }}
        </div>
    </div>

    <form method="GET" action="{{ route($routePrefix . 'index') }}">
        <div class="directory-search-input-wrapper">
            <span class="search-icon">
                <x-icon name="magnifying-glass" class="w-4 h-4" />
            </span>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, roll ID, employee ID, phone, or CNIC..." autofocus>
            <button type="submit" class="directory-search-btn">
                Search
            </button>
        </div>

        <div class="directory-type-tabs">
            <a href="{{ route($routePrefix . 'index', ['search' => $search, 'type' => 'all']) }}" class="directory-tab-btn {{ $filterType === 'all' ? 'active' : '' }}">
                <x-icon name="layer-group" class="w-3.5 h-3.5" /> All ({{ $students->count() + $teachers->count() }})
            </a>
            <a href="{{ route($routePrefix . 'index', ['search' => $search, 'type' => 'students']) }}" class="directory-tab-btn {{ $filterType === 'students' ? 'active' : '' }}">
                <x-icon name="user-graduate" class="w-3.5 h-3.5" /> Students ({{ $students->count() }})
            </a>
            <a href="{{ route($routePrefix . 'index', ['search' => $search, 'type' => 'teachers']) }}" class="directory-tab-btn {{ $filterType === 'teachers' ? 'active' : '' }}">
                <x-icon name="chalkboard-user" class="w-3.5 h-3.5" /> Faculty &amp; Staff ({{ $teachers->count() }})
            </a>
            @if($search !== '')
                <a href="{{ route($routePrefix . 'index', ['type' => $filterType]) }}" class="directory-tab-btn" style="color:#DC2626;background:#FEF2F2;border-color:#FEE2E2">
                    <x-icon name="xmark" class="w-3.5 h-3.5" /> Clear Filter
                </a>
            @endif
        </div>
    </form>
</div>

<!-- ── RESULTS DISPLAY ── -->
@if($filterType === 'all' || $filterType === 'students')
    <div style="margin-bottom:22px">
        <div style="font-family:'Manrope',sans-serif;font-size:14px;font-weight:800;color:#0F172A;margin-bottom:10px;display:flex;align-items:center;gap:8px">
            <x-icon name="user-graduate" class="w-4 h-4 text-emerald-600" />
            <span>Student Profiles &amp; Class Records</span>
            <span style="font-size:11px;font-weight:700;color:#047857;background:#DCFCE7;border:1px solid #BBF7D0;padding:1px 7px;border-radius:12px">{{ $students->count() }} Found</span>
        </div>

        @if($students->isEmpty())
            <div class="card text-center" style="padding:20px;font-size:12px;color:#64748B;border-radius:8px">
                No student profiles matching search parameters.
            </div>
        @else
            <div class="records-grid">
                @foreach($students as $st)
                    <div class="profile-card">
                        <div>
                            <div class="card-top-row">
                                @if($st->passport_picture_path)
                                    <img src="{{ Storage::url($st->passport_picture_path) }}" class="avatar-box" alt="{{ $st->full_name }}">
                                @else
                                    <div class="avatar-box">
                                        <x-icon name="user-graduate" class="w-4 h-4 text-slate-500" />
                                    </div>
                                @endif

                                <div class="card-title-area">
                                    <div class="name-title" title="{{ $st->full_name }}">{{ $st->full_name }}</div>
                                    <span class="id-badge">{{ $st->roll_number }}</span>
                                </div>
                            </div>

                            <div class="card-meta-list">
                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="layer-group" class="w-3 h-3 text-slate-400" /> Class:
                                    </span>
                                    <span class="meta-val" title="{{ $st->classSection?->instituteClass?->name ?? 'N/A' }} — Sec {{ $st->classSection?->section_name ?? $st->classSection?->name ?? 'N/A' }}">
                                        {{ $st->classSection?->instituteClass?->name ?? 'N/A' }} ({{ $st->classSection?->section_name ?? $st->classSection?->name ?? 'N/A' }})
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="user" class="w-3 h-3 text-slate-400" /> Guardian:
                                    </span>
                                    <span class="meta-val" title="{{ $st->father_guardian_name ?? 'N/A' }}">
                                        {{ $st->father_guardian_name ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="phone" class="w-3 h-3 text-slate-400" /> Phone:
                                    </span>
                                    <span class="meta-val">
                                        {{ $st->guardian_phone ?? $st->phone ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="address-card" class="w-3 h-3 text-slate-400" /> CNIC:
                                    </span>
                                    <span class="meta-val" style="font-family:monospace;font-size:11px">
                                        {{ $st->student_bform_cnic ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="envelope" class="w-3 h-3 text-slate-400" /> Email:
                                    </span>
                                    <span class="meta-val" title="{{ $st->email }}">
                                        {{ $st->email ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer-action">
                            <a href="{{ route($routePrefix . 'student', $st->id) }}" class="view-profile-link">
                                <span>View Profile</span>
                                <x-icon name="arrow-right" class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

@if($filterType === 'all' || $filterType === 'teachers')
    <div>
        <div style="font-family:'Manrope',sans-serif;font-size:14px;font-weight:800;color:#0F172A;margin-bottom:10px;display:flex;align-items:center;gap:8px">
            <x-icon name="chalkboard-user" class="w-4 h-4 text-blue-600" />
            <span>Faculty &amp; Staff Records</span>
            <span style="font-size:11px;font-weight:700;color:#1D4ED8;background:#E8CEAA;border:1px solid #BFDBFE;padding:1px 7px;border-radius:12px">{{ $teachers->count() }} Found</span>
        </div>

        @if($teachers->isEmpty())
            <div class="card text-center" style="padding:20px;font-size:12px;color:#64748B;border-radius:8px">
                No faculty members matching search parameters.
            </div>
        @else
            <div class="records-grid">
                @foreach($teachers as $tc)
                    <div class="profile-card">
                        <div>
                            <div class="card-top-row">
                                <div class="avatar-box" style="background:#FBF3E8;border-color:#E8CEAA">
                                    <x-icon name="chalkboard-user" class="w-4 h-4 text-blue-600" />
                                </div>

                                <div class="card-title-area">
                                    <div class="name-title" title="{{ $tc->full_name }}">{{ $tc->full_name }}</div>
                                    <span class="id-badge faculty">{{ $tc->employee_id ?? 'EMP-' . $tc->id }}</span>
                                </div>
                            </div>

                            <div class="card-meta-list">
                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="graduation-cap" class="w-3 h-3 text-slate-400" /> Qualification:
                                    </span>
                                    <span class="meta-val" title="{{ $tc->qualification ?? 'Faculty Member' }}">
                                        {{ $tc->qualification ?? 'Faculty Member' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="phone" class="w-3 h-3 text-slate-400" /> Phone:
                                    </span>
                                    <span class="meta-val">
                                        {{ $tc->phone ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="envelope" class="w-3 h-3 text-slate-400" /> Email:
                                    </span>
                                    <span class="meta-val" title="{{ $tc->email }}">
                                        {{ $tc->email ?? 'N/A' }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="money-bill" class="w-3 h-3 text-slate-400" /> Base Salary:
                                    </span>
                                    <span class="meta-val" style="font-weight:700;color:#0F172A">
                                        PKR {{ number_format($tc->basic_salary_pkr ?? 0) }}
                                    </span>
                                </div>

                                <div class="meta-row">
                                    <span class="meta-label">
                                        <x-icon name="file-lines" class="w-3 h-3 text-slate-400" /> Salary Slips:
                                    </span>
                                    <span class="meta-val">
                                        {{ $tc->salarySlips->count() }} Uploaded
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer-action">
                            <a href="{{ route($routePrefix . 'teacher', $tc->id) }}" class="view-profile-link faculty">
                                <span>Profile &amp; Slips</span>
                                <x-icon name="arrow-right" class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
@endsection

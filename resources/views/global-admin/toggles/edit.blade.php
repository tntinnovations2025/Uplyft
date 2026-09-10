@extends('global-admin.layouts.app')
@section('breadcrumb', 'Modular Feature Switchboard — ' . $institute->name)
@section('title', 'SaaS Feature Switchboard — ' . $institute->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

<style>
    /* ── Classy Switchboard Styling (Bright Daylight Theme) ───────────────────────────── */
    .switchboard-hero {
        position: relative;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 28px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .hero-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
    }

    .institute-identity {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .inst-avatar-box {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 800;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(225, 48, 108, 0.35);
        flex-shrink: 0;
    }

    .inst-details h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .live-beacon {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 9999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #059669;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .live-beacon-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
        animation: pulseBeacon 2s infinite;
    }
    @keyframes pulseBeacon {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }

    /* ── Progress Meter Box ──────────────────────────────────── */
    .meter-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 20px;
        min-width: 240px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .meter-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
    }
    .meter-count {
        font-size: 18px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        color: #0f172a;
    }
    .meter-count span {
        color: #e1306c;
    }
    .meter-bar-track {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 9999px;
        overflow: hidden;
    }
    .meter-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #fd1d1d, #e1306c, #833ab4);
        border-radius: 9999px;
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Presets & Filter Toolbar ────────────────────────────── */
    .switchboard-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 280px;
        max-width: 440px;
    }
    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 14px;
    }
    .search-input {
        width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px 12px 42px;
        color: #0f172a;
        font-size: 13px;
        outline: none;
        transition: all 0.25s;
    }
    .search-input:focus {
        border-color: #e1306c;
        box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.15);
    }

    .preset-pill-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .preset-btn {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 14px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .preset-btn:hover {
        background: #fdf2f8;
        color: #be185d;
        border-color: #fbcfe8;
        transform: translateY(-1px);
    }
    .preset-btn.active-preset {
        background: linear-gradient(135deg, rgba(225, 48, 108, 0.12), rgba(131, 58, 180, 0.08));
        border-color: #e1306c;
        color: #be185d;
    }

    /* ── Category Sections ───────────────────────────────────── */
    .category-section {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 18px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    .category-section:hover {
        border-color: rgba(225, 48, 108, 0.3);
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .category-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .category-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fdf2f8;
        color: #be185d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        border: 1px solid #fbcfe8;
    }
    .category-title-wrap h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.2px;
    }
    .category-count-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
    }

    .category-actions {
        display: flex;
        gap: 6px;
    }
    .cat-btn {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 12px;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cat-btn:hover {
        background: #ffffff;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* ── Feature Cards Grid ──────────────────────────────────── */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }

    .feature-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        position: relative;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .feature-card.is-active {
        background: #fdf2f8;
        border-color: #fbcfe8;
        box-shadow: 0 4px 14px rgba(225, 48, 108, 0.08);
    }
    .feature-card:hover {
        transform: translateY(-2px);
        border-color: #e1306c;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .card-icon-title {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .feat-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
        transition: all 0.25s;
    }
    .feature-card.is-active .feat-icon-box {
        background: #fce7f3;
        color: #be185d;
        border-color: #fbcfe8;
    }

    .feat-meta-title h3 {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .feat-badge {
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: 0.6px;
        padding: 2px 6px;
        border-radius: 5px;
        background: #f1f5f9;
        color: #64748b;
        display: inline-block;
    }
    .feature-card.is-active .feat-badge {
        background: #fce7f3;
        color: #be185d;
    }

    .card-desc {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.45;
        font-weight: 500;
    }

    .card-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
    .card-key-tag {
        font-size: 10.5px;
        font-family: monospace;
        color: #64748b;
    }
    .card-key-tag code {
        background: #f1f5f9;
        padding: 2px 5px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    .status-indicator {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .status-indicator.active {
        color: #059669;
    }
    .status-indicator.inactive {
        color: #94a3b8;
    }

    /* ── Classy iOS Neon Toggle Switch ───────────────────────── */
    .ios-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .ios-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .ios-slider {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        border: 1px solid #cbd5e1;
        border-radius: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .ios-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background-color: #ffffff;
        border-radius: 50%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    }
    .ios-switch input:checked + .ios-slider {
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        border-color: transparent;
        box-shadow: 0 0 10px rgba(225, 48, 108, 0.3);
    }
    .ios-switch input:checked + .ios-slider:before {
        transform: translateX(20px);
        background-color: #ffffff;
    }

    /* ── Floating Sticky Action Bar ────── */
    .floating-action-bar {
        position: fixed;
        bottom: 24px;
        left: 300px;
        right: 36px;
        max-width: 1200px;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;
        padding: 14px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        z-index: 999;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @media (max-width: 1024px) {
        .floating-action-bar {
            left: 20px;
            right: 20px;
            bottom: 16px;
            padding: 12px 18px;
        }
    }
    .floating-left {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }
    .change-badge {
        padding: 4px 10px;
        border-radius: 6px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #d97706;
        font-size: 12px;
        font-weight: 700;
        display: none;
    }
    .change-badge.show {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .save-deploy-btn {
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        border: none;
        color: #ffffff;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.3px;
        padding: 10px 22px;
        border-radius: 10px;
        cursor: pointer;
        box-shadow: 0 4px 18px rgba(225, 48, 108, 0.35);
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .save-deploy-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(225, 48, 108, 0.45);
    }
    .save-deploy-btn:active {
        transform: translateY(0);
    }

    /* ── Live Toast Feedback ─────────────────────────────────── */
    .switch-toast {
        position: fixed;
        top: 24px;
        right: 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateY(-15px);
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .switch-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<!-- HERO HEADER -->
<div class="switchboard-hero">
    <div class="hero-top-row">
        <div class="institute-identity">
            <div class="inst-avatar-box">
                {{ substr($institute->name, 0, 1) }}
            </div>
            <div class="inst-details">
                <h1>
                    <span>{{ $institute->name }}</span>
                    <span class="live-beacon">
                        <span class="live-beacon-dot"></span>
                        Live Multi-Tenant Sync
                    </span>
                </h1>
                <div style="color:var(--text-muted);font-size:13px;margin-top:4px;display:flex;align-items:center;gap:12px">
                    <span>
                        <i class="fa-solid fa-layer-group mr-1" style="color:#0284c7"></i>
                        <strong style="color:#0f172a">{{ ucfirst($institute->subscription_tier) }} Tier</strong>
                    </span>
                    <span>•</span>
                    <span>
                        <i class="fa-solid fa-location-dot mr-1" style="color:#64748b"></i>
                        {{ $institute->city ?? 'Main Campus' }}
                    </span>
                    <span>•</span>
                    <a href="{{ route('global-admin.institutes.show', $institute) }}" style="color:#0284c7;text-decoration:none;font-weight:700">
                        View Institute Dossier →
                    </a>
                </div>
            </div>
        </div>

        <!-- METER CARD -->
        @php
            $activeCount = collect($featureKeys)->filter(fn($k) => $toggles->$k ?? false)->count();
            $totalCount = count($featureKeys);
            $pct = round(($activeCount / max(1, $totalCount)) * 100);
        @endphp
        <div class="meter-card">
            <div class="meter-header">
                <span>Active Modules</span>
                <span id="meter-pct" style="font-weight:700;color:var(--accent-cyan)">{{ $pct }}%</span>
            </div>
            <div class="meter-count">
                <span id="active-count-display">{{ $activeCount }}</span> / {{ $totalCount }}
            </div>
            <div class="meter-bar-track">
                <div class="meter-bar-fill" id="meter-fill" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER & PRESETS TOOLBAR -->
<div class="switchboard-toolbar">
    <!-- Live Search -->
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="feature-search" class="search-input" placeholder="Search 25 SaaS modules by name, feature key, or category..." onkeyup="filterFeatures()">
    </div>

    <!-- Quick Presets -->
    <div class="preset-pill-group">
        <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px">Presets:</span>
        <button type="button" class="preset-btn" onclick="applyPresetTier('enterprise')">
            <i class="fa-solid fa-rocket text-cyan-400"></i> Full Enterprise (25/25)
        </button>
        <button type="button" class="preset-btn" onclick="applyPresetTier('standard')">
            <i class="fa-solid fa-star text-amber-400"></i> Standard Growth
        </button>
        <button type="button" class="preset-btn" onclick="applyPresetTier('basic')">
            <i class="fa-solid fa-shield text-indigo-400"></i> Starter Basic
        </button>
        <button type="button" class="preset-btn" onclick="applyPresetTier('all_off')">
            <i class="fa-solid fa-lock text-rose-400"></i> Lock All
        </button>
    </div>
</div>

<!-- MASTER FORM -->
<form method="POST" action="{{ route('global-admin.institutes.toggles.update', $institute) }}" id="feature-toggle-form">
    @csrf
    @method('PUT')

    @php
        $categories = [
            'governance' => [
                'title' => 'Executive Portals & Master Governance',
                'icon' => 'fa-solid fa-crown',
                'keys' => ['principal_portal', 'teacher_portal', 'parent_portal', 'staff_governance', 'security_management']
            ],
            'admissions' => [
                'title' => 'Admissions, Enrollment & Institutional Directory',
                'icon' => 'fa-solid fa-user-graduate',
                'keys' => ['registration_portals', 'master_directory']
            ],
            'financials' => [
                'title' => 'Billing, Fee Invoicing & Financial Accounts',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'keys' => ['fee_invoicing', 'financial_accounts', 'scholarships']
            ],
            'academics' => [
                'title' => 'Academics, Class Scheduling & Attendance Matrix',
                'icon' => 'fa-solid fa-school',
                'keys' => ['classes_sections', 'subjects_catalog', 'teacher_allocations', 'faculty_hours', 'rooms_facilities', 'attendance_system', 'timetable']
            ],
            'ai_lms' => [
                'title' => 'AI Tutor Suite, LMS & Cloud Communication',
                'icon' => 'fa-solid fa-brain',
                'keys' => ['ai_bot', 'practice_tests', 'lms_content', 'assessment_engine', 'datesheet_manager', 'exam_reports', 'grading_normalizer', 'sms_notifications']
            ],
        ];
    @endphp

    @foreach($categories as $catKey => $cat)
        <div class="category-section" data-cat="{{ $catKey }}" id="sec-{{ $catKey }}">
            <div class="category-header">
                <div class="category-title-wrap">
                    <div class="category-icon-box">
                        <i class="{{ $cat['icon'] }}"></i>
                    </div>
                    <div>
                        <h2>{{ $cat['title'] }}</h2>
                    </div>
                    <span class="category-count-badge cat-counter" data-cat="{{ $catKey }}">
                        {{ count($cat['keys']) }} Modules
                    </span>
                </div>

                <div class="category-actions">
                    <button type="button" class="cat-btn" onclick="toggleCategoryGroup('{{ $catKey }}', true)">
                        <i class="fa-solid fa-check mr-1"></i> Enable All
                    </button>
                    <button type="button" class="cat-btn" onclick="toggleCategoryGroup('{{ $catKey }}', false)">
                        <i class="fa-solid fa-xmark mr-1"></i> Disable All
                    </button>
                </div>
            </div>

            <div class="features-grid">
                @foreach($cat['keys'] as $key)
                    @php
                        $meta = $featureMetadata[$key] ?? [
                            'icon' => 'fa-solid fa-cube',
                            'badge' => 'MODULE',
                            'desc' => 'Configure modular rights for this feature.',
                        ];
                        $label = $featureLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                        $isActive = old($key, $toggles->$key ?? false);
                    @endphp
                    <div class="feature-card {{ $isActive ? 'is-active' : '' }}" data-key="{{ $key }}" data-search-target="{{ strtolower($label . ' ' . $key . ' ' . $meta['desc'] . ' ' . $meta['badge']) }}">
                        <div class="card-top">
                            <div class="card-icon-title">
                                <div class="feat-icon-box">
                                    <i class="{{ $meta['icon'] }}"></i>
                                </div>
                                <div class="feat-meta-title">
                                    <h3>{{ $label }}</h3>
                                    <span class="feat-badge">{{ $meta['badge'] }}</span>
                                </div>
                            </div>

                            <!-- iOS Switch -->
                            <label class="ios-switch">
                                <input type="checkbox" 
                                       name="{{ $key }}" 
                                       id="toggle-{{ $key }}" 
                                       class="feature-checkbox" 
                                       data-key="{{ $key }}" 
                                       value="1" 
                                       {{ $isActive ? 'checked' : '' }}
                                       onchange="handleToggleChange('{{ $key }}', this)">
                                <span class="ios-slider"></span>
                            </label>
                        </div>

                        <div class="card-desc">
                            {{ $meta['desc'] }}
                        </div>

                        <div class="card-bottom">
                            <div class="card-key-tag">
                                <code>{{ $key }}</code>
                            </div>
                            <div class="status-indicator {{ $isActive ? 'active' : 'inactive' }}" id="status-{{ $key }}">
                                <i class="fa-solid {{ $isActive ? 'fa-circle-check' : 'fa-circle-minus' }}"></i>
                                <span>{{ $isActive ? 'Active' : 'Disabled' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="height: 90px;"><!-- spacer for floating bar --></div>

    <!-- FLOATING STICKY ACTION BAR -->
    <div class="floating-action-bar">
        <div class="floating-left">
            <div style="font-size:13px;color:var(--text);font-weight:600">
                <span id="float-active-count" style="color:var(--accent-cyan);font-weight:800">{{ $activeCount }}</span> of {{ $totalCount }} Features Enabled
            </div>
            <span class="change-badge" id="unsaved-badge">
                <i class="fa-solid fa-circle-exclamation"></i> Unsaved Changes
            </span>
        </div>

        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route('global-admin.institutes.show', $institute) }}" class="cat-btn" style="padding:10px 18px;font-size:13px;border-radius:10px">
                Discard / Back
            </a>
            <button type="submit" class="save-deploy-btn" id="save-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i> Save & Deploy Switchboard
            </button>
        </div>
    </div>
</form>

<!-- TOAST NOTIFICATION -->
<div class="switch-toast" id="switch-toast">
    <i class="fa-solid fa-circle-check text-cyan-400" id="toast-icon"></i>
    <span id="toast-msg">Feature updated successfully.</span>
</div>

<script>
    const INSTITUTE_ID = {{ $institute->id }};
    const TOTAL_KEYS = {{ $totalCount }};
    let hasUnsavedChanges = false;

    // Handle individual toggle change
    async function handleToggleChange(key, inputElem) {
        const isChecked = inputElem.checked;
        const card = inputElem.closest('.feature-card');
        const statusInd = document.getElementById(`status-${key}`);

        // Update card visual state immediately
        if (isChecked) {
            card.classList.add('is-active');
            statusInd.className = 'status-indicator active';
            statusInd.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Active</span>';
        } else {
            card.classList.remove('is-active');
            statusInd.className = 'status-indicator inactive';
            statusInd.innerHTML = '<i class="fa-solid fa-circle-minus"></i> <span>Disabled</span>';
        }

        recalculateStats();
        markUnsavedChanges();

        // Send instant background AJAX sync
        try {
            const formData = new FormData();
            formData.append('feature_key', key);
            formData.append('state', isChecked ? '1' : '0');

            const res = await fetch(`{{ route('global-admin.institutes.toggles.update', $institute) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message || `${isChecked ? 'Enabled' : 'Disabled'} ${key}`, 'success');
            }
        } catch (e) {
            console.log('Form fallback will save on final submit.');
        }
    }

    // Toggle entire category
    function toggleCategoryGroup(catKey, state) {
        const section = document.getElementById(`sec-${catKey}`);
        if (!section) return;
        const checkboxes = section.querySelectorAll('.feature-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked !== state) {
                cb.checked = state;
                const key = cb.dataset.key;
                const card = cb.closest('.feature-card');
                const statusInd = document.getElementById(`status-${key}`);
                if (state) {
                    card.classList.add('is-active');
                    if (statusInd) statusInd.className = 'status-indicator active';
                    if (statusInd) statusInd.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Active</span>';
                } else {
                    card.classList.remove('is-active');
                    if (statusInd) statusInd.className = 'status-indicator inactive';
                    if (statusInd) statusInd.innerHTML = '<i class="fa-solid fa-circle-minus"></i> <span>Disabled</span>';
                }
            }
        });
        recalculateStats();
        markUnsavedChanges();
        showToast(`${state ? 'Enabled' : 'Disabled'} all modules in section`, 'info');
    }

    // Quick presets
    async function applyPresetTier(tier) {
        try {
            const formData = new FormData();
            formData.append('tier', tier);

            const res = await fetch(`{{ route('global-admin.institutes.toggles.apply-tier', $institute) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.toggles) {
                // Apply toggles to DOM
                for (const [k, val] of Object.entries(data.toggles)) {
                    const cb = document.getElementById(`toggle-${k}`);
                    if (cb) {
                        cb.checked = !!val;
                        const card = cb.closest('.feature-card');
                        const statusInd = document.getElementById(`status-${k}`);
                        if (val) {
                            card.classList.add('is-active');
                            if (statusInd) statusInd.className = 'status-indicator active';
                            if (statusInd) statusInd.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Active</span>';
                        } else {
                            card.classList.remove('is-active');
                            if (statusInd) statusInd.className = 'status-indicator inactive';
                            if (statusInd) statusInd.innerHTML = '<i class="fa-solid fa-circle-minus"></i> <span>Disabled</span>';
                        }
                    }
                }
                recalculateStats();
                showToast(data.message, 'success');
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Recalculate meter and counters
    function recalculateStats() {
        const allChecked = document.querySelectorAll('.feature-checkbox:checked').length;
        const pct = Math.round((allChecked / TOTAL_KEYS) * 100);

        document.getElementById('active-count-display').innerText = allChecked;
        document.getElementById('float-active-count').innerText = allChecked;
        document.getElementById('meter-pct').innerText = `${pct}%`;
        document.getElementById('meter-fill').style.width = `${pct}%`;
    }

    function markUnsavedChanges() {
        hasUnsavedChanges = true;
        const badge = document.getElementById('unsaved-badge');
        if (badge) badge.classList.add('show');
    }

    // Instant Filter / Search
    function filterFeatures() {
        const query = document.getElementById('feature-search').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.feature-card');

        cards.forEach(card => {
            const target = card.dataset.searchTarget || '';
            if (query === '' || target.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide empty categories
        document.querySelectorAll('.category-section').forEach(sec => {
            const visibleCards = sec.querySelectorAll('.feature-card:not([style*="display: none"])');
            if (visibleCards.length === 0) {
                sec.style.display = 'none';
            } else {
                sec.style.display = 'block';
            }
        });
    }

    // Toast notification
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('switch-toast');
        const icon = document.getElementById('toast-icon');
        document.getElementById('toast-msg').innerText = msg;

        if (type === 'error') {
            toast.style.borderColor = '#f43f5e';
            icon.className = 'fa-solid fa-circle-xmark text-rose-400';
        } else {
            toast.style.borderColor = '#06b6d4';
            icon.className = 'fa-solid fa-circle-check text-cyan-400';
        }

        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 2600);
    }
</script>
@endsection

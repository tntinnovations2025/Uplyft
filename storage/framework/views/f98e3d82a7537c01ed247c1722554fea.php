<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('title', 'Global Admin Hub'); ?> — UPLYFT Governance</title>
    <meta name="description" content="UPLYFT Global Multi-Tenant Administration Command Center" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* ── Ink & Amber Design System ─────────────────────────── */
            --ink:              #0E0E11;
            --ink-surface:      #1C1C20;
            --ink-hover:        #252528;

            --page-bg:          #EFEEEA;
            --card-surface:     #F9F8F5;
            --border:           #E1DFD7;

            --amber:            #D48A2E;
            --amber-hover:      #C07A22;
            --amber-on:         #1A1200;
            --amber-tint-bg:    #F8E9D3;
            --amber-tint-text:  #8A5A10;

            --text-primary:     #1B1A17;
            --text-secondary:   #68665D;
            --text-faint:       #A19E92;

            --sidebar-text:     #C7C6CB;
            --sidebar-strong:   #F5F4F1;
            --sidebar-faint:    #6B6A6E;

            --success-bg:       #E3EFE2;
            --success-text:     #2E6E42;
            --warning-text:     #8F5F10;
            --danger-bg:        #F6E4E1;
            --danger-text:      #A2412C;
            --tertiary-bg:      #E7ECF6;
            --tertiary-text:    #3A529C;

            --radius-card:      14px;
            --radius-btn:       10px;
            --radius-pill:      9999px;
            --radius-chip:      8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Ink Sidebar ───────────────────────────────────────────────────── */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: var(--ink);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            overscroll-behavior: contain;
            box-shadow: 1px 0 2px rgba(0,0,0,0.3);
        }

        .sidebar-logo {
            padding: 22px 20px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .logo-icon {
            width: 38px; height: 38px;
            background: var(--amber);
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            color: var(--amber-on);
            flex-shrink: 0;
            box-shadow: 0 0 14px rgba(212,138,46,0.35);
        }

        .logo-text {
            font-family: 'Manrope', sans-serif;
            font-size: 17px; font-weight: 800;
            letter-spacing: -0.3px;
            color: var(--sidebar-strong);
        }
        .logo-sub {
            font-size: 9.5px;
            color: var(--sidebar-faint);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar-nav {
            flex: 1;
            padding: 10px 14px;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #FFFFFF rgba(255, 255, 255, 0.1);
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 9999px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #FFFFFF !important;
            border-radius: 9999px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #E2E8F0 !important;
        }

        .nav-section-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--sidebar-faint);
            padding: 22px 12px 10px;
            font-weight: 700;
        }

        .nav-section-label:first-child { padding-top: 4px; }

        .nav-link {
            display: flex; align-items: center; gap: 13px;
            padding: 9px 10px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--sidebar-text);
            font-size: 14.5px; font-weight: 500;
            background: transparent;
            border: 1px solid transparent;
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .nav-link:hover {
            background: var(--ink-hover);
            color: var(--sidebar-strong);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(212, 138, 46, 0.16) 0%, rgba(212, 138, 46, 0.05) 100%) !important;
            border: 1px solid rgba(212, 138, 46, 0.55) !important;
            box-shadow: 0 0 0 1px rgba(212, 138, 46, 0.12), 0 0 24px rgba(212, 138, 46, 0.35), inset 0 0 14px rgba(212, 138, 46, 0.55) !important;
            color: #F0B45D !important;
            font-weight: 600;
        }

        .nav-link.active::before,
        .nav-link.active::after {
            display: none !important;
            content: none !important;
        }

        .nav-link .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px; height: 38px;
            border-radius: 11px;
            font-size: 15px;
            background: var(--ink-surface);
            color: #9C9BA1;
            flex-shrink: 0;
            transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .nav-link:hover .icon {
            background: var(--ink-hover);
            color: var(--sidebar-text);
        }

        .nav-link .icon svg {
            width: 19px; height: 19px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.75;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
            transition: stroke 0.18s ease;
        }

        .nav-link.active .icon {
            background: #D48A2E !important;
            color: #1A1200 !important;
            box-shadow: 0 0 14px rgba(212, 138, 46, 0.55) !important;
            border-color: transparent !important;
        }

        .nav-link.active .icon svg {
            stroke: #1A1200 !important;
        }

        .nav-link.active .icon i,
        .nav-link.active i {
            color: #1A1200 !important;
            background: transparent !important;
        }

        /* ── Categorized Drill-Down Submenu Navigation ── */
        .sidebar-hub-card-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            background: #17191C;
            border: 1.5px solid #2A2C30;
            cursor: pointer;
            text-align: left;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .sidebar-hub-card-btn:hover {
            background: #2A2C30;
            border-color: #3A3D42;
            transform: translateY(-1px) translateX(2px);
        }
        .sidebar-hub-card-btn.active {
            background: rgba(212, 138, 46, 0.16) !important;
            border-color: rgba(212, 138, 46, 0.55) !important;
            box-shadow: 0 0 0 1px rgba(212, 138, 46, 0.12), 0 0 20px rgba(212, 138, 46, 0.25) !important;
        }
        .hub-btn-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .hub-btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #2A2C30;
            border: 1px solid #3A3D42;
            color: #A19E92;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .hub-btn-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .hub-btn-title {
            font-size: 12.5px;
            font-weight: 800;
            color: #F9F8F5 !important;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-family: 'Outfit', sans-serif;
            transition: color 0.18s ease;
        }
        .sidebar-hub-card-btn:hover .hub-btn-title {
            color: #FFFFFF !important;
        }
        .sidebar-hub-card-btn.active .hub-btn-title {
            color: #F0B45D !important;
        }
        .hub-btn-desc {
            font-size: 9.5px;
            font-weight: 600;
            color: #8A877E !important;
            margin-top: 2px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-hub-card-btn:hover .hub-btn-desc {
            color: #A19E92 !important;
        }
        .sidebar-hub-card-btn.active .hub-btn-desc {
            color: rgba(240, 180, 93, 0.8) !important;
        }
        .hub-btn-arrow {
            font-size: 11px;
            color: #8A877E;
            transition: transform 0.2s ease, color 0.2s ease;
            flex-shrink: 0;
        }
        .sidebar-hub-card-btn:hover .hub-btn-arrow {
            color: #D48A2E;
            transform: translateX(2px);
        }
        .sidebar-hub-card-btn.active .hub-btn-arrow {
            color: #F0B45D !important;
        }
        .subnav-back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 8px 12px;
            border-radius: 10px;
            background: #17191C;
            border: 1px solid #2A2C30;
            color: #F0B45D !important;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            margin-bottom: 8px;
        }
        .subnav-back-btn:hover {
            background: #24272C;
            border-color: #D48A2E;
            color: #FFFFFF !important;
            transform: translateX(-2px);
        }
        .subnav-back-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: rgba(212, 138, 46, 0.15);
            border: 1px solid rgba(212, 138, 46, 0.35);
            color: #F0B45D;
            flex-shrink: 0;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .subnav-back-btn:hover .subnav-back-icon {
            background: #D48A2E;
            border-color: #C07A22;
            color: #1A1200;
            transform: translateX(-3px);
            box-shadow: 0 2px 10px rgba(212, 138, 46, 0.3);
        }
        .subnav-back-btn:hover .subnav-back-icon svg {
            stroke: #1A1200 !important;
        }
        .subnav-header {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #2A2C30;
        }
        .subnav-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 8px;
            border-radius: 7px;
            background: #17191C;
            border: 1px solid #2A2C30;
            width: 100%;
        }
        .sidebar-subnav-panel {
            animation: fadeInSubnav 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeInSubnav {
            from { opacity: 0; transform: translateX(6px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 10.5px;
            color: var(--sidebar-faint);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-badge {
            background: rgba(212,138,46,0.14);
            color: #F0B45D;
            border: 1px solid rgba(212,138,46,0.28);
            padding: 2px 7px;
            border-radius: var(--radius-pill);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ── Main Layout & Topbar ──────────────────────────────────────────── */
        .main {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 250px);
        }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 28px;
            border-bottom: 1px solid var(--border);
            background: var(--card-surface);
            position: sticky; top: 0; z-index: 90;
        }

        .breadcrumb { font-size: 13px; color: var(--text-faint); font-weight: 500; }
        .breadcrumb span { color: var(--text-primary); font-weight: 700; }

        .topbar-actions { display: flex; align-items: center; gap: 14px; }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--success-bg);
            color: var(--success-text);
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .admin-badge {
            background: var(--ink);
            color: var(--sidebar-strong);
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.3px;
        }

        .content { padding: 32px; flex: 1; }

        /* ── Cards ──────────────────────────────────────────────────────────── */
        .card, .glass-card {
            background: var(--card-surface);
            border: none;
            border-radius: var(--radius-card);
            padding: 24px;
            margin-bottom: 22px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-family: 'Manrope', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--text-primary);
        }

        /* ── Buttons ───────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-btn);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: background 0.15s ease, box-shadow 0.15s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--amber);
            color: var(--amber-on) !important;
        }
        .btn-primary:hover {
            background: var(--amber-hover);
        }
        .btn-primary:active { transform: scale(0.98); }

        .btn-secondary, .btn-ghost {
            background: var(--card-surface);
            color: var(--text-primary) !important;
            border: 1px solid var(--border);
        }
        .btn-secondary:hover, .btn-ghost:hover {
            background: var(--page-bg);
            border-color: #CBC8BE;
        }

        .btn-danger {
            background: var(--danger-bg);
            color: var(--danger-text) !important;
        }
        .btn-danger:hover { background: #EEDAD6; }

        .btn-success {
            background: var(--success-bg);
            color: var(--success-text) !important;
        }
        .btn-success:hover { background: #D6E8D5; }

        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: var(--radius-pill);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge-green, .badge-success  { background: var(--success-bg); color: var(--success-text); }
        .badge-red, .badge-danger     { background: var(--danger-bg); color: var(--danger-text); }
        .badge-rose                   { background: var(--danger-bg); color: var(--danger-text); }
        .badge-purple                 { background: var(--amber-tint-bg); color: var(--amber-tint-text); }
        .badge-cyan, .badge-sky       { background: var(--tertiary-bg); color: var(--tertiary-text); }
        .badge-yellow, .badge-amber   { background: var(--amber-tint-bg); color: var(--amber-tint-text); }
        .badge-indigo, .badge-primary { background: var(--tertiary-bg); color: var(--tertiary-text); }

        /* ── Tables ────────────────────────────────────────────────────────── */
        table, .data-table { width: 100%; border-collapse: collapse; text-align: left; }

        th {
            font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.3px;
            color: var(--text-faint); padding: 12px 16px;
            border-bottom: 1px solid var(--border); font-weight: 600;
            background: transparent;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px; color: var(--text-primary);
            font-weight: 500;
        }

        tr:hover td { background: rgba(0,0,0,0.015); }

        /* ── Forms ─────────────────────────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }

        label {
            display: block; font-size: 10.5px; font-weight: 600;
            margin-bottom: 7px; color: var(--text-faint);
            text-transform: uppercase; letter-spacing: 0.3px;
        }

        input, select, textarea {
            width: 100%; padding: 11px 14px;
            background: var(--card-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-btn);
            color: var(--text-primary); font-size: 13px;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--amber);
            box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.12);
        }

        /* ── Alerts ────────────────────────────────────────────────────────── */
        .alert {
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; justify-content: space-between;
        }
        .alert-success { background: var(--success-bg); color: var(--success-text); }
        .alert-error   { background: var(--danger-bg);  color: var(--danger-text); }

        /* ── Confirmation Modal ────────────────────────────────────────────── */
        .apple-liquid-glass-overlay {
            position: fixed; inset: 0; z-index: 99999;
            background: rgba(23, 25, 28, 0.50);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
        }

        .apple-liquid-glass-card {
            background: var(--card-surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.18);
            animation: modalPop 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative; overflow: hidden;
        }

        @keyframes modalPop {
            0%   { transform: scale(0.96) translateY(10px); opacity: 0; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }

        .apple-liquid-input {
            background: var(--card-surface) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-btn) !important;
            padding: 12px 16px !important;
            font-size: 13px !important;
            color: var(--text-primary) !important;
        }
        .apple-liquid-input:focus {
            border-color: var(--amber) !important;
            box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.12) !important;
        }

        .apple-liquid-btn-cancel {
            background: var(--page-bg);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            border-radius: var(--radius-btn);
            font-weight: 600; padding: 10px 20px;
            cursor: pointer; transition: background 0.15s ease;
        }
        .apple-liquid-btn-cancel:hover { background: #E5E4E0; color: var(--text-primary); }

        .apple-liquid-btn-primary {
            background: var(--amber);
            color: var(--amber-on);
            border: none;
            border-radius: var(--radius-btn);
            font-weight: 700; padding: 10px 24px;
            cursor: pointer; transition: background 0.15s ease;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .apple-liquid-btn-primary:hover { background: var(--amber-hover); }

        .apple-liquid-close-btn {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--page-bg);
            border: 1px solid var(--border);
            color: var(--text-faint);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 13px; font-weight: 700;
            transition: background 0.15s ease;
        }
        .apple-liquid-close-btn:hover { background: #E5E4E0; color: var(--text-primary); }

        /* ── Utility stat classes (used by non-dashboard pages) ─────────── */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }

        .stat-card {
            background: var(--card-surface);
            border: none;
            border-radius: var(--radius-card);
            padding: 22px;
        }

        .stat-label { font-size: 10.5px; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

    
    <?php echo $__env->make('partials.institute-loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Sidebar Nav -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">U</div>
        <div>
            <div class="logo-text">UPLYFT</div>
            <div class="logo-sub">Platform Admin</div>
        </div>
    </div>

    <?php
        $adminSubGroup = null;
        if (request()->routeIs('global-admin.organizations.*') || request()->routeIs('global-admin.institutes.*')) {
            $adminSubGroup = 'organizations';
        } elseif (request()->routeIs('global-admin.accounts.*') || request()->routeIs('global-admin.password-resets.*')) {
            $adminSubGroup = 'access';
        }
    ?>

    <nav class="sidebar-nav custom-scrollbar" style="padding: 10px 10px 20px;">
        
        <div id="admin-main-nav" class="space-y-1.5" style="display: <?php echo e($adminSubGroup ? 'none' : 'block'); ?>;">
            <a href="<?php echo e(route('global-admin.dashboard')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.dashboard') ? 'active' : ''); ?>" style="margin-bottom: 8px;">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chart-pie']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chart-pie']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Platform Overview
            </a>

            <div style="padding: 4px 6px 2px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 10px; font-weight: 800; color: #8A877E; text-transform: uppercase; letter-spacing: 0.8px;">
                    GLOBAL HUBS
                </span>
            </div>

            <!-- Hub 1: Organizations & Campuses -->
            <button type="button" onclick="showAdminSubNav('organizations')" class="sidebar-hub-card-btn <?php echo e($adminSubGroup === 'organizations' ? 'active' : ''); ?>">
                <div class="hub-btn-left">
                    <span class="hub-btn-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sitemap']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sitemap']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <div class="hub-btn-text">
                        <span class="hub-btn-title">Organizations &amp; Campuses</span>
                        <span class="hub-btn-desc">Networks, Institutes, Setup</span>
                    </div>
                </div>
                <span class="hub-btn-arrow"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chevron-right','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
            </button>

            <!-- Hub 2: Access & Security -->
            <button type="button" onclick="showAdminSubNav('access')" class="sidebar-hub-card-btn <?php echo e($adminSubGroup === 'access' ? 'active' : ''); ?>">
                <div class="hub-btn-left">
                    <span class="hub-btn-icon" style="background:rgba(234,179,8,0.15);color:#eab308;"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'user-shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-shield']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <div class="hub-btn-text">
                        <span class="hub-btn-title">Access &amp; Security</span>
                        <span class="hub-btn-desc">Principal Accounts, Resets</span>
                    </div>
                </div>
                <span class="hub-btn-arrow"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'chevron-right','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
            </button>

            <!-- Settings -->
            <a href="<?php echo e(route('profile.edit')); ?>" class="nav-link <?php echo e(request()->routeIs('profile.edit') ? 'active' : ''); ?>" style="margin-top: 8px;">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'user-gear']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-gear']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Settings
            </a>

            <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin-top: 4px;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link" style="cursor:pointer;width:100%;text-align:left;">
                    <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right-from-bracket']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-from-bracket']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Sign Out
                </button>
            </form>
        </div>

        
        <div id="subnav-organizations" class="sidebar-subnav-panel space-y-1" style="display: <?php echo e($adminSubGroup === 'organizations' ? 'block' : 'none'); ?>;">
            <div class="subnav-header">
                <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                    <span class="subnav-back-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-left','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-left','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <span>Back</span>
                </button>
                <div class="subnav-badge" style="color:#60a5fa;">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sitemap','class' => 'w-3.5 h-3.5 text-[#60a5fa]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sitemap','class' => 'w-3.5 h-3.5 text-[#60a5fa]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    <span>Organizations &amp; Campuses</span>
                </div>
            </div>

            <a href="<?php echo e(route('global-admin.organizations.index')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.organizations.index') || request()->routeIs('global-admin.organizations.show') || request()->routeIs('global-admin.organizations.edit') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sitemap']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sitemap']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Registered Organizations
            </a>
            <a href="<?php echo e(route('global-admin.organizations.create')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.organizations.create') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'square-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'square-plus']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Register Organization
            </a>
            <a href="<?php echo e(route('global-admin.institutes.index')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.institutes.index') || request()->routeIs('global-admin.institutes.show') || request()->routeIs('global-admin.institutes.edit') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'building-columns']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'building-columns']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Registered Institutes
            </a>
            <a href="<?php echo e(route('global-admin.institutes.create')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.institutes.create') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Register Institute
            </a>
        </div>

        
        <div id="subnav-access" class="sidebar-subnav-panel space-y-1" style="display: <?php echo e($adminSubGroup === 'access' ? 'block' : 'none'); ?>;">
            <div class="subnav-header">
                <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                    <span class="subnav-back-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-left','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-left','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
                    <span>Back</span>
                </button>
                <div class="subnav-badge" style="color:#eab308;">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'user-shield','class' => 'w-3.5 h-3.5 text-[#eab308]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-shield','class' => 'w-3.5 h-3.5 text-[#eab308]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    <span>Access Control &amp; Security</span>
                </div>
            </div>

            <a href="<?php echo e(route('global-admin.accounts.principals.index')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.accounts.principals.*') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'user-shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user-shield']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Principal Accounts
            </a>
            <a href="<?php echo e(route('global-admin.password-resets.index')); ?>"
               class="nav-link <?php echo e(request()->routeIs('global-admin.password-resets.*') ? 'active' : ''); ?>">
                <span class="icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'key']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'key']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span> Password Resets
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <span>UPLYFT v2.4</span>
    </div>
</aside>

<!-- Main Area -->
<main class="main">
    <header class="topbar">
        <div class="breadcrumb">
            <span style="color:var(--amber);font-weight:700">GOVERNANCE</span> &#8250; <span><?php echo $__env->yieldContent('breadcrumb', 'Overview'); ?></span>
        </div>
        <div class="topbar-actions">
            <span class="status-pill">
                <span style="width:6px;height:6px;background:var(--success-text);border-radius:50%;display:inline-block"></span>
                ISOLATION ACTIVE
            </span>
            <span class="admin-badge">SUPER ADMIN</span>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)"><?php echo e(auth()->user()->name ?? 'Global Admin'); ?></div>
        </div>
    </header>

    <div class="content">
        <?php echo $__env->make('partials.alert-notifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</main>




<div id="globalClassyConfirmModal" class="apple-liquid-glass-overlay" style="display:none;z-index:999999;">
    <div class="apple-liquid-glass-card" style="max-width:480px;width:92%;padding:28px 30px;text-align:left">
        <div id="globalClassyTopBorder" style="position:absolute;top:0;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#A2412C,transparent)"></div>

        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px">
            <div id="globalClassyIconBox" style="width:48px;height:48px;border-radius:14px;background:#F6E4E1;color:#A2412C;border:1px solid #E8C8C2;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 id="globalClassyTitle" style="font-family:'Manrope',sans-serif;font-size:19px;font-weight:800;color:#1B1A17;margin:0;letter-spacing:-0.3px">Confirm Action</h3>
                <p id="globalClassySubtitle" style="font-size:12.5px;color:#68665D;margin-top:3px;font-weight:500">Please review before proceeding.</p>
            </div>
        </div>

        <div id="globalClassyMessageBox" style="background:rgba(246,228,225,0.5);border:1px solid #E8C8C2;border-radius:14px;padding:16px;margin-bottom:24px">
            <div id="globalClassyMessage" style="font-size:13px;color:#A2412C;line-height:1.55;font-weight:600"></div>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
            <button type="button" id="globalClassyCancelBtn" onclick="closeClassyConfirm(false)" style="padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;background:#EFEEEA;border:1px solid #E1DFD7;color:#68665D;cursor:pointer">
                Cancel
            </button>
            <button type="button" id="globalClassyConfirmBtn" onclick="closeClassyConfirm(true)" style="padding:10px 22px;border-radius:10px;font-size:13px;background:#A2412C;color:#fff;border:none;font-weight:700;cursor:pointer">
                Confirm
            </button>
        </div>
    </div>
</div>

    <script>
        var globalClassyResolver = null;
        var globalClassyPendingForm = null;

        function showClassyConfirm(options) {
            options = options || {};
            return new Promise(function(resolve) {
                var modal = document.getElementById('globalClassyConfirmModal');
                var titleEl = document.getElementById('globalClassyTitle');
                var subTitleEl = document.getElementById('globalClassySubtitle');
                var msgEl = document.getElementById('globalClassyMessage');
                var confirmBtn = document.getElementById('globalClassyConfirmBtn');
                var cancelBtn = document.getElementById('globalClassyCancelBtn');
                var iconBox = document.getElementById('globalClassyIconBox');
                var msgBox = document.getElementById('globalClassyMessageBox');
                var topBorder = document.getElementById('globalClassyTopBorder');

                titleEl.textContent = options.title || 'Confirm Action';
                subTitleEl.textContent = options.subtitle || 'Please review before proceeding.';
                msgEl.innerHTML = options.message || 'Are you sure you want to perform this action?';
                confirmBtn.innerHTML = options.confirmText || 'Confirm';
                cancelBtn.innerHTML = options.cancelText || 'Cancel';

                if (options.variant === 'warning') {
                    iconBox.style.background = '#F8E9D3';
                    iconBox.style.color = '#8A5A10';
                    iconBox.style.borderColor = '#E5D3B8';
                    iconBox.innerHTML = '<i class="fa-solid fa-bolt"></i>';
                    msgBox.style.background = 'rgba(248,233,211,0.5)';
                    msgBox.style.borderColor = '#E5D3B8';
                    msgEl.style.color = '#8A5A10';
                    topBorder.style.background = 'linear-gradient(90deg,transparent,#D48A2E,transparent)';
                    confirmBtn.style.background = '#D48A2E';
                    confirmBtn.style.boxShadow = 'none';
                } else {
                    iconBox.style.background = '#F6E4E1';
                    iconBox.style.color = '#A2412C';
                    iconBox.style.borderColor = '#E8C8C2';
                    iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                    msgBox.style.background = 'rgba(246,228,225,0.5)';
                    msgBox.style.borderColor = '#E8C8C2';
                    msgEl.style.color = '#A2412C';
                    topBorder.style.background = 'linear-gradient(90deg,transparent,#A2412C,transparent)';
                    confirmBtn.style.background = '#A2412C';
                    confirmBtn.style.boxShadow = 'none';
                }

                globalClassyResolver = resolve;
                modal.style.display = 'flex';
            });
        }

        function closeClassyConfirm(result) {
            document.getElementById('globalClassyConfirmModal').style.display = 'none';
            if (globalClassyResolver) {
                globalClassyResolver(result);
                globalClassyResolver = null;
            }
            if (result && globalClassyPendingForm) {
                var form = globalClassyPendingForm;
                globalClassyPendingForm = null;
                form.submit();
            } else {
                globalClassyPendingForm = null;
            }
        }

        document.addEventListener('submit', function(e) {
            var form = e.target;
            var onsubmitAttr = form.getAttribute('onsubmit');
            if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                e.preventDefault();
                e.stopPropagation();

                var msg = 'Are you sure you want to proceed?';
                var match = onsubmitAttr.match(/confirm\((['"])([\s\S]*?)\1\)/);
                if (match && match[2]) {
                    msg = match[2].replace(/\\n/g, '<br>').replace(/\n/g, '<br>');
                }

                form.removeAttribute('onsubmit');
                globalClassyPendingForm = form;

                showClassyConfirm({
                    title: 'Confirm Action',
                    subtitle: 'Destructive action notification',
                    message: msg,
                    confirmText: '<i class="fa-solid fa-trash-can mr-1"></i> Confirm',
                    cancelText: 'Cancel',
                    variant: 'danger'
                }).then(function(confirmed) {
                    if (!confirmed) {
                        form.setAttribute('onsubmit', onsubmitAttr);
                    }
                });
            }
        }, true);

        document.addEventListener('DOMContentLoaded', function() {
            var sidebarContainer = document.querySelector('.sidebar');
            var sidebarNav = document.querySelector('.sidebar-nav') || sidebarContainer;
            if (!sidebarContainer) return;

            sidebarContainer.style.overscrollBehavior = 'contain';
            sidebarContainer.style.overscrollBehaviorY = 'contain';

            sidebarContainer.addEventListener('wheel', function(e) {
                var targetContainer = sidebarNav || sidebarContainer;
                var delta = e.deltaY;
                var scrollTop = targetContainer.scrollTop;
                var scrollHeight = targetContainer.scrollHeight;
                var clientHeight = targetContainer.clientHeight;
                var isScrollable = scrollHeight > clientHeight + 1;

                if (!isScrollable) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                if (delta < 0 && scrollTop <= 0) {
                    targetContainer.scrollTop = 0;
                    e.preventDefault();
                    e.stopPropagation();
                } else if (delta > 0 && scrollTop + clientHeight >= scrollHeight - 1) {
                    targetContainer.scrollTop = scrollHeight - clientHeight;
                    e.preventDefault();
                    e.stopPropagation();
                } else if (!targetContainer.contains(e.target)) {
                    targetContainer.scrollTop += delta;
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    e.stopPropagation();
                }
            }, { passive: false });

            var savedScroll = sessionStorage.getItem('sidebar_scroll_position');
            if (savedScroll !== null && sidebarNav) {
                sidebarNav.scrollTop = parseInt(savedScroll, 10);
            }

            if (sidebarNav) {
                sidebarNav.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebar_scroll_position', sidebarNav.scrollTop);
                }, { passive: true });

                var navItems = sidebarNav.querySelectorAll('.nav-item, .nav-link, a');
                for (var i = 0; i < navItems.length; i++) {
                    navItems[i].addEventListener('click', function() {
                        sessionStorage.setItem('sidebar_scroll_position', sidebarNav.scrollTop);
                    });
                }
            }
        });

        // ── Categorized Drill-Down Submenu Navigation Controller ──
        var _activeAdminSubnav = null;

        function isAdminSubnavOpen() {
            return _activeAdminSubnav !== null;
        }

        function showAdminSubNav(groupKey) {
            var mainNav = document.getElementById('admin-main-nav');
            if (mainNav) mainNav.style.display = 'none';
            document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
            var target = document.getElementById('subnav-' + groupKey);
            if (target) {
                target.style.display = 'block';
                _activeAdminSubnav = groupKey;
                sessionStorage.setItem('uplyft_subnav_admin', groupKey);
                history.replaceState({ uplyft_subnav: groupKey }, '', window.location.href);
            }
        }

        function showAdminMainNav() {
            document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
            var mainNav = document.getElementById('admin-main-nav');
            if (mainNav) { mainNav.style.display = 'block'; }
            _activeAdminSubnav = null;
            sessionStorage.removeItem('uplyft_subnav_admin');
        }

        function backToDashboard() {
            showAdminMainNav();
        }

        // ── Quit Confirmation Modal ──
        (function() {
            var s = document.createElement('style');
            s.textContent = '.uplyft-quit-overlay{position:fixed;inset:0;z-index:999999;background:rgba(14,14,17,0.65);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;padding:16px;animation:uqfi .2s ease forwards}@keyframes uqfi{from{opacity:0}to{opacity:1}}.uplyft-quit-card{background:#F9F8F5;border:1.5px solid #E1DFD7;border-radius:18px;box-shadow:0 24px 60px -10px rgba(0,0,0,0.3);max-width:420px;width:92%;padding:28px 28px 24px;text-align:center;animation:uqfs .28s cubic-bezier(0.16,1,0.3,1) forwards}@keyframes uqfs{from{opacity:0;transform:scale(0.94) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}.uplyft-quit-icon{width:52px;height:52px;border-radius:14px;background:#F6E4E1;color:#A2412C;border:1px solid #EAC8C1;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 16px}.uplyft-quit-title{font-family:"Manrope",sans-serif;font-size:18px;font-weight:800;color:#1B1A17;margin:0 0 6px}.uplyft-quit-msg{font-size:13px;color:#68665D;line-height:1.5;margin:0 0 22px}.uplyft-quit-actions{display:flex;gap:10px;justify-content:center}.uplyft-quit-cancel{padding:10px 24px;border-radius:10px;font-size:13px;font-weight:700;background:#F2EFEB;border:1px solid #E1DFD7;color:#68665D;cursor:pointer;transition:all .18s}.uplyft-quit-cancel:hover{background:#EAE8E1;color:#1B1A17}.uplyft-quit-confirm{padding:10px 28px;border-radius:10px;font-size:13px;font-weight:800;background:#A2412C;color:#fff;border:none;cursor:pointer;transition:all .18s}.uplyft-quit-confirm:hover{background:#8B3524;transform:translateY(-1px)}';
            document.head.appendChild(s);
        })();

        function uplyftShowQuitConfirm(loginUrl) {
            if (document.querySelector('.uplyft-quit-overlay')) return;
            var overlay = document.createElement('div');
            overlay.className = 'uplyft-quit-overlay';
            overlay.innerHTML = '<div class="uplyft-quit-card"><div class="uplyft-quit-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></div><h3 class="uplyft-quit-title">Are you sure you want to quit?</h3><p class="uplyft-quit-msg">You will be signed out and redirected to the login page.</p><div class="uplyft-quit-actions"><button class="uplyft-quit-cancel" onclick="this.closest(\'.uplyft-quit-overlay\').remove()">Cancel</button><button class="uplyft-quit-confirm" onclick="window.location.href=\'' + loginUrl + '\'">Confirm</button></div></div>';
            overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
            var escHandler = function(e) {
                if (e.key === 'Escape' && document.querySelector('.uplyft-quit-overlay')) {
                    overlay.remove();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
            document.body.appendChild(overlay);
        }

        // ── Core Back Button & Sidebar Sync Engine ──
        (function() {
            var isLoginPage = window.location.pathname.indexOf('/login') !== -1 || window.location.pathname.indexOf('-login') !== -1;
            if (isLoginPage) return;

            var isDashboardPage = function() {
                var p = window.location.pathname;
                return p.endsWith('/dashboard') || p === '/student' || p === '/teacher' || p === '/principal' || p === '/admin';
            };

            var portalPrefixes = ['/student', '/teacher', '/principal', '/lms', '/admin'];

            var loginUrl = '<?php echo e(route("global-admin.login")); ?>';

            var quitShown = false;

            window.addEventListener('load', function() {
                var savedSubnav = sessionStorage.getItem('uplyft_subnav_admin');
                if (savedSubnav && savedSubnav !== 'main') {
                    history.pushState({ uplyft_subnav: savedSubnav }, '', window.location.href);
                }
            });

            window.addEventListener('pageshow', function(e) {
                if (!e.persisted) return;
                var savedSubnav = sessionStorage.getItem('uplyft_subnav_admin');
                if (savedSubnav && savedSubnav !== 'main') {
                    showAdminSubNav(savedSubnav);
                } else if (isDashboardPage()) {
                    showAdminMainNav();
                }
            });

            window.addEventListener('popstate', function(e) {
                var p = window.location.pathname;

                var isLogin = p.indexOf('/login') !== -1 || p.indexOf('-login') !== -1;
                if (isLogin) {
                    history.pushState(null, '', window.location.href);
                    if (!quitShown) { quitShown = true; uplyftShowQuitConfirm(loginUrl); }
                    return;
                }

                if (isAdminSubnavOpen()) {
                    showAdminMainNav();
                    return;
                }

                var isPortal = portalPrefixes.some(function(pr) { return p.startsWith(pr); });
                if (isPortal) {
                    if (!quitShown) { quitShown = true; uplyftShowQuitConfirm(loginUrl); }
                }
            });
        })();
    </script>
</body>
</html>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\global-admin\layouts\app.blade.php ENDPATH**/ ?>
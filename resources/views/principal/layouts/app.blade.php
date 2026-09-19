<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Principal Portal') — UPLYFT Academic Suite</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg:            #EFEEEA;
            --surface:       #F9F8F5;
            --surface-glass: #F9F8F5;
            --surface2:      #F2EFEB;
            --border:        #E1DFD7;
            --border-glow:   rgba(212, 138, 46, 0.25);
            
            --brand-primary: #D48A2E;
            --brand-primary-hover: #C07A22;
            --brand-subtle:  #F8E9D3;
            --brand-border:  #E8CEAA;
            
            --accent-amber:  #D48A2E;
            --text:          #1B1A17;
            --text-muted:    #68665D;
            --text-faint:    #A19E92;
            --danger:        #A2412C;
            --danger-bg:     #F6E4E1;
            --success:       #2E6E42;
            --success-bg:    #E3EFE2;
            --info:          #3A529C;
            --info-bg:       #E7ECF6;
            --warning:       #8A5A10;
            --radius:        14px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body {
            background: #EFEEEA !important;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Ink & Amber Dark Sidebar Navigation */
        .sidebar {
            width: 270px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            background: #0E0E11 !important;
            border-right: 1px solid #1C1C20 !important;
            padding: 10px 10px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
            overflow: hidden;
            z-index: 100;
            box-shadow: none !important;
            box-sizing: border-box;
            scrollbar-width: none;
            overscroll-behavior: contain;
            overscroll-behavior-y: contain;
        }

        .sidebar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
        }

        .sidebar-header-area {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar .brand-header-container {
            padding: 4px 6px 4px !important;
            gap: 8px !important;
            background: #17191C !important;
            border-bottom: 1px solid #2A2C30 !important;
            border-radius: 10px;
        }

        .sidebar .brand-logo-img-wrapper {
            width: 30px !important;
            height: 30px !important;
            border-radius: 8px !important;
            padding: 1px !important;
            background: #2A2C30 !important;
        }

        .sidebar-active-session-box {
            margin: 2px 2px 6px;
            padding: 6px 10px;
            background: #17191C !important;
            border: 1px solid #2A2C30 !important;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .nav-section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6B6A6E !important;
            margin: 10px 6px 4px;
            font-weight: 800;
            font-family: 'Manrope', sans-serif !important;
        }

        .nav-links { display: flex; flex-direction: column; gap: 3px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 12px 7px 12px;
            border-radius: 9px;
            color: #A19E92;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            background: #17191C;
            border: 1px solid #2A2C30;
        }

        .nav-item:hover {
            background: #2A2C30;
            border-color: #3A3D42;
            color: #F9F8F5;
            transform: translateX(3px);
            box-shadow: none;
        }

        /* ── Ink & Amber Active State ── */
        .nav-item.active {
            color: #F0B45D !important;
            font-weight: 700;
            background: rgba(212, 138, 46, 0.16) !important;
            border: 1px solid rgba(212, 138, 46, 0.55) !important;
            box-shadow: 
                0 0 0 1px rgba(212, 138, 46, 0.12),
                0 0 20px rgba(212, 138, 46, 0.25) !important;
        }

        .nav-item.active::before {
            display: none !important;
            content: none !important;
        }

        .nav-item.active::after {
            display: none !important;
            content: none !important;
        }

        /* ── Sidebar Bottom Actions (Settings & Sign Out) ── */
        .sidebar-bottom-actions {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-shrink: 0;
            padding-top: 10px;
            border-top: 1px solid #1C1C20;
        }

        .nav-item-signout {
            color: #E05252 !important;
            border-color: #351C1C !important;
            background: #191212 !important;
        }

        .nav-item-signout .icon {
            background: #2D1818 !important;
            border-color: #4A2222 !important;
            color: #E05252 !important;
        }

        .nav-item-signout:hover {
            background: #2A1717 !important;
            border-color: #E05252 !important;
            color: #FF7B7B !important;
            box-shadow: 0 2px 10px rgba(224, 82, 82, 0.2) !important;
        }

        .nav-item-signout:hover .icon {
            background: #E05252 !important;
            color: #FFFFFF !important;
            border-color: #E05252 !important;
        }

        .nav-item .icon, .category-btn-left .icon { 
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            font-size: 12.5px;
            background: #2A2C30;
            border: 1px solid #3A3D42;
            color: #A19E92;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            flex-shrink: 0;
        }
        .nav-item:hover .icon, .sidebar-category-btn:hover .category-btn-left .icon {
            background: #D48A2E;
            color: #1A1200 !important;
            border-color: #C07A22;
            transform: scale(1.06);
            box-shadow: 0 2px 8px rgba(212, 138, 46, 0.25);
        }
        .nav-item .icon svg, .category-btn-left .icon svg {
            width: 16px; height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.75;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
            transition: stroke 0.18s ease;
        }
        .nav-item.active .icon svg, .sidebar-category-btn.has-active .category-btn-left .icon svg {
            stroke: #1A1200 !important;
        }
        .nav-item.active .icon, .sidebar-category-btn.has-active .category-btn-left .icon {
            background: #D48A2E !important;
            border-color: #C07A22 !important;
            color: #1A1200 !important;
        }
        .sidebar-category-btn.has-active .sidebar-category-chevron {
            color: #D48A2E !important;
        }

        /* ── Sidebar Drill-Down Navigation Engine ── */
        .sidebar-nav-container {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #FFFFFF rgba(255, 255, 255, 0.1);
            overscroll-behavior: contain;
            overscroll-behavior-y: contain;
        }

        .sidebar-nav-container::-webkit-scrollbar {
            display: block !important;
            width: 6px !important;
        }

        .sidebar-nav-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 9999px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: #FFFFFF !important;
            border-radius: 9999px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: #E2E8F0 !important;
        }

        .sidebar-view {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-height: 100%;
            animation: sidebarViewSlide 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .sidebar-view.hidden {
            display: none !important;
        }

        @keyframes sidebarViewSlide {
            from { opacity: 0; transform: translateX(6px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .sidebar-category-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 8.5px 12px 8.5px 12px;
            border-radius: 9px;
            color: #A19E92;
            background: #17191C;
            border: 1px solid #2A2C30;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            margin-bottom: 4px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-category-btn:hover {
            background: #2A2C30;
            border-color: #3A3D42;
            color: #F9F8F5;
            transform: translateX(3px);
        }

        /* ── Category Button Active Ink & Amber State ── */
        .sidebar-category-btn.has-active {
            color: #F0B45D !important;
            font-weight: 700;
            background: rgba(212, 138, 46, 0.16) !important;
            border: 1px solid rgba(212, 138, 46, 0.55) !important;
            box-shadow: 
                0 0 0 1px rgba(212, 138, 46, 0.12),
                0 0 20px rgba(212, 138, 46, 0.25) !important;
        }

        .sidebar-category-btn.has-active::before {
            display: none !important;
            content: none !important;
        }

        .category-btn-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-btn-left .icon {
            font-size: 14px;
            width: 18px;
            text-align: center;
        }

        .sidebar-category-chevron {
            font-size: 11px;
            color: #6B6A6E;
            font-weight: 800;
            transition: transform 0.18s ease, color 0.18s ease;
        }

        .sidebar-category-btn:hover .sidebar-category-chevron {
            color: #D48A2E;
            transform: translateX(2px);
        }

        /* Sub-Menu Focus View Components */
        .sidebar-back-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            padding: 6px 10px;
            background: #17191C;
            border: 1px solid #2A2C30;
            border-radius: 8px;
            color: #A19E92;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            margin-bottom: 6px;
        }

        .sidebar-back-btn:hover {
            background: #2A2C30;
            color: #F9F8F5;
            transform: translateX(-2px);
        }

        .back-arrow-icon {
            font-size: 12px;
            color: #D48A2E;
            font-weight: 900;
            display: inline-block;
            transition: transform 0.18s ease;
        }

        .sidebar-back-btn:hover .back-arrow-icon {
            transform: translateX(-2px);
        }

        .sidebar-active-section-banner {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: #17191C;
            border: 1px solid #2A2C30;
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .sidebar-active-section-banner .section-icon {
            font-size: 15px;
            flex-shrink: 0;
            color: #D48A2E;
        }

        .sidebar-active-section-banner .section-title {
            font-family: 'Manrope', sans-serif;
            font-size: 12.5px;
            font-weight: 800;
            color: #F9F8F5;
            line-height: 1.2;
        }

        .sidebar-active-section-banner .section-subtitle {
            font-size: 9px;
            font-weight: 800;
            color: #D48A2E;
            letter-spacing: 0.6px;
        }

        .subgroup-panel,
        .sidebar-subgroup-panel {
            display: none;
            flex-direction: column;
            gap: 2px;
        }

        .subgroup-panel.active,
        .sidebar-subgroup-panel.active {
            display: flex;
        }

        .sidebar-sub-module-list {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        /* ── Sidebar 2-Name Hub Navigation System ── */
        .sidebar-hub-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 4px 2px;
        }

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
        }

        .sidebar-hub-card-btn:hover {
            background: #2A2C30;
            border-color: #3A3D42;
            transform: translateY(-1px) translateX(2px);
        }

        .sidebar-hub-card-btn.active {
            background: rgba(212, 138, 46, 0.16) !important;
            border-color: rgba(212, 138, 46, 0.55) !important;
            box-shadow: 
                0 0 0 1px rgba(212, 138, 46, 0.12),
                0 0 20px rgba(212, 138, 46, 0.25) !important;
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

        .hub-btn-icon svg {
            width: 18px; height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.75;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
            transition: stroke 0.18s ease;
        }

        .sidebar-hub-card-btn:hover .hub-btn-icon {
            background: #D48A2E;
            border-color: #C07A22;
            color: #1A1200;
            transform: scale(1.08);
            box-shadow: 0 2px 8px rgba(212, 138, 46, 0.25);
        }

        .sidebar-hub-card-btn.active .hub-btn-icon {
            background: #D48A2E !important;
            border-color: transparent !important;
            color: #1A1200 !important;
            box-shadow: 0 0 14px rgba(212, 138, 46, 0.55) !important;
        }
        .sidebar-hub-card-btn.active .hub-btn-icon svg {
            stroke: #1A1200 !important;
        }
        .sidebar-hub-card-btn.active .hub-btn-icon i {
            color: #1A1200 !important;
            background: transparent !important;
        }

        .hub-btn-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .hub-btn-title {
            font-size: 12.5px;
            font-weight: 800;
            color: #F9F8F5;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-family: 'Outfit', sans-serif;
            transition: color 0.18s ease;
        }

        .sidebar-hub-card-btn:hover .hub-btn-title {
            color: #FFFFFF;
        }

        .sidebar-hub-card-btn.active .hub-btn-title {
            color: #F0B45D !important;
        }

        .hub-btn-desc {
            font-size: 9.5px;
            font-weight: 600;
            color: #8A877E;
            margin-top: 2px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-hub-card-btn:hover .hub-btn-desc {
            color: #A19E92;
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

        /* ── Categorized Drill-Down Submenu Navigation ── */
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

        /* ── Scroll-Free Command Hub Modals ── */
        .portal-hub-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow: hidden !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        .portal-hub-modal-overlay.active {
            display: flex !important;
        }

        .portal-hub-modal-card {
            background: #F9F8F5;
            border: 1.5px solid #E1DFD7;
            border-radius: 18px;
            box-shadow: 0 24px 50px -10px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            width: 100%;
            box-sizing: border-box;
            animation: hubModalSpringIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            overflow: hidden !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        @keyframes hubModalSpringIn {
            from { opacity: 0; transform: scale(0.96) translateY(6px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .portal-hub-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px 10px;
            border-bottom: 1px solid #E1DFD7;
            background: #F2EFEB;
        }

        .portal-hub-modal-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .portal-hub-modal-icon-badge {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #1A1200;
            background: #D48A2E;
            box-shadow: 0 2px 8px rgba(212, 138, 46, 0.25);
            flex-shrink: 0;
        }

        .portal-hub-modal-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #1B1A17;
            line-height: 1.2;
        }

        .portal-hub-modal-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #68665D;
            letter-spacing: 0.2px;
        }

        .portal-hub-modal-close-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid rgba(203, 213, 225, 0.8);
            background: rgba(255, 255, 255, 0.85);
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.15s ease;
        }

        .portal-hub-modal-close-btn:hover {
            background: #f43f5e;
            border-color: #f43f5e;
            color: #fff;
            transform: scale(1.08);
        }

        /* 4-column Operations Grid */
        .portal-hub-modules-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            padding: 10px 14px 12px;
            box-sizing: border-box;
            overflow: hidden !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        .portal-hub-module-card {
            background: #FFFFFF;
            border: 1px solid #E1DFD7;
            border-radius: 11px;
            padding: 7px 9px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.18s ease;
            min-width: 0;
            box-sizing: border-box;
        }

        .portal-hub-module-card:hover {
            background: #FFFFFF;
            border-color: #D48A2E;
            box-shadow: 0 4px 12px rgba(212, 138, 46, 0.12);
        }

        .portal-hub-card-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #EAE8E1;
        }

        .portal-hub-mini-icon {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9.5px;
            color: #fff;
            flex-shrink: 0;
        }

        .portal-hub-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 11px;
            font-weight: 800;
            color: #1B1A17;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .portal-hub-links-list {
            display: flex;
            flex-direction: column;
            gap: 1.5px;
        }

        .portal-hub-link {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 2.5px 6px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 600;
            color: #68665D;
            text-decoration: none;
            line-height: 1.25;
            transition: all 0.14s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .portal-hub-link:hover {
            background: #F8E9D3;
            color: #8A5A10;
            transform: translateX(2px);
        }

        .portal-hub-link.active {
            background: rgba(212, 138, 46, 0.16) !important;
            color: #8A5A10 !important;
            font-weight: 800;
            border: 1px solid rgba(212, 138, 46, 0.55) !important;
            box-shadow: 0 0 12px rgba(212, 138, 46, 0.25) !important;
        }

        .portal-hub-link-dot {
            width: 3.5px;
            height: 3.5px;
            border-radius: 50%;
            background: #A19E92;
            flex-shrink: 0;
            transition: background 0.14s;
        }

        .portal-hub-link:hover .portal-hub-link-dot {
            background: #D48A2E;
        }

        .portal-hub-link.active .portal-hub-link-dot {
            background: #D48A2E !important;
            box-shadow: 0 0 6px rgba(212, 138, 46, 0.55);
        }

        .sidebar-bottom-actions {
            flex-shrink: 0;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #2A2C30;
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: transparent;
        }

        .sidebar-bottom-actions .nav-item {
            padding: 8px 12px;
            font-size: 12.5px;
        }

        .nav-item-signout {
            color: #E05252 !important;
            border: 1px solid #2A2C30 !important;
            background: #17191C !important;
        }

        .nav-item-signout .icon {
            background: rgba(162, 65, 44, 0.15) !important;
            border-color: rgba(162, 65, 44, 0.35) !important;
            color: #E05252 !important;
        }

        .nav-item-signout:hover {
            background: rgba(162, 65, 44, 0.18) !important;
            border-color: rgba(162, 65, 44, 0.6) !important;
            color: #FF7B7B !important;
        }

        .nav-item-signout:hover .icon {
            background: #A2412C !important;
            border-color: #A2412C !important;
            color: #FFFFFF !important;
            box-shadow: 0 2px 8px rgba(162, 65, 44, 0.35) !important;
        }

        /* Main Area */
        .main { margin-left: 270px; width: calc(100% - 270px); flex: 1; display: flex; flex-direction: column; min-width: 0; min-height: 100vh; position: relative; z-index: 10; background: #EFEEEA !important; }

        /* Liquid Glass Topbar / Header */
        .topbar {
            height: 64px;
            border-bottom: 1px solid #E1DFD7;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F9F8F5;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: none;
        }

        .breadcrumb { font-size: 13px; color: #68665D; font-weight: 600; font-family: 'Inter', sans-serif; }
        .breadcrumb span {
            color: #1B1A17;
            font-weight: 700;
        }

        .content { padding: 32px; flex: 1; overflow-y: auto; background: #EFEEEA; }

        /* UI Cards & Solid Amber Buttons */
        .card {
            background: #F9F8F5 !important;
            border: 1px solid #E1DFD7;
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: none !important;
            transition: all 0.22s ease;
            position: relative;
        }

        .card:hover {
            border-color: #D3D0C5;
            box-shadow: none !important;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #E1DFD7;
        }

        .card-title {
            font-family: 'Manrope', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #1B1A17;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: #D48A2E !important;
            color: #1A1200 !important;
            border: 1px solid #C07A22 !important;
            box-shadow: none !important;
            text-shadow: none !important;
            font-weight: 800 !important;
        }

        .btn-primary:hover {
            background: #C07A22 !important;
            color: #1A1200 !important;
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary, .btn-ghost {
            background: #F9F8F5;
            color: #1B1A17 !important;
            border: 1px solid #E1DFD7;
            box-shadow: none !important;
        }
        .btn-secondary:hover, .btn-ghost:hover {
            border-color: #D3D0C5;
            color: #1B1A17 !important;
            background: #F2EFEB;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #F6E4E1 !important;
            color: #A2412C !important;
            border: 1px solid #EAC8C1 !important;
            box-shadow: none !important;
        }
        .btn-danger:hover {
            background: #EED1CC !important;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #E3EFE2 !important;
            color: #2E6E42 !important;
            border: 1px solid #C7DEC5 !important;
            box-shadow: none !important;
        }
        .btn-success:hover {
            background: #D5E7D3 !important;
            transform: translateY(-1px);
        }

        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 9px; }

        /* Tables & Badges */
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #68665D; border-bottom: 1px solid #E1DFD7; font-weight: 700; background: #F4F3EE; font-family: 'Manrope', sans-serif; }
        td { padding: 14px 16px; font-size: 13.5px; border-bottom: 1px solid #EAE8E1; color: #1B1A17; background: #F9F8F5; }
        tr:hover td { background: #F2EFEB; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-green { background: #E3EFE2; color: #2E6E42; border: 1px solid #C7DEC5; }
        .badge-yellow, .badge-amber { background: #F8E9D3; color: #8A5A10; border: 1px solid #E8CEAA; }
        .badge-purple { background: #E7ECF6; color: #3A529C; border: 1px solid #CCD7ED; }
        .badge-blue { background: #E7ECF6; color: #3A529C; border: 1px solid #CCD7ED; }
        .badge-ig, .badge-indigo { background: #F8E9D3; color: #8A5A10; border: 1px solid #E8CEAA; }

        /* Form Controls */
        .form-group { margin-bottom: 18px; }
        .form-group > label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 7px; color: #68665D; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Manrope', sans-serif; }
        .form-group input:not([type="checkbox"]):not([type="radio"]), .form-group select, .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            background: #F9F8F5;
            border: 1px solid #E1DFD7;
            border-radius: 10px;
            color: #1B1A17;
            font-size: 13.5px;
            outline: none;
            transition: all .2s;
        }
        .form-group input:not([type="checkbox"]):not([type="radio"]):focus, .form-group select:focus { border-color: #D48A2E; box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.20); }

        /* Alert Toast */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success { background: #E3EFE2; border: 1px solid #C7DEC5; color: #2E6E42; }
        .alert-error { background: #F6E4E1; border: 1px solid #EAC8C1; color: #A2412C; }
        .alert-warning { background: #F8E9D3; border: 1px solid #E8CEAA; color: #8A5A10; }

        /* ── Modals & Popovers Engine ─────────── */
        .apple-liquid-glass-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(14, 14, 17, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .apple-liquid-glass-card {
            background: #F9F8F5;
            border: 1px solid #E1DFD7;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            animation: appleLiquidSpringPop 0.36s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            overflow: hidden;
        }

        @keyframes appleLiquidSpringPop {
            0% {
                transform: scale(0.95) translateY(12px);
                opacity: 0;
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .apple-liquid-input {
            background: #ffffff !important;
            border: 1px solid #E1DFD7 !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 13.5px !important;
            color: #1B1A17 !important;
            box-shadow: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .apple-liquid-input:focus {
            background: #ffffff !important;
            border-color: #D48A2E !important;
            box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.20) !important;
        }

        .apple-liquid-btn-cancel {
            background: #F2EFEB;
            border: 1px solid #E1DFD7;
            color: #68665D;
            border-radius: 12px;
            font-weight: 700;
            padding: 10px 20px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }
        .apple-liquid-btn-cancel:hover {
            background: #EAE8E1;
            color: #1B1A17;
            transform: translateY(-1px);
        }

        .apple-liquid-btn-primary {
            background: #D48A2E;
            color: #1A1200;
            border: 1px solid #C07A22;
            border-radius: 12px;
            font-weight: 800;
            padding: 10px 24px;
            box-shadow: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .apple-liquid-btn-primary:hover {
            background: #C07A22;
            transform: translateY(-1px);
        }

        .apple-liquid-close-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #F2EFEB;
            border: 1px solid #E1DFD7;
            color: #68665D;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
            font-weight: 800;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .apple-liquid-close-btn:hover {
            background: #EAE8E1;
            color: #1B1A17;
            transform: scale(1.08);
        }
    </style>
</head>
<body>

    {{-- Universal Multi-Tenant Branded Loader --}}
    @include('partials.institute-loader')

    @php
        $topUser = auth()->user();
        $topOrg = null;
        $topCampuses = collect();
        $topActiveCampusId = null;

        if ($topUser && !$topUser->isGlobalAdmin()) {
            $orgId = $topUser->organization_id;
            if (!$orgId && $topUser->institute_id) {
                $userInst = \App\Models\Institute::withoutGlobalScopes()->find($topUser->institute_id);
                $orgId = $userInst?->organization_id;
            }

            if ($orgId) {
                $topOrg = \App\Models\Organization::find($orgId);
                $topActiveCampusId = method_exists($topUser, 'getActiveInstituteId') ? $topUser->getActiveInstituteId() : $topUser->institute_id;
                $topCampuses = \App\Models\Institute::withoutGlobalScopes()
                    ->where('organization_id', $orgId)
                    ->get();
            }
        }

        $currentSubGroup = null;
        if (request()->routeIs('principal.students.*') || request()->routeIs('principal.scholarships.*') || request()->is('principal/students*') || request()->is('principal/scholarships*') || request()->routeIs('principal.attendance*') || request()->is('principal/attendance*') || request()->routeIs('principal.directory.*') || request()->is('principal/directory*')) {
            $currentSubGroup = 'students';
        } elseif (request()->routeIs('principal.staff.*') || request()->routeIs('principal.teachers.availability.*') || request()->routeIs('*.password-resets.*') || request()->routeIs('principal.rooms.*') || request()->is('principal/staff*') || request()->is('principal/teachers/availability*') || request()->is('*/password-resets*') || request()->is('principal/rooms*')) {
            $currentSubGroup = 'staff';
        } elseif (request()->routeIs('*.invoices.*') || request()->routeIs('*.accounts.*') || request()->is('principal/invoices*') || request()->is('principal/accounts*') || (request()->routeIs('principal.settings.*') && (request('tab') === 'financial' || request('tab') === 'bank'))) {
            $currentSubGroup = 'finance';
        } elseif (request()->routeIs('principal.academic-terms.*') || request()->routeIs('principal.classes-subjects.*') || request()->routeIs('principal.timetables.*') || request()->is('principal/academic-terms*') || request()->is('principal/classes-subjects*') || request()->is('principal/timetables*')) {
            $currentSubGroup = 'academic';
        } elseif (request()->routeIs('lms.chatbot.*') || request()->routeIs('lms.practice-test.*') || request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') || request()->is('lms/chatbot*') || request()->is('lms/practice-test*') || request()->is('lms/subjects*') || request()->is('lms/materials*')) {
            $currentSubGroup = 'ai';
        } elseif (request()->routeIs('lms.assessments.*') || request()->routeIs('lms.mocks.*') || request()->routeIs('lms.test-results.*') || request()->routeIs('lms.datesheet.*') || request()->routeIs('lms.exam-report.*') || request()->routeIs('lms.grades.*') || request()->is('lms/assessments*') || request()->is('lms/mocks*') || request()->is('lms/test-results*') || request()->is('lms/datesheet*') || request()->is('lms/exam-report*') || request()->is('lms/grades*')) {
            $currentSubGroup = 'exams';
        } elseif (request()->routeIs('principal.settings.*') || request()->routeIs('principal.organization.campuses.*') || request()->routeIs('principal.attendance-settings.*') || request()->routeIs('principal.security.*') || request()->routeIs('profile.*') || request()->is('principal/settings*') || request()->is('principal/organization/campuses*') || request()->is('principal/attendance-settings*') || request()->is('principal/security*')) {
            $currentSubGroup = 'settings';
        }
    @endphp

    <aside class="sidebar flex flex-col justify-between" style="background:#0E0E11;border-right:1px solid #2A2C30;box-shadow:none;">
        <div class="sidebar-header-area">
            @include('partials.brand-header')
        </div>

        <div class="sidebar-nav-container custom-scrollbar">
            {{-- ── 1. TOP-LEVEL MAIN MENU (Categories) ── --}}
            <div id="principal-main-nav" class="space-y-1.5" style="padding: 4px 6px 10px; display: {{ $currentSubGroup ? 'none' : 'block' }};">
                <!-- Direct Quick Access: Dashboard -->
                <a href="{{ route('principal.dashboard') }}" class="glossy-nav-item {{ request()->routeIs('principal.dashboard') ? 'active' : '' }}" style="margin-bottom: 8px;">
                    <span class="icon"><x-icon name="chart-pie" /></span>
                    <span>Dashboard</span>
                </a>

                <div style="padding: 6px 6px 4px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 10px; font-weight: 800; color: #8A877E; text-transform: uppercase; letter-spacing: 0.8px;">
                        PORTAL HUBS &amp; MODULES
                    </span>
                </div>

                <!-- 1. Student Operations Hub -->
                <button type="button" onclick="showSubNav('students')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'students' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;"><x-icon name="users" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Student Operations</span>
                            <span class="hub-btn-desc">Directory, Attendance, Rosters</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 2. Faculty & Staff Hub -->
                <button type="button" onclick="showSubNav('staff')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'staff' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(234,179,8,0.15);color:#eab308;"><x-icon name="chalkboard-user" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Faculty &amp; Staff</span>
                            <span class="hub-btn-desc">Teachers, Rooms, Authorities</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 3. Financial Operations Hub -->
                <button type="button" onclick="showSubNav('finance')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'finance' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;"><x-icon name="receipt" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Financial Operations</span>
                            <span class="hub-btn-desc">Billing, Invoices, Ledger</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 4. Academic & Timetable Hub -->
                <button type="button" onclick="showSubNav('academic')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'academic' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;"><x-icon name="calendar-days" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Academic &amp; Timetable</span>
                            <span class="hub-btn-desc">Terms, Classes, Schedule</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 5. AI & Learning Suite Hub -->
                <button type="button" onclick="showSubNav('ai')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'ai' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(236,72,153,0.15);color:#f472b6;"><x-icon name="brain" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">AI &amp; Learning Suite</span>
                            <span class="hub-btn-desc">AI Assistant, Notes, Study</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 6. Assessments & Grading Hub -->
                <button type="button" onclick="showSubNav('exams')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'exams' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(20,184,166,0.15);color:#2dd4bf;"><x-icon name="file-signature" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Assessments &amp; Grading</span>
                            <span class="hub-btn-desc">Exams, Reports, Criteria</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                <!-- 7. Campus Administration Hub -->
                <button type="button" onclick="showSubNav('settings')" class="sidebar-hub-card-btn {{ $currentSubGroup === 'settings' ? 'active' : '' }}">
                    <div class="hub-btn-left">
                        <span class="hub-btn-icon" style="background:rgba(148,163,184,0.15);color:#cbd5e1;"><x-icon name="sliders" /></span>
                        <div class="hub-btn-text">
                            <span class="hub-btn-title">Administration</span>
                            <span class="hub-btn-desc">Settings, Security, Quotas</span>
                        </div>
                    </div>
                    <span class="hub-btn-arrow"><x-icon name="chevron-right" class="w-3.5 h-3.5" /></span>
                </button>

                {{-- Organization Campus Registration --}}
                @if(isset($topOrg) && $topOrg && auth()->user()->isPrincipal())
                    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #2A2C30;">
                        <a href="{{ route('principal.organization.campuses.create') }}" class="glossy-nav-item {{ request()->routeIs('principal.organization.campuses.create') ? 'active' : '' }}">
                            <span class="icon"><x-icon name="plus" /></span>
                            <span>Register New Campus</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- ── 2. SUBMENU: STUDENT OPERATIONS ── --}}
            <div id="subnav-students" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'students' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#60a5fa;">
                        <x-icon name="users" class="w-3.5 h-3.5 text-[#60a5fa]" />
                        <span>Student Operations</span>
                    </div>
                </div>

                <a href="{{ route('principal.students.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.students.*') || request()->is('principal/students*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="users" /></span>
                    <span>Student Directory</span>
                </a>
                <a href="{{ route('principal.directory.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.directory.*') || request()->is('principal/directory*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="building-columns" /></span>
                    <span>Search Profile</span>
                </a>
                <a href="{{ route('principal.attendance') }}" class="glossy-nav-item {{ (request()->routeIs('principal.attendance*') || request()->is('principal/attendance*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="clipboard-user" /></span>
                    <span>Daily Student Attendance</span>
                </a>
                <a href="{{ route('principal.scholarships.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.scholarships.*') || request()->is('principal/scholarships*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="award" /></span>
                    <span>Scholarship Programs</span>
                </a>
            </div>

            {{-- ── 3. SUBMENU: FACULTY & STAFF ── --}}
            <div id="subnav-staff" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'staff' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#eab308;">
                        <x-icon name="chalkboard-user" class="w-3.5 h-3.5 text-[#eab308]" />
                        <span>Faculty &amp; Staff</span>
                    </div>
                </div>

                <a href="{{ route('principal.staff.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.staff.*') || request()->is('principal/staff*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="chalkboard-user" /></span>
                    <span>Faculty &amp; Staff Directory</span>
                </a>
                <a href="{{ route('principal.teachers.availability.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.teachers.availability.*') || request()->is('principal/teachers/availability*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="calendar-check" /></span>
                    <span>Teacher Availabilities</span>
                </a>
                <a href="{{ route('principal.staff.authorities') }}" class="glossy-nav-item {{ request()->routeIs('principal.staff.authorities') ? 'active' : '' }}">
                    <span class="icon"><x-icon name="shield-halved" /></span>
                    <span>Assigned Authorities</span>
                </a>
                <a href="{{ route('principal.rooms.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.rooms.*') || request()->is('principal/rooms*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="door-open" /></span>
                    <span>Campus Rooms</span>
                </a>
                <a href="{{ route('principal.password-resets.index') }}" class="glossy-nav-item {{ (request()->routeIs('*.password-resets.*') || request()->is('*/password-resets*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="key" /></span>
                    <span>Staff Password Resets</span>
                </a>
            </div>

            {{-- ── 4. SUBMENU: FINANCIAL OPERATIONS ── --}}
            <div id="subnav-finance" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'finance' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#4ade80;">
                        <x-icon name="receipt" class="w-3.5 h-3.5 text-[#4ade80]" />
                        <span>Financial Operations</span>
                    </div>
                </div>

                <a href="{{ route('principal.settings.index', ['tab' => 'financial']) }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && request('tab') === 'financial') ? 'active' : '' }}">
                    <span class="icon"><x-icon name="receipt" /></span>
                    <span>Financial Ledger &amp; Expenses</span>
                </a>
                <a href="{{ route('principal.settings.index', ['tab' => 'bank']) }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && request('tab') === 'bank') ? 'active' : '' }}">
                    <span class="icon"><x-icon name="file-invoice-dollar" /></span>
                    <span>Fee Invoices &amp; Billing</span>
                </a>
                @if(Route::has('principal.accounts.index'))
                    <a href="{{ route('principal.accounts.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.accounts.*') || request()->is('principal/accounts*')) ? 'active' : '' }}">
                        <span class="icon"><x-icon name="wallet" /></span>
                        <span>Campus Accounts &amp; Vouchers</span>
                    </a>
                @endif
            </div>

            {{-- ── 5. SUBMENU: ACADEMIC & TIMETABLE ── --}}
            <div id="subnav-academic" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'academic' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#c084fc;">
                        <x-icon name="calendar-days" class="w-3.5 h-3.5 text-[#c084fc]" />
                        <span>Academic &amp; Timetable</span>
                    </div>
                </div>

                <a href="{{ route('principal.academic-terms.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.academic-terms.*') || request()->is('principal/academic-terms*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="calendar-check" /></span>
                    <span>Academic Terms &amp; Years</span>
                </a>
                <a href="{{ route('principal.classes-subjects.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.classes-subjects.*') || request()->is('principal/classes-subjects*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="layer-group" /></span>
                    <span>Classes &amp; Subjects</span>
                </a>
                <a href="{{ route('principal.timetables.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.timetables.*') || request()->is('principal/timetables*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="calendar-days" /></span>
                    <span>Timetable</span>
                </a>
            </div>

            {{-- ── 6. SUBMENU: AI & LEARNING SUITE ── --}}
            <div id="subnav-ai" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'ai' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#f472b6;">
                        <x-icon name="brain" class="w-3.5 h-3.5 text-[#f472b6]" />
                        <span>AI &amp; Learning Suite</span>
                    </div>
                </div>

                <a href="{{ route('lms.subjects.list') }}" class="glossy-nav-item {{ (request()->routeIs('lms.subjects.*') || request()->routeIs('lms.materials.*') || request()->is('lms/subjects*') || request()->is('lms/materials*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="book-bookmark" /></span>
                    <span>Course Materials &amp; Notes</span>
                </a>
                @if(Route::has('lms.chatbot.index'))
                    <a href="{{ route('lms.chatbot.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.chatbot.*') ? 'active' : '' }}">
                        <span class="icon"><x-icon name="robot" /></span>
                        <span>AI Study Assistant</span>
                    </a>
                @endif
                @if(Route::has('lms.practice-test.index'))
                    <a href="{{ route('lms.practice-test.index') }}" class="glossy-nav-item {{ request()->routeIs('lms.practice-test.*') ? 'active' : '' }}">
                        <span class="icon"><x-icon name="bolt" /></span>
                        <span>AI Practice Tests</span>
                    </a>
                @endif
            </div>

            {{-- ── 7. SUBMENU: ASSESSMENTS & GRADING ── --}}
            <div id="subnav-exams" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'exams' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#2dd4bf;">
                        <x-icon name="file-signature" class="w-3.5 h-3.5 text-[#2dd4bf]" />
                        <span>Assessments &amp; Grading</span>
                    </div>
                </div>

                <a href="{{ route('lms.assessments.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.assessments.*') || request()->is('lms/assessments*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="file-pen" /></span>
                    <span>Assessment Creator</span>
                </a>
                <a href="{{ route('lms.mocks.create') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.create') || request()->routeIs('teacher.mocks.create')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="bullseye" /></span>
                    <span>🎯 Generate Mocks</span>
                </a>
                <a href="{{ route('lms.mocks.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.mocks.index') || request()->routeIs('teacher.mocks.index') || request()->routeIs('lms.mocks.show') || request()->routeIs('teacher.mocks.show')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="list-check" /></span>
                    <span>📋 View Mocks</span>
                </a>
                <a href="{{ route('lms.test-results.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.test-results.*') || request()->is('lms/test-results*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="square-poll-vertical" /></span>
                    <span>Test Results &amp; Grading</span>
                </a>
                <a href="{{ route('lms.datesheet.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.datesheet.*') || request()->is('lms/datesheet*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="calendar-week" /></span>
                    <span>Exam Datesheets</span>
                </a>
                <a href="{{ route('lms.exam-report.index') }}" class="glossy-nav-item {{ (request()->routeIs('lms.exam-report.*') || request()->is('lms/exam-report*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="file-lines" /></span>
                    <span>Exam Performance Reports</span>
                </a>
                <a href="{{ route('lms.grades.weightages.page') }}" class="glossy-nav-item {{ (request()->routeIs('lms.grades.*') || request()->is('lms/grades*')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="scale-balanced" /></span>
                    <span>Grading Weights &amp; Criteria</span>
                </a>
            </div>

            {{-- ── 8. SUBMENU: CAMPUS ADMINISTRATION ── --}}
            <div id="subnav-settings" class="sidebar-subnav-panel space-y-1" style="padding: 4px 6px 10px; display: {{ $currentSubGroup === 'settings' ? 'block' : 'none' }};">
                <div class="subnav-header">
                    <button type="button" onclick="backToDashboard()" class="subnav-back-btn">
                        <span class="subnav-back-icon"><x-icon name="arrow-left" class="w-4 h-4" /></span>
                        <span>Back</span>
                    </button>
                    <div class="subnav-badge" style="color:#cbd5e1;">
                        <x-icon name="sliders" class="w-3.5 h-3.5 text-[#cbd5e1]" />
                        <span>Administration Settings</span>
                    </div>
                </div>

                <a href="{{ route('principal.settings.index') }}" class="glossy-nav-item {{ (request()->routeIs('principal.settings.*') && !request('tab')) ? 'active' : '' }}">
                    <span class="icon"><x-icon name="gear" /></span>
                    <span>Institute Settings</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="glossy-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="icon"><x-icon name="user-gear" /></span>
                    <span>Personal Settings</span>
                </a>
            </div>
        </div>

        <div class="sidebar-bottom-actions" style="margin-top:auto;padding:12px;border-top:1px solid #2A2C30;background:#121316;">
            <div class="flex flex-col gap-1.5">
                <button type="button" onclick="showSubNav('settings')" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-[#C9C7BD] hover:text-[#FFFFFF] hover:bg-[#1E1F22] border border-[#303136] bg-[#191A1C] transition cursor-pointer {{ $currentSubGroup === 'settings' ? 'active' : '' }}">
                    <x-icon name="sliders" class="w-3.5 h-3.5 text-[#9CA3AF]" />
                    <span>Settings</span>
                </button>
                <form method="POST" action="{{ route('logout') }}" class="w-full" style="margin:0;">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-[#E05252] hover:text-[#FF7B7B] border border-[#351C1C] bg-[#191212] hover:bg-[#2A1717] transition cursor-pointer">
                        <x-icon name="arrow-right-from-bracket" class="w-3.5 h-3.5 text-[#E05252]" />
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="breadcrumb">
                <span>@yield('breadcrumb', 'Overview')</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                {{-- Real-Time Operations Notification Bell --}}
                @include('principal.partials.notification-bell')

                @if(isset($topOrg) && $topOrg)
                    @php
                        $usedCount = $topCampuses->count();
                        $maxQuota = $topOrg->max_campuses;
                        $isQuotaFull = $usedCount >= $maxQuota;
                        $activeCampusModel = $topCampuses->firstWhere('id', $topActiveCampusId) ?? $topUser->institute;
                    @endphp

                    {{-- ── Rightmost Circular Icon to switch campus/organization profile ── --}}
                    <div style="position:relative;display:inline-block">
                        <button type="button" 
                                onclick="event.stopPropagation(); toggleTopNavProfileSwitcher()" 
                                style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;border:2px solid #a7f3d0;box-shadow:0 2px 8px rgba(16,185,129,0.25);cursor:pointer;transition:all 0.2s;user-select:none;position:relative;overflow:hidden" 
                                title="Switch Campus & Organization Profile ({{ $activeCampusModel?->name ?? $topOrg->name }})">
                            @if($activeCampusModel?->logo_url)
                                <img src="{{ $activeCampusModel->logo_url }}" alt="{{ $activeCampusModel->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%" />
                            @else
                                <i class="fa-solid fa-building"></i>
                            @endif
                            <span style="position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;background:#10b981;border:2px solid #ffffff;z-index:2"></span>
                        </button>

                        {{-- Floating Profile Switcher Popover --}}
                        <div id="top-nav-profile-switcher-pop" style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:340px;z-index:999999;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:20px;box-shadow:0 20px 50px -10px rgba(15,23,42,0.25);padding:14px;animation:appleLiquidSpringPop 0.25s ease forwards">
                            
                            {{-- Header --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9;margin-bottom:10px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="width:34px;height:34px;border-radius:10px;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;font-size:14px;overflow:hidden;flex-shrink:0">
                                        @if($activeCampusModel?->logo_url)
                                            <img src="{{ $activeCampusModel->logo_url }}" alt="{{ $activeCampusModel->name }}" style="width:100%;height:100%;object-fit:cover" />
                                        @else
                                            <i class="fa-solid fa-building"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:800;color:#0f172a">{{ $topOrg->name }}</div>
                                        <div style="font-size:11px;color:#64748b;font-weight:600">Multi-Campus Organization</div>
                                    </div>
                                </div>
                                <span style="font-size:11px;font-weight:800;background:#e0e7ff;color:#4338ca;padding:3px 9px;border-radius:12px;border:1px solid #c7d2fe">
                                    {{ $usedCount }}/{{ $maxQuota }} Profiles
                                </span>
                            </div>

                            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;padding-left:2px">
                                Switch Active Campus Profile
                            </div>

                            {{-- Campus List --}}
                            <div style="max-height:280px;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding-right:2px">
                                @foreach($topCampuses as $tcamp)
                                    @php $isTopCurr = (int)$tcamp->id === (int)$topActiveCampusId; @endphp
                                    <form method="POST" action="{{ route('tenant.switch-institute') }}" style="margin:0">
                                        @csrf
                                        <input type="hidden" name="institute_id" value="{{ $tcamp->id }}">
                                        <button type="submit" style="width:100%;text-align:left;padding:10px 12px;border-radius:14px;border:1.5px solid {{ $isTopCurr ? '#818cf8' : '#e2e8f0' }};background:{{ $isTopCurr ? '#eef2ff' : '#ffffff' }};cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:all 0.18s;box-shadow:{{ $isTopCurr ? '0 2px 8px rgba(79,70,229,0.12)' : 'none' }}">
                                            <div style="display:flex;align-items:center;gap:10px;min-width:0">
                                                <div style="width:32px;height:32px;border-radius:10px;background:{{ $isTopCurr ? 'linear-gradient(135deg,#4f46e5,#6366f1)' : '#cbd5e1' }};color:#ffffff;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:{{ $isTopCurr ? '0 2px 6px rgba(79,70,229,0.3)' : 'none' }};overflow:hidden">
                                                    @if($tcamp->logo_url)
                                                        <img src="{{ $tcamp->logo_url }}" alt="{{ $tcamp->name }}" style="width:100%;height:100%;object-fit:cover" />
                                                    @else
                                                        {{ $tcamp->display_initial }}
                                                    @endif
                                                </div>
                                                <div style="min-width:0">
                                                    <div style="font-size:12.5px;font-weight:{{ $isTopCurr ? '800' : '700' }};color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                                        {{ $tcamp->name }}
                                                    </div>
                                                    <div style="font-size:10.5px;color:{{ $isTopCurr ? '#4338ca' : '#64748b' }};font-weight:600;display:flex;align-items:center;gap:4px">
                                                        @if($isTopCurr)
                                                            <i class="fa-solid fa-check text-emerald-500"></i> Active Profile
                                                        @else
                                                            {{ $tcamp->city ?? 'Campus Profile' }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @if($isTopCurr)
                                                <div style="width:20px;height:20px;border-radius:50%;background:#4f46e5;color:#fff;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>

                        </div>
                    </div>

                    <script>
                        function toggleTopNavProfileSwitcher() {
                            var pop = document.getElementById('top-nav-profile-switcher-pop');
                            if (pop) {
                                pop.style.display = (pop.style.display === 'none' || !pop.style.display) ? 'block' : 'none';
                            }
                        }
                        document.addEventListener('click', function(e) {
                            var pop = document.getElementById('top-nav-profile-switcher-pop');
                            if (pop && !e.target.closest('#top-nav-profile-switcher-pop') && !e.target.closest('button[onclick*="toggleTopNavProfileSwitcher"]')) {
                                pop.style.display = 'none';
                            }
                        });
                    </script>
                @endif
            </div>
        </header>

        <div class="content">
            @include('partials.alert-notifications')
            @yield('content')
        </div>
    </main>



    <!-- GLOBAL CLASSY CONFIRMATION MODAL -->
    <div id="globalClassyConfirmModal" class="apple-liquid-glass-overlay" style="display:none;z-index:999999;">
        <div class="apple-liquid-glass-card" style="max-width:480px;width:92%;padding:28px 30px;text-align:left">
            <div id="globalClassyTopBorder" style="position:absolute;top:0;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#ef4444,transparent)"></div>

            <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px">
                <div id="globalClassyIconBox" style="width:48px;height:48px;border-radius:14px;background:#F6E4E1;color:#A2412C;border:1px solid #EAC8C1;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                    <x-icon name="triangle-exclamation" class="w-6 h-6" />
                </div>
                <div>
                    <h3 id="globalClassyTitle" style="font-family:'Manrope',sans-serif;font-size:19px;font-weight:800;color:#1B1A17;margin:0;letter-spacing:-0.4px">Confirm Action</h3>
                    <p id="globalClassySubtitle" style="font-size:12.5px;color:#68665D;margin-top:3px;font-weight:500">Please review before proceeding.</p>
                </div>
            </div>

            <div id="globalClassyMessageBox" style="background:#F6E4E1;border:1px solid #EAC8C1;border-radius:14px;padding:16px;margin-bottom:24px">
                <div id="globalClassyMessage" style="font-size:13.5px;color:#A2412C;line-height:1.55;font-weight:600"></div>
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
                <button type="button" id="globalClassyCancelBtn" onclick="closeClassyConfirm(false)" style="padding:10px 20px;border-radius:12px;font-size:13px;font-weight:700;background:#F9F8F5;border:1px solid #E1DFD7;color:#68665D;cursor:pointer">
                    Cancel
                </button>
                <button type="button" id="globalClassyConfirmBtn" onclick="closeClassyConfirm(true)" style="padding:10px 22px;border-radius:12px;font-size:13px;background:#A2412C;color:#fff;border:none;font-weight:800;cursor:pointer">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- Executive Desk Hub Modal (Strictly Zero Scroll: Fits Screen Completely) --}}
    {{-- ========================================================================= --}}
    <script>
        function openPortalHubModal(type) {
            if (type === 'executive') {
                window.location.href = "{{ route('principal.dashboard') }}";
            }
        }

        function closePortalHubModal() {
            // no-op
        }

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

                if (!modal) {
                    resolve(confirm(options.message || 'Confirm?'));
                    return;
                }

                titleEl.textContent = options.title || 'Confirm Action';
                subTitleEl.textContent = options.subtitle || 'Please review before proceeding.';
                msgEl.innerHTML = options.message || 'Are you sure you want to perform this action?';
                confirmBtn.innerHTML = options.confirmText || 'Confirm';
                cancelBtn.innerHTML = options.cancelText || 'Cancel';

                if (options.variant === 'warning') {
                    iconBox.style.background = '#fffbeb';
                    iconBox.style.color = '#d97706';
                    iconBox.style.borderColor = '#fde68a';
                    iconBox.innerHTML = '<i class="fa-solid fa-bolt"></i>';
                    msgBox.style.background = 'rgba(255,251,235,0.7)';
                    msgBox.style.borderColor = '#fde68a';
                    msgEl.style.color = '#92400e';
                    topBorder.style.background = 'linear-gradient(90deg,transparent,#f59e0b,transparent)';
                    confirmBtn.style.background = 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)';
                    confirmBtn.style.boxShadow = '0 4px 14px rgba(217,119,6,0.35)';
                } else {
                    iconBox.style.background = '#fef2f2';
                    iconBox.style.color = '#ef4444';
                    iconBox.style.borderColor = '#fecaca';
                    iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                    msgBox.style.background = 'rgba(254,242,242,0.65)';
                    msgBox.style.borderColor = '#fecaca';
                    msgEl.style.color = '#991b1b';
                    topBorder.style.background = 'linear-gradient(90deg,transparent,#ef4444,transparent)';
                    confirmBtn.style.background = 'linear-gradient(135deg,#ef4444 0%,#dc2626 100%)';
                    confirmBtn.style.boxShadow = '0 4px 14px rgba(220,38,38,0.35)';
                }

                globalClassyResolver = resolve;
                modal.style.display = 'flex';
            });
        }

        function closeClassyConfirm(result) {
            var modal = document.getElementById('globalClassyConfirmModal');
            if (modal) modal.style.display = 'none';
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
            document.querySelectorAll('.sidebar, .app-sidebar').forEach(function(sidebar) {
                sidebar.style.overscrollBehavior = 'contain';
                sidebar.style.overscrollBehaviorY = 'contain';

                sidebar.addEventListener('wheel', function(e) {
                    var scrollContainer = sidebar.querySelector('.sidebar-nav-container, .sidebar-nav') || (sidebar.scrollHeight > sidebar.clientHeight ? sidebar : null);
                    
                    if (!scrollContainer) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    var delta = e.deltaY;
                    var scrollTop = scrollContainer.scrollTop;
                    var scrollHeight = scrollContainer.scrollHeight;
                    var clientHeight = scrollContainer.clientHeight;
                    var isScrollable = scrollHeight > clientHeight + 1;

                    if (!isScrollable) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }

                    if (delta < 0 && scrollTop <= 0) {
                        scrollContainer.scrollTop = 0;
                        e.preventDefault();
                        e.stopPropagation();
                    } else if (delta > 0 && scrollTop + clientHeight >= scrollHeight - 1) {
                        scrollContainer.scrollTop = scrollHeight - clientHeight;
                        e.preventDefault();
                        e.stopPropagation();
                    } else if (!scrollContainer.contains(e.target)) {
                        scrollContainer.scrollTop += delta;
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        e.stopPropagation();
                    }
                }, { passive: false });
            });
        });

        // ── Categorized Drill-Down Submenu Navigation Controller ──
        var _activePrincipalSubnav = null;

        function isPrincipalSubnavOpen() {
            return _activePrincipalSubnav !== null;
        }

        function showSubNav(groupKey) {
            var mainNav = document.getElementById('principal-main-nav');
            if (mainNav) mainNav.style.display = 'none';
            document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
            var target = document.getElementById('subnav-' + groupKey);
            if (target) {
                target.style.display = 'block';
                _activePrincipalSubnav = groupKey;
                sessionStorage.setItem('uplyft_subnav_principal', groupKey);
                history.replaceState({ uplyft_subnav: groupKey }, '', window.location.href);
            }
        }

        function showMainNav() {
            document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
            var mainNav = document.getElementById('principal-main-nav');
            if (mainNav) { mainNav.style.display = 'block'; }
            _activePrincipalSubnav = null;
            sessionStorage.removeItem('uplyft_subnav_principal');
        }

        function backToDashboard() {
            showMainNav();
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

            var loginUrl = '{{ route("principal.login") }}';

            var quitShown = false;

            window.addEventListener('load', function() {
                var savedSubnav = sessionStorage.getItem('uplyft_subnav_principal');
                if (savedSubnav && savedSubnav !== 'main') {
                    history.pushState({ uplyft_subnav: savedSubnav }, '', window.location.href);
                }
            });

            window.addEventListener('pageshow', function(e) {
                if (!e.persisted) return;
                var savedSubnav = sessionStorage.getItem('uplyft_subnav_principal');
                if (savedSubnav && savedSubnav !== 'main') {
                    showSubNav(savedSubnav);
                } else if (isDashboardPage()) {
                    showMainNav();
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

                if (isPrincipalSubnavOpen()) {
                    showMainNav();
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

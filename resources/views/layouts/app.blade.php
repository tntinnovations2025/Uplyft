<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'UPLYFT Platform')) - UPLYFT Academic Suite</title>

        <!-- Favicon & Touch Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ file_exists(public_path('favicon-32x32.png')) ? filemtime(public_path('favicon-32x32.png')) : 2 }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ file_exists(public_path('favicon-16x16.png')) ? filemtime(public_path('favicon-16x16.png')) : 2 }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ file_exists(public_path('favicon.ico')) ? filemtime(public_path('favicon.ico')) : 2 }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/uplyft-logo.png') }}?v={{ file_exists(public_path('images/uplyft-logo.png')) ? filemtime(public_path('images/uplyft-logo.png')) : 2 }}">

        <!-- Google Fonts: Manrope (Headings & Numbers) & Inter (UI & Body) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome 6 Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- Tailwind CSS & Vite Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
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
                --success-bg:       #E3EFE2;
                --success-text:     #2E6E42;
                --danger-bg:        #F6E4E1;
                --danger-text:      #A2412C;
                --info-bg:          #E7ECF6;
                --info-text:        #3A529C;

                /* Legacy-palette aliases: old views (LMS/student chatbot, materials,
                   grades, exams) reference these tokens; map them to Ink & Amber. */
                --bg:               #EFEEEA;
                --surface:          #F9F8F5;
                --surface-glass:    #F9F8F5;
                --surface2:         #F2EFEB;
                --text:             #1B1A17;
                --text-muted:       #68665D;
                --text-faint:       #A19E92;
                --accent:           #D48A2E;
                --accent-hover:     #C07A22;
                --accent-on:        #1A1200;
                --accent2:          #8A5A10;
                --danger:           #A2412C;
                --success:          #2E6E42;
                --warning:          #8A5A10;
                --info:             #3A529C;
            }

            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                background: #EFEEEA fixed !important;
                color: #1B1A17;
                min-height: 100vh;
                overflow-x: hidden;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar { width: 5px; height: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: rgba(161, 158, 146, 0.45); border-radius: 9999px; }
            ::-webkit-scrollbar-thumb:hover { background: rgba(104, 102, 93, 0.7); }

            /* Explicit Sidebar & Main Workspace Positioning */
            .app-sidebar {
                width: 270px;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 100;
                background: #0E0E11 !important;
                border-right: 1px solid #1C1C20 !important;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #FFFFFF rgba(255, 255, 255, 0.1);
                box-shadow: 1px 0 2px rgba(0,0,0,0.3) !important;
                overscroll-behavior: contain;
                overscroll-behavior-y: contain;
            }
            .app-sidebar::-webkit-scrollbar {
                width: 6px;
            }
            .app-sidebar::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.05);
                border-radius: 9999px;
            }
            .app-sidebar::-webkit-scrollbar-thumb {
                background: #FFFFFF !important;
                border-radius: 9999px;
            }
            .app-sidebar::-webkit-scrollbar-thumb:hover {
                background: #E2E8F0 !important;
            }

            .liquid-glass-header {
                background: #F9F8F5 !important;
                border-bottom: 1px solid #E1DFD7 !important;
                box-shadow: none !important;
            }

            .app-main-wrapper {
                margin-left: 270px !important;
                width: calc(100% - 270px) !important;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                position: relative;
                z-index: 10;
                background: #EFEEEA !important;
            }

            @media (max-width: 1024px) {
                .app-sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
                .app-sidebar.open { transform: translateX(0); }
                .app-main-wrapper { margin-left: 0 !important; width: 100% !important; }
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
        </style>
    </head>
    <body class="font-sans antialiased text-ink min-h-screen relative selection:bg-amber-500 selection:text-amber-950" style="background: #EFEEEA fixed;">
        
        {{-- Universal Multi-Tenant Branded Loader --}}
        @include('partials.institute-loader')

        <div class="min-h-screen flex relative z-10">
            {{-- ========================================================================= --}}
            {{-- Left Sidebar Navigation --}}
            {{-- ========================================================================= --}}
            @if(View::exists('layouts.navigation'))
                @include('layouts.navigation')
            @endif

            {{-- ========================================================================= --}}
            {{-- Main Workspace Container --}}
            {{-- ========================================================================= --}}
            <div class="app-main-wrapper">
                
                {{-- Frosted Header --}}
                <header class="sticky top-0 z-40 liquid-glass-header">
                    <div class="py-3.5 px-6 lg:px-8 flex items-center justify-between gap-4">
                        {{-- Page Heading / Slot --}}
                        <div class="flex items-center gap-3">
                            @isset($header)
                                {{ $header }}
                            @else
                                @hasSection('page-header')
                                    <h2 class="font-display font-extrabold text-xl text-ink tracking-tight flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(212,138,46,0.5)]"></span>
                                        @yield('page-header')
                                    </h2>
                                @else
                                    <div class="flex items-center gap-2 text-sm text-ink-secondary font-semibold">
                                        <span class="text-ink font-extrabold tracking-tight">{{ config('app.name', 'UPLYFT') }}</span>
                                        <span>/</span>
                                        <span class="text-amber-tint-text font-bold">Dashboard</span>
                                    </div>
                                @endif
                            @endisset
                        </div>

                        {{-- Global Ambient Glass Controls / Status Bar --}}
                        <div class="flex items-center gap-3.5">
                            {{-- Active Academic Session / Term Pill --}}
                            @php
                                $headerActiveTerm = null;
                                if (auth()->check() && auth()->user()->institute_id) {
                                    $headerActiveTerm = \App\Models\AcademicTerm::getActiveTerm(auth()->user()->institute_id);
                                }
                            @endphp
                            @if(auth()->check() && auth()->user()->institute_id)
                            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#E1DFD7] bg-[#F9F8F5] text-xs font-bold text-[#1B1A17]">
                                <span class="relative flex h-2 w-2">
                                    @if($headerActiveTerm)
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    @else
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    @endif
                                </span>
                                <span>{{ $headerActiveTerm?->name ?? 'No Active Term' }}</span>
                            </div>
                            @endif

                            {{-- User Profile Pill --}}
                            @auth
                                <div class="flex items-center gap-2.5 pl-3 border-l border-[#E1DFD7]">
                                    <div class="w-8 h-8 rounded-xl bg-[#D48A2E] border border-[#C07A22] flex items-center justify-center text-[#1A1200] text-xs font-extrabold shadow-sm relative overflow-hidden">
                                        <span class="relative z-10">{{ Auth::user()->display_initials }}</span>
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <p class="text-xs font-extrabold text-[#1B1A17] leading-tight">{{ Auth::user()->display_name }}</p>
                                        <p class="text-[10px] text-[#68665D] leading-tight font-medium">{{ Auth::user()->display_email }}</p>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </header>

                {{-- Page Main Content --}}
                <main class="flex-1 py-6 px-6 lg:px-8 w-full max-w-7xl mx-auto">
                    @include('partials.alert-notifications')
                    @if(isset($slot) && $slot instanceof \Illuminate\View\ComponentSlot)
                        {{ $slot }}
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            // ── Sidebar Scroll Containment ──
            document.addEventListener('DOMContentLoaded', function() {
                var sidebarNav = document.querySelector('.app-sidebar');
                if (!sidebarNav) return;
                sidebarNav.style.overscrollBehavior = 'contain';
                sidebarNav.style.overscrollBehaviorY = 'contain';
                sidebarNav.addEventListener('wheel', function(e) {
                    var delta = e.deltaY;
                    var scrollTop = sidebarNav.scrollTop;
                    var scrollHeight = sidebarNav.scrollHeight;
                    var clientHeight = sidebarNav.clientHeight;
                    if (scrollHeight <= clientHeight + 1) { e.preventDefault(); e.stopPropagation(); return; }
                    if (delta < 0 && scrollTop <= 0) { sidebarNav.scrollTop = 0; e.preventDefault(); e.stopPropagation(); }
                    else if (delta > 0 && scrollTop + clientHeight >= scrollHeight - 1) { sidebarNav.scrollTop = scrollHeight - clientHeight; e.preventDefault(); e.stopPropagation(); }
                    else { e.stopPropagation(); }
                }, { passive: false });
                var savedScroll = sessionStorage.getItem('uplyft_sidebar_scroll');
                if (savedScroll !== null) sidebarNav.scrollTop = parseInt(savedScroll, 10);
                sidebarNav.addEventListener('scroll', function() { sessionStorage.setItem('uplyft_sidebar_scroll', sidebarNav.scrollTop); }, { passive: true });
                sidebarNav.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() { sessionStorage.setItem('uplyft_sidebar_scroll', sidebarNav.scrollTop); });
                });
            });

            // ── Drill-Down Submenu Navigation Controller ──
            // Tracks which subnav is currently open (null = main nav visible)
            var _activeRoleSubnav = null;

            function isRoleSubnavOpen() {
                return _activeRoleSubnav !== null;
            }

            function showRoleSubNav(groupKey) {
                var mainNav = document.getElementById('role-main-nav');
                if (mainNav) mainNav.style.display = 'none';
                document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
                var target = document.getElementById('subnav-' + groupKey);
                if (target) {
                    target.style.display = 'block';
                    _activeRoleSubnav = groupKey;
                    sessionStorage.setItem('uplyft_subnav_role', groupKey);
                    // Push a history entry so Back button closes this submenu
                    // replaceState avoids duplicates when navigating between subnavs
                    history.replaceState({ uplyft_subnav: groupKey }, '', window.location.href);
                }
            }

            function showRoleMainNav() {
                document.querySelectorAll('.sidebar-subnav-panel').forEach(function(p) { p.style.display = 'none'; });
                var mainNav = document.getElementById('role-main-nav');
                if (mainNav) { mainNav.style.display = 'block'; }
                _activeRoleSubnav = null;
                sessionStorage.removeItem('uplyft_subnav_role');
            }

            // "Back" button on subnav panels: return to main nav, no page navigation
            function backToDashboard() {
                showRoleMainNav();
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
                // Escape key closes the quit confirmation (same as Cancel)
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
            // Manages history stack so Back button dismisses submenus before showing quit.
            // Uses sessionStorage to restore sidebar state after bfcache back/forward.
            (function() {
                var isLoginPage = window.location.pathname.indexOf('/login') !== -1 || window.location.pathname.indexOf('-login') !== -1;
                if (isLoginPage) return;

                var isDashboardPage = function() {
                    var p = window.location.pathname;
                    return p.endsWith('/dashboard') || p === '/student' || p === '/teacher' || p === '/principal' || p === '/admin';
                };

                var portalPrefixes = ['/student', '/teacher', '/principal', '/lms', '/admin'];

                var loginUrl = '{{ url("/login") }}';
                @if(auth()->check())
                    @if(auth()->user()->isPrincipal())
                        loginUrl = '{{ route("principal.login") }}';
                    @elseif(auth()->user()->isGlobalAdmin())
                        loginUrl = '{{ route("global-admin.login") }}';
                    @else
                        loginUrl = '{{ route("login") }}';
                    @endif
                @endif

                var quitShown = false;

                // On initial load: if PHP set an active subnav, push a history entry
                // so the Back button can dismiss it. Skip if restored from bfcache.
                window.addEventListener('load', function() {
                    var savedSubnav = sessionStorage.getItem('uplyft_subnav_role');
                    if (savedSubnav && savedSubnav !== 'main') {
                        history.pushState({ uplyft_subnav: savedSubnav }, '', window.location.href);
                    }
                });

                // On bfcache restoration (Back/Forward): restore sidebar subnav from sessionStorage.
                // Do NOT push state — the previous entry already exists in the stack.
                window.addEventListener('pageshow', function(e) {
                    if (!e.persisted) return;
                    var savedSubnav = sessionStorage.getItem('uplyft_subnav_role');
                    if (savedSubnav && savedSubnav !== 'main') {
                        showRoleSubNav(savedSubnav);
                    } else if (isDashboardPage()) {
                        showRoleMainNav();
                    }
                });

                // Back/Forward button handler
                window.addEventListener('popstate', function(e) {
                    var p = window.location.pathname;

                    // If we navigated to a login page, show quit confirmation
                    var isLogin = p.indexOf('/login') !== -1 || p.indexOf('-login') !== -1;
                    if (isLogin) {
                        history.pushState(null, '', window.location.href);
                        if (!quitShown) { quitShown = true; uplyftShowQuitConfirm(loginUrl); }
                        return;
                    }

                    // If a submenu was open, close it and return to main nav
                    if (isRoleSubnavOpen()) {
                        showRoleMainNav();
                        return;
                    }

                    // On a portal page with no submenus open — show quit confirmation
                    var isPortal = portalPrefixes.some(function(pr) { return p.startsWith(pr); });
                    if (isPortal) {
                        if (!quitShown) { quitShown = true; uplyftShowQuitConfirm(loginUrl); }
                    }
                });
            })();
        </script>
    </body>
</html>

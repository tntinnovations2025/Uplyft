<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Uplyft SaaS Management</title>
    
    <!-- Tailwind CSS CDN & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --accent: #06b6d4;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            min-height: 100vh;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        .glass-panel {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            transition: all 0.2s ease;
        }

        .glass-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        /* Sidebar Nav Active Link Styling */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-radius: 12px;
            color: var(--text-muted);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(79, 70, 229, 0.15));
            border-left: 4px solid var(--primary);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }
    </style>
    @yield('styles')
</head>
<body class="flex h-screen overflow-hidden">

    <!-- SIDEBAR NAVIGATION -->
    <aside class="w-64 glass-panel m-3 flex flex-col justify-between hidden md:flex shrink-0">
        <div>
            <!-- LOGO BRAND -->
            <div class="p-6 flex items-center gap-3 border-b border-white/10">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-xl shadow-lg shadow-indigo-500/30">
                    U
                </div>
                <div>
                    <h1 class="font-bold text-xl tracking-tight text-white">UPLYFT<span class="text-indigo-400 text-sm font-semibold ml-1">SaaS</span></h1>
                    <p class="text-xs text-slate-400">Multi-Institute Platform</p>
                </div>
            </div>

            <!-- NAVIGATION LINKS -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard Home</span>
                </a>

                <a href="{{ route('admissions.index') }}" class="nav-link {{ request()->routeIs('admissions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-graduate w-5 text-center"></i>
                    <span>Student Admissions</span>
                </a>

                <a href="{{ route('teachers.onboarding') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user w-5 text-center"></i>
                    <span>Teacher Onboarding</span>
                </a>

                <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-user w-5 text-center"></i>
                    <span>Attendance Roster</span>
                </a>
            </nav>
        </div>

        <!-- TENANT FOOTER -->
        <div class="p-4 m-3 rounded-xl bg-slate-900/60 border border-white/5 flex items-center gap-3">
            <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
            <div class="overflow-hidden">
                <p class="text-xs font-semibold text-white truncate">Uplyft Academy</p>
                <p class="text-[10px] text-slate-400">Tenant ID Scope: Active</p>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- HEADER BAR -->
        <header class="h-16 glass-panel m-3 mb-0 px-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-semibold text-white">@yield('page-header', 'Platform Operations')</h2>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-300 text-xs border border-indigo-500/20">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Multi-Tenancy Isolated</span>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center font-bold text-white shadow-md">
                    A
                </div>
            </div>
        </header>

        <!-- MAIN BODY VIEW CONTAINER -->
        <main class="flex-1 overflow-y-auto p-4">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>

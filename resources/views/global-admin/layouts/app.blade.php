<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Global Admin') — UPLYFT</title>
    <meta name="description" content="UPLYFT Global Administration Panel" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #0d0f1a;
            --surface:     #141628;
            --surface2:    #1c1f38;
            --border:      #2a2d4a;
            --accent:      #6c63ff;
            --accent2:     #a78bfa;
            --success:     #22d3a0;
            --danger:      #f87171;
            --warning:     #fbbf24;
            --text:        #e2e8f0;
            --text-muted:  #94a3b8;
            --radius:      12px;
            --shadow:      0 4px 24px rgba(0,0,0,0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ─────────────────────────────────── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 28px 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border);
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .logo-text { font-size: 18px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-sub  { font-size: 10px; color: var(--accent2); letter-spacing: 2px; text-transform: uppercase; }

        .sidebar-nav { flex: 1; padding: 20px 12px; }
        .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            padding: 12px 12px 6px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px; font-weight: 500;
            transition: all .2s;
            margin-bottom: 2px;
        }
        .nav-link:hover, .nav-link.active {
            background: var(--surface2);
            color: var(--text);
        }
        .nav-link.active { color: var(--accent2); }
        .nav-link .icon { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ── Main ────────────────────────────────────── */
        .main {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 32px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            position: sticky; top: 0; z-index: 50;
        }
        .breadcrumb { font-size: 14px; color: var(--text-muted); }
        .breadcrumb span { color: var(--text); font-weight: 600; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .admin-badge {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            padding: 4px 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700; letter-spacing: 1px;
        }

        .content { padding: 32px; flex: 1; }

        /* ── Components ──────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .card-title { font-size: 16px; font-weight: 700; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none;
            transition: all .2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
        }
        .btn-primary:hover { opacity: .88; transform: translateY(-1px); }
        .btn-ghost {
            background: var(--surface2); color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent2); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { opacity: .85; }
        .btn-success { background: var(--success); color: #0d0f1a; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        .badge {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .badge-green  { background: rgba(34,211,160,.15); color: var(--success); }
        .badge-red    { background: rgba(248,113,113,.15); color: var(--danger); }
        .badge-purple { background: rgba(108,99,255,.2);  color: var(--accent2); }
        .badge-yellow { background: rgba(251,191,36,.15); color: var(--warning); }
        .badge-blue   { background: rgba(96,165,250,.15); color: #60a5fa; }

        table { width: 100%; border-collapse: collapse; }
        th {
            font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-muted); padding: 10px 14px; text-align: left;
            border-bottom: 1px solid var(--border);
        }
        td { padding: 13px 14px; border-bottom: 1px solid rgba(42,45,74,.5); font-size: 14px; }
        tr:hover td { background: var(--surface2); }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 7px; color: var(--text-muted); }
        input, select, textarea {
            width: 100%; padding: 10px 14px;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 8px; color: var(--text); font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--accent);
        }
        .form-error { font-size: 12px; color: var(--danger); margin-top: 4px; }

        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 0; border-bottom: 1px solid rgba(42,45,74,.5);
        }
        .toggle-label { font-size: 14px; font-weight: 500; }
        .toggle-switch {
            position: relative; width: 44px; height: 24px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-track {
            position: absolute; inset: 0;
            background: var(--surface2); border-radius: 24px;
            cursor: pointer; transition: .3s;
            border: 1px solid var(--border);
        }
        .toggle-track::before {
            content: ''; position: absolute;
            width: 16px; height: 16px;
            left: 4px; top: 3px;
            background: var(--text-muted);
            border-radius: 50%; transition: .3s;
        }
        .toggle-switch input:checked + .toggle-track {
            background: var(--accent);
            border-color: var(--accent);
        }
        .toggle-switch input:checked + .toggle-track::before {
            transform: translateX(18px);
            background: #fff;
        }

        /* Alert flash messages */
        .alert {
            padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;
            font-size: 14px; font-weight: 500;
        }
        .alert-success { background: rgba(34,211,160,.12); border: 1px solid rgba(34,211,160,.3); color: var(--success); }
        .alert-error   { background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.3); color: var(--danger); }

        /* Stat cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .stat-value { font-size: 32px; font-weight: 800; margin-top: 6px; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .page-title  { font-size: 22px; font-weight: 800; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🎓</div>
        <div>
            <div class="logo-text">UPLYFT</div>
            <div class="logo-sub">Global Admin</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="{{ route('global-admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('global-admin.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>

        <div class="nav-section-label">Institutes</div>
        <a href="{{ route('global-admin.institutes.index') }}"
           class="nav-link {{ request()->routeIs('global-admin.institutes.*') ? 'active' : '' }}">
            <span class="icon">🏫</span> All Institutes
        </a>
        <a href="{{ route('global-admin.institutes.create') }}"
           class="nav-link {{ request()->routeIs('global-admin.institutes.create') ? 'active' : '' }}">
            <span class="icon">➕</span> Register New
        </a>

        <div class="nav-section-label">Account</div>
        <a href="{{ route('profile.edit') }}" class="nav-link">
            <span class="icon">👤</span> Profile
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;width:100%;text-align:left;color:var(--text-muted);">
                <span class="icon">🚪</span> Logout
            </button>
        </form>
    </nav>

    <div class="sidebar-footer">
        UPLYFT v1.0 &mdash; Module 1
    </div>
</aside>

<!-- Main -->
<main class="main">
    <header class="topbar">
        <div class="breadcrumb">
            Global Admin &rsaquo; <span>@yield('breadcrumb', 'Dashboard')</span>
        </div>
        <div class="topbar-actions">
            <span class="admin-badge">GLOBAL ADMIN</span>
            <span style="font-size:13px;color:var(--text-muted)">{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </header>

    <div class="content">

        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

</body>
</html>

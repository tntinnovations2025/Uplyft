<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Principal Portal') — UPLYFT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f19;
            --surface: #121827;
            --surface2: #1e293b;
            --border: #2e3d56;
            --accent: #6c63ff;
            --accent-hover: #5b52e0;
            --accent2: #00ced1;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --danger: #ff4757;
            --success: #2ed573;
            --warning: #ffa502;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: sticky;
            top: 0;
            left: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            flex-shrink: 0;
            overflow-y: auto;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px;
        }
        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(108,99,255,0.3);
        }
        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin: 12px 12px 6px;
            font-weight: 600;
        }

        .nav-links { display: flex; flex-direction: column; gap: 4px; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .nav-item:hover, .nav-item.active {
            background: var(--surface2);
            color: var(--text);
        }
        .nav-item.active {
            border-left: 3px solid var(--accent);
            background: linear-gradient(90deg, rgba(108,99,255,0.15), transparent);
            color: #fff;
        }

        /* Main Content */
        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; min-height: 100vh; }
        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(18, 24, 39, 0.85);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .breadcrumb { font-size: 14px; color: var(--text-muted); }
        .breadcrumb span { color: var(--text); font-weight: 600; }

        .content { padding: 32px; flex: 1; overflow-y: auto; }

        /* UI Cards & Buttons */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 18px;
            font-weight: 700;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: #fff;
            box-shadow: 0 4px 12px rgba(108,99,255,0.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(108,99,255,0.35); }
        .btn-ghost { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--border); }
        .btn-danger { background: rgba(255,71,87,0.15); color: var(--danger); border: 1px solid rgba(255,71,87,0.3); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* Tables & Badges */
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); font-weight: 600; }
        td { padding: 14px 16px; font-size: 14px; border-bottom: 1px solid var(--border); }
        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-green { background: rgba(46,213,115,0.15); color: var(--success); border: 1px solid rgba(46,213,115,0.3); }
        .badge-yellow { background: rgba(255,165,2,0.15); color: var(--warning); border: 1px solid rgba(255,165,2,0.3); }
        .badge-purple { background: rgba(108,99,255,0.15); color: var(--accent); border: 1px solid rgba(108,99,255,0.3); }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text); }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            outline: none;
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--accent); }
        .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

        /* Alert Toast */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success { background: rgba(46,213,115,0.15); border: 1px solid rgba(46,213,115,0.3); color: var(--success); }
        .alert-error { background: rgba(255,71,87,0.15); border: 1px solid rgba(255,71,87,0.3); color: var(--danger); }
        .alert-warning { background: rgba(255,165,2,0.15); border: 1px solid rgba(255,165,2,0.3); color: var(--warning); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">🎓</div>
            <div>
                <div class="brand-title">UPLYFT</div>
                <div style="font-size:11px;color:var(--text-muted)">Principal Portal</div>
            </div>
        </div>

        <div class="nav-links">
            <div class="nav-section-title">OVERVIEW</div>
            <a href="{{ route('principal.dashboard') }}" class="nav-item {{ request()->routeIs('principal.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard
            </a>

            <div class="nav-section-title">ACADEMICS & SCHEDULING</div>
            <a href="{{ route('principal.academic-terms.index') }}" class="nav-item {{ request()->routeIs('principal.academic-terms.*') ? 'active' : '' }}">
                <span>📅</span> Academic Terms
            </a>
            <a href="{{ route('principal.rooms.index') }}" class="nav-item {{ request()->routeIs('principal.rooms.*') ? 'active' : '' }}">
                <span>🏢</span> Rooms & Facilities
            </a>
            <a href="{{ route('principal.classes-subjects.index') }}" class="nav-item {{ request()->routeIs('principal.classes-subjects.*') ? 'active' : '' }}">
                <span>📚</span> Classes & Subjects
            </a>
            <a href="{{ route('principal.teachers.availability.index') }}" class="nav-item {{ request()->routeIs('principal.teachers.availability.*') ? 'active' : '' }}">
                <span>⏰</span> Teacher Availability
            </a>
            <a href="{{ route('principal.assignments.index') }}" class="nav-item {{ request()->routeIs('principal.assignments.*') ? 'active' : '' }}">
                <span>📌</span> Assign Subject &amp; Teacher
            </a>
            <a href="{{ route('principal.timetables.index') }}" class="nav-item {{ request()->routeIs('principal.timetables.*') ? 'active' : '' }}">
                <span>🗓️</span> Timetable Matrix
            </a>

            <div class="nav-section-title">STAFF & GOVERNANCE</div>
            <a href="{{ route('principal.staff.index') }}" class="nav-item {{ request()->routeIs('principal.staff.*') ? 'active' : '' }}">
                <span>👥</span> Faculty & Staff Roster
            </a>

            <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--border)">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item" style="width:100%;border:none;background:none;cursor:pointer;color:var(--danger)">
                        <span>🚪</span> Log Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="breadcrumb">
                Principal Portal &rsaquo; <span>@yield('breadcrumb', 'Overview')</span>
            </div>
            <div style="display:flex;align-items:center;gap:16px">
                @php
                    $activeTermNav = \App\Models\AcademicTerm::where('institute_id', auth()->user()->institute_id)->where('is_active', true)->first();
                @endphp
                @if($activeTermNav)
                    <span class="badge badge-green">Session: {{ $activeTermNav->name }}</span>
                @else
                    <span class="badge badge-yellow">⚠️ No Active Term</span>
                @endif
                <div style="font-size:14px;font-weight:600">{{ auth()->user()->name }}</div>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅ {{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>

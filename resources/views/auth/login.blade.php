<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Uplyft Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
        }
        .blob-1 { width: 500px; height: 500px; background: #6366f1; top: -150px; left: -100px; }
        .blob-2 { width: 400px; height: 400px; background: #06b6d4; bottom: -100px; right: -80px; }
        .glass-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
        }
        .glass-input {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255,255,255,0.12);
            color: #ffffff;
            transition: all 0.2s ease;
            border-radius: 12px;
            padding: 12px 16px;
            width: 100%;
            font-size: 14px;
        }
        .glass-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
        }
        .glass-input::placeholder { color: #64748b; }
        .submit-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99,102,241,0.35);
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99,102,241,0.5);
        }
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="w-full max-w-md px-4">
        <div class="glass-card p-8 shadow-2xl">
            
            <!-- BRAND HEADER -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4 shadow-xl shadow-indigo-600/40">
                    U
                </div>
                <h1 style="font-family: 'Outfit', sans-serif;" class="text-2xl font-bold text-white">
                    UPLYFT <span class="text-indigo-400">Portal</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1">Sign in with your assigned credentials</p>
            </div>

            <!-- FLASH SUCCESS -->
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- ERRORS -->
            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- LOGIN FORM -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        <i class="fa-solid fa-id-badge mr-1 text-indigo-400"></i>
                        Roll Number / Employee ID / Email
                    </label>
                    <input type="text" name="login_id" value="{{ old('login_id') }}"
                           placeholder="STD-2026-0001 or your email"
                           class="glass-input" required autofocus>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        <i class="fa-solid fa-lock mr-1 text-indigo-400"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="passwordField"
                               placeholder="Enter your password"
                               class="glass-input pr-10" required>
                        <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition">
                            <i class="fa-solid fa-eye text-xs" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="remember" id="remember" class="accent-indigo-500 w-3.5 h-3.5">
                    <label for="remember" class="text-xs text-slate-400">Keep me signed in</label>
                </div>

                <button type="submit" class="submit-btn w-full py-3.5 rounded-xl text-white font-semibold text-sm mt-2">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Sign In to Portal
                </button>
            </form>

            <!-- ROLE HINT BADGES -->
            <div class="mt-6 pt-5 border-t border-white/10">
                <p class="text-[10px] text-slate-500 text-center mb-3 font-medium uppercase tracking-wide">Login Portal Roles</p>
                <div class="flex items-center justify-center gap-2 flex-wrap">
                    <span class="role-badge bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                        <i class="fa-solid fa-user-tie text-[9px]"></i> Admin
                    </span>
                    <span class="role-badge bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                        <i class="fa-solid fa-chalkboard-user text-[9px]"></i> Teacher
                    </span>
                    <span class="role-badge bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                        <i class="fa-solid fa-user-graduate text-[9px]"></i> Student
                    </span>
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-600 mt-4">
            Uplyft Multi-Institute SaaS &bull; Laravel 11 &bull; Developer B Portal
        </p>
    </div>

    <script>
        function togglePwd() {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('eyeIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

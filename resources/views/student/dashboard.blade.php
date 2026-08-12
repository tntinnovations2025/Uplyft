@extends('layouts.app')

@section('title', 'My Student Portal')
@section('page-header', 'Student Portal')

@section('content')
<div class="space-y-6">

    <!-- WELCOME BANNER -->
    <div class="glass-panel p-6 bg-gradient-to-r from-emerald-900/50 via-slate-900/60 to-indigo-900/40 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-full bg-emerald-500/30 text-emerald-300 flex items-center justify-center text-xl font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-xs text-slate-400">
                        Roll Number: <span class="text-emerald-400 font-bold">{{ auth()->user()->login_id }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-300 text-[11px] font-semibold border border-emerald-500/20">
                    <i class="fa-solid fa-user-graduate mr-1"></i> Student Account
                </span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 text-[11px] font-semibold border border-indigo-500/20">
                    <i class="fa-solid fa-building mr-1"></i> Uplyft Academy
                </span>
            </div>
        </div>
    </div>

    <!-- METRICS GRID / WIDGETS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Widget 1: GPA/Grades Summary -->
        <div class="glass-panel p-6 flex flex-col items-center justify-center gap-2 relative overflow-hidden">
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Current CGPA</h3>
            <div class="text-4xl font-bold text-white mt-2 mb-1">3.8<span class="text-xl text-slate-500">/4.0</span></div>
            <div class="flex items-center gap-1 text-emerald-400 text-xs font-semibold">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>Top 10% of class</span>
            </div>
        </div>

        <!-- Widget 2: Attendance Donut Chart (Simulated) -->
        <div class="glass-panel p-6 flex flex-col items-center justify-center gap-4 relative overflow-hidden">
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wider w-full text-center">Attendance</h3>
            
            <!-- CSS Donut Chart representation -->
            <div class="relative w-24 h-24 rounded-full flex items-center justify-center" style="background: conic-gradient(#10b981 0% 92%, #334155 92% 100%);">
                <div class="absolute inset-0 m-3 bg-slate-900 rounded-full flex items-center justify-center flex-col">
                    <span class="text-lg font-bold text-white">92%</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-400">Excellent Standing</p>
        </div>

        <!-- Widget 3: Upcoming Deadlines -->
        <div class="glass-panel p-6 flex flex-col gap-3 relative overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Upcoming Deadlines</h3>
                <span class="px-2 py-0.5 rounded text-[10px] bg-red-500/20 text-red-400 font-bold border border-red-500/30">2 Due Soon</span>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <i class="fa-solid fa-file-pdf text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">CS-201 Midterm Essay</p>
                            <p class="text-xs text-slate-400">Computer Science Dept</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-red-400">Tomorrow</p>
                        <p class="text-[10px] text-slate-500">11:59 PM</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                            <i class="fa-solid fa-laptop-code text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Math-104 Online Quiz</p>
                            <p class="text-xs text-slate-400">Mathematics Dept</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-amber-400">In 3 Days</p>
                        <p class="text-[10px] text-slate-500">08:00 AM</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Quick Action / Invoice -->
    <div class="glass-panel p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border border-indigo-500/30 bg-indigo-500/5 relative overflow-hidden">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Fall Semester Fee Invoice</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Your latest fee voucher has been generated and is due on Oct 15th.</p>
            </div>
        </div>
        
        <button class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition">
            <i class="fa-solid fa-download"></i>
            <span>Download Invoice PDF</span>
        </button>
    </div>

    <!-- LOGOUT LINK -->
    <div class="text-right">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition flex items-center gap-1.5 ml-auto">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>
@endsection

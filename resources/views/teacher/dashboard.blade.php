@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-header', 'Teacher Portal')

@section('content')
<div class="space-y-6">

    <!-- WELCOME BANNER -->
    <div class="glass-panel p-6 bg-gradient-to-r from-cyan-900/50 via-slate-900/60 to-indigo-900/40 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-full bg-cyan-500/30 text-cyan-300 flex items-center justify-center text-xl font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Welcome back, Professor {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-xs text-slate-400">
                        Employee ID: <span class="text-cyan-400 font-bold">{{ auth()->user()->login_id }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-cyan-500/15 text-cyan-300 text-[11px] font-semibold border border-cyan-500/20">
                    <i class="fa-solid fa-chalkboard-user mr-1"></i> Faculty Member
                </span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 text-[11px] font-semibold border border-indigo-500/20">
                    <i class="fa-solid fa-building mr-1"></i> Uplyft Academy
                </span>
            </div>
        </div>
    </div>

    <!-- METRICS GRID / WIDGETS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Widget 1: Today's Classes -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-cyan-500/50 transition duration-300">
            <div class="flex justify-between items-start">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <span class="px-2 py-1 rounded-md bg-cyan-500/10 text-cyan-300 text-[10px] font-bold">3 Classes</span>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Today's Classes</h3>
                <p class="text-xs text-slate-400 leading-relaxed">View your schedule for today and manage lecture materials.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5 flex flex-col gap-2">
                <div class="flex items-center justify-between text-xs text-slate-300 bg-white/5 p-2 rounded-lg">
                    <span><i class="fa-regular fa-clock mr-1 text-cyan-400"></i> 09:00 AM</span>
                    <span class="font-semibold text-white">CS-101 Intro</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-300 bg-white/5 p-2 rounded-lg">
                    <span><i class="fa-regular fa-clock mr-1 text-cyan-400"></i> 11:30 AM</span>
                    <span class="font-semibold text-white">CS-201 Advanced</span>
                </div>
            </div>
        </div>

        <!-- Widget 2: Pending Assignments -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-amber-500/50 transition duration-300">
            <div class="flex justify-between items-start">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-pen"></i>
                </div>
                <span class="px-2 py-1 rounded-md bg-amber-500/10 text-amber-300 text-[10px] font-bold">12 Pending</span>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Assignments to Grade</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Review submissions and provide feedback for your students.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <button class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-xs font-semibold border border-amber-500/15 transition">
                    <i class="fa-solid fa-check-double text-[10px]"></i>
                    <span>Grade Submissions</span>
                </button>
            </div>
        </div>

        <!-- Widget 3: Quick Attendance -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-emerald-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Quick Attendance</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Launch the interactive roster to mark attendance for your current class.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <button class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-play text-[10px]"></i>
                    <span>Start Roster for CS-101</span>
                </button>
            </div>
        </div>
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

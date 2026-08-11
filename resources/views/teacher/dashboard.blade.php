@extends('layouts.app')

@section('title', 'Teacher Portal')
@section('page-header', 'Teacher Operations Portal')

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
                    <h1 class="text-xl font-bold text-white">Welcome, {{ auth()->user()->name }}! 🎓</h1>
                    <p class="text-xs text-slate-400">
                        Employee ID: <span class="text-cyan-400 font-bold">{{ auth()->user()->login_id }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-cyan-500/15 text-cyan-300 text-[11px] font-semibold border border-cyan-500/20">
                    <i class="fa-solid fa-chalkboard-user mr-1"></i> Faculty Account
                </span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 text-[11px] font-semibold border border-indigo-500/20">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Tenant Isolated
                </span>
            </div>
        </div>
    </div>

    <!-- MODULE PLACEHOLDER CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Placeholder 1: My Classes -->
        <div class="glass-panel p-6 flex flex-col gap-4 group hover:border-cyan-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-people-roof"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">My Assigned Classes</h3>
                <p class="text-xs text-slate-400 leading-relaxed">View all class assignments, student rosters per subject, academic term schedules, and course materials distribution.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-cyan-500/10 text-cyan-300 text-[10px] font-semibold border border-cyan-500/15">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    <span>Coming in Module 6</span>
                </div>
            </div>
        </div>

        <!-- Placeholder 2: Mark Attendance -->
        <div class="glass-panel p-6 flex flex-col gap-4 group hover:border-emerald-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Mark Attendance Roster</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Mark class attendance quickly. Toggle student presence statuses (Present, Absent, Late, Leave) and bulk-save records per date.
                </p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition">
                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    <span>Open Attendance Roster</span>
                </a>
            </div>
        </div>

        <!-- Placeholder 3: Upload Subject Books -->
        <div class="glass-panel p-6 flex flex-col gap-4 group hover:border-amber-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">Upload Subject Books</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Upload course materials, textbooks, syllabi, and reference documents in PDF format for student access via the LMS portal.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-300 text-[10px] font-semibold border border-amber-500/15">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    <span>Module 6 — LMS Integration</span>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTION LINK TO ATTENDANCE -->
    <div class="glass-panel p-4 flex items-center justify-between">
        <div class="flex items-center gap-3 text-sm text-slate-300">
            <i class="fa-solid fa-circle-info text-indigo-400"></i>
            <span>You can mark attendance for your classes from the admin portal while Teacher Portal is being built out.</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
            @csrf
            <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition flex items-center gap-1.5">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page-header', 'Dashboard Overview')

@section('content')


<div class="space-y-6">

    <!-- WELCOME HERO BANNER -->
    <div class="glass-panel p-6 bg-gradient-to-r from-indigo-900/50 via-slate-900/60 to-cyan-900/40 relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-2xl font-bold text-white mb-2">Welcome to Uplyft Operations Center</h1>
            <p class="text-sm text-slate-300 leading-relaxed">
                Centralized multi-tenant portal for student admissions, staff onboarding, and daily academic attendance management.
            </p>
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Students -->
        <div class="glass-panel p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Registered Students</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($totalStudents) }}</h3>
                <p class="text-[11px] text-emerald-400 mt-1"><i class="fa-solid fa-arrow-up text-[9px] mr-1"></i> Module 4 Active</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <!-- Metric 2: Teachers -->
        <div class="glass-panel p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-medium">Onboarded Teachers</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($totalTeachers) }}</h3>
                <p class="text-[11px] text-cyan-400 mt-1"><i class="fa-solid fa-file-check text-[9px] mr-1"></i> Transcripts Verified</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <!-- Metric 3: Attendance -->
        <div class="glass-panel p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-medium">Daily Attendance Logs</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($totalAttendance) }}</h3>
                <p class="text-[11px] text-emerald-400 mt-1"><i class="fa-solid fa-clock text-[9px] mr-1"></i> Module 5 Active</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
        </div>

        <!-- Metric 4: Seat Availability -->
        <div class="glass-panel p-5 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-medium">Seat Availability / Capacity</p>
                <h3 class="text-lg font-bold text-white mt-1 truncate max-w-[140px]">{{ number_format($totalStudents) }} / 500</h3>
                <p class="text-[11px] text-amber-400 mt-1"><i class="fa-solid fa-chair text-[9px] mr-1"></i> {{ 500 - $totalStudents }} Seats Left</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
        </div>
    </div>

    <!-- ACTION CARDS NAVIGATION -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Action 1: Admissions -->
        <div class="glass-panel p-6 flex flex-col justify-between hover:border-indigo-500/50 transition duration-300">
            <div>
                <div class="w-10 h-10 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center text-lg mb-4">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Manage Students</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">
                    Register applicants, configure tax status (filer vs. non-filer), preview real-time fee ledgers, and download PDF invoices.
                </p>
            </div>
            <a href="{{ route('admissions.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition">
                <span>Launch Admissions Portal</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Action 2: Teacher Onboarding -->
        <div class="glass-panel p-6 flex flex-col justify-between hover:border-cyan-500/50 transition duration-300">
            <div>
                <div class="w-10 h-10 rounded-lg bg-cyan-600/30 text-cyan-400 flex items-center justify-center text-lg mb-4">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Manage Teachers</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">
                    Onboard new faculty members, record qualifications, and securely upload academic transcripts under isolated tenant paths.
                </p>
            </div>
            <a href="{{ route('teachers.onboarding') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition">
                <span>Launch Onboarding Portal</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Action 3: Attendance -->
        <div class="glass-panel p-6 flex flex-col justify-between hover:border-emerald-500/50 transition duration-300">
            <div>
                <div class="w-10 h-10 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center text-lg mb-4">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Global Attendance Overview</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">
                    Inspect class rosters by term and date, toggle student statuses (Present, Absent, Late, Leave), and log bulk records.
                </p>
            </div>
            <a href="{{ route('attendance.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition">
                <span>Launch Attendance Roster</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
@endsection

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

    <!-- MODULE PLACEHOLDER CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Placeholder 1: My Fee Ledger -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-indigo-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">My Fee Ledger & Invoices</h3>
                <p class="text-xs text-slate-400 leading-relaxed">View outstanding fees, payment history, tax-adjusted admission invoices, and downloadable PDF receipts.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-300 text-[10px] font-semibold border border-indigo-500/15">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    <span>Coming in Module 6</span>
                </div>
            </div>
        </div>

        <!-- Placeholder 2: My Attendance -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-emerald-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">My Attendance Log</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Track your daily attendance records by term. View Present, Absent, Late, and Leave status logs per academic term.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-300 text-[10px] font-semibold border border-emerald-500/15">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    <span>Coming in Module 6</span>
                </div>
            </div>
        </div>

        <!-- Placeholder 3: My Exams -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-amber-500/50 transition duration-300">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base mb-1">My Exams & Marks</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Access exam schedules, view graded results, AI-automated mark sheets, and end-of-year grade normalization reports.</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-300 text-[10px] font-semibold border border-amber-500/15">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    <span>Module 6 — LMS & AI Grading</span>
                </div>
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

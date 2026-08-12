@extends('layouts.app')

@section('title', 'Assignments & Exams')
@section('page-header', 'Learning Management System')

@section('content')
<div class="space-y-6">
    <div class="glass-panel p-6 bg-gradient-to-r from-indigo-900/40 to-slate-900/60 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Assignments & Exams</h2>
            <p class="text-xs text-slate-400">Manage your course submissions and upcoming assessments.</p>
        </div>
        <span class="px-3 py-1 rounded text-xs bg-amber-500/20 text-amber-400 font-bold border border-amber-500/30">Module 6 LMS Preview</span>
    </div>

    <div class="glass-panel overflow-hidden">
        
        <!-- Tabs -->
        <div class="flex border-b border-white/10 bg-slate-900/40">
            <button class="px-6 py-4 text-sm font-semibold text-indigo-400 border-b-2 border-indigo-500 bg-white/5">
                Upcoming Deadlines
            </button>
            <button class="px-6 py-4 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                Past Submissions
            </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6 space-y-4">
            
            <!-- Item 1 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10 hover:border-indigo-500/30 transition">
                <div class="flex items-start sm:items-center gap-4 mb-3 sm:mb-0">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[9px] bg-slate-700 text-slate-300 font-bold uppercase tracking-wider">CS-201</span>
                            <span class="px-2 py-0.5 rounded text-[9px] bg-red-500/20 text-red-400 font-bold uppercase tracking-wider">Due Tomorrow</span>
                        </div>
                        <h4 class="text-sm font-bold text-white">Midterm Essay: History of Computing</h4>
                        <p class="text-xs text-slate-400 mt-1">Please submit your PDF strictly following the format guidelines.</p>
                    </div>
                </div>
                <button class="w-full sm:w-auto px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shrink-0">
                    Submit Assignment
                </button>
            </div>

            <!-- Item 2 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl bg-white/5 border border-white/10 hover:border-cyan-500/30 transition">
                <div class="flex items-start sm:items-center gap-4 mb-3 sm:mb-0">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[9px] bg-slate-700 text-slate-300 font-bold uppercase tracking-wider">MATH-104</span>
                            <span class="px-2 py-0.5 rounded text-[9px] bg-amber-500/20 text-amber-400 font-bold uppercase tracking-wider">Due in 3 Days</span>
                        </div>
                        <h4 class="text-sm font-bold text-white">Online Quiz 2: Matrices</h4>
                        <p class="text-xs text-slate-400 mt-1">45-minute timed quiz covering chapters 3 and 4.</p>
                    </div>
                </div>
                <button class="w-full sm:w-auto px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition shrink-0">
                    Start Quiz
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

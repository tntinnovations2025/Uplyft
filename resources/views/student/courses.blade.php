@extends('layouts.app')

@section('title', 'My Courses')
@section('page-header', 'Enrolled Courses')

@section('content')
<div class="space-y-6">
    <div class="glass-panel p-6 bg-gradient-to-r from-indigo-900/40 to-slate-900/60 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Fall 2026 Semester</h2>
            <p class="text-xs text-slate-400">View course materials, syllabus, and announcements.</p>
        </div>
        <span class="px-3 py-1 rounded text-xs bg-amber-500/20 text-amber-400 font-bold border border-amber-500/30">Module 6 Preview</span>
    </div>

    <!-- Course Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Course 1 -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-indigo-500/50 transition duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-bl-full -mr-4 -mt-4"></div>
            <div>
                <span class="px-2 py-1 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-bold">CS-101</span>
                <h3 class="font-bold text-white text-lg mt-2 mb-1 z-10 relative">Intro to Computer Science</h3>
                <p class="text-xs text-slate-400 z-10 relative">Prof. Alan Turing</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5 flex gap-2">
                <button class="flex-1 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition">View Course</button>
            </div>
        </div>

        <!-- Course 2 -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-cyan-500/50 transition duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/10 rounded-bl-full -mr-4 -mt-4"></div>
            <div>
                <span class="px-2 py-1 rounded-md bg-cyan-500/20 text-cyan-300 text-[10px] font-bold">MATH-201</span>
                <h3 class="font-bold text-white text-lg mt-2 mb-1 z-10 relative">Linear Algebra</h3>
                <p class="text-xs text-slate-400 z-10 relative">Prof. John Nash</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5 flex gap-2">
                <button class="flex-1 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold transition">View Course</button>
            </div>
        </div>

        <!-- Course 3 -->
        <div class="glass-panel p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-emerald-500/50 transition duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full -mr-4 -mt-4"></div>
            <div>
                <span class="px-2 py-1 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">ENG-105</span>
                <h3 class="font-bold text-white text-lg mt-2 mb-1 z-10 relative">Technical Communication</h3>
                <p class="text-xs text-slate-400 z-10 relative">Dr. Emily Bronte</p>
            </div>
            <div class="mt-auto pt-4 border-t border-white/5 flex gap-2">
                <button class="flex-1 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition">View Course</button>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'My Schedule')
@section('page-header', 'Teacher Schedule')

@section('content')
<div class="space-y-6">
    <div class="glass-panel p-6 bg-gradient-to-r from-cyan-900/40 to-slate-900/60 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Weekly Teaching Schedule</h2>
            <p class="text-xs text-slate-400">Your assigned classes for the current semester.</p>
        </div>
        <span class="px-3 py-1 rounded text-xs bg-amber-500/20 text-amber-400 font-bold border border-amber-500/30">Module 6 Preview</span>
    </div>

    <!-- Static HTML Calendar Grid -->
    <div class="glass-panel p-6">
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <div class="grid grid-cols-6 gap-4 text-center mb-4">
                    <div class="text-xs font-semibold text-slate-500 uppercase">Time</div>
                    <div class="text-xs font-semibold text-white uppercase">Monday</div>
                    <div class="text-xs font-semibold text-white uppercase">Tuesday</div>
                    <div class="text-xs font-semibold text-white uppercase">Wednesday</div>
                    <div class="text-xs font-semibold text-white uppercase">Thursday</div>
                    <div class="text-xs font-semibold text-white uppercase">Friday</div>
                </div>

                <!-- 09:00 AM -->
                <div class="grid grid-cols-6 gap-4 mb-4">
                    <div class="text-xs text-slate-400 text-center py-3">09:00 AM</div>
                    <div class="bg-cyan-500/10 border border-cyan-500/20 rounded-lg p-3 text-left">
                        <div class="text-sm font-bold text-cyan-300">CS-101</div>
                        <div class="text-[10px] text-slate-400">Lab A (BS-CS)</div>
                    </div>
                    <div class="bg-transparent border border-dashed border-white/10 rounded-lg p-3"></div>
                    <div class="bg-cyan-500/10 border border-cyan-500/20 rounded-lg p-3 text-left">
                        <div class="text-sm font-bold text-cyan-300">CS-101</div>
                        <div class="text-[10px] text-slate-400">Lab A (BS-CS)</div>
                    </div>
                    <div class="bg-transparent border border-dashed border-white/10 rounded-lg p-3"></div>
                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-3 text-left">
                        <div class="text-sm font-bold text-emerald-300">CS-305</div>
                        <div class="text-[10px] text-slate-400">Hall B (BS-SE)</div>
                    </div>
                </div>

                <!-- 11:30 AM -->
                <div class="grid grid-cols-6 gap-4 mb-4">
                    <div class="text-xs text-slate-400 text-center py-3">11:30 AM</div>
                    <div class="bg-transparent border border-dashed border-white/10 rounded-lg p-3"></div>
                    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-3 text-left">
                        <div class="text-sm font-bold text-indigo-300">SE-202</div>
                        <div class="text-[10px] text-slate-400">Room 405 (BS-SE)</div>
                    </div>
                    <div class="bg-transparent border border-dashed border-white/10 rounded-lg p-3"></div>
                    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-3 text-left">
                        <div class="text-sm font-bold text-indigo-300">SE-202</div>
                        <div class="text-[10px] text-slate-400">Room 405 (BS-SE)</div>
                    </div>
                    <div class="bg-transparent border border-dashed border-white/10 rounded-lg p-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Attendance Record')
@section('page-header', 'Attendance Record')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="glass-panel p-6 flex flex-col items-center justify-center gap-4">
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wider w-full text-center">Overall Attendance</h3>
            
            <div class="relative w-32 h-32 rounded-full flex items-center justify-center" style="background: conic-gradient(#10b981 0% {{ $percentage }}%, #334155 {{ $percentage }}% 100%);">
                <div class="absolute inset-0 m-4 bg-slate-900 rounded-full flex items-center justify-center flex-col">
                    <span class="text-2xl font-bold text-white">{{ $percentage }}%</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-400">Total Days: {{ $totalDays }} | Present: {{ $presentDays }}</p>
        </div>

        <!-- Detailed List -->
        <div class="glass-panel p-6 md:col-span-2">
            <h3 class="text-lg font-bold text-white mb-4">Daily Logs</h3>
            <div class="overflow-y-auto max-h-[300px] pr-2 space-y-2 custom-scrollbar">
                @forelse($attendances as $log)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/10">
                        <div class="flex items-center gap-3">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span class="text-sm font-medium text-white">{{ $log->date->format('M d, Y') }}</span>
                        </div>
                        <div>
                            @if($log->status === 'Present')
                                <span class="px-3 py-1 rounded text-[10px] bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">Present</span>
                            @elseif($log->status === 'Absent')
                                <span class="px-3 py-1 rounded text-[10px] bg-red-500/20 text-red-400 font-bold border border-red-500/30">Absent</span>
                            @elseif($log->status === 'Late')
                                <span class="px-3 py-1 rounded text-[10px] bg-amber-500/20 text-amber-400 font-bold border border-amber-500/30">Late</span>
                            @else
                                <span class="px-3 py-1 rounded text-[10px] bg-slate-500/20 text-slate-400 font-bold border border-slate-500/30">Leave</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-sm">
                        No attendance records found for this term.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

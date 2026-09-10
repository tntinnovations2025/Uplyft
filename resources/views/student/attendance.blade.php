@extends('layouts.app')

@section('title', 'Attendance Records')
@section('page-header', 'Attendance Records')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="liquid-glass-card p-6 flex flex-col items-center justify-center gap-4 border border-slate-200/90 shadow-2xs">
            <h3 class="text-slate-500 text-xs font-extrabold uppercase tracking-wider w-full text-center">Overall Attendance</h3>
            
            <div class="relative w-32 h-32 rounded-full flex items-center justify-center shadow-inner" style="background: conic-gradient(#10b981 0% {{ $percentage }}%, #e2e8f0 {{ $percentage }}% 100%);">
                <div class="absolute inset-0 m-3.5 bg-white rounded-full flex items-center justify-center flex-col shadow-2xs">
                    <span class="text-2xl font-extrabold text-slate-900 font-display">{{ $percentage }}%</span>
                </div>
            </div>
            
            <p class="text-xs text-slate-600 font-medium">Total Sessions: <strong class="text-slate-900">{{ $totalDays }}</strong> | Attended: <strong class="text-emerald-700">{{ $presentDays }}</strong></p>
        </div>

        <!-- Detailed Daily Logs -->
        <div class="liquid-glass-card p-6 md:col-span-2 border border-slate-200/90 shadow-2xs">
            <h3 class="text-lg font-extrabold text-slate-900 mb-4 font-display flex items-center gap-2">
                <i class="fa-solid fa-clipboard-user text-pink-600"></i> Daily Attendance Logs
            </h3>
            <div class="overflow-y-auto max-h-[300px] pr-2 space-y-2.5 custom-scrollbar">
                @forelse($attendances as $log)
                    @php
                        $st = strtolower($log->status);
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/90 hover:bg-pink-50/30 transition">
                        <div class="flex items-center gap-3">
                            <i class="fa-regular fa-calendar text-pink-600"></i>
                            <span class="text-xs font-bold text-slate-800">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</span>
                        </div>
                        <div>
                            @if($st === 'present')
                                <span class="badge badge-emerald text-xs font-bold">Present</span>
                            @elseif($st === 'absent')
                                <span class="badge badge-rose text-xs font-bold">Absent</span>
                            @elseif($st === 'late')
                                <span class="badge badge-amber text-xs font-bold">Late</span>
                            @else
                                <span class="badge badge-slate text-xs font-bold">Leave</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-xs font-medium">
                        No attendance records found for this term.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Subject-Wise Attendance Breakdown -->
    @if(isset($subjectWiseAttendance) && $subjectWiseAttendance->isNotEmpty())
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs">
            <h3 class="text-lg font-extrabold text-slate-900 mb-4 flex items-center gap-2 font-display">
                <i class="fa-solid fa-book text-pink-600"></i> Subject-Wise Attendance Percentage
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($subjectWiseAttendance as $subAtt)
                    @php
                        $subPct = $subAtt['percentage'];
                        $badgeClass = $subPct >= 75 ? 'badge-emerald' : ($subPct >= 50 ? 'badge-amber' : 'badge-rose');
                        $barColor = $subPct >= 75 ? '#10b981' : ($subPct >= 50 ? '#f59e0b' : '#f43f5e');
                    @endphp
                    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-pink-700 font-bold uppercase tracking-wider font-mono">{{ $subAtt['subject_code'] }}</div>
                            <div class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $subAtt['subject_name'] }}</div>
                            <div class="text-xs text-slate-500 font-medium mt-1">
                                {{ $subAtt['present_sessions'] }} / {{ $subAtt['total_sessions'] }} Sessions
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge {{ $badgeClass }} text-xs font-extrabold">
                                {{ $subPct }}%
                            </span>
                            <div class="w-16 h-1.5 bg-slate-200 rounded-full mt-2 overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $subPct }}%; background-color: {{ $barColor }};"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

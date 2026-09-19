@extends('layouts.app')

@section('title', 'Timetable')
@section('page-header', 'Timetable')

@section('content')
<div class="space-y-6">
    <!-- TOP BANNER -->
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden glass-specular-top">
        <div class="relative z-10">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 flex items-center gap-2.5 font-display">
                <i class="fa-solid fa-calendar-days text-pink-500"></i>
                <span>Timetable</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Your assigned classes, sections, and laboratories for the active session.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge badge-indigo text-xs font-bold">
                <i class="fa-solid fa-graduation-cap"></i> {{ $activeTerm->name ?? 'Active Session' }}
            </span>
            
            <!-- View Mode Switcher -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/90 shadow-2xs">
                <button type="button" onclick="switchView('grid')" id="btn-view-grid" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-pink-600 shadow-xs transition flex items-center gap-1">
                    <i class="fa-solid fa-border-all text-[11px]"></i>
                    <span>Matrix Grid</span>
                </button>
                <button type="button" onclick="switchView('list')" id="btn-view-list" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1">
                    <i class="fa-solid fa-bars-staggered text-[11px]"></i>
                    <span>Horizontal Bars</span>
                </button>
            </div>
        </div>
    </div>

    <!-- VIEW 1: WEEKLY TIMETABLE CALENDAR MATRIX GRID -->
    <div id="view-grid" class="liquid-glass-card p-6 border border-slate-200/90 shadow-sm">
        <div class="overflow-x-auto">
            <div class="min-w-[850px]">
                <div class="grid grid-cols-6 gap-4 text-center mb-4">
                    <div class="text-xs font-extrabold text-pink-700 uppercase tracking-wider py-2.5 bg-pink-50 rounded-xl border border-pink-200/70">TIME</div>
                    @foreach($daysOfWeek as $day)
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider py-2.5 bg-slate-100/90 rounded-xl border border-slate-200 shadow-2xs">
                            {{ ucfirst($day) }}
                        </div>
                    @endforeach
                </div>

                @foreach($timeSlotsList as $tSlot)
                    @php
                        $rowTimeRange = $tSlot;
                        foreach($daysOfWeek as $d) {
                            if (!empty($weeklyGrid[$tSlot][$d]['time_range'])) {
                                $rowTimeRange = $weeklyGrid[$tSlot][$d]['time_range'];
                                break;
                            }
                        }
                    @endphp
                    <div class="grid grid-cols-6 gap-4 mb-4">
                        <div class="text-xs text-pink-700 font-mono font-bold text-center flex flex-col items-center justify-center bg-pink-50/80 rounded-xl border border-pink-200/70 p-3 shadow-2xs">
                            <i class="fa-regular fa-clock text-pink-600 mb-1 text-sm"></i>
                            <span class="text-[11px] font-extrabold text-slate-900 leading-tight">{{ $rowTimeRange }}</span>
                        </div>
                        @foreach($daysOfWeek as $day)
                            @php $cell = $weeklyGrid[$tSlot][$day] ?? null; @endphp
                            @if($cell)
                                @php
                                    $theme = $cell['color_theme'] ?? 'indigo';
                                    $badgeStyle = match($theme) {
                                        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
                                        'amber' => 'bg-amber-50 text-amber-800 border-amber-200/70',
                                        'purple' => 'bg-purple-50 text-purple-700 border-purple-200/70',
                                        'cyan', 'sky' => 'bg-sky-50 text-sky-700 border-sky-200/70',
                                        default => 'bg-indigo-50 text-indigo-700 border-indigo-200/70',
                                    };
                                @endphp
                                <div class="bg-white border border-slate-200/90 hover:border-pink-300 rounded-xl p-3.5 text-left shadow-2xs hover:shadow-xs transition duration-200 flex flex-col justify-between space-y-2">
                                    <div class="text-xs font-extrabold text-slate-900 truncate">
                                        {{ $cell['subject_name'] }}
                                        <span class="text-pink-600 text-[10.5px] font-bold">({{ $cell['subject_code'] }})</span>
                                    </div>
                                    
                                    <!-- ASSIGNED CLASS & SECTION -->
                                    <div class="text-[11px] font-extrabold {{ $badgeStyle }} flex items-center gap-1.5 px-2.5 py-1 rounded-lg border">
                                        <i class="fa-solid fa-chalkboard-user text-[10px]"></i>
                                        <span class="truncate">{{ $cell['section_name'] }}</span>
                                    </div>

                                    <!-- ROOM NUMBER -->
                                    <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/70">
                                        <i class="fa-solid fa-location-dot text-slate-400 text-[10px]"></i>
                                        <span class="truncate">{{ $cell['room_name'] }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="bg-slate-50/50 border border-dashed border-slate-200 rounded-xl p-3 flex items-center justify-center">
                                    <span class="text-[11px] text-slate-400 font-semibold">Free Slot</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- VIEW 2: HORIZONTAL BARS TIMETABLE LIST VIEW -->
    <div id="view-list" class="liquid-glass-card p-6 border border-slate-200/90 shadow-sm hidden">
        <div class="space-y-6">
            @foreach($daysOfWeek as $day)
                @php
                    $daySlots = $slotsData->where('day', $day)->sortBy('start_time');
                @endphp
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <h3 class="text-base font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2 font-display">
                            <i class="fa-regular fa-calendar-check text-pink-500"></i>
                            <span>{{ $day }}</span>
                        </h3>
                        <span class="badge badge-pink text-xs font-bold">
                            {{ $daySlots->count() }} {{ Str::plural('Lecture', $daySlots->count()) }}
                        </span>
                    </div>

                    @if($daySlots->isEmpty())
                        <div class="py-6 text-center text-xs text-slate-500 italic">No assigned classes scheduled for {{ ucfirst($day) }}.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($daySlots as $slotItem)
                                @php
                                    $diffMins = $slotItem['duration_mins'] ?? 60;
                                    $barWidthPercent = min(100, max(25, round(($diffMins / 180) * 100)));
                                @endphp
                                <div class="bg-white border border-slate-200/90 hover:border-pink-300 rounded-xl p-4 transition shadow-2xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="px-3 py-1 rounded-lg bg-pink-50 text-pink-700 font-mono text-xs font-bold border border-pink-200/70 flex items-center gap-1.5">
                                                <i class="fa-regular fa-clock text-pink-500"></i>
                                                <span>{{ $slotItem['time_range'] ?? $slotItem['start_time'] }}</span>
                                            </span>
                                            <span class="text-xs font-extrabold text-slate-900">{{ $slotItem['subject_code'] }} — {{ $slotItem['subject_name'] }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <!-- PROMINENT CLASS & SECTION TAG -->
                                            <span class="badge badge-indigo text-xs font-bold">
                                                <i class="fa-solid fa-chalkboard"></i>
                                                <span>{{ $slotItem['section_name'] }}</span>
                                            </span>
                                            <span class="badge badge-emerald text-xs font-bold">
                                                <i class="fa-solid fa-door-open"></i>
                                                <span>{{ $slotItem['room_name'] }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- HORIZONTAL PROGRESS DURATION BAR -->
                                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden my-2.5 border border-slate-200 shadow-inner">
                                        <div class="h-full bg-gradient-to-r from-[#fd1d1d] via-[#e1306c] to-[#833ab4] rounded-full transition-all duration-500" style="width: {{ $barWidthPercent }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function switchView(mode) {
        const gridView = document.getElementById('view-grid');
        const listView = document.getElementById('view-list');
        const btnGrid = document.getElementById('btn-view-grid');
        const btnList = document.getElementById('btn-view-list');

        if (mode === 'grid') {
            gridView.classList.remove('hidden');
            listView.classList.add('hidden');
            btnGrid.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-pink-600 shadow-xs transition flex items-center gap-1";
            btnList.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1";
        } else {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
            btnList.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-pink-600 shadow-xs transition flex items-center gap-1";
            btnGrid.className = "px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1";
        }
    }
</script>
@endsection


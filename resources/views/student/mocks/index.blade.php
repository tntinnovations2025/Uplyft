@extends('layouts.app')

@section('title', 'Official Mock Examinations')
@section('page-header', 'Mock Examinations')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- 1. TOP HERO BANNER (INK & AMBER) -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-6 shadow-sm relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-[#F8E9D3] border border-[#E8CEAA] text-[#8A5A10] text-xs font-extrabold mb-2.5">
                    <i class="fa-solid fa-graduation-cap text-[#D48A2E]"></i>
                    <span>Official CAIE &amp; Cambridge Examination Hub</span>
                </div>
                <h1 class="text-2xl font-black text-[#1B1A17] font-display tracking-tight">
                    Mock Examinations &amp; Assessments
                </h1>
                <p class="text-xs text-[#68665D] mt-1 max-w-2xl leading-relaxed">
                    Access timed, syllabus-calibrated mock examinations scheduled by your course instructors. Real-time auto-gating ensures testing rigor under exact examination conditions.
                </p>
            </div>

            <!-- STAT COUNTER TILES -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div class="bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3.5 py-2.5 text-center shadow-2xs">
                    <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">Total</div>
                    <div class="text-lg font-black text-[#1B1A17] mt-0.5">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-[#E3EFE2] border border-[#C5DDC3] rounded-xl px-3.5 py-2.5 text-center shadow-2xs">
                    <div class="text-[10px] font-bold text-[#2E6E42] uppercase tracking-wider">Active</div>
                    <div class="text-lg font-black text-[#2E6E42] mt-0.5">{{ $stats['active'] }}</div>
                </div>
                <div class="bg-[#F8E9D3] border border-[#E8CEAA] rounded-xl px-3.5 py-2.5 text-center shadow-2xs">
                    <div class="text-[10px] font-bold text-[#8A5A10] uppercase tracking-wider">Scheduled</div>
                    <div class="text-lg font-black text-[#8A5A10] mt-0.5">{{ $stats['locked'] }}</div>
                </div>
                <div class="bg-[#E7ECF6] border border-[#CAD5EC] rounded-xl px-3.5 py-2.5 text-center shadow-2xs">
                    <div class="text-[10px] font-bold text-[#3A529C] uppercase tracking-wider">Completed</div>
                    <div class="text-lg font-black text-[#3A529C] mt-0.5">{{ $stats['completed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. FLASH MESSAGES -->
    @if(session('error'))
        <div class="bg-[#F6E4E1] border border-[#ECCAC4] text-[#A2412C] px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2.5 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('success'))
        <div class="bg-[#E3EFE2] border border-[#C5DDC3] text-[#2E6E42] px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2.5 shadow-sm">
            <i class="fa-solid fa-circle-check text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 3. MOCKS GRID -->
    @if($mocks->isEmpty())
        <div class="bg-[#F9F8F5] border border-dashed border-[#E1DFD7] rounded-2xl p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#F8E9D3] border border-[#E8CEAA] text-[#D48A2E] flex items-center justify-center text-xl mx-auto mb-3.5 shadow-sm">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h3 class="text-base font-extrabold text-[#1B1A17] font-display">No Mock Examinations Scheduled</h3>
            <p class="text-xs text-[#68665D] max-w-md mx-auto mt-1 leading-relaxed">
                Your teachers have not published any mock examinations for your enrolled subjects yet. Check back closer to term assessments.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($mocks as $mock)
                <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-5 shadow-sm flex flex-col justify-between hover:border-[#D48A2E]/50 transition-all group">
                    <!-- CARD HEADER -->
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 bg-[#FFFFFF] border border-[#E1DFD7] text-[#1B1A17] font-bold text-[11px] px-2.5 py-1 rounded-lg shadow-2xs">
                                <i class="fa-solid fa-book-bookmark text-[#D48A2E]"></i>
                                {{ $mock->subject->subject_name ?? $mock->subject->name ?? 'Subject' }}
                            </span>

                            <!-- STATUS BADGE -->
                            @if($mock->schedule_state === 'active')
                                <span class="inline-flex items-center gap-1.5 bg-[#E3EFE2] border border-[#C5DDC3] text-[#2E6E42] text-[10px] font-extrabold px-2.5 py-1 rounded-full animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#2E6E42]"></span> Active &amp; Ready
                                </span>
                            @elseif($mock->schedule_state === 'completed')
                                <span class="inline-flex items-center gap-1.5 bg-[#E7ECF6] border border-[#CAD5EC] text-[#3A529C] text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check"></i> Completed
                                </span>
                            @elseif($mock->schedule_state === 'locked')
                                <span class="inline-flex items-center gap-1.5 bg-[#F8E9D3] border border-[#E8CEAA] text-[#8A5A10] text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-lock"></i> Locked
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-[#F2EFEB] border border-[#E1DFD7] text-[#68665D] text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Expired
                                </span>
                            @endif
                        </div>

                        <!-- EXAM TITLE -->
                        <h3 class="text-base font-extrabold text-[#1B1A17] font-display leading-snug group-hover:text-[#D48A2E] transition-colors mb-2">
                            {{ $mock->title }}
                        </h3>

                        <!-- METADATA STRIP -->
                        <div class="space-y-1.5 text-xs text-[#68665D] border-t border-b border-[#E1DFD7] py-3 my-3">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-regular fa-calendar text-[#8A877E]"></i> Date:
                                </span>
                                <span class="font-bold text-[#1B1A17] text-[11px]">
                                    {{ $mock->resolved_start_time ? $mock->resolved_start_time->format('D, M d, Y') : 'Date TBA' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-regular fa-clock text-[#8A877E]"></i> Window:
                                </span>
                                <span class="font-bold text-[#1B1A17] text-[11px]">
                                    {{ $mock->resolved_start_time ? $mock->resolved_start_time->format('h:i A') : '' }} – {{ $mock->resolved_end_time ? $mock->resolved_end_time->format('h:i A') : '' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-stopwatch text-[#8A877E]"></i> Duration &amp; MCQs:
                                </span>
                                <span class="font-bold text-[#1B1A17] text-[11px]">
                                    {{ $mock->duration_minutes ?: 45 }} Mins &bull; {{ $mock->questions->count() ?: ($mock->total_mcqs ?: 30) }} MCQs
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[11px]">
                                    <i class="fa-solid fa-user-tie text-[#8A877E]"></i> Assigned Teacher:
                                </span>
                                <span class="font-semibold text-[#1B1A17] text-[11px]">
                                    {{ $mock->teacher->name ?? ($mock->creator->name ?? 'Course Faculty') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- CARD FOOTER & ACTION GATING -->
                    <div class="pt-2">
                        @if($mock->schedule_state === 'completed')
                            <div class="flex items-center justify-between mb-2 px-1">
                                <span class="text-[11px] font-bold text-[#68665D]">Your Score:</span>
                                <span class="text-xs font-black text-[#2E6E42] bg-[#E3EFE2] border border-[#C5DDC3] px-2 py-0.5 rounded-md">
                                    {{ number_format($mock->submission_record->total_score, 0) }} / {{ $mock->questions->count() }}
                                </span>
                            </div>
                            <a href="{{ route('student.mocks.result', ['assessment' => $mock->id, 'submission' => $mock->submission_record->id]) }}" 
                               class="w-full bg-[#FFFFFF] hover:bg-[#F2EFEB] border border-[#E1DFD7] text-[#1B1A17] font-extrabold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-2xs">
                                <i class="fa-solid fa-chart-simple text-[#D48A2E]"></i>
                                <span>View Results &amp; Citations</span>
                            </a>
                        @elseif($mock->schedule_state === 'active')
                            <a href="{{ route('student.mocks.take', $mock->id) }}" 
                               class="w-full bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-[0_4px_14px_rgba(212,138,46,0.25)] hover:shadow-[0_6px_20px_rgba(212,138,46,0.35)]">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Start Mock Examination</span>
                            </a>
                        @elseif($mock->schedule_state === 'locked')
                            <div class="text-center mb-2">
                                <span class="text-[11px] font-extrabold text-[#8A5A10] flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                    {{ $mock->countdown_string ?? 'Opens soon' }}
                                </span>
                            </div>
                            <button type="button" disabled 
                                    class="w-full bg-[#F2EFEB] border border-[#E1DFD7] text-[#8A877E] font-bold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-lock"></i>
                                <span>Not Started Yet</span>
                            </button>
                        @else
                            <button type="button" disabled 
                                    class="w-full bg-[#F2EFEB] border border-[#E1DFD7] text-[#8A877E] font-bold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="fa-solid fa-ban"></i>
                                <span>Missed / Window Closed</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

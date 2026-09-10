@extends('layouts.app')

@section('title', 'Assignments & Tests')
@section('page-header', 'Assignments & Tests')

@section('content')
<div class="space-y-6">
    {{-- Top Header Banner --}}
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200/90 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2 font-display">
                <span>📝 Class Assignments &amp; Exams</span>
                @if($classSection)
                    <span class="badge badge-pink text-xs font-bold">
                        {{ $classSection->full_name }}
                    </span>
                @endif
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">
                All generated quizzes, paper tests, and formal exams published for your class appear here automatically.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-bold text-slate-500">Total Assessments</div>
                <div class="text-lg font-extrabold text-pink-700 font-display">{{ $assessments->count() }} Available</div>
            </div>
        </div>
    </div>

    <div class="liquid-glass-card overflow-hidden border border-slate-200/90 shadow-2xs">
        
        {{-- Section Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50/70">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 font-display">
                <i class="fa-solid fa-list-check text-pink-600"></i>
                <span>Assigned Class Assessments</span>
            </h3>
            <span class="text-xs text-slate-500 font-medium">Updates live when teachers or AI generate tests</span>
        </div>

        {{-- Assessment List --}}
        <div class="p-6 space-y-4">
            @forelse($assessments as $asm)
                @php
                    $isSubmitted = $asm->has_submitted ?? false;
                    $subjectName = $asm->subject->subject_name ?? 'General Subject';
                    $subjectCode = $asm->subject->subject_code ?? 'SUB';
                    $typeCapitalized = ucfirst($asm->type ?? 'Quiz');
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs hover:border-pink-300 transition-all gap-4">
                    
                    {{-- Left side details --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $isSubmitted ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-pink-50 text-pink-600 border border-pink-200' }} flex items-center justify-center shrink-0 text-lg shadow-2xs">
                            <i class="fa-solid {{ $isSubmitted ? 'fa-circle-check' : 'fa-file-pen' }}"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                <span class="badge badge-pink font-mono text-[10px] font-extrabold uppercase tracking-wider">
                                    {{ $subjectCode }} &bull; {{ $subjectName }}
                                </span>
                                <span class="badge badge-purple text-[10px] font-extrabold uppercase tracking-wider">
                                    {{ $typeCapitalized }}
                                </span>
                                @if($asm->duration_minutes)
                                    <span class="badge badge-amber text-[10px] font-extrabold">
                                        ⏱️ {{ $asm->duration_minutes }} Mins
                                    </span>
                                @endif
                                @if($isSubmitted)
                                    <span class="badge badge-emerald text-[10px] font-bold">
                                        ✅ Submitted
                                    </span>
                                @else
                                    <span class="badge badge-cyan text-[10px] font-bold animate-pulse">
                                        🟢 Open for Submission
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-base font-extrabold text-slate-900 font-display">{{ $asm->title }}</h4>

                            <div class="flex items-center gap-4 text-xs text-slate-500 font-medium mt-1.5 flex-wrap">
                                <span>🎯 Max Marks: <strong class="text-slate-800">{{ $asm->total_marks }} Pts</strong></span>
                                <span>❓ Questions: <strong class="text-slate-800">{{ $asm->questions->count() }}</strong></span>
                                <span>📅 Assigned: <strong class="text-slate-700">{{ $asm->created_at->format('M d, Y') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Right side Action Button --}}
                    <div class="flex items-center gap-3 shrink-0">
                        @if($isSubmitted)
                            <div class="text-right mr-2">
                                <div class="text-[10px] font-extrabold text-slate-500 uppercase">Score Awarded</div>
                                <div class="text-sm font-extrabold text-emerald-700 font-mono">
                                    {{ number_format($asm->total_score_obtained, 1) }} / {{ $asm->total_marks }} Pts
                                </div>
                            </div>
                            <a href="{{ route('lms.assessments.take', $asm->id) }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold transition inline-flex items-center gap-2 shadow-2xs">
                                <i class="fa-solid fa-eye"></i> View Submission
                            </a>
                        @else
                            <a href="{{ route('lms.assessments.take', $asm->id) }}" class="btn-primary px-5 py-2.5 text-xs font-extrabold shadow-md inline-flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i> Take Assessment
                            </a>
                        @endif
                    </div>

                </div>
            @empty
                <div class="text-center py-12 bg-slate-50/50 rounded-2xl border border-dashed border-slate-300">
                    <div class="text-4xl mb-3">📝</div>
                    <h4 class="text-base font-extrabold text-slate-800 font-display mb-1">No Active Assessments Found</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto font-medium">
                        When your teacher or principal creates or generates an assessment for your class, it will instantly show up here.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

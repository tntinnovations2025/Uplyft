@extends('lms.layouts.app')

@section('title', 'Practice Test Results & AI Scorecard')
@section('breadcrumb', 'Test Scorecard')

@section('content')
@php
    $percentage = $test->total_marks > 0 ? round(($test->obtained_marks / $test->total_marks) * 100, 1) : 0;
    $gradeBadgeClass = $percentage >= 80 
        ? 'badge-emerald' 
        : ($percentage >= 50 
            ? 'badge-amber' 
            : 'badge-rose');
@endphp

<!-- Scorecard Summary Banner -->
<div class="mb-8 p-6 md:p-8 rounded-2xl liquid-glass-card bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 shadow-2xs relative overflow-hidden glass-specular-top">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge badge-pink text-xs font-bold">
                    Official AI Scorecard
                </span>
            </div>
            <h1 class="font-extrabold text-2xl md:text-3xl text-slate-900 tracking-tight font-display">{{ $test->title }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-2 flex items-center gap-2">
                <i class="fa-solid fa-book-open text-pink-600"></i>
                <span>Subject: <strong class="text-slate-800">{{ $test->subject->subject_name ?? 'Subject' }}</strong></span>
                <span>•</span>
                <span>Evaluated against indexed textbook RAG context</span>
            </p>
        </div>

        <div class="flex items-center gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs shrink-0">
            <div class="text-right">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Score</div>
                <div class="font-extrabold text-3xl text-pink-700 font-mono font-display">
                    {{ $test->obtained_marks }} <span class="text-sm font-normal text-slate-400">/ {{ $test->total_marks }}</span>
                </div>
            </div>
            <div class="px-4 py-2 rounded-xl text-lg font-black tracking-wide shadow-2xs badge {{ $gradeBadgeClass }}">
                {{ $percentage }}%
            </div>
        </div>
    </div>
</div>

<!-- Detailed Question Breakdown -->
<div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-200">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 border border-pink-200/60 flex items-center justify-center font-bold text-sm">
                📝
            </span>
            <h3 class="text-lg font-extrabold text-slate-900 font-display">Question-by-Question Evaluation Breakdown</h3>
        </div>

        <a href="{{ route('lms.practice-test.index') }}" class="btn-primary px-4 py-2 text-xs font-bold shadow-sm inline-flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-rotate-right"></i>
            <span>Take Another Practice Test</span>
        </a>
    </div>

    <div class="space-y-4">
        @foreach(($test->evaluation_results ?? []) as $res)
            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-4 hover:border-pink-300 transition-all">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-300 text-slate-800 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 font-display">
                            {{ $res['question_index'] }}
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 leading-relaxed">
                                {{ $res['question_text'] }}
                            </h4>
                            <span class="inline-block mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500 px-2 py-0.5 rounded bg-slate-100 border border-slate-200">
                                Type: {{ strtoupper($res['question_type'] ?? 'short') }}
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if(($res['obtained_marks'] ?? 0) == ($res['max_marks'] ?? 0))
                            <span class="badge badge-emerald text-xs font-bold">
                                +{{ $res['obtained_marks'] }} / {{ $res['max_marks'] }} pts
                            </span>
                        @elseif(($res['obtained_marks'] ?? 0) > 0)
                            <span class="badge badge-amber text-xs font-bold">
                                +{{ $res['obtained_marks'] }} / {{ $res['max_marks'] }} pts
                            </span>
                        @else
                            <span class="badge badge-rose text-xs font-bold">
                                0 / {{ $res['max_marks'] }} pts
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <div class="font-bold text-[10px] uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i class="fa-solid fa-user text-pink-600"></i>
                            <span>Your Submitted Answer</span>
                        </div>
                        <div class="text-slate-800 font-medium whitespace-pre-wrap leading-relaxed">
                            {{ $res['student_answer'] ?: '(No answer provided)' }}
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-indigo-50/50 border border-indigo-200/60 space-y-1">
                        <div class="font-bold text-[10px] uppercase tracking-wider text-indigo-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-book-bookmark"></i>
                            <span>Textbook Model Solution</span>
                        </div>
                        <div class="text-indigo-950 font-medium whitespace-pre-wrap leading-relaxed">
                            {{ $res['correct_answer'] }}
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-purple-50/60 border-l-4 border-purple-500 text-xs text-purple-950 leading-relaxed flex items-start gap-2.5">
                    <span class="text-base text-purple-600 shrink-0">🤖</span>
                    <div>
                        <strong class="text-purple-900 font-bold">AI Grading Analysis:</strong>
                        <span class="ml-1 text-slate-700 font-medium">{{ $res['feedback'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

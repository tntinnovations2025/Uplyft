@extends('lms.layouts.app')

@section('title', 'Attempting Practice Test')
@section('breadcrumb', 'Attempt Practice Test')

@section('content')
<!-- Sticky Header Bar -->
<div class="sticky top-20 z-30 mb-8 liquid-glass-card bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 rounded-2xl p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 glass-specular-top">
    <div>
        <div class="flex items-center gap-2">
            <span class="badge badge-pink text-xs font-bold">
                Self-Practice
            </span>
            <h2 class="font-extrabold text-xl text-slate-900 tracking-tight font-display">{{ $test->title }}</h2>
        </div>
        <div class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-3">
            <span>Subject: <strong class="text-slate-800">{{ $test->subject->subject_name ?? 'Subject' }}</strong></span>
            <span>•</span>
            <span>{{ count($test->questions) }} Questions</span>
            <span>•</span>
            <span>Total Marks: <strong class="text-pink-700 font-bold">{{ $test->total_marks }} pts</strong></span>
        </div>
    </div>

    @if($test->scheduled_end_at)
        <div class="flex items-center gap-3 bg-white border border-slate-200 px-4 py-2.5 rounded-xl shadow-2xs">
            <span class="text-xl">⏳</span>
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Time Remaining</div>
                <div id="countdown-timer" class="font-mono text-lg font-extrabold text-pink-600">
                    Calculating...
                </div>
            </div>
        </div>
    @endif
</div>

<form id="practice-test-form" method="POST" action="{{ route('lms.practice-test.submit', $test->id) }}" class="space-y-6">
    @csrf

    @foreach($test->questions as $idx => $q)
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs space-y-4 hover:border-pink-300 transition-all">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#fd1d1d] to-[#e1306c] text-white flex items-center justify-center font-extrabold text-sm shrink-0 shadow-sm border border-white/60 font-display">
                        {{ $idx + 1 }}
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 leading-relaxed font-display">
                            {{ $q['question_text'] }}
                        </h3>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1.5 flex items-center gap-2">
                            <span class="badge badge-purple text-[10px]">
                                Type: {{ strtoupper($q['question_type'] ?? 'short') }}
                            </span>
                            <span>•</span>
                            <span class="text-indigo-700 font-bold">Marks: {{ $q['max_marks'] ?? 5 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(($q['question_type'] ?? 'short') === 'mcq' && !empty($q['options']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                    @foreach($q['options'] as $optIdx => $option)
                        <label class="flex items-center gap-3 bg-slate-50/80 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-pink-400 hover:bg-pink-50/30 transition-all group">
                            <input type="radio" name="answers[{{ $idx }}]" value="{{ $option }}" class="w-4 h-4 text-pink-600 bg-white border-slate-300 focus:ring-pink-500">
                            <span class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            @elseif(($q['question_type'] ?? 'short') === 'long')
                <div class="pt-2">
                    <textarea name="answers[{{ $idx }}]" rows="5" placeholder="Write your detailed essay / comprehensive response here..." class="styled-textarea"></textarea>
                </div>
            @else
                <div class="pt-2">
                    <textarea name="answers[{{ $idx }}]" rows="3" placeholder="Write your answer here..." class="styled-textarea"></textarea>
                </div>
            @endif
        </div>
    @endforeach

    <div class="pt-4 flex justify-end">
        <button type="submit" class="btn-primary py-4 px-8 text-sm font-extrabold flex items-center gap-2 shadow-md">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>Submit Practice Test for AI Grading</span>
        </button>
    </div>
</form>

<style>
    .styled-textarea {
        width: 100%;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 500;
        font-family: inherit;
        transition: all 0.2s ease;
        outline: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .styled-textarea:hover {
        border-color: #94a3b8;
    }
    .styled-textarea:focus {
        border-color: #e1306c;
        box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.15);
    }
</style>

@if($test->scheduled_end_at)
<script>
    const endTimestamp = new Date("{{ $test->scheduled_end_at->toIso8601String() }}").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = endTimestamp - now;

        if (diff <= 0) {
            document.getElementById('countdown-timer').innerText = "00:00 (Auto-Submitting)";
            document.getElementById('countdown-timer').className = "font-mono text-lg font-bold text-rose-600 animate-pulse";
            document.getElementById('practice-test-form').submit();
            return;
        }

        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('countdown-timer').innerText =
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
</script>
@endif
@endsection

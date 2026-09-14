@extends('layouts.app')

@section('title', 'Exam Result: ' . $assessment->title)
@section('page-header', 'Examination Result')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-20">

    <!-- 1. HERO SCORE CARD (INK & AMBER) -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-3xl p-6 md:p-8 shadow-sm relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
            <!-- LEFT: TITLE & SUBMISSION DETAILS -->
            <div class="space-y-2 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-[#F8E9D3] border border-[#E8CEAA] text-[#8A5A10] text-xs font-extrabold">
                    <i class="fa-solid fa-square-poll-vertical text-[#D48A2E]"></i>
                    <span>Official Assessment Evaluation</span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-[#1B1A17] font-display">
                    {{ $assessment->title }}
                </h1>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-xs text-[#68665D]">
                    <span class="font-bold text-[#D48A2E]">{{ $assessment->subject->subject_name ?? $assessment->subject->name ?? 'Subject' }}</span>
                    <span>&bull;</span>
                    <span>Submitted: {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : 'Completed' }}</span>
                    <span>&bull;</span>
                    @if($submission->isAutoSubmitted())
                        <span class="inline-flex items-center gap-1 text-[#8A5A10] bg-[#F8E9D3] px-2 py-0.5 rounded font-bold">
                            <i class="fa-solid fa-stopwatch"></i> Auto-Submitted (Time Limit)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[#2E6E42] bg-[#E3EFE2] px-2 py-0.5 rounded font-bold">
                            <i class="fa-solid fa-check"></i> Standard Submission
                        </span>
                    @endif
                </div>
            </div>

            <!-- RIGHT: SCORE & GRADE BADGE -->
            <div class="flex items-center gap-5 bg-[#FFFFFF] border border-[#E1DFD7] rounded-2xl p-5 shadow-sm">
                <!-- SCORE CIRCLE / PILL -->
                <div class="text-center">
                    <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">Final Score</div>
                    <div class="text-2xl md:text-3xl font-black text-[#1B1A17] font-display mt-0.5">
                        {{ number_format($score, 0) }} <span class="text-base text-[#8A877E]">/ {{ $totalQuestions }}</span>
                    </div>
                    <div class="text-xs font-extrabold text-[#D48A2E] mt-0.5">
                        {{ $percentage }}%
                    </div>
                </div>

                <div class="w-px h-12 bg-[#E1DFD7]"></div>

                <!-- GRADE BADGE -->
                <div class="text-center">
                    <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">CAIE Grade</div>
                    <div class="text-3xl font-black font-display mt-0.5" style="color: {{ $gradeColor }};">
                        {{ $grade }}
                    </div>
                    <div class="text-[10px] font-extrabold text-[#68665D]">
                        {{ $gradeBadge }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. PERFORMANCE BREAKDOWN TILES -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-4 text-center shadow-2xs">
            <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">Total Questions</div>
            <div class="text-xl font-black text-[#1B1A17] mt-1">{{ $totalQuestions }}</div>
            <div class="text-[10px] text-[#8A877E] mt-0.5">100% Weight</div>
        </div>

        <div class="bg-[#E3EFE2] border border-[#C5DDC3] rounded-2xl p-4 text-center shadow-2xs">
            <div class="text-[10px] font-bold text-[#2E6E42] uppercase tracking-wider">Correct Answers</div>
            <div class="text-xl font-black text-[#2E6E42] mt-1">{{ $correct }}</div>
            <div class="text-[10px] text-[#2E6E42] font-bold mt-0.5">+{{ $correct }} Marks</div>
        </div>

        <div class="bg-[#F6E4E1] border border-[#ECCAC4] rounded-2xl p-4 text-center shadow-2xs">
            <div class="text-[10px] font-bold text-[#A2412C] uppercase tracking-wider">Incorrect Answers</div>
            <div class="text-xl font-black text-[#A2412C] mt-1">{{ $incorrect }}</div>
            <div class="text-[10px] text-[#A2412C] font-bold mt-0.5">0 Marks Deducted</div>
        </div>

        <div class="bg-[#F2EFEB] border border-[#E1DFD7] rounded-2xl p-4 text-center shadow-2xs">
            <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">Unanswered</div>
            <div class="text-xl font-black text-[#68665D] mt-1">{{ $unanswered }}</div>
            <div class="text-[10px] text-[#8A877E] mt-0.5">Skipped</div>
        </div>
    </div>

    <!-- 3. QUESTION-BY-QUESTION CITATION & REVIEW STRIP -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base font-extrabold text-[#1B1A17] font-display flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#D48A2E]"></i>
                <span>Question-by-Question Diagnostic &amp; Textbook Citations</span>
            </h2>
            <a href="{{ route('student.mocks.index') }}" class="text-xs font-bold text-[#D48A2E] hover:text-[#C07A22] flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Back to Mocks
            </a>
        </div>

        @foreach($reviewQuestions as $index => $item)
            @php
                $q = $item['question'];
                $userChoice = $item['user_choice'];
                $isCorrect = $item['is_correct'];
                $isUnanswered = $item['is_unanswered'];
                $options = (array) ($q->options ?: []);
                ksort($options);
            @endphp
            <div class="bg-[#F9F8F5] border rounded-2xl p-6 shadow-sm space-y-4 transition-all
                {{ $isCorrect ? 'border-[#C5DDC3]' : ($isUnanswered ? 'border-[#E1DFD7]' : 'border-[#ECCAC4]') }}">
                
                <!-- QUESTION HEADER -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl font-extrabold text-xs flex items-center justify-center font-display border
                            {{ $isCorrect ? 'bg-[#E3EFE2] text-[#2E6E42] border-[#C5DDC3]' : ($isUnanswered ? 'bg-[#F2EFEB] text-[#68665D] border-[#E1DFD7]' : 'bg-[#F6E4E1] text-[#A2412C] border-[#ECCAC4]') }}">
                            Q{{ $index + 1 }}
                        </span>

                        @if($isCorrect)
                            <span class="text-xs font-extrabold text-[#2E6E42] bg-[#E3EFE2] border border-[#C5DDC3] px-2.5 py-0.5 rounded-lg flex items-center gap-1">
                                <i class="fa-solid fa-check"></i> Correct (+1 Mark)
                            </span>
                        @elseif($isUnanswered)
                            <span class="text-xs font-bold text-[#68665D] bg-[#F2EFEB] border border-[#E1DFD7] px-2.5 py-0.5 rounded-lg">
                                Unanswered (0 Marks)
                            </span>
                        @else
                            <span class="text-xs font-extrabold text-[#A2412C] bg-[#F6E4E1] border border-[#ECCAC4] px-2.5 py-0.5 rounded-lg flex items-center gap-1">
                                <i class="fa-solid fa-xmark"></i> Incorrect (0 Marks)
                            </span>
                        @endif
                    </div>

                    @if($q->chapter_reference)
                        <span class="text-[11px] bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold px-2.5 py-0.5 rounded-lg">
                            {{ $q->chapter_reference }}
                        </span>
                    @endif
                </div>

                <!-- QUESTION STATEMENT -->
                <div class="text-sm font-semibold text-[#1B1A17] leading-relaxed">
                    {{ $q->statement }}
                </div>

                <!-- OPTIONS BREAKDOWN -->
                <div class="space-y-2">
                    @foreach($options as $optKey => $optVal)
                        @php
                            $isSelected = ($userChoice === $optKey);
                            $isTargetAnswer = (strtoupper(trim((string)$optKey)) === strtoupper(trim((string)$q->correct_answer)));
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl border text-xs md:text-sm font-medium
                            {{ $isTargetAnswer ? 'bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42] font-bold' : ($isSelected ? 'bg-[#F6E4E1] border-[#ECCAC4] text-[#A2412C]' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17]') }}">
                            
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-xs flex-shrink-0
                                {{ $isTargetAnswer ? 'bg-[#2E6E42] text-white shadow-2xs' : ($isSelected ? 'bg-[#A2412C] text-white' : 'bg-[#F2EFEB] text-[#68665D] border border-[#E1DFD7]') }}">
                                {{ $optKey }}
                            </span>

                            <span class="flex-1 leading-snug">{{ $optVal }}</span>

                            @if($isTargetAnswer)
                                <span class="text-xs font-bold text-[#2E6E42] flex items-center gap-1 bg-[#FFFFFF]/80 px-2 py-0.5 rounded-md border border-[#C5DDC3]">
                                    <i class="fa-solid fa-circle-check"></i> Correct Answer
                                </span>
                            @endif

                            @if($isSelected && !$isTargetAnswer)
                                <span class="text-xs font-bold text-[#A2412C] flex items-center gap-1 bg-[#FFFFFF]/80 px-2 py-0.5 rounded-md border border-[#ECCAC4]">
                                    <i class="fa-solid fa-circle-xmark"></i> Your Selection
                                </span>
                            @elseif($isSelected && $isTargetAnswer)
                                <span class="text-xs font-bold text-[#2E6E42] flex items-center gap-1 bg-[#FFFFFF]/80 px-2 py-0.5 rounded-md border border-[#C5DDC3]">
                                    <i class="fa-solid fa-user-check"></i> Your Selection
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- RAG RATIONALE & CITATIONS -->
                @if($q->explanation || $q->chapter_reference)
                    <div class="bg-[#F2EFEB] border border-[#E1DFD7] rounded-xl p-4 text-xs text-[#68665D] space-y-1.5 mt-2">
                        <div class="flex items-center gap-1.5 font-extrabold text-[#8A5A10] uppercase text-[10px]">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Textbook Citation &amp; Examiner Rationale:</span>
                        </div>
                        <p class="leading-relaxed m-0 text-[#1B1A17]">
                            {{ $q->explanation ?: "The correct Cambridge standard answer is Option {$q->correct_answer} as derived from syllabus unit {$q->chapter_reference}." }}
                        </p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- 4. BOTTOM NAVIGATION -->
    <div class="flex items-center justify-between pt-4">
        <a href="{{ route('student.mocks.index') }}" 
           class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#1B1A17] hover:border-[#D48A2E] font-bold text-xs py-3 px-6 rounded-xl flex items-center gap-2 transition-all shadow-2xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Return to Mock Examinations</span>
        </a>

        <a href="{{ route('student.dashboard') }}" 
           class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs py-3 px-6 rounded-xl flex items-center gap-2 transition-all shadow-[0_4px_14px_rgba(212,138,46,0.25)]">
            <i class="fa-solid fa-house"></i>
            <span>Student Dashboard</span>
        </a>
    </div>
</div>
@endsection

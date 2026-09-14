@extends('layouts.app')

@section('title', 'Exam: ' . $assessment->title)

@section('content')
<div x-data="mockExam({
        totalQuestions: {{ $assessment->questions->count() }},
        initialSeconds: {{ (int) $remainingSeconds }},
        assessmentId: {{ $assessment->id }},
        studentId: {{ auth()->id() }}
    })" 
    x-init="initExam()"
    class="max-w-5xl mx-auto space-y-6 pb-20">

    <!-- 1. STICKY TOP EXAMINATION BAR (INK & AMBER) -->
    <header class="sticky top-0 z-30 bg-[#F9F8F5]/95 backdrop-blur-md border border-[#E1DFD7] rounded-2xl p-4 shadow-md flex flex-wrap items-center justify-between gap-4">
        <!-- LEFT: EXAM INFO -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#F8E9D3] border border-[#E8CEAA] flex items-center justify-center text-[#D48A2E] text-base font-extrabold flex-shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h1 class="text-sm md:text-base font-extrabold text-[#1B1A17] font-display line-clamp-1">
                    {{ $assessment->title }}
                </h1>
                <div class="flex items-center gap-2 text-xs text-[#68665D] mt-0.5">
                    <span class="font-bold text-[#D48A2E]">{{ $assessment->subject->subject_name ?? $assessment->subject->name ?? 'Subject' }}</span>
                    <span>&bull;</span>
                    <span>Standard: {{ strtoupper(str_replace('_', ' ', $assessment->exam_standard ?? 'CAIE')) }}</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: PROGRESS & ACTIVE COUNTDOWN TIMER -->
        <div class="flex items-center gap-4">
            <!-- QUESTION PROGRESS -->
            <div class="hidden sm:flex flex-col items-end">
                <div class="text-[10px] font-bold text-[#68665D] uppercase tracking-wider">Progress</div>
                <div class="text-xs font-black text-[#1B1A17] mt-0.5">
                    <span x-text="answeredCount">0</span> / <span x-text="totalQuestions">{{ $assessment->questions->count() }}</span> Answered
                </div>
            </div>

            <!-- ALPINE.JS COUNTDOWN TIMER -->
            <div :class="remainingSeconds <= 300 ? 'bg-[#F6E4E1] border-[#ECCAC4] text-[#A2412C] animate-pulse' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17]'"
                 class="border px-4 py-2 rounded-xl flex items-center gap-2.5 shadow-2xs transition-colors">
                <i :class="remainingSeconds <= 300 ? 'fa-solid fa-triangle-exclamation text-[#A2412C]' : 'fa-regular fa-clock text-[#D48A2E]'"></i>
                <div class="flex flex-col items-center">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-[#68665D]" x-show="remainingSeconds > 300">Time Left</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-[#A2412C]" x-show="remainingSeconds <= 300">Cutoff Nearing</span>
                    <span class="text-base font-mono font-black tracking-wider leading-none" x-text="formattedTime">--:--</span>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="button" 
                    @click="openConfirmModal()"
                    class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(212,138,46,0.25)] flex items-center gap-2 transition-all">
                <i class="fa-solid fa-paper-plane"></i>
                <span class="hidden md:inline">Finish &amp; Submit</span>
            </button>
        </div>
    </header>

    <!-- 2. EXAM INSTRUCTIONS CALLOUT -->
    @if($assessment->instructions)
        <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-4 shadow-2xs">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-lg bg-[#F8E9D3] text-[#D48A2E] flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="text-xs text-[#68665D] leading-relaxed">
                    <strong class="text-[#1B1A17] block mb-1">Official Examination Instructions:</strong>
                    <div class="whitespace-pre-line">{{ $assessment->instructions }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. QUESTION FORM & MCQ FLOW -->
    <form id="mockForm" method="POST" action="{{ route('student.mocks.submit', $assessment->id) }}" class="space-y-5">
        @csrf
        <input type="hidden" name="is_auto_submit" id="is_auto_submit" value="0">

        @foreach($assessment->questions as $index => $q)
            <div id="card-question-{{ $q->id }}" 
                 class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-6 shadow-sm transition-all hover:border-[#D48A2E]/40"
                 :class="answers['{{ $q->id }}'] ? 'border-[#C5DDC3] bg-[#FAFBF9]' : ''">
                
                <!-- STEM HEADER -->
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-[#F8E9D3] border border-[#E8CEAA] text-[#8A5A10] font-extrabold text-xs flex items-center justify-center font-display">
                            Q{{ $index + 1 }}
                        </span>
                        <span class="text-xs font-bold text-[#68665D]">1 Mark</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($q->chapter_reference)
                            <span class="text-[10px] bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold px-2 py-0.5 rounded-md">
                                {{ $q->chapter_reference }}
                            </span>
                        @endif
                        <span x-show="answers['{{ $q->id }}']" 
                              class="text-[10px] bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] font-extrabold px-2 py-0.5 rounded-md flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Answered
                        </span>
                    </div>
                </div>

                <!-- QUESTION STEM -->
                <div class="text-sm md:text-base font-semibold text-[#1B1A17] leading-relaxed mb-4">
                    {{ $q->statement }}
                </div>

                <!-- VERTICALLY STACKED OPTIONS (A, B, C, D, E) -->
                <div class="space-y-2">
                    @php
                        $options = (array) ($q->options ?: []);
                        ksort($options);
                    @endphp
                    @foreach($options as $optKey => $optVal)
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all text-xs md:text-sm font-medium select-none"
                               :class="answers['{{ $q->id }}'] === '{{ $optKey }}' 
                                   ? 'bg-[#F8E9D3] border-[#D48A2E] text-[#1B1A17] font-bold shadow-2xs' 
                                   : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17] hover:border-[#D48A2E]/50'">
                            <input type="radio" 
                                   name="answers[{{ $q->id }}]" 
                                   value="{{ $optKey }}" 
                                   class="hidden"
                                   @change="selectAnswer('{{ $q->id }}', '{{ $optKey }}')"
                                   :checked="answers['{{ $q->id }}'] === '{{ $optKey }}'">
                            
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-xs flex-shrink-0 transition-colors"
                                  :class="answers['{{ $q->id }}'] === '{{ $optKey }}' 
                                      ? 'bg-[#D48A2E] text-white shadow-2xs' 
                                      : 'bg-[#F2EFEB] text-[#68665D] border border-[#E1DFD7]'">
                                {{ $optKey }}
                            </span>

                            <span class="flex-1 leading-snug">{{ $optVal }}</span>

                            <i class="fa-solid fa-circle-check text-[#D48A2E] text-base" 
                               x-show="answers['{{ $q->id }}'] === '{{ $optKey }}'"></i>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- BOTTOM ACTION STRIP -->
        <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-5 flex items-center justify-between gap-4 shadow-sm">
            <div class="text-xs text-[#68665D]">
                <span>All progress automatically saved in draft storage.</span>
            </div>
            <button type="button" 
                    @click="openConfirmModal()"
                    class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-sm py-3 px-6 rounded-xl shadow-[0_4px_14px_rgba(212,138,46,0.25)] flex items-center gap-2 transition-all">
                <i class="fa-solid fa-check-double"></i>
                <span>Submit Final Exam</span>
            </button>
        </div>
    </form>

    <!-- CONFIRMATION MODAL -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 bg-[#1B1A17]/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl w-full max-w-md p-6 text-[#1B1A17] shadow-2xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#F8E9D3] text-[#D48A2E] flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#1B1A17] font-display">Confirm Exam Submission</h3>
                    <p class="text-xs text-[#68665D]">Are you ready to finalize your test?</p>
                </div>
            </div>

            <div class="bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl p-4 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-[#68665D]">Total Questions:</span>
                    <strong class="text-[#1B1A17]" x-text="totalQuestions">0</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#2E6E42] font-bold">Answered:</span>
                    <strong class="text-[#2E6E42]" x-text="answeredCount">0</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#A2412C] font-bold">Unanswered:</span>
                    <strong class="text-[#A2412C]" x-text="totalQuestions - answeredCount">0</strong>
                </div>
                <div class="flex justify-between pt-1 border-t border-[#E1DFD7]">
                    <span class="text-[#68665D]">Time Remaining:</span>
                    <strong class="font-mono text-[#D48A2E]" x-text="formattedTime">00:00</strong>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" 
                        @click="showModal = false"
                        class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-4 py-2 rounded-xl text-xs font-bold">
                    Continue Exam
                </button>
                <button type="button" 
                        @click="executeSubmit(false)"
                        class="bg-[#D48A2E] hover:bg-[#C07A22] text-white px-5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Confirm &amp; Submit</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mockExam', (config) => ({
        totalQuestions: config.totalQuestions,
        remainingSeconds: config.initialSeconds,
        assessmentId: config.assessmentId,
        studentId: config.studentId,
        timerInterval: null,
        showModal: false,
        answers: {},

        get answeredCount() {
            return Object.keys(this.answers).filter(k => this.answers[k] !== null && this.answers[k] !== '').length;
        },

        get formattedTime() {
            const m = Math.floor(this.remainingSeconds / 60);
            const s = this.remainingSeconds % 60;
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },

        initExam() {
            // 1. Restore answers from localStorage draft if available
            const storageKey = `mock_draft_${this.assessmentId}_${this.studentId}`;
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved) {
                    this.answers = JSON.parse(saved) || {};
                }
            } catch (e) {
                console.warn("Could not load local draft", e);
            }

            // 2. Start Countdown Timer
            this.timerInterval = setInterval(() => {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                } else {
                    clearInterval(this.timerInterval);
                    this.forceAutoSubmit();
                }
            }, 1000);
        },

        selectAnswer(questionId, optionKey) {
            this.answers[questionId] = optionKey;
            this.saveDraft();
        },

        saveDraft() {
            const storageKey = `mock_draft_${this.assessmentId}_${this.studentId}`;
            try {
                localStorage.setItem(storageKey, JSON.stringify(this.answers));
            } catch (e) {
                console.warn("Could not save local draft", e);
            }
        },

        openConfirmModal() {
            this.showModal = true;
        },

        executeSubmit(isAuto = false) {
            // Clear local storage draft upon submission
            const storageKey = `mock_draft_${this.assessmentId}_${this.studentId}`;
            try { localStorage.removeItem(storageKey); } catch (e) {}

            if (isAuto) {
                document.getElementById('is_auto_submit').value = '1';
            }

            const form = document.getElementById('mockForm');
            if (form) {
                form.submit();
            }
        },

        forceAutoSubmit() {
            alert("Examination time has expired! Your answers are being automatically submitted.");
            this.executeSubmit(true);
        }
    }));
});
</script>
@endsection

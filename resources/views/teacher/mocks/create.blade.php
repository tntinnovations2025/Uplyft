@extends('layouts.app')

@section('title', 'O/A Levels Mock Examination Generation Engine')
@section('page-header', 'O/A Levels Mock Examination Generation Engine')

@section('content')
<div class="space-y-6 max-w-full overflow-x-hidden">

    <!-- TOP HERO HEADER -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-[11px] px-3 py-1 rounded-full uppercase tracking-wider font-display">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D48A2E]"></span>
                        Cambridge CAIE &amp; Edexcel Standard
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold text-[11px] px-3 py-1 rounded-full">
                        <i class="fa-solid fa-brain text-[10px]"></i> Few-Shot RAG Pipeline
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] font-bold text-[11px] px-3 py-1 rounded-full">
                        <i class="fa-solid fa-shield-halved text-[10px]"></i> 100% Zero-Duplication
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#1B1A17] tracking-tight font-display">
                    O/A Levels Mock Examination Generation Engine
                </h1>
                <p class="text-xs text-[#68665D] font-medium max-w-2xl leading-relaxed">
                    Autonomous high-rigor Cambridge O/A Level &amp; Edexcel exam creator with 8–10 PDF past-paper style conditioning and normalized collision filtering.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('lms.mocks.index') }}" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#D48A2E]"></i> View Mocks
                </a>
                <button type="button" onclick="openPastPaperUploadModal()" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Ingest Past Paper PDFs
                </button>
                <a href="{{ route('lms.assessments.index') }}" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN WORKSPACE (Responsive Flex Architecture) -->
    <div class="flex flex-col xl:flex-row items-start gap-6 w-full">

        <!-- LEFT COLUMN: CONFIGURATION PANEL (Fixed Width on Desktop, Stacks on Smaller Screens) -->
        <div class="w-full xl:w-[380px] xl:flex-shrink-0 bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-5 shadow-sm space-y-5">
            
            <div class="flex items-center justify-between pb-3 border-b border-[#E1DFD7]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] flex items-center justify-center text-xs">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <h2 class="text-xs font-extrabold text-[#1B1A17] uppercase tracking-wider font-display">
                        Examination Parameters
                    </h2>
                </div>
                <span class="text-[10px] font-bold text-[#8A5A10] bg-[#F8E9D3] px-2 py-0.5 rounded-md border border-[#E8CEAA]">Config</span>
            </div>

            <form id="mock-config-form" onsubmit="handleGenerateMock(event)" class="space-y-4">
                
                <!-- 1. SUBJECT SELECTOR -->
                <div>
                    <label for="mock_subject_id" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                        Subject (O/A Level) <span class="text-[#A2412C]">*</span>
                    </label>
                    <select id="mock_subject_id" name="subject_id" required onchange="handleSubjectChange(this.value)" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] transition-all shadow-sm">
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->subject_name ?? $sub->name }} ({{ $sub->subject_code ?? $sub->code ?? 'CAIE' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. PAST PAPER COVERAGE CHIP -->
                <div id="subject-exemplar-banner" class="bg-[#F2EFEB] border border-[#E1DFD7] rounded-xl p-3.5 transition-all">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-extrabold text-[#68665D] uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-database text-[#D48A2E]"></i> Exemplar Knowledge Bank
                        </span>
                        <span id="exemplar-count-badge" class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] text-[10px] font-extrabold px-2 py-0.5 rounded-md">
                            0 Indexed
                        </span>
                    </div>
                    <p id="exemplar-status-text" class="text-[11px] text-[#68665D] m-0 leading-tight">
                        Select a subject to inspect uploaded Cambridge past paper coverage.
                    </p>
                </div>

                <!-- 3. EXAMINATION STANDARD -->
                <div>
                    <label for="mock_exam_standard" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                        Examination Standard <span class="text-[#A2412C]">*</span>
                    </label>
                    <select id="mock_exam_standard" name="exam_standard" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] transition-all shadow-sm">
                        <option value="caie_o_level">Cambridge CAIE O Level (Paper 1 MCQ)</option>
                        <option value="caie_a_level">Cambridge CAIE A Level (Paper 1 MCQ)</option>
                        <option value="edexcel_igcse">Pearson Edexcel IGCSE Standard MCQ</option>
                    </select>
                </div>

                <!-- 4. QUESTION VOLUME & TIMING -->
                <div>
                    <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                        MCQ Count &amp; Duration <span class="text-[#A2412C]">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <label id="lbl-mcq-10" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl cursor-pointer transition-all shadow-sm hover:border-[#D48A2E]/50 text-center">
                            <input type="radio" name="total_mcqs" value="10" onchange="handleMcqCountChange(10)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">10</span>
                            <span class="text-[9px] text-[#68665D] font-bold">15m</span>
                        </label>
                        <label id="lbl-mcq-20" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl cursor-pointer transition-all shadow-sm hover:border-[#D48A2E]/50 text-center">
                            <input type="radio" name="total_mcqs" value="20" onchange="handleMcqCountChange(20)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">20</span>
                            <span class="text-[9px] text-[#68665D] font-bold">30m</span>
                        </label>
                        <label id="lbl-mcq-30" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#D48A2E] rounded-xl cursor-pointer transition-all shadow-sm text-center">
                            <input type="radio" name="total_mcqs" value="30" checked onchange="handleMcqCountChange(30)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">30</span>
                            <span class="text-[9px] text-[#D48A2E] font-bold">45m</span>
                        </label>
                        <label id="lbl-mcq-40" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl cursor-pointer transition-all shadow-sm hover:border-[#D48A2E]/50 text-center">
                            <input type="radio" name="total_mcqs" value="40" onchange="handleMcqCountChange(40)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">40</span>
                            <span class="text-[9px] text-[#68665D] font-bold">60m</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="mock_custom_mcqs" class="block text-[10px] font-bold text-[#68665D] mb-1">Custom MCQ Count</label>
                            <input type="number" id="mock_custom_mcqs" min="5" max="100" placeholder="e.g. 25" oninput="handleCustomMcqInput(this.value)" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] shadow-sm">
                        </div>
                        <div>
                            <label for="mock_duration_minutes" class="block text-[10px] font-bold text-[#68665D] mb-1">Duration (Mins) <span class="text-[#A2412C]">*</span></label>
                            <input type="number" id="mock_duration_minutes" name="duration_minutes" value="45" min="5" max="300" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-2.5 py-1.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- 5. DYNAMIC MCQ OPTION COUNT (PART 1) -->
                <div>
                    <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                        Option Density per MCQ <span class="text-[#A2412C]">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label id="lbl-opt-3" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl cursor-pointer transition-all shadow-sm hover:border-[#D48A2E]/50">
                            <input type="radio" name="options_per_mcq" value="3" onchange="handleOptionsCountChange(3)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">3 Choices</span>
                            <span class="text-[10px] text-[#68665D] font-semibold mt-0.5">(A–C)</span>
                        </label>
                        <label id="lbl-opt-4" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#D48A2E] rounded-xl cursor-pointer transition-all shadow-sm">
                            <input type="radio" name="options_per_mcq" value="4" checked onchange="handleOptionsCountChange(4)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">4 Choices</span>
                            <span class="text-[10px] text-[#D48A2E] font-bold mt-0.5">(A–D CAIE)</span>
                        </label>
                        <label id="lbl-opt-5" class="flex flex-col items-center justify-center p-2.5 bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl cursor-pointer transition-all shadow-sm hover:border-[#D48A2E]/50">
                            <input type="radio" name="options_per_mcq" value="5" onchange="handleOptionsCountChange(5)" class="hidden">
                            <span class="text-xs font-extrabold text-[#1B1A17]">5 Choices</span>
                            <span class="text-[10px] text-[#68665D] font-semibold mt-0.5">(A–E)</span>
                        </label>
                    </div>
                </div>

                <!-- 6. SCHEDULING CONTROLS (PART 1 & 2) -->
                <div class="p-3.5 bg-[#F2EFEB] border border-[#E1DFD7] rounded-2xl space-y-2.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-[#1B1A17] flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar-check text-[#D48A2E]"></i> Exam Schedule &amp; Window
                        </span>
                        <span class="text-[10px] text-[#68665D] font-bold">Auto-Gating</span>
                    </div>

                    <div>
                        <label for="mock_scheduled_date" class="block text-[10px] font-bold text-[#68665D] mb-1">
                            Exam Date <span class="text-[#A2412C]">*</span>
                        </label>
                        <input type="date" id="mock_scheduled_date" name="scheduled_date" value="{{ date('Y-m-d') }}" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-1.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="mock_start_time" class="block text-[10px] font-bold text-[#68665D] mb-1">
                                Start Time <span class="text-[#A2412C]">*</span>
                            </label>
                            <input type="time" id="mock_start_time" name="start_time" value="{{ date('H:i') }}" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-2 py-1.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] shadow-sm">
                        </div>
                        <div>
                            <label for="mock_end_time" class="block text-[10px] font-bold text-[#68665D] mb-1">
                                End Time (Cutoff) <span class="text-[#A2412C]">*</span>
                            </label>
                            <input type="time" id="mock_end_time" name="end_time" value="{{ date('H:i', strtotime('+60 minutes')) }}" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-2 py-1.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- 7. SYLLABUS TOPICS -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="mock_custom_topics" class="text-xs font-bold text-[#1B1A17]">
                            Focus Topics / Syllabus Chapters
                        </label>
                        <span class="text-[10px] text-[#A19E92]">Click pills to toggle</span>
                    </div>
                    <div id="topic-tags-container" class="flex flex-wrap gap-1.5 mb-2 min-h-[28px] max-h-[110px] overflow-y-auto">
                        <span class="text-[11px] text-[#A19E92] italic">Select a subject above to load indexed topics.</span>
                    </div>
                    <input type="text" id="mock_custom_topics" placeholder="Custom topics (e.g. Organic, Kinetics)..." class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-medium text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] transition-colors shadow-sm">
                </div>

                <!-- 8. CLASS SECTION & ACADEMIC TERM -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-3">
                    <div>
                        <label for="mock_class_section_id" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                            Target Class Section <span class="text-[#A2412C]">*</span>
                        </label>
                        <select id="mock_class_section_id" name="class_section_id" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] transition-colors shadow-sm">
                            <option value="">-- All Sections in Class (Class-Wide) --</option>
                            @foreach($classSections as $sec)
                                <option value="{{ $sec->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $sec->instituteClass->name ?? 'Class' }} - {{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mock_academic_term_id" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                            Academic Term <span class="text-[#A2412C]">*</span>
                        </label>
                        <select id="mock_academic_term_id" name="academic_term_id" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] transition-colors shadow-sm">
                            @foreach($academicTerms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- GENERATE ACTION BUTTON -->
                <div class="pt-2">
                    <button type="submit" id="btn-generate-mock" class="w-full bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-sm py-3.5 rounded-xl cursor-pointer flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(212,138,46,0.25)] hover:shadow-[0_6px_20px_rgba(212,138,46,0.35)] transition-all">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Generate Cambridge Mock</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT COLUMN: GENERATION WORKFLOW & LIVE EXAMINATION PREVIEW (Flex 1, Min-Width 0) -->
        <div class="flex-1 min-w-0 w-full space-y-4">
            
            <!-- EMPTY / INITIAL STATE BANNER -->
            <div id="mock-placeholder-card" class="bg-[#F9F8F5] border border-dashed border-[#E1DFD7] rounded-2xl p-10 md:p-14 text-center shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-[#F8E9D3] border border-[#E8CEAA] flex items-center justify-center mx-auto mb-4 text-[#D48A2E] text-2xl shadow-sm">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="text-lg font-extrabold text-[#1B1A17] mb-2 font-display">
                    Ready for Cambridge Examination Generation
                </h3>
                <p class="text-xs text-[#68665D] max-w-lg mx-auto mb-6 leading-relaxed">
                    Select your subject, question volume (30 or 40 MCQs), option density (3, 4, or 5 choices), and configure syllabus topics. The engine will retrieve 8–10 past paper exemplars and apply 100% Zero-Duplication collision filtering.
                </p>
                <div class="inline-flex flex-wrap justify-center gap-3 text-xs font-bold text-[#68665D] mb-6">
                    <span class="inline-flex items-center gap-1.5 bg-[#FFFFFF] border border-[#E1DFD7] px-3 py-1.5 rounded-xl shadow-2xs">
                        <i class="fa-solid fa-check text-[#2E6E42]"></i> Pure MCQ Format
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-[#FFFFFF] border border-[#E1DFD7] px-3 py-1.5 rounded-xl shadow-2xs">
                        <i class="fa-solid fa-check text-[#2E6E42]"></i> Cambridge Distractor Rationale
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-[#FFFFFF] border border-[#E1DFD7] px-3 py-1.5 rounded-xl shadow-2xs">
                        <i class="fa-solid fa-check text-[#2E6E42]"></i> Collision-Free Stem Hashing
                    </span>
                </div>

                <div class="p-4 rounded-xl bg-[#F2EFEB] border border-[#E1DFD7] max-w-md mx-auto text-left flex items-start gap-3">
                    <div class="w-6 h-6 rounded-md bg-[#D48A2E] text-white flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div class="text-[11px] text-[#68665D] leading-relaxed">
                        <strong class="text-[#1B1A17]">Tip:</strong> Ingest 8–10 PDF past papers for your subject using the top right button to give the Chief Examiner AI few-shot stylistic fidelity.
                    </div>
                </div>
            </div>

            <!-- GENERATION IN PROGRESS ANIMATION & STEPPER -->
            <div id="mock-progress-card" class="hidden bg-[#F9F8F5] border-2 border-[#D48A2E] rounded-2xl p-10 text-center shadow-sm">
                <div class="inline-block relative mb-4">
                    <div class="w-14 h-14 border-4 border-[#F8E9D3] border-t-[#D48A2E] rounded-full animate-spin"></div>
                </div>
                <h3 class="text-base font-extrabold text-[#1B1A17] mb-1 font-display">
                    Engineering Cambridge Mock Examination...
                </h3>
                <p id="mock-progress-phase" class="text-xs text-[#8A5A10] font-bold mb-6">
                    Step 1/4: Retrieving textbook syllabus chunks &amp; concept vectors...
                </p>

                <!-- PIPELINE STEPS -->
                <div class="max-w-md mx-auto text-left flex flex-col gap-2.5 text-xs font-semibold bg-[#FFFFFF] border border-[#E1DFD7] p-4 rounded-xl">
                    <div id="step-rag" class="flex items-center gap-2.5 text-[#D48A2E]">
                        <i class="fa-regular fa-circle-dot"></i> RAG Textbook &amp; Syllabus Context Retrieval
                    </div>
                    <div id="step-fewshot" class="flex items-center gap-2.5 text-[#A19E92]">
                        <i class="fa-regular fa-circle"></i> Past Paper Exemplars (Few-Shot Style Conditioning)
                    </div>
                    <div id="step-groq" class="flex items-center gap-2.5 text-[#A19E92]">
                        <i class="fa-regular fa-circle"></i> High-Rigor Cambridge Question Setter (Groq LLM)
                    </div>
                    <div id="step-dedup" class="flex items-center gap-2.5 text-[#A19E92]">
                        <i class="fa-regular fa-circle"></i> Zero-Duplication Normalized Collision Matrix (&gt;0.88 Cosine)
                    </div>
                </div>
            </div>

            <!-- GENERATION RESULTS & LIVE MCQ REVIEW -->
            <div id="mock-results-card" class="hidden space-y-4">
                <!-- SUMMARY ACTION BAR -->
                <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-5 shadow-sm flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h3 id="res-mock-title" class="text-base font-extrabold text-[#1B1A17] font-display mb-1">
                            Cambridge O Level Mock Examination
                        </h3>
                        <div class="flex items-center gap-3.5 text-xs text-[#68665D] flex-wrap">
                            <span><i class="fa-solid fa-list-check text-[#D48A2E]"></i> <strong id="res-total-count" class="text-[#1B1A17]">30</strong> Questions</span>
                            <span><i class="fa-solid fa-layer-group text-[#3A529C]"></i> <strong id="res-opt-count" class="text-[#1B1A17]">4</strong> Options/MCQ</span>
                            <span><i class="fa-solid fa-stopwatch text-[#8A5A10]"></i> <strong id="res-time-limit" class="text-[#1B1A17]">45</strong> Mins</span>
                            <span class="text-[#2E6E42] font-bold"><i class="fa-solid fa-shield-check"></i> 0% Duplication Collision</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="printCurrentMock(false)" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-print text-[#D48A2E]"></i> Print Paper
                        </button>
                        <button type="button" onclick="printCurrentMock(true)" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-key text-[#8A5A10]"></i> Print Key
                        </button>
                        <button type="button" onclick="handleGenerateMock(event)" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#68665D] text-[#68665D] hover:text-[#1B1A17] px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-rotate"></i> Regenerate
                        </button>
                        <button type="button" onclick="openSaveModal()" id="btn-open-save-modal" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white px-5 py-2.5 rounded-xl text-xs font-extrabold transition-all shadow-[0_4px_14px_rgba(212,138,46,0.25)] flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Save / Schedule Exam
                        </button>
                    </div>
                </div>

                <!-- DEDUPLICATION STATS STRIP -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl p-3.5 text-center shadow-sm">
                        <div class="text-[10px] text-[#68665D] uppercase font-extrabold tracking-wider">Candidates Evaluated</div>
                        <div id="stat-evaluated" class="text-lg font-extrabold text-[#1B1A17] mt-0.5">30</div>
                    </div>
                    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl p-3.5 text-center shadow-sm">
                        <div class="text-[10px] text-[#68665D] uppercase font-extrabold tracking-wider">Exact Hashes Blocked</div>
                        <div id="stat-exact-blocked" class="text-lg font-extrabold text-[#A2412C] mt-0.5">0</div>
                    </div>
                    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl p-3.5 text-center shadow-sm">
                        <div class="text-[10px] text-[#68665D] uppercase font-extrabold tracking-wider">Semantic Sim (&gt;0.88) Blocked</div>
                        <div id="stat-semantic-blocked" class="text-lg font-extrabold text-[#8A5A10] mt-0.5">0</div>
                    </div>
                    <div class="bg-[#E3EFE2] border border-[#C5DDC3] rounded-xl p-3.5 text-center shadow-sm">
                        <div class="text-[10px] text-[#2E6E42] uppercase font-extrabold tracking-wider">Approved MCQs</div>
                        <div id="stat-approved" class="text-lg font-extrabold text-[#2E6E42] mt-0.5">30</div>
                    </div>
                </div>

                <!-- QUESTIONS CONTAINER -->
                <div id="questions-list-container" class="space-y-4">
                    <!-- Rendered via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: INGEST PAST PAPER PDFS -->
<div id="past-paper-modal" class="hidden fixed inset-0 bg-[#1B1A17]/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl w-full max-w-lg p-6 text-[#1B1A17] shadow-2xl">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-[#E1DFD7]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#F6E4E1] text-[#A2412C] flex items-center justify-center text-lg font-extrabold">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#1B1A17] font-display">Ingest Past Paper PDFs</h3>
                    <p class="text-xs text-[#68665D]">Upload 1–10 Cambridge / Edexcel past paper PDFs</p>
                </div>
            </div>
            <button type="button" onclick="closePastPaperUploadModal()" class="text-[#68665D] hover:text-[#1B1A17] text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="upload-past-papers-form" onsubmit="handleUploadPastPapers(event)" class="space-y-4">
            <div>
                <label for="modal_upload_subject_id" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Target Subject <span class="text-[#A2412C]">*</span>
                </label>
                <select id="modal_upload_subject_id" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2.5 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
                    <option value="">-- Select Subject --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->subject_name ?? $sub->name }} ({{ $sub->subject_code ?? $sub->code ?? 'CAIE' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_exam_series" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Exam Series Tag (Optional)
                </label>
                <input type="text" id="modal_exam_series" placeholder="e.g. May/June 2023 Paper 12" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-medium text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
            </div>

            <div>
                <label for="modal_pdf_files" class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Select PDF Files (Up to 10 files) <span class="text-[#A2412C]">*</span>
                </label>
                <input type="file" id="modal_pdf_files" multiple accept="application/pdf" required class="w-full bg-[#FFFFFF] border border-dashed border-[#E1DFD7] rounded-xl p-5 text-xs text-[#68665D] text-center cursor-pointer">
                <div class="text-[11px] text-[#A19E92] mt-1.5">PDFs will be parsed for questions, options, and concept vectors.</div>
            </div>

            <div id="modal-upload-status" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" onclick="closePastPaperUploadModal()" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-4 py-2 rounded-xl text-xs font-bold">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-upload" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white px-5 py-2 rounded-xl text-xs font-extrabold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: SAVE / CONFIGURE MOCK EXAMINATION DELIVERY -->
<div id="save-mock-modal" class="hidden fixed inset-0 bg-[#1B1A17]/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl w-full max-w-lg p-6 text-[#1B1A17] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E1DFD7]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#F8E9D3] text-[#8A5A10] flex items-center justify-center text-lg font-extrabold">
                    <i class="fa-solid fa-floppy-disk"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#1B1A17] font-display">Save Mock Examination</h3>
                    <p class="text-xs text-[#68665D]">Configure storage &amp; optional student portal delivery</p>
                </div>
            </div>
            <button type="button" onclick="closeSaveModal()" class="text-[#68665D] hover:text-[#1B1A17] text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- PORTAL DELIVERY OPTIONAL TOGGLE -->
        <div class="bg-[#FFFFFF] border-2 border-[#E1DFD7] rounded-xl p-4 transition-all">
            <div class="flex items-start justify-between gap-4">
                <label class="font-extrabold text-sm text-[#1B1A17] flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" id="modal_deliver_on_portal" onchange="togglePortalDeliveryOptions(this.checked)" class="w-4 h-4 rounded text-[#D48A2E] focus:ring-[#D48A2E] border-[#E1DFD7] accent-[#D48A2E]">
                    <span>Deliver on Student Online Portal (Optional)</span>
                </label>
                <span id="portal-mode-pill" class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-md bg-[#F2EFEB] text-[#68665D] uppercase tracking-wider">
                    Offline / Print Only
                </span>
            </div>
            <p class="text-xs text-[#68665D] mt-2 leading-relaxed">
                Leave unchecked if you only want to save this mock for physical printing or in-class paper exams. Check it if students will take this examination online via their student portals.
            </p>
        </div>

        <!-- DYNAMIC SCHEDULE FIELDS -->
        <div id="modal-portal-schedule-fields" class="hidden space-y-3 p-4 bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl transition-all">
            <div class="text-xs font-bold text-[#1B1A17] flex items-center gap-2 mb-1">
                <i class="fa-solid fa-clock text-[#D48A2E]"></i> Examination Schedule Window
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-[#68665D] mb-1">Scheduled Date</label>
                    <input type="date" id="modal_scheduled_date" value="{{ date('Y-m-d') }}" class="w-full bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#68665D] mb-1">Duration Limit (Minutes)</label>
                    <input type="number" id="modal_duration_minutes" value="45" min="5" max="300" class="w-full bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-[#68665D] mb-1">Opens (Start Time)</label>
                    <input type="time" id="modal_start_time" value="{{ date('H:i') }}" class="w-full bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#68665D] mb-1">Cutoff (End Time)</label>
                    <input type="time" id="modal_end_time" value="{{ date('H:i', strtotime('+120 minutes')) }}" class="w-full bg-[#F9F8F5] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17]">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E1DFD7]">
            <button type="button" onclick="closeSaveModal()" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] font-bold text-xs px-4 py-2.5 rounded-xl">
                Cancel
            </button>
            <button type="button" onclick="submitPublishMock()" id="btn-modal-save" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs px-5 py-2.5 rounded-xl shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-check"></i> <span id="btn-modal-save-text">Save Offline Paper</span>
            </button>
        </div>
    </div>
</div>

<script>
let currentGeneratedExam = null;

function handleMcqCountChange(count) {
    document.getElementById('mock_custom_mcqs').value = '';
    [10, 20, 30, 40].forEach(num => {
        const lbl = document.getElementById(`lbl-mcq-${num}`);
        if (lbl) {
            if (num === count) {
                lbl.classList.add('border-[#D48A2E]');
                lbl.classList.remove('border-[#E1DFD7]');
                const subSpan = lbl.querySelector('span:last-child');
                if (subSpan) { subSpan.classList.add('text-[#D48A2E]'); subSpan.classList.remove('text-[#68665D]'); }
            } else {
                lbl.classList.add('border-[#E1DFD7]');
                lbl.classList.remove('border-[#D48A2E]');
                const subSpan = lbl.querySelector('span:last-child');
                if (subSpan) { subSpan.classList.add('text-[#68665D]'); subSpan.classList.remove('text-[#D48A2E]'); }
            }
        }
    });

    const durationMap = { 10: 15, 20: 30, 30: 45, 40: 60 };
    const durInput = document.getElementById('mock_duration_minutes');
    if (durInput && durationMap[count]) {
        durInput.value = durationMap[count];
    }
}

function handleCustomMcqInput(val) {
    const num = parseInt(val, 10);
    if (!isNaN(num) && num > 0) {
        // Deselect standard radios
        [10, 20, 30, 40].forEach(n => {
            const lbl = document.getElementById(`lbl-mcq-${n}`);
            if (lbl) {
                lbl.classList.add('border-[#E1DFD7]');
                lbl.classList.remove('border-[#D48A2E]');
                const subSpan = lbl.querySelector('span:last-child');
                if (subSpan) { subSpan.classList.add('text-[#68665D]'); subSpan.classList.remove('text-[#D48A2E]'); }
                const radio = lbl.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
            }
        });
        const durInput = document.getElementById('mock_duration_minutes');
        if (durInput) {
            durInput.value = Math.max(15, Math.round(num * 1.5));
        }
    }
}

function handleOptionsCountChange(count) {
    [3, 4, 5].forEach(num => {
        const lbl = document.getElementById(`lbl-opt-${num}`);
        if (lbl) {
            if (num === count) {
                lbl.classList.add('border-[#D48A2E]');
                lbl.classList.remove('border-[#E1DFD7]');
                lbl.querySelector('span:last-child').classList.add('text-[#D48A2E]');
                lbl.querySelector('span:last-child').classList.remove('text-[#68665D]');
            } else {
                lbl.classList.add('border-[#E1DFD7]');
                lbl.classList.remove('border-[#D48A2E]');
                lbl.querySelector('span:last-child').classList.add('text-[#68665D]');
                lbl.querySelector('span:last-child').classList.remove('text-[#D48A2E]');
            }
        }
    });
}

async function handleSubjectChange(subjectId) {
    if (!subjectId) return;

    const banner = document.getElementById('subject-exemplar-banner');
    const countBadge = document.getElementById('exemplar-count-badge');
    const statusText = document.getElementById('exemplar-status-text');
    const tagsContainer = document.getElementById('topic-tags-container');

    statusText.innerText = 'Checking past paper coverage...';

    try {
        const res = await fetch(`{{ url('lms/mocks/subject-status') }}/${subjectId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();

        if (data.success) {
            countBadge.innerText = `${data.exemplar_count} Indexed`;
            statusText.innerText = `${data.past_paper_uploads.length} series papers uploaded (${data.exemplar_count} question exemplars).`;

            if (data.known_topics && data.known_topics.length > 0) {
                tagsContainer.innerHTML = data.known_topics.map(t => 
                    `<button type="button" onclick="toggleTopicTag('${t}')" class="bg-[#FFFFFF] text-[#68665D] border border-[#E1DFD7] text-[10px] font-bold px-2 py-0.5 rounded-md hover:border-[#D48A2E] hover:text-[#D48A2E] transition-all">${t}</button>`
                ).join('');
            } else {
                tagsContainer.innerHTML = '<span class="text-[11px] text-[#A19E92]">No past paper topics found yet. Use custom input below.</span>';
            }
        }
    } catch (e) {
        console.error("Subject status error", e);
    }
}

function toggleTopicTag(topic) {
    const input = document.getElementById('mock_custom_topics');
    let current = input.value ? input.value.split(',').map(s => s.trim()).filter(Boolean) : [];
    if (!current.includes(topic)) {
        current.push(topic);
    } else {
        current = current.filter(t => t !== topic);
    }
    input.value = current.join(', ');
}

function openPastPaperUploadModal() {
    const modal = document.getElementById('past-paper-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePastPaperUploadModal() {
    const modal = document.getElementById('past-paper-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function handleUploadPastPapers(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-upload');
    const statusDiv = document.getElementById('modal-upload-status');
    const subjectId = document.getElementById('modal_upload_subject_id').value;
    const series = document.getElementById('modal_exam_series').value;
    const files = document.getElementById('modal_pdf_files').files;

    if (!files || files.length === 0) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Ingesting & Extracting...';
    statusDiv.classList.remove('hidden');
    statusDiv.className = 'p-3 rounded-xl text-xs font-semibold bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC]';
    statusDiv.innerText = `Parsing ${files.length} Past Paper PDFs for Cambridge MCQ exemplars...`;

    const formData = new FormData();
    formData.append('subject_id', subjectId);
    formData.append('exam_series', series);
    for (let i = 0; i < files.length; i++) {
        formData.append('past_papers[]', files[i]);
    }

    try {
        const res = await fetch(`{{ route('lms.mocks.uploadPastPapers') }}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            statusDiv.className = 'p-3 rounded-xl text-xs font-semibold bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3]';
            statusDiv.innerText = `Success: Ingested ${data.total_exemplars_extracted} MCQs across ${files.length} papers.`;
            setTimeout(() => {
                closePastPaperUploadModal();
                handleSubjectChange(subjectId);
            }, 1200);
        } else {
            statusDiv.className = 'p-3 rounded-xl text-xs font-semibold bg-[#F6E4E1] text-[#A2412C] border border-[#ECCAC4]';
            statusDiv.innerText = data.message || 'Error processing past paper PDFs.';
        }
    } catch (err) {
        statusDiv.className = 'p-3 rounded-xl text-xs font-semibold bg-[#F6E4E1] text-[#A2412C] border border-[#ECCAC4]';
        statusDiv.innerText = `Upload failed: ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest';
    }
}

async function handleGenerateMock(e) {
    if (e) e.preventDefault();

    const subjectId = document.getElementById('mock_subject_id').value;
    const examStandard = document.getElementById('mock_exam_standard').value;
    
    const customMcqVal = document.getElementById('mock_custom_mcqs')?.value;
    const radioMcq = document.querySelector('input[name="total_mcqs"]:checked');
    const totalMcqs = (customMcqVal && parseInt(customMcqVal, 10) > 0) 
        ? parseInt(customMcqVal, 10) 
        : (radioMcq ? parseInt(radioMcq.value, 10) : 30);

    const optionsRadio = document.querySelector('input[name="options_per_mcq"]:checked');
    const optionsPerMcq = optionsRadio ? optionsRadio.value : 4;
    const topics = document.getElementById('mock_custom_topics').value;

    if (!subjectId) {
        alert("Please select a subject.");
        return;
    }

    // UI State
    document.getElementById('mock-placeholder-card').classList.add('hidden');
    document.getElementById('mock-results-card').classList.add('hidden');
    document.getElementById('mock-progress-card').classList.remove('hidden');

    const btn = document.getElementById('btn-generate-mock');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Generating Cambridge Mock...';

    // Simulated phase updates
    const phaseText = document.getElementById('mock-progress-phase');
    setTimeout(() => { phaseText.innerText = "Step 2/4: Loading 8-10 Cambridge Past Paper Exemplars..."; }, 1500);
    setTimeout(() => { phaseText.innerText = "Step 3/4: Prompting Chief Examiner (Groq LLM) for novel MCQs..."; }, 3200);
    setTimeout(() => { phaseText.innerText = "Step 4/4: Filtering collisions through Normalized Deduplication Matrix..."; }, 5000);

    try {
        const res = await fetch(`{{ route('lms.mocks.generate') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject_id: subjectId,
                exam_standard: examStandard,
                total_mcqs: totalMcqs,
                options_per_mcq: optionsPerMcq,
                topics: topics
            })
        });

        const data = await res.json();

        if (data.success) {
            currentGeneratedExam = data;
            renderGeneratedExam(data);
        } else {
            alert(`Generation Error: ${data.message || 'Unknown error'}`);
            document.getElementById('mock-placeholder-card').classList.remove('hidden');
        }
    } catch (err) {
        alert(`Generation request failed: ${err.message}`);
        document.getElementById('mock-placeholder-card').classList.remove('hidden');
    } finally {
        document.getElementById('mock-progress-card').classList.add('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Cambridge Mock';
    }
}

function renderGeneratedExam(data) {
    document.getElementById('mock-results-card').classList.remove('hidden');

    document.getElementById('res-mock-title').innerText = data.title || 'Cambridge O Level Mock Examination';
    document.getElementById('res-total-count').innerText = data.questions.length;
    document.getElementById('res-opt-count').innerText = data.options_per_mcq || 4;
    
    const durInput = document.getElementById('mock_duration_minutes');
    document.getElementById('res-time-limit').innerText = durInput ? durInput.value : (data.time_limit_minutes || 45);

    document.getElementById('stat-evaluated').innerText = data.stats.total_evaluated;
    document.getElementById('stat-exact-blocked').innerText = data.stats.exact_hash_blocked;
    document.getElementById('stat-semantic-blocked').innerText = data.stats.semantic_similarity_blocked;
    document.getElementById('stat-approved').innerText = data.stats.approved_questions;

    const list = document.getElementById('questions-list-container');
    list.innerHTML = data.questions.map((q, idx) => {
        const optionKeys = Object.keys(q.options || {}).sort();
        return `
        <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex justify-between items-start gap-4">
                <div class="flex items-center gap-2">
                    <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-xs px-2.5 py-0.5 rounded-md font-display">
                        Q${idx + 1}
                    </span>
                    <span class="text-xs text-[#68665D] font-bold">1 Mark</span>
                </div>
                <span class="text-[11px] bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold px-2 py-0.5 rounded-md">
                    ${q.topic || 'Cambridge Core Syllabus'}
                </span>
            </div>

            <div class="text-sm font-semibold text-[#1B1A17] leading-relaxed">
                ${q.question_stem}
            </div>

            <div class="space-y-1.5 pt-1">
                ${optionKeys.map(k => {
                    const isCorrect = k === q.correct_answer;
                    return `
                    <div class="flex items-center gap-2.5 p-2.5 rounded-xl border text-xs font-semibold ${isCorrect ? 'bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42]' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17]'}">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center font-extrabold text-[11px] ${isCorrect ? 'bg-[#2E6E42] text-white' : 'bg-[#F2EFEB] text-[#68665D] border border-[#E1DFD7]'}">
                            ${k}
                        </span>
                        <span class="flex-1">${q.options[k]}</span>
                        ${isCorrect ? '<i class="fa-solid fa-circle-check text-[#2E6E42]"></i>' : ''}
                    </div>
                    `;
                }).join('')}
            </div>

            ${q.explanation ? `
            <div class="bg-[#F2EFEB] border border-[#E1DFD7] rounded-xl p-3 text-xs text-[#68665D] mt-2">
                <span class="font-extrabold text-[#8A5A10] uppercase text-[10px] block mb-0.5">Chief Examiner Rationale &amp; Textbook Citation:</span>
                ${q.explanation}
            </div>
            ` : ''}
        </div>
        `;
    }).join('');
}

function openSaveModal() {
    if (!currentGeneratedExam) {
        alert("Please generate a mock exam first.");
        return;
    }
    const modal = document.getElementById('save-mock-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeSaveModal() {
    const modal = document.getElementById('save-mock-modal');
    if (modal) modal.classList.add('hidden');
}

function togglePortalDeliveryOptions(isPortal) {
    const fields = document.getElementById('modal-portal-schedule-fields');
    const pill = document.getElementById('portal-mode-pill');
    const btnText = document.getElementById('btn-modal-save-text');

    if (isPortal) {
        if (fields) fields.classList.remove('hidden');
        if (pill) {
            pill.textContent = 'Online Portal Delivery';
            pill.className = 'px-2.5 py-0.5 text-[10px] font-extrabold rounded-md bg-[#E3EFE2] text-[#2E6E42] uppercase tracking-wider';
        }
        if (btnText) btnText.textContent = 'Publish to Student Portal';
    } else {
        if (fields) fields.classList.add('hidden');
        if (pill) {
            pill.textContent = 'Offline / Print Only';
            pill.className = 'px-2.5 py-0.5 text-[10px] font-extrabold rounded-md bg-[#F2EFEB] text-[#68665D] uppercase tracking-wider';
        }
        if (btnText) btnText.textContent = 'Save Offline Paper';
    }
}

async function submitPublishMock() {
    if (!currentGeneratedExam) return;

    const classSectionId = document.getElementById('mock_class_section_id').value;
    const academicTermId = document.getElementById('mock_academic_term_id').value;
    const deliverOnPortal = document.getElementById('modal_deliver_on_portal')?.checked || false;

    const scheduledDate = document.getElementById('modal_scheduled_date')?.value || '{{ date('Y-m-d') }}';
    const startTime = document.getElementById('modal_start_time')?.value || '{{ date('H:i') }}';
    const endTime = document.getElementById('modal_end_time')?.value || '{{ date('H:i', strtotime('+120 minutes')) }}';
    const durationInput = document.getElementById('modal_duration_minutes');
    const durationMinutes = durationInput ? parseInt(durationInput.value, 10) : (currentGeneratedExam.time_limit_minutes || 45);

    if (!academicTermId) {
        alert("Please specify Academic Term before saving.");
        return;
    }

    const btn = document.getElementById('btn-modal-save');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Saving...';

    try {
        const payload = {
            subject_id: currentGeneratedExam.subject_id,
            class_section_id: classSectionId || null,
            academic_term_id: academicTermId,
            title: currentGeneratedExam.title,
            exam_standard: currentGeneratedExam.exam_standard,
            options_per_mcq: currentGeneratedExam.options_per_mcq || 4,
            time_limit_minutes: durationMinutes,
            scheduled_date: scheduledDate,
            start_time: startTime,
            end_time: endTime,
            deliver_on_portal: deliverOnPortal,
            questions: currentGeneratedExam.questions
        };

        const res = await fetch(`{{ route('lms.mocks.publish') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            alert(data.message || "Mock Examination successfully saved!");
            closeSaveModal();
            window.location.href = data.redirect_url || `{{ route('lms.mocks.index') }}`;
        } else {
            alert(`Saving failed: ${data.message || 'Unknown error'}`);
        }
    } catch (err) {
        alert(`Saving error: ${err.message}`);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span id="btn-modal-save-text">Save Examination</span>';
    }
}

function printCurrentMock(withAnswers) {
    if (!currentGeneratedExam || !currentGeneratedExam.questions) {
        alert("Please generate a mock exam first.");
        return;
    }

    const subSelect = document.getElementById('mock_subject_id');
    const subName = subSelect && subSelect.selectedIndex > 0 ? subSelect.options[subSelect.selectedIndex].text : 'Subject';
    const examTitle = currentGeneratedExam.title || 'Cambridge Mock Examination';
    const totalQ = currentGeneratedExam.questions.length;
    const durationMins = currentGeneratedExam.time_limit_minutes || 45;
    const standard = (currentGeneratedExam.exam_standard || 'CAIE O LEVEL').toUpperCase().replace(/_/g, ' ');

    const printWindow = window.open('', '_blank');
    let questionsHtml = '';

    currentGeneratedExam.questions.forEach((q, idx) => {
        const optionKeys = Object.keys(q.options || {}).sort();
        const correctKey = (q.correct_answer || '').toUpperCase().trim();

        let optsHtml = '';
        optionKeys.forEach(k => {
            const isCorrect = withAnswers && k === correctKey;
            optsHtml += `
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:4px;font-size:12px;${isCorrect ? 'color:#2E6E42;font-weight:bold' : ''}">
                    <span style="font-weight:bold;min-width:18px">${k}</span>
                    <span style="flex:1">${q.options[k]} ${isCorrect ? '<strong>[✓ Correct]</strong>' : ''}</span>
                </div>
            `;
        });

        let rationaleHtml = '';
        if (withAnswers && q.explanation) {
            rationaleHtml = `
                <div style="margin-top:4px;margin-left:26px;background:#F4F2EB;border-left:3px solid #D48A2E;padding:4px 8px;font-size:10px;color:#555">
                    <strong>Topic / Citation:</strong> ${q.topic || 'Syllabus'}<br/>
                    <strong>Chief Examiner Rationale:</strong> ${q.explanation}
                </div>
            `;
        }

        questionsHtml += `
            <div style="margin-bottom:18px;page-break-inside:avoid;break-inside:avoid">
                <div style="display:flex;gap:8px;align-items:baseline;margin-bottom:6px">
                    <span style="font-weight:bold;font-size:13px;min-width:24px">${idx + 1}</span>
                    <div style="flex:1;font-size:12.5px;font-weight:500">${q.question_stem}</div>
                    <span style="font-size:11px;font-weight:bold;color:#444">[1]</span>
                </div>
                <div style="margin-left:26px">
                    ${optsHtml}
                </div>
                ${rationaleHtml}
            </div>
        `;
    });

    let markingKeyHtml = '';
    if (withAnswers) {
        let keyRows = '';
        currentGeneratedExam.questions.forEach((q, i) => {
            keyRows += `
                <tr>
                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold">Q${i + 1}</td>
                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:900;color:#2E6E42">${(q.correct_answer || 'A').toUpperCase()}</td>
                    <td style="border:1px solid #000;padding:4px;font-size:10px">${q.topic || 'Syllabus'}</td>
                </tr>
            `;
        });
        markingKeyHtml = `
            <div style="page-break-before:always;margin-top:30px;padding-top:20px;border-top:2px solid #000">
                <h3 style="text-align:center;text-transform:uppercase;font-size:14px;margin:0 0 10px 0">Official Marking Scheme & Answer Key</h3>
                <table style="width:100%;max-width:500px;margin:0 auto;border-collapse:collapse;border:1px solid #000;font-size:11px">
                    <thead>
                        <tr style="background:#EEEEEE">
                            <th style="border:1px solid #000;padding:4px">Q#</th>
                            <th style="border:1px solid #000;padding:4px">Key</th>
                            <th style="border:1px solid #000;padding:4px">Topic</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${keyRows}
                    </tbody>
                </table>
            </div>
        `;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${examTitle} - ${withAnswers ? 'Marking Scheme' : 'Question Paper'}</title>
            <style>
                @page { size: A4 portrait; margin: 15mm 12mm 15mm 12mm; }
                body { font-family: Helvetica, Arial, sans-serif; color: #000; margin: 0; padding: 10px; line-height: 1.4; }
                * { box-sizing: border-box; }
            </style>
        </head>
        <body>
            <div style="border-bottom:2px solid #000;padding-bottom:12px;margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <h2 style="margin:0;font-size:16px;text-transform:uppercase">${subName}</h2>
                        <div style="font-size:12px;font-weight:bold;margin-top:2px">${examTitle}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="border:1px solid #000;padding:2px 8px;font-weight:bold;font-size:10px;display:inline-block">${standard}</div>
                        <div style="font-size:11px;font-weight:bold;margin-top:4px">Time: ${durationMins} Minutes</div>
                        <div style="font-size:11px;color:#555">Total: ${totalQ} Marks</div>
                    </div>
                </div>
                <table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:11px">
                    <tr>
                        <td style="border:1px solid #000;padding:4px;width:20%;background:#F0F0F0;font-weight:bold">Candidate Name:</td>
                        <td style="border:1px solid #000;padding:4px;width:40%"></td>
                        <td style="border:1px solid #000;padding:4px;width:20%;background:#F0F0F0;font-weight:bold">Center Number:</td>
                        <td style="border:1px solid #000;padding:4px;width:20%;text-align:center;font-weight:bold">PK 0 0 1</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000;padding:4px;background:#F0F0F0;font-weight:bold">Roll / Seat No:</td>
                        <td style="border:1px solid #000;padding:4px"></td>
                        <td style="border:1px solid #000;padding:4px;background:#F0F0F0;font-weight:bold">Candidate No:</td>
                        <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold">&nbsp;</td>
                    </tr>
                </table>
            </div>
            <div style="border:1px solid #333;background:#FAFAFA;padding:8px 12px;margin-bottom:16px;font-size:10px">
                <strong>READ THESE INSTRUCTIONS FIRST:</strong> Answer all questions. For each question there are four choices A, B, C, and D. Choose the ONE you consider correct and mark your answer clearly. Each correct answer scores one mark.
            </div>
            <div>
                ${questionsHtml}
            </div>
            ${markingKeyHtml}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 400);
}
</script>
@endsection

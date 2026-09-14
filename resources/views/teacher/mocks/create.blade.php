@extends('layouts.app')

@section('title', 'O/A Levels Mock Examination Generation Engine')
@section('page-header', 'O/A Levels Mock Examination Generation Engine')

@section('content')
<div class="mock-engine-container" style="background: #0F172A; min-height: calc(100vh - 120px); border-radius: 16px; padding: 24px; color: #F8FAFC; border: 1px solid #334155; font-family: inherit;">

    <!-- TOP HEADER -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="background: #14B8A6; color: #0F172A; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                    CAIE &amp; EDEXCEL STANDARD
                </span>
                <span style="background: rgba(99, 102, 241, 0.2); color: #A5B4FC; border: 1px solid #6366F1; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                    <i class="fa-solid fa-brain"></i> Few-Shot RAG Pipeline
                </span>
                <span style="background: rgba(16, 185, 129, 0.2); color: #6EE7B7; border: 1px solid #10B981; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                    <i class="fa-solid fa-shield-halved"></i> 100% Zero-Duplication
                </span>
            </div>
            <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #F8FAFC; letter-spacing: -0.5px;">
                O/A Levels Mock Examination Generation Engine
            </h1>
            <p style="font-size: 13px; color: #94A3B8; margin: 4px 0 0 0;">
                Autonomous high-rigor Cambridge O/A Level &amp; Edexcel exam creator with 8–10 PDF past-paper style conditioning and normalized collision filtering.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="openPastPaperUploadModal()" style="background: #1E293B; border: 1px solid #475569; color: #F8FAFC; padding: 9px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                <i class="fa-solid fa-file-pdf" style="color: #F43F5E;"></i> Ingest Past Paper PDFs
            </button>
            <a href="{{ route('lms.assessments.index') }}" style="background: transparent; border: 1px solid #334155; color: #94A3B8; padding: 9px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Assessments
            </a>
        </div>
    </div>

    <!-- MAIN GRID: CONFIGURATION (LEFT) + REAL-TIME PREVIEW & WORKFLOW (RIGHT) -->
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;" class="mock-layout-grid">

        <!-- LEFT COLUMN: CONFIGURATION PANEL -->
        <div style="background: #1E293B; border: 1px solid #334155; border-radius: 14px; padding: 20px;" class="mock-config-card">
            <h2 style="font-size: 15px; font-weight: 700; color: #14B8A6; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sliders"></i> Examination Parameters
            </h2>

            <form id="mock-config-form" onsubmit="handleGenerateMock(event)">
                <!-- SUBJECT SELECTOR -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Subject (O/A Level) <span style="color: #F43F5E;">*</span>
                    </label>
                    <select id="mock_subject_id" name="subject_id" required onchange="handleSubjectChange(this.value)" style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?? 'CAIE' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- SUBJECT STATUS & EXEMPLAR BADGE -->
                <div id="subject-exemplar-banner" style="background: #0F172A; border: 1px solid #334155; border-radius: 10px; padding: 12px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase;">Past Paper Exemplar Bank</span>
                        <span id="exemplar-count-badge" style="background: rgba(20, 184, 166, 0.15); color: #14B8A6; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 4px;">
                            0 Indexed
                        </span>
                    </div>
                    <p id="exemplar-status-text" style="font-size: 11px; color: #64748B; margin: 0;">
                        Select a subject to inspect uploaded Cambridge past paper coverage.
                    </p>
                </div>

                <!-- EXAM STANDARD -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Examination Standard <span style="color: #F43F5E;">*</span>
                    </label>
                    <select id="mock_exam_standard" name="exam_standard" required style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
                        <option value="caie_o_level">Cambridge CAIE O Level (Paper 1 MCQ)</option>
                        <option value="caie_a_level">Cambridge CAIE A Level (Paper 1 MCQ)</option>
                        <option value="edexcel_igcse">Pearson Edexcel IGCSE Standard MCQ</option>
                    </select>
                </div>

                <!-- QUESTION COUNT & DURATION TOGGLE -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Question Volume &amp; Standard Timing <span style="color: #F43F5E;">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <label id="lbl-mcq-30" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px; background: #0F172A; border: 2px solid #14B8A6; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="total_mcqs" value="30" checked onchange="handleMcqCountChange(30)" style="display: none;">
                            <span style="font-size: 15px; font-weight: 800; color: #F8FAFC;">30 MCQs</span>
                            <span style="font-size: 11px; color: #14B8A6; font-weight: 600;">45 Minutes</span>
                        </label>
                        <label id="lbl-mcq-40" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px; background: #0F172A; border: 2px solid #334155; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="total_mcqs" value="40" onchange="handleMcqCountChange(40)" style="display: none;">
                            <span style="font-size: 15px; font-weight: 800; color: #F8FAFC;">40 MCQs</span>
                            <span style="font-size: 11px; color: #94A3B8; font-weight: 600;">60 Minutes</span>
                        </label>
                    </div>
                </div>

                <!-- SYLLABUS TOPICS -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Focus Topics / Syllabus Chapters
                    </label>
                    <div id="topic-tags-container" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; min-height: 24px;">
                        <span style="font-size: 11px; color: #64748B;">Select a subject to view indexed syllabus topics</span>
                    </div>
                    <input type="text" id="mock_custom_topics" placeholder="Custom topics (comma separated)..." style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 8px 10px; color: #F8FAFC; font-size: 12px; outline: none;">
                </div>

                <!-- TARGET CLASS SECTION & ACADEMIC TERM -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Target Class Section <span style="color: #F43F5E;">*</span>
                    </label>
                    <select id="mock_class_section_id" name="class_section_id" required style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
                        <option value="">-- Select Section --</option>
                        @foreach($classSections as $sec)
                            <option value="{{ $sec->id }}">{{ $sec->instituteClass->name ?? 'Class' }} - {{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                        Academic Term <span style="color: #F43F5E;">*</span>
                    </label>
                    <select id="mock_academic_term_id" name="academic_term_id" required style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- GENERATE ACTION BUTTON -->
                <button type="submit" id="btn-generate-mock" style="width: 100%; background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); color: #0F172A; font-weight: 800; font-size: 14px; padding: 12px; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(20, 184, 166, 0.35); transition: all 0.2s;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Generate Cambridge Mock</span>
                </button>
            </form>
        </div>

        <!-- RIGHT COLUMN: GENERATION WORKFLOW & LIVE EXAMINATION PREVIEW -->
        <div>
            <!-- EMPTY / INITIAL STATE BANNER -->
            <div id="mock-placeholder-card" style="background: #1E293B; border: 1px dashed #334155; border-radius: 14px; padding: 48px 24px; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.25); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                    <i class="fa-solid fa-graduation-cap" style="font-size: 28px; color: #6366F1;"></i>
                </div>
                <h3 style="font-size: 18px; font-weight: 700; color: #F8FAFC; margin: 0 0 8px 0;">
                    Ready for Cambridge Examination Generation
                </h3>
                <p style="font-size: 13px; color: #94A3B8; max-width: 520px; margin: 0 auto 20px auto; line-height: 1.6;">
                    Select your subject, question volume (30 or 40 MCQs), and configure syllabus topics. The engine will retrieve 8–10 past paper exemplars and apply 100% Zero-Duplication collision filtering.
                </p>
                <div style="display: inline-flex; gap: 16px; font-size: 12px; color: #CBD5E1;">
                    <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-check" style="color: #14B8A6;"></i> Pure 4-Option MCQs</span>
                    <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-check" style="color: #14B8A6;"></i> Cambridge Distractor Rationale</span>
                    <span style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-check" style="color: #14B8A6;"></i> Collision-Free Stem Hashing</span>
                </div>
            </div>

            <!-- GENERATION IN PROGRESS ANIMATION & STEPPER -->
            <div id="mock-progress-card" style="display: none; background: #1E293B; border: 1px solid #6366F1; border-radius: 14px; padding: 36px 24px; text-align: center;">
                <div style="display: inline-block; position: relative; margin-bottom: 20px;">
                    <div style="width: 56px; height: 56px; border: 4px solid rgba(99, 102, 241, 0.2); border-top-color: #6366F1; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; color: #F8FAFC; margin: 0 0 6px 0;">
                    Engineering Cambridge Mock Examination...
                </h3>
                <p id="mock-progress-phase" style="font-size: 13px; color: #A5B4FC; margin: 0 0 20px 0; font-weight: 600;">
                    Step 1/4: Retrieving textbook syllabus chunks &amp; concept vectors...
                </p>

                <!-- PIPELINE STEPS -->
                <div style="max-width: 480px; margin: 0 auto; text-align: left; display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                    <div id="step-rag" style="display: flex; align-items: center; gap: 10px; color: #94A3B8;">
                        <i class="fa-regular fa-circle-dot" style="color: #6366F1;"></i> RAG Textbook &amp; Syllabus Context Retrieval
                    </div>
                    <div id="step-fewshot" style="display: flex; align-items: center; gap: 10px; color: #64748B;">
                        <i class="fa-regular fa-circle"></i> Past Paper Exemplars (Few-Shot Style Conditioning)
                    </div>
                    <div id="step-groq" style="display: flex; align-items: center; gap: 10px; color: #64748B;">
                        <i class="fa-regular fa-circle"></i> High-Rigor Cambridge Question Setter (Groq LLM)
                    </div>
                    <div id="step-dedup" style="display: flex; align-items: center; gap: 10px; color: #64748B;">
                        <i class="fa-regular fa-circle"></i> Zero-Duplication Normalized Collision Matrix (&gt;0.88 Cosine)
                    </div>
                </div>
            </div>

            <!-- GENERATION RESULTS & LIVE MCQ REVIEW -->
            <div id="mock-results-card" style="display: none;">
                <!-- SUMMARY ACTION BAR -->
                <div style="background: #1E293B; border: 1px solid #334155; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                    <div>
                        <h3 id="res-mock-title" style="font-size: 16px; font-weight: 800; color: #F8FAFC; margin: 0 0 4px 0;">
                            Cambridge O Level Mock Examination
                        </h3>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #94A3B8;">
                            <span><i class="fa-solid fa-list-check" style="color: #14B8A6;"></i> <strong id="res-total-count" style="color:#F8FAFC;">30</strong> Questions</span>
                            <span><i class="fa-solid fa-stopwatch" style="color: #6366F1;"></i> <strong id="res-time-limit" style="color:#F8FAFC;">45</strong> Mins</span>
                            <span><i class="fa-solid fa-shield-check" style="color: #10B981;"></i> 0% Duplication Collision</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="handleGenerateMock(event)" style="background: #0F172A; border: 1px solid #475569; color: #CBD5E1; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-rotate"></i> Regenerate
                        </button>
                        <button type="button" onclick="handlePublishMock()" id="btn-publish-mock" style="background: #14B8A6; color: #0F172A; font-weight: 800; font-size: 13px; padding: 8px 18px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 10px rgba(20, 184, 166, 0.3);">
                            <i class="fa-solid fa-circle-check"></i> Publish Official Mock Exam
                        </button>
                    </div>
                </div>

                <!-- DEDUPLICATION STATS STRIP -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
                    <div style="background: #1E293B; border: 1px solid #334155; border-radius: 10px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #94A3B8; text-transform: uppercase; font-weight: 700;">Candidates Evaluated</div>
                        <div id="stat-evaluated" style="font-size: 18px; font-weight: 800; color: #F8FAFC; margin-top: 2px;">30</div>
                    </div>
                    <div style="background: #1E293B; border: 1px solid #334155; border-radius: 10px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #94A3B8; text-transform: uppercase; font-weight: 700;">Exact Hashes Blocked</div>
                        <div id="stat-exact-blocked" style="font-size: 18px; font-weight: 800; color: #F43F5E; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: #1E293B; border: 1px solid #334155; border-radius: 10px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #94A3B8; text-transform: uppercase; font-weight: 700;">Semantic Sim (&gt;0.88) Blocked</div>
                        <div id="stat-semantic-blocked" style="font-size: 18px; font-weight: 800; color: #F59E0B; margin-top: 2px;">0</div>
                    </div>
                    <div style="background: #1E293B; border: 1px solid #14B8A6; border-radius: 10px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #14B8A6; text-transform: uppercase; font-weight: 700;">Approved MCQs</div>
                        <div id="stat-approved" style="font-size: 18px; font-weight: 800; color: #14B8A6; margin-top: 2px;">30</div>
                    </div>
                </div>

                <!-- QUESTIONS CONTAINER -->
                <div id="questions-list-container" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Rendered via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: INGEST PAST PAPER PDFS (8–10 PAPERS) -->
<div id="past-paper-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #1E293B; border: 1px solid #475569; border-radius: 16px; width: 540px; max-width: 90vw; padding: 24px; color: #F8FAFC; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(244, 63, 94, 0.15); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-file-pdf" style="color: #F43F5E;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Ingest Past Paper PDFs</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: #94A3B8;">Upload 1–10 Cambridge / Edexcel past paper PDFs</p>
                </div>
            </div>
            <button type="button" onclick="closePastPaperUploadModal()" style="background: none; border: none; color: #94A3B8; font-size: 18px; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="upload-past-papers-form" onsubmit="handleUploadPastPapers(event)">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                    Target Subject <span style="color: #F43F5E;">*</span>
                </label>
                <select id="modal_upload_subject_id" required style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
                    <option value="">-- Select Subject --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code ?? 'CAIE' }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                    Exam Series Tag (Optional)
                </label>
                <input type="text" id="modal_exam_series" placeholder="e.g. May/June 2023 Paper 12" style="width: 100%; background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 9px 12px; color: #F8FAFC; font-size: 13px; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #CBD5E1; margin-bottom: 6px;">
                    Select PDF Files (Up to 10 files) <span style="color: #F43F5E;">*</span>
                </label>
                <input type="file" id="modal_pdf_files" multiple accept="application/pdf" required style="width: 100%; background: #0F172A; border: 1px dashed #475569; border-radius: 8px; padding: 16px; color: #CBD5E1; font-size: 12px; text-align: center; cursor: pointer;">
                <div style="font-size: 11px; color: #64748B; margin-top: 4px;">PDFs will be parsed for questions, options (A, B, C, D), and concept vectors.</div>
            </div>

            <div id="modal-upload-status" style="display: none; margin-bottom: 14px; padding: 10px; border-radius: 8px; font-size: 12px;"></div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closePastPaperUploadModal()" style="background: #0F172A; border: 1px solid #334155; color: #94A3B8; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-upload" style="background: #14B8A6; color: #0F172A; font-weight: 800; font-size: 13px; padding: 8px 18px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
.mock-engine-container input:focus, .mock-engine-container select:focus {
    border-color: #14B8A6 !important;
}
@media (max-width: 900px) {
    .mock-layout-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
let currentGeneratedExam = null;

function handleMcqCountChange(count) {
    const lbl30 = document.getElementById('lbl-mcq-30');
    const lbl40 = document.getElementById('lbl-mcq-40');
    if (count === 30) {
        lbl30.style.borderColor = '#14B8A6';
        lbl40.style.borderColor = '#334155';
    } else {
        lbl30.style.borderColor = '#334155';
        lbl40.style.borderColor = '#14B8A6';
    }
}

async function handleSubjectChange(subjectId) {
    const bannerText = document.getElementById('exemplar-status-text');
    const badge = document.getElementById('exemplar-count-badge');
    const topicContainer = document.getElementById('topic-tags-container');

    if (!subjectId) {
        badge.innerText = '0 Indexed';
        bannerText.innerText = 'Select a subject to inspect uploaded Cambridge past paper coverage.';
        topicContainer.innerHTML = '<span style="font-size: 11px; color: #64748B;">Select a subject to view indexed syllabus topics</span>';
        return;
    }

    try {
        const res = await fetch(`{{ url('/lms/mocks/subject-status') }}/${subjectId}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (data.success) {
            badge.innerText = `${data.exemplar_count} Exemplars`;
            bannerText.innerText = `${data.past_paper_uploads.length} past paper PDF(s) parsed. ${data.rag_indexed_count} textbook material(s) indexed for RAG.`;

            // Populate known topics
            topicContainer.innerHTML = '';
            if (data.known_topics && data.known_topics.length > 0) {
                data.known_topics.forEach(top => {
                    const tag = document.createElement('span');
                    tag.style.cssText = 'background: #0F172A; border: 1px solid #334155; color: #A5B4FC; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; transition: all 0.15s;';
                    tag.innerText = top;
                    tag.onclick = () => {
                        const customInput = document.getElementById('mock_custom_topics');
                        let current = customInput.value ? customInput.value.split(',').map(s => s.trim()) : [];
                        if (!current.includes(top)) {
                            current.push(top);
                            customInput.value = current.join(', ');
                        }
                    };
                    topicContainer.appendChild(tag);
                });
            } else {
                topicContainer.innerHTML = '<span style="font-size: 11px; color: #64748B;">No specific topic tags yet. Enter topics below or upload past papers.</span>';
            }
        }
    } catch (e) {
        console.error("Failed fetching subject status:", e);
    }
}

function openPastPaperUploadModal() {
    const currentSubjectId = document.getElementById('mock_subject_id').value;
    if (currentSubjectId) {
        document.getElementById('modal_upload_subject_id').value = currentSubjectId;
    }
    document.getElementById('past-paper-modal').style.display = 'flex';
}

function closePastPaperUploadModal() {
    document.getElementById('past-paper-modal').style.display = 'none';
    document.getElementById('modal-upload-status').style.display = 'none';
}

async function handleUploadPastPapers(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-upload');
    const statusDiv = document.getElementById('modal-upload-status');
    const subjectId = document.getElementById('modal_upload_subject_id').value;
    const series = document.getElementById('modal_exam_series').value;
    const files = document.getElementById('modal_pdf_files').files;

    if (!files.length) {
        alert('Please select at least 1 PDF file.');
        return;
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('subject_id', subjectId);
    formData.append('exam_series', series);
    for (let i = 0; i < files.length; i++) {
        formData.append('past_papers[]', files[i]);
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Ingesting &amp; Parsing MCQs...';
    statusDiv.style.display = 'block';
    statusDiv.style.background = 'rgba(99, 102, 241, 0.15)';
    statusDiv.style.color = '#A5B4FC';
    statusDiv.style.border = '1px solid #6366F1';
    statusDiv.innerText = 'Uploading and extracting Cambridge standard MCQs & concept vectors...';

    try {
        const res = await fetch('{{ route('lms.mocks.uploadPastPapers') }}', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (data.success) {
            statusDiv.style.background = 'rgba(16, 185, 129, 0.15)';
            statusDiv.style.color = '#6EE7B7';
            statusDiv.style.border = '1px solid #10B981';
            statusDiv.innerText = data.message;
            handleSubjectChange(subjectId);
            setTimeout(() => {
                closePastPaperUploadModal();
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest';
            }, 1800);
        } else {
            statusDiv.style.background = 'rgba(244, 63, 94, 0.15)';
            statusDiv.style.color = '#FDA4AF';
            statusDiv.style.border = '1px solid #F43F5E';
            statusDiv.innerText = data.message || 'Error processing past papers.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest';
        }
    } catch (err) {
        statusDiv.style.background = 'rgba(244, 63, 94, 0.15)';
        statusDiv.style.color = '#FDA4AF';
        statusDiv.style.border = '1px solid #F43F5E';
        statusDiv.innerText = 'Network error during upload: ' + err.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Ingest';
    }
}

async function handleGenerateMock(e) {
    if (e && e.preventDefault) e.preventDefault();

    const subjectId = document.getElementById('mock_subject_id').value;
    if (!subjectId) {
        alert('Please select a subject.');
        return;
    }

    const examStandard = document.getElementById('mock_exam_standard').value;
    const totalMcqs = document.querySelector('input[name="total_mcqs"]:checked').value;
    const customTopics = document.getElementById('mock_custom_topics').value;
    const topics = customTopics ? customTopics.split(',').map(s => s.trim()).filter(Boolean) : [];

    // Switch UI states
    document.getElementById('mock-placeholder-card').style.display = 'none';
    document.getElementById('mock-results-card').style.display = 'none';
    document.getElementById('mock-progress-card').style.display = 'block';

    const progressPhase = document.getElementById('mock-progress-phase');
    const stepRag = document.getElementById('step-rag');
    const stepFewshot = document.getElementById('step-fewshot');
    const stepGroq = document.getElementById('step-groq');
    const stepDedup = document.getElementById('step-dedup');

    // Simulate animated stepper while request runs
    let step = 1;
    const interval = setInterval(() => {
        step++;
        if (step === 2) {
            progressPhase.innerText = 'Step 2/4: Retrieving Cambridge Few-Shot Past Paper Exemplars...';
            stepRag.style.color = '#10B981';
            stepFewshot.style.color = '#6366F1';
        } else if (step === 3) {
            progressPhase.innerText = 'Step 3/4: Querying Groq Chief Examiner LLM for high-rigor MCQs...';
            stepFewshot.style.color = '#10B981';
            stepGroq.style.color = '#6366F1';
        } else if (step === 4) {
            progressPhase.innerText = 'Step 4/4: Validating Zero-Duplication Normalized Collision Matrix (>0.88 Cosine)...';
            stepGroq.style.color = '#10B981';
            stepDedup.style.color = '#6366F1';
        }
    }, 1800);

    try {
        const res = await fetch('{{ route('lms.mocks.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject_id: subjectId,
                exam_standard: examStandard,
                total_mcqs: parseInt(totalMcqs),
                topics: topics
            })
        });

        clearInterval(interval);
        const data = await res.json();

        if (data.success && data.questions && data.questions.length > 0) {
            currentGeneratedExam = data;
            renderExamResults(data);
        } else {
            alert('Generation failed: ' + (data.message || 'No questions generated. Check Groq API configuration.'));
            document.getElementById('mock-progress-card').style.display = 'none';
            document.getElementById('mock-placeholder-card').style.display = 'block';
        }
    } catch (err) {
        clearInterval(interval);
        alert('Server or network error: ' + err.message);
        document.getElementById('mock-progress-card').style.display = 'none';
        document.getElementById('mock-placeholder-card').style.display = 'block';
    }
}

function renderExamResults(data) {
    document.getElementById('mock-progress-card').style.display = 'none';
    document.getElementById('mock-results-card').style.display = 'block';

    document.getElementById('res-mock-title').innerText = data.title;
    document.getElementById('res-total-count').innerText = data.total_mcqs;
    document.getElementById('res-time-limit').innerText = data.time_limit_minutes;

    if (data.deduplication_stats) {
        document.getElementById('stat-evaluated').innerText = data.deduplication_stats.total_candidates_evaluated || data.total_mcqs;
        document.getElementById('stat-exact-blocked').innerText = data.deduplication_stats.exact_duplicates_rejected || 0;
        document.getElementById('stat-semantic-blocked').innerText = data.deduplication_stats.semantic_duplicates_rejected || 0;
        document.getElementById('stat-approved').innerText = data.total_mcqs;
    }

    const container = document.getElementById('questions-list-container');
    container.innerHTML = '';

    data.questions.forEach((q, idx) => {
        const card = document.createElement('div');
        card.style.cssText = 'background: #1E293B; border: 1px solid #334155; border-radius: 12px; padding: 18px; transition: border-color 0.2s;';

        const cogColors = {
            'Recall': '#38BDF8',
            'Understanding': '#818CF8',
            'Application': '#34D399',
            'Analysis': '#F472B6'
        };
        const cogColor = cogColors[q.cognitive_level] || '#34D399';

        let optionsHtml = '';
        const keys = ['A', 'B', 'C', 'D'];
        keys.forEach(k => {
            const isCorrect = (q.correct_answer === k);
            const optStyle = isCorrect
                ? 'background: rgba(20, 184, 166, 0.12); border: 1px solid #14B8A6; color: #F8FAFC;'
                : 'background: #0F172A; border: 1px solid #334155; color: #CBD5E1;';
            const badgeStyle = isCorrect
                ? 'background: #14B8A6; color: #0F172A;'
                : 'background: #334155; color: #94A3B8;';

            optionsHtml += `
                <div style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 8px; ${optStyle}">
                    <span style="font-weight: 800; font-size: 11px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 5px; flex-shrink: 0; ${badgeStyle}">${k}</span>
                    <span style="font-size: 13px; line-height: 1.5;">${q.options[k] || ''}</span>
                    ${isCorrect ? '<span style="margin-left: auto; font-size: 11px; color: #14B8A6; font-weight: 700;"><i class="fa-solid fa-check"></i> Key Answer</span>' : ''}
                </div>
            `;
        });

        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background: #0F172A; border: 1px solid #475569; color: #F8FAFC; font-weight: 800; font-size: 12px; padding: 2px 8px; border-radius: 6px;">
                        Q${q.question_number}
                    </span>
                    <span style="font-size: 12px; font-weight: 600; color: #94A3B8;">
                        ${q.topic}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background: rgba(255, 255, 255, 0.05); color: ${cogColor}; border: 1px solid ${cogColor}40; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 5px;">
                        ${q.cognitive_level}
                    </span>
                    <span style="background: rgba(16, 185, 129, 0.1); color: #6EE7B7; border: 1px solid #10B98140; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 5px;" title="SHA-256 Stem: ${q.stem_hash}">
                        <i class="fa-solid fa-fingerprint"></i> Unique Hash Verified
                    </span>
                </div>
            </div>

            <div style="font-size: 14px; font-weight: 600; color: #F8FAFC; margin-bottom: 14px; line-height: 1.6;">
                ${q.question_stem}
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                ${optionsHtml}
            </div>

            ${q.explanation ? `
                <details style="background: #0F172A; border: 1px solid #334155; border-radius: 8px; padding: 8px 12px; font-size: 12px;">
                    <summary style="cursor: pointer; color: #A5B4FC; font-weight: 700; outline: none; user-select: none;">
                        <i class="fa-solid fa-chalkboard-user"></i> Cambridge Mark Scheme Rationale & Distractor Analysis
                    </summary>
                    <div style="color: #94A3B8; margin-top: 8px; line-height: 1.5;">
                        ${q.explanation}
                    </div>
                </details>
            ` : ''}
        `;

        container.appendChild(card);
    });
}

async function handlePublishMock() {
    if (!currentGeneratedExam || !currentGeneratedExam.questions || currentGeneratedExam.questions.length === 0) {
        alert('No generated exam to publish.');
        return;
    }

    const subjectId = document.getElementById('mock_subject_id').value;
    const classSectionId = document.getElementById('mock_class_section_id').value;
    const academicTermId = document.getElementById('mock_academic_term_id').value;

    if (!classSectionId) {
        alert('Please select a Target Class Section.');
        document.getElementById('mock_class_section_id').focus();
        return;
    }
    if (!academicTermId) {
        alert('Please select an Academic Term.');
        document.getElementById('mock_academic_term_id').focus();
        return;
    }

    const btn = document.getElementById('btn-publish-mock');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publishing Official Mock...';

    try {
        const res = await fetch('{{ route('lms.mocks.publish') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject_id: subjectId,
                class_section_id: classSectionId,
                academic_term_id: academicTermId,
                title: currentGeneratedExam.title,
                exam_standard: currentGeneratedExam.exam_standard,
                time_limit_minutes: currentGeneratedExam.time_limit_minutes,
                questions: currentGeneratedExam.questions
            })
        });

        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Publishing error: ' + (data.message || 'Unknown error.'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Publish Official Mock Exam';
        }
    } catch (err) {
        alert('Publishing error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Publish Official Mock Exam';
    }
}
</script>
@endsection

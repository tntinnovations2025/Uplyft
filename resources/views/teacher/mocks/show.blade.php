@extends('layouts.app')

@section('title', $assessment->title . ' - Mock Review & Editor')
@section('page-header', 'Mock Review & In-Place Question Editor')

@section('content')
<div class="mock-engine-container space-y-6 max-w-full overflow-x-hidden">

    <!-- TOP HEADER (Ink & Amber Daylight Surface) -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-6 shadow-sm flex flex-wrap justify-between items-start gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2 flex-wrap">
                <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-[11px] px-2.5 py-1 rounded-md uppercase tracking-wider font-display">
                    {{ strtoupper(str_replace('_', ' ', $assessment->exam_standard ?? 'CAIE O LEVEL')) }}
                </span>
                <span class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#1B1A17] text-[11px] font-bold px-2.5 py-1 rounded-md">
                    {{ $assessment->questions->count() }} MCQs · {{ $assessment->options_per_mcq ?? 4 }} Options
                </span>
                <span class="bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold text-[11px] px-2.5 py-1 rounded-md">
                    <i class="fa-solid fa-stopwatch"></i> {{ $assessment->duration_minutes ?? 45 }} Mins
                </span>
                <span class="bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] font-bold text-[11px] px-2.5 py-1 rounded-md">
                    <i class="fa-solid fa-shield-halved"></i> 100% Zero-Duplication Verified
                </span>
            </div>

            <h1 class="text-2xl font-extrabold text-[#1B1A17] tracking-tight font-display mb-1.5">
                {{ $assessment->title }}
            </h1>

            <div class="flex items-center gap-3.5 text-xs text-[#68665D] flex-wrap">
                <span><i class="fa-solid fa-book text-[#D48A2E]"></i> {{ $assessment->subject->name ?? 'Subject' }} ({{ $assessment->subject->subject_code ?? '5054' }})</span>
                <span><i class="fa-solid fa-users text-[#3A529C]"></i> {{ $assessment->classSection->instituteClass->name ?? 'Class' }} - {{ $assessment->classSection->name ?? 'Section' }}</span>
                <span><i class="fa-solid fa-calendar text-[#8A5A10]"></i> {{ $assessment->academicTerm->name ?? 'Term' }}</span>
                <span><i class="fa-solid fa-user-tie text-[#68665D]"></i> Created by: {{ $assessment->creator->name ?? 'Teacher' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('lms.mocks.print', $assessment->id) }}" target="_blank" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print text-[#D48A2E]"></i> Print Paper
            </a>
            <a href="{{ route('lms.mocks.print', [$assessment->id, 'with_answers' => 1]) }}" target="_blank" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-key text-[#8A5A10]"></i> Print Key
            </a>
            <a href="{{ route('lms.mocks.pdf', $assessment->id) }}" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-file-pdf text-[#D48A2E]"></i> Download PDF
            </a>
            <button type="button" onclick="togglePortalDelivery()" id="btn-toggle-portal" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all shadow-sm flex items-center gap-2 border {{ ($assessment->is_published_student ?? false) ? 'bg-[#E3EFE2] text-[#2E6E42] border-[#C5DDC3]' : 'bg-[#F8E9D3] text-[#8A5A10] border-[#E8CEAA]' }}">
                <i class="fa-solid fa-globe"></i>
                <span id="btn-toggle-portal-text">{{ ($assessment->is_published_student ?? false) ? 'Student Portal: LIVE' : 'Student Portal: OFF' }}</span>
            </button>
            <a href="{{ route('lms.mocks.index') }}" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#68665D] hover:text-[#1B1A17] px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> All Mocks
            </a>
        </div>
    </div>

    <!-- NOTIFICATION TOAST -->
    <div id="inplace-toast" class="hidden bg-[#E3EFE2] border border-[#C5DDC3] text-[#2E6E42] rounded-xl p-3.5 text-xs font-bold items-center justify-between shadow-sm">
        <span id="inplace-toast-text"></span>
        <button type="button" onclick="document.getElementById('inplace-toast').classList.add('hidden')" class="text-[#2E6E42] hover:text-[#1B1A17] cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- QUESTIONS LIST (VERTICAL STACK) -->
    <div class="space-y-4" id="questions-container">
        @forelse($assessment->questions as $index => $q)
            @php
                $opts = is_array($q->options) ? $q->options : json_decode($q->options ?? '{}', true);
                $optKeys = array_keys($opts);
                $correctKey = strtoupper(trim($q->correct_answer));
            @endphp
            <div id="question-card-{{ $q->id }}" class="mock-question-card bg-[#F9F8F5] border border-[#E1DFD7] hover:border-[#D48A2E]/50 rounded-2xl p-6 shadow-sm space-y-4 transition-all">
                
                <!-- CARD HEADER: Q-NUMBER, TOPIC, EDIT BUTTON -->
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-xs px-2.5 py-0.5 rounded-md font-display">
                            Q{{ $q->sort_order ?? ($index + 1) }}
                        </span>
                        <span id="card-topic-{{ $q->id }}" class="text-[11px] font-bold text-[#3A529C] bg-[#E7ECF6] border border-[#CAD5EC] px-2.5 py-0.5 rounded-md">
                            <i class="fa-solid fa-bookmark text-[10px]"></i> {{ $q->chapter_reference ?: 'Core Syllabus Concept' }}
                        </span>
                        <span class="bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] text-[10px] font-bold px-2 py-0.5 rounded-md" title="Hash: {{ $q->question_hash }}">
                            <i class="fa-solid fa-fingerprint"></i> Hash Verified
                        </span>
                    </div>

                    <div>
                        <button type="button" onclick="openEditModal({{ $q->id }})" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] text-xs font-bold px-3.5 py-1.5 rounded-xl cursor-pointer flex items-center gap-2 transition-all shadow-sm">
                            <i class="fa-solid fa-pen-to-square text-[#D48A2E]"></i> Edit Question
                        </button>
                    </div>
                </div>

                <!-- QUESTION STEM -->
                <div id="card-statement-{{ $q->id }}" class="text-sm font-semibold text-[#1B1A17] leading-relaxed whitespace-pre-line">
                    {{ $q->statement }}
                </div>

                <!-- STACKED VERTICAL OPTIONS -->
                <div id="card-options-{{ $q->id }}" class="space-y-2">
                    @foreach($opts as $letter => $val)
                        @php
                            $isCorrect = (strtoupper(trim($letter)) === $correctKey);
                        @endphp
                        <div class="flex items-start gap-3 p-3 rounded-xl border text-xs font-semibold {{ $isCorrect ? 'bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42]' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17]' }}">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center font-extrabold text-[11px] flex-shrink-0 {{ $isCorrect ? 'bg-[#2E6E42] text-white' : 'bg-[#F2EFEB] text-[#68665D] border border-[#E1DFD7]' }}">
                                {{ $letter }}
                            </span>
                            <span class="text-xs leading-normal pt-0.5 flex-1">{{ $val }}</span>
                            @if($isCorrect)
                                <span class="ml-auto text-[11px] text-[#2E6E42] font-extrabold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-check"></i> Key Answer
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- FOOTER STATUS BAR (ALWAYS VISIBLE TO TEACHER) -->
                <div class="bg-[#F2EFEB] border border-[#E1DFD7] rounded-xl p-3.5 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-extrabold text-[#68665D] uppercase tracking-wider">Correct Answer:</span>
                            <span id="card-correct-badge-{{ $q->id }}" class="bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] font-extrabold text-xs px-2.5 py-0.5 rounded-md">
                                Option {{ $correctKey }}
                            </span>
                        </div>
                        <span class="text-[11px] text-[#A19E92] font-medium">
                            1 Mark · No Negative Marking
                        </span>
                    </div>

                    <!-- MARK SCHEME RATIONALE -->
                    <div class="text-xs text-[#68665D] leading-relaxed pt-2 border-t border-[#E1DFD7]">
                        <strong class="text-[#8A5A10]"><i class="fa-solid fa-chalkboard-user"></i> Cambridge Mark Scheme Rationale:</strong>
                        <div id="card-explanation-{{ $q->id }}" class="mt-1 text-[#1B1A17]">
                            {{ $opts['_explanation'] ?? ($opts['explanation'] ?? 'Standard analytical derivation according to Cambridge international assessment criteria.') }}
                        </div>
                    </div>
                </div>

                <!-- HIDDEN DATA STORE FOR MODAL -->
                <div id="raw-data-{{ $q->id }}" class="hidden" 
                    data-statement="{{ e($q->statement) }}"
                    data-options="{{ json_encode($opts) }}"
                    data-correct="{{ $correctKey }}"
                    data-topic="{{ e($q->chapter_reference ?? '') }}"
                    data-sort="{{ $q->sort_order ?? ($index + 1) }}"
                    data-explanation="{{ e($opts['_explanation'] ?? ($opts['explanation'] ?? '')) }}">
                </div>
            </div>
        @empty
            <div class="bg-[#F9F8F5] border border-dashed border-[#E1DFD7] rounded-2xl p-12 text-center text-[#68665D] text-xs">
                No questions found in this Mock Examination.
            </div>
        @endforelse
    </div>
</div>

<!-- IN-PLACE LIVE EDITING MODAL / DRAWER -->
<div id="edit-question-modal" class="hidden fixed inset-0 bg-[#1B1A17]/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col text-[#1B1A17] shadow-2xl overflow-hidden">
        
        <!-- MODAL HEADER -->
        <div class="flex justify-between items-center p-4 px-6 border-b border-[#E1DFD7] bg-[#F2EFEB]">
            <div class="flex items-center gap-2.5">
                <span id="modal-q-badge" class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-xs px-2.5 py-0.5 rounded-md font-display">
                    Q1
                </span>
                <h3 class="m-0 text-sm font-extrabold text-[#1B1A17] font-display">Edit Mock Question</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-[#68665D] hover:text-[#1B1A17] text-lg cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- MODAL BODY -->
        <form id="inplace-edit-form" onsubmit="handleSaveQuestion(event)" class="p-6 overflow-y-auto flex-1 space-y-4">
            <input type="hidden" id="edit_question_id">

            <!-- STATEMENT -->
            <div>
                <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Question Statement / Problem Stem <span class="text-[#A2412C]">*</span>
                </label>
                <textarea id="edit_statement" rows="3" required class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl p-3 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] leading-relaxed resize-y"></textarea>
            </div>

            <!-- TOPIC TAG -->
            <div>
                <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Syllabus Topic / Citation Reference
                </label>
                <input type="text" id="edit_topic" placeholder="e.g. Kinematics, Dynamics & Momentum" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-medium text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
            </div>

            <!-- DYNAMIC OPTIONS -->
            <div>
                <label class="block text-xs font-bold text-[#1B1A17] mb-2">
                    Options &amp; Answer Choices <span class="text-[#A2412C]">*</span>
                </label>
                <div id="modal-options-container" class="space-y-2.5">
                    <!-- Rendered dynamically by openEditModal() -->
                </div>
            </div>

            <!-- CORRECT ANSWER SELECTOR (SEGMENTED) -->
            <div>
                <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Designate Correct Answer Key <span class="text-[#A2412C]">*</span>
                </label>
                <div id="modal-correct-radios" class="flex gap-2 flex-wrap">
                    <!-- Rendered dynamically -->
                </div>
            </div>

            <!-- EXPLANATION / RATIONALE -->
            <div>
                <label class="block text-xs font-bold text-[#1B1A17] mb-1.5">
                    Cambridge Mark Scheme Rationale / Teacher's Note
                </label>
                <textarea id="edit_explanation" rows="2" placeholder="Explain why the correct answer is true and clarify distractor traps..." class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl p-3 text-xs font-medium text-[#1B1A17] focus:outline-none focus:border-[#D48A2E] leading-relaxed resize-y"></textarea>
            </div>
        </form>

        <!-- MODAL FOOTER -->
        <div class="flex justify-end gap-2.5 p-4 px-6 border-t border-[#E1DFD7] bg-[#F2EFEB]">
            <button type="button" onclick="closeEditModal()" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-4 py-2 rounded-xl text-xs font-bold cursor-pointer transition-all">
                Cancel
            </button>
            <button type="button" id="btn-save-question" onclick="handleSaveQuestion(event)" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs px-5 py-2 rounded-xl cursor-pointer flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-check"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
let currentEditingQuestionId = null;

function openEditModal(questionId) {
    currentEditingQuestionId = questionId;
    const rawDiv = document.getElementById(`raw-data-${questionId}`);
    if (!rawDiv) return;

    const statement = rawDiv.dataset.statement;
    const options = JSON.parse(rawDiv.dataset.options || '{}');
    const correct = rawDiv.dataset.correct || 'A';
    const topic = rawDiv.dataset.topic || '';
    const sort = rawDiv.dataset.sort || '1';
    const explanation = rawDiv.dataset.explanation || '';

    document.getElementById('edit_question_id').value = questionId;
    document.getElementById('modal-q-badge').innerText = `Q${sort}`;
    document.getElementById('edit_statement').value = statement;
    document.getElementById('edit_topic').value = topic;
    document.getElementById('edit_explanation').value = explanation;

    // Build Option inputs (A, B, C, D, optional E)
    const optContainer = document.getElementById('modal-options-container');
    const radioContainer = document.getElementById('modal-correct-radios');

    const cleanKeys = Object.keys(options).filter(k => ['A', 'B', 'C', 'D', 'E'].includes(k.toUpperCase())).sort();

    optContainer.innerHTML = cleanKeys.map(k => `
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] flex items-center justify-center font-extrabold text-xs flex-shrink-0">
                ${k}
            </span>
            <input type="text" id="edit_opt_${k}" value="${escapeHtml(options[k])}" required class="flex-1 bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
        </div>
    `).join('');

    radioContainer.innerHTML = cleanKeys.map(k => `
        <label id="lbl-modal-rad-${k}" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border cursor-pointer font-extrabold text-xs transition-all ${k === correct ? 'bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42]' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#68665D]'}">
            <input type="radio" name="edit_correct_key" value="${k}" ${k === correct ? 'checked' : ''} onchange="updateCorrectRadioVisual('${k}', ${JSON.stringify(cleanKeys)})" class="hidden">
            <span>Key ${k}</span>
        </label>
    `).join('');

    const modal = document.getElementById('edit-question-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function updateCorrectRadioVisual(selectedKey, allKeys) {
    allKeys.forEach(k => {
        const lbl = document.getElementById(`lbl-modal-rad-${k}`);
        if (lbl) {
            if (k === selectedKey) {
                lbl.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-xl border cursor-pointer font-extrabold text-xs transition-all bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42]';
            } else {
                lbl.className = 'flex items-center gap-2 px-3.5 py-1.5 rounded-xl border cursor-pointer font-extrabold text-xs transition-all bg-[#FFFFFF] border-[#E1DFD7] text-[#68665D]';
            }
        }
    });
}

function closeEditModal() {
    const modal = document.getElementById('edit-question-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentEditingQuestionId = null;
}

async function handleSaveQuestion(e) {
    if (e) e.preventDefault();
    if (!currentEditingQuestionId) return;

    const btn = document.getElementById('btn-save-question');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Saving...';

    const questionId = document.getElementById('edit_question_id').value;
    const statement = document.getElementById('edit_statement').value;
    const topic = document.getElementById('edit_topic').value;
    const explanation = document.getElementById('edit_explanation').value;
    const correctRadio = document.querySelector('input[name="edit_correct_key"]:checked');
    const correctKey = correctRadio ? correctRadio.value : 'A';

    // Collect options
    const options = {};
    ['A', 'B', 'C', 'D', 'E'].forEach(k => {
        const input = document.getElementById(`edit_opt_${k}`);
        if (input) {
            options[k] = input.value;
        }
    });

    try {
        const res = await fetch(`{{ url('lms/mocks/questions') }}/${questionId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                statement: statement,
                options: options,
                correct_answer: correctKey,
                chapter_reference: topic,
                explanation: explanation
            })
        });

        const data = await res.json();

        if (data.success) {
            updateCardDOM(questionId, data.question);
            closeEditModal();
            showToast(data.message || 'Question updated successfully.');
        } else {
            alert(`Update error: ${data.message || 'Validation failed.'}`);
        }
    } catch (err) {
        alert(`Save failed: ${err.message}`);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Changes';
    }
}

function updateCardDOM(questionId, qData) {
    // 1. Update Statement
    const stmtEl = document.getElementById(`card-statement-${questionId}`);
    if (stmtEl) stmtEl.innerText = qData.statement;

    // 2. Update Topic Tag
    const topicEl = document.getElementById(`card-topic-${questionId}`);
    if (topicEl) topicEl.innerHTML = `<i class="fa-solid fa-bookmark text-[10px]"></i> ${qData.chapter_reference || 'Core Syllabus Concept'}`;

    // 3. Update Options List
    const optContainer = document.getElementById(`card-options-${questionId}`);
    if (optContainer && qData.options) {
        const optionKeys = Object.keys(qData.options).sort();
        optContainer.innerHTML = optionKeys.map(k => {
            const isCorrect = (k.toUpperCase() === qData.correct_answer.toUpperCase());
            return `
                <div class="flex items-start gap-3 p-3 rounded-xl border text-xs font-semibold ${isCorrect ? 'bg-[#E3EFE2] border-[#C5DDC3] text-[#2E6E42]' : 'bg-[#FFFFFF] border-[#E1DFD7] text-[#1B1A17]'}">
                    <span class="w-6 h-6 rounded-lg flex items-center justify-center font-extrabold text-[11px] flex-shrink-0 ${isCorrect ? 'bg-[#2E6E42] text-white' : 'bg-[#F2EFEB] text-[#68665D] border border-[#E1DFD7]'}">
                        ${k}
                    </span>
                    <span class="text-xs leading-normal pt-0.5 flex-1">${escapeHtml(qData.options[k])}</span>
                    ${isCorrect ? `
                        <span class="ml-auto text-[11px] text-[#2E6E42] font-extrabold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i> Key Answer
                        </span>
                    ` : ''}
                </div>
            `;
        }).join('');
    }

    // 4. Update Footer Status Bar & Explanation
    const badgeEl = document.getElementById(`card-correct-badge-${questionId}`);
    if (badgeEl) badgeEl.innerText = `Option ${qData.correct_answer}`;

    const explEl = document.getElementById(`card-explanation-${questionId}`);
    if (explEl && qData.explanation) explEl.innerText = qData.explanation;

    // 5. Update Hidden Raw Data Store
    const rawDiv = document.getElementById(`raw-data-${questionId}`);
    if (rawDiv) {
        rawDiv.dataset.statement = qData.statement;
        rawDiv.dataset.options = JSON.stringify(qData.options);
        rawDiv.dataset.correct = qData.correct_answer;
        rawDiv.dataset.topic = qData.chapter_reference || '';
        rawDiv.dataset.explanation = qData.explanation || '';
    }
}

function showToast(msg) {
    const toast = document.getElementById('inplace-toast');
    const toastText = document.getElementById('inplace-toast-text');
    toastText.innerText = msg;
    toast.classList.remove('hidden');
    toast.classList.add('flex');
    setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('flex');
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
async function togglePortalDelivery() {
    const btn = document.getElementById('btn-toggle-portal');
    const btnText = document.getElementById('btn-toggle-portal-text');
    btn.disabled = true;

    try {
        const res = await fetch("{{ route('lms.mocks.togglePortal', $assessment->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            if (data.is_portal_visible) {
                btn.className = "px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all shadow-sm flex items-center gap-2 border bg-[#E3EFE2] text-[#2E6E42] border-[#C5DDC3]";
                btnText.textContent = "Student Portal: LIVE";
            } else {
                btn.className = "px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all shadow-sm flex items-center gap-2 border bg-[#F8E9D3] text-[#8A5A10] border-[#E8CEAA]";
                btnText.textContent = "Student Portal: OFF";
            }
        } else {
            alert(data.message || 'Error updating portal delivery status.');
        }
    } catch (e) {
        alert('Network error: ' + e.message);
    } finally {
        btn.disabled = false;
    }
}
</script>
@endsection

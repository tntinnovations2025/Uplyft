<?php $__env->startSection('title', 'Assessment & Exam Creator'); ?>
<?php $__env->startSection('breadcrumb', 'Assessments'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $instituteId = auth()->user()->institute_id;
    $subjects  = \App\Models\Subject::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))->orderBy('subject_name')->get();
    $classes   = \App\Models\InstituteClass::where('institute_id', $instituteId)->get();
    $sections  = \App\Models\ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))->orderBy('section_name')->get();
    $terms     = \App\Models\AcademicTerm::where('institute_id', $instituteId)->orderByDesc('is_active')->orderBy('name')->get();
    $types     = \App\Models\Assessment::TYPES;
    $isPrincipal = auth()->user()->isPrincipal();
    $isTeacher   = auth()->user()->isTeacher();
    $isStudent   = auth()->user()->isStudent();
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">📝 Assessment &amp; Exam Creator</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Create, publish, and evaluate quizzes, paper tests, and term examinations.
        </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php if($isStudent): ?>
            <button class="btn btn-ghost" onclick="openPracticeModal()">🎯 Practice Quiz</button>
        <?php endif; ?>
        <?php if($isTeacher || $isPrincipal): ?>
            <button class="btn btn-ghost" onclick="openAiGeneratorModal()">🤖 AI Exam Generator</button>
            <button class="btn btn-primary" onclick="openCreateModal()">➕ New Assessment</button>
        <?php endif; ?>
    </div>
</div>


<div class="card">
    <form method="GET" action="<?php echo e(route('lms.assessments.index')); ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end">
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Subject</label>
            <select name="subject_id">
                <option value="">All Subjects</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>" <?php if(request('subject_id') == $s->id): echo 'selected'; endif; ?>><?php echo e($s->subject_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Academic Term</label>
            <select name="academic_term_id">
                <option value="">All Terms</option>
                <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>" <?php if(request('academic_term_id') == $t->id): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:180px;margin:0">
            <label>Class Section</label>
            <select name="class_section_id">
                <option value="">All Sections</option>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sec->id); ?>" <?php if(request('class_section_id') == $sec->id): echo 'selected'; endif; ?>><?php echo e($sec->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($sec->section_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:160px;margin:0">
            <label>Type</label>
            <select name="type">
                <option value="">All Types</option>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php if(request('type') == $type): echo 'selected'; endif; ?>><?php echo e(ucfirst($type)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Filter</button>
    </form>
</div>


<?php $__empty_1 = true; $__currentLoopData = $assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assessment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="card" style="padding:20px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:12px">
            <div style="display:flex;gap:14px;align-items:flex-start">
                <div class="badge badge-purple" style="margin-top:2px"><?php echo e(ucfirst($assessment->type)); ?></div>
                <div>
                    <h3 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700"><?php echo e($assessment->title); ?></h3>
                    <p class="muted" style="margin-top:4px">
                        <?php echo e($assessment->subject->subject_name ?? '—'); ?> ·
                        <?php echo e($assessment->classSection->section_name ?? '—'); ?> ·
                        <?php echo e($assessment->academicTerm->name ?? '—'); ?>

                        <?php if($assessment->creator): ?>
                            <br>Created by <?php echo e($assessment->creator->name); ?>

                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;white-space:nowrap">
                <?php
                    $statusClass = match($assessment->status) {
                        'draft' => 'badge-yellow',
                        'published', 'in_progress' => 'badge-green',
                        default => 'badge-purple',
                    };
                ?>
                <span class="badge <?php echo e($statusClass); ?>"><?php echo e(str_replace('_', ' ', $assessment->status)); ?></span>
            </div>
        </div>

        <div style="display:flex;gap:24px;flex-wrap:wrap" class="muted">
            <span>🎯 <?php echo e($assessment->total_marks); ?> marks</span>
            <span>🔢 <?php echo e($assessment->questions_count ?? $assessment->questions->count()); ?> questions</span>
            <span>🤖 <?php echo e($assessment->evaluation_mode === 'ai' ? 'AI Evaluation' : 'Manual Evaluation'); ?></span>
            <?php if($assessment->start_time): ?>
                <span>🗓️ <?php echo e($assessment->start_time->format('M d, H:i')); ?> → <?php echo e($assessment->end_time?->format('M d, H:i')); ?></span>
            <?php endif; ?>
            <?php if($assessment->result_deadline): ?>
                <span>⏳ Result deadline: <?php echo e($assessment->result_deadline->format('M d, Y')); ?></span>
            <?php endif; ?>
        </div>

        <?php if($assessment->questions->isNotEmpty()): ?>
            <details style="margin-top:14px">
                <summary style="cursor:pointer;font-size:13px;font-weight:600;color:var(--accent)">View <?php echo e($assessment->questions->count()); ?> question(s)</summary>
                <div style="margin-top:10px;padding:14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;font-size:13px" class="muted">
                    <?php $__currentLoopData = $assessment->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="margin-bottom:8px">
                            <b>[<?php echo e($q->marks); ?> pts] <?php echo e(ucfirst($q->question_type)); ?>:</b> <?php echo e($q->statement); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </details>
        <?php endif; ?>

        <?php if($isTeacher || $isPrincipal): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                <a href="<?php echo e(route('lms.test-results.marksheet', $assessment->id)); ?>" class="btn btn-primary btn-sm">👁️ View Marks</a>

                <?php if($assessment->status === 'draft'): ?>
                    <form method="POST" action="<?php echo e(route('lms.assessments.publish', $assessment)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-ghost btn-sm">🚀 Publish</button>
                    </form>
                    <button class="btn btn-ghost btn-sm" onclick="openQuestionsModal(<?php echo e($assessment->id); ?>, '<?php echo e($assessment->title); ?>')">➕ Add Questions</button>
                <?php endif; ?>

                <?php if($assessment->isMandatory() && $isPrincipal): ?>
                    <button class="btn btn-ghost btn-sm" onclick="openScheduleModal(<?php echo e($assessment->id); ?>, '<?php echo e($assessment->title); ?>')">🗓️ Set Exam Schedule</button>
                <?php endif; ?>

                <?php if($assessment->status === 'published' || $assessment->status === 'in_progress'): ?>
                    <form method="POST" action="<?php echo e(route('lms.assessments.autoGradeMcqs', $assessment)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-ghost btn-sm">⚡ Auto-Grade MCQs</button>
                    </form>
                <?php endif; ?>

                <?php if($assessment->status === 'draft'): ?>
                    <form method="POST" action="<?php echo e(route('lms.assessments.destroy', $assessment)); ?>"
                          onsubmit="return confirm('Delete this assessment and all its questions?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card" style="text-align:center;padding:60px">
        <div style="font-size:40px;margin-bottom:12px">📝</div>
        <h3 style="font-weight:700;margin-bottom:6px">No assessments found</h3>
        <p class="muted">Create your first assessment to get started.</p>
    </div>
<?php endif; ?>

<?php echo e($assessments->links()); ?>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('modals'); ?>

<div class="modal-backdrop" id="create-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:560px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5);max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">New Assessment</h3>
            <button onclick="closeModal('create-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="<?php echo e(route('lms.assessments.store')); ?>">
            <?php echo csrf_field(); ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Subject *</label>
                    <select name="subject_id" required>
                        <option value="">Select subject</option>
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>"><?php echo e($s->subject_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Academic Term *</label>
                    <select name="academic_term_id" required>
                        <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php if($t->is_active): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class Section *</label>
                    <select name="class_section_id" required>
                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sec->id); ?>"><?php echo e($sec->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($sec->section_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type *</label>
                    <select name="type" required>
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(in_array($type, ['midterm', 'final', 'exam']) && !auth()->user()->isAdministration()): ?>
                                <?php continue; ?>
                            <?php endif; ?>
                            <option value="<?php echo e($type); ?>"><?php echo e(ucfirst($type)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Test Title / Name *</label>
                    <input type="text" name="title" required maxlength="255" placeholder="e.g., Chapter 1 Math Quiz / Midterm 2026">
                </div>
                <div class="form-group">
                    <label>Test Date *</label>
                    <input type="datetime-local" name="start_time" required value="<?php echo e(date('Y-m-d\TH:i')); ?>">
                </div>
                <div class="form-group">
                    <label>Total Marks *</label>
                    <input type="number" name="total_marks" required min="1" max="1000" value="100">
                </div>
                <div class="form-group">
                    <label>Evaluation Mode</label>
                    <select name="evaluation_mode">
                        <option value="manual">Manual</option>
                        <option value="ai">AI (LLM)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Instructions</label>
                    <input type="text" name="instructions" maxlength="5000" placeholder="Optional instructions for students">
                </div>
                <div class="form-group">
                    <label>Enable Time Limit</label>
                    <select name="has_time_limit" onchange="document.getElementById('create-duration-group').style.display = this.value == '1' ? 'block' : 'none'">
                        <option value="0">No Time Limit</option>
                        <option value="1">Enable Time Limit</option>
                    </select>
                </div>
                <div class="form-group" id="create-duration-group" style="display:none">
                    <label>Time Limit (Minutes)</label>
                    <input type="number" name="duration_minutes" min="1" max="300" value="30">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px">
                <button type="button" class="btn btn-ghost" onclick="closeModal('create-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Assessment</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-backdrop" id="questions-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:760px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5);max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">Add Questions — <span id="questions-modal-title"></span></h3>
            <button onclick="closeModal('questions-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="<?php echo e(url('/lms/assessments')); ?>/QUESTION_ID/questions" id="questions-form">
            <?php echo csrf_field(); ?>
            <div id="questions-list"></div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="addQuestionRow()" style="margin-bottom:16px">➕ Add Question</button>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeModal('questions-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Questions</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-backdrop" id="schedule-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:480px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">Set Exam Schedule — <span id="schedule-modal-title"></span></h3>
            <button onclick="closeModal('schedule-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="<?php echo e(url('/lms/assessments/')); ?>/SCHEDULE_ID/schedule" id="schedule-form">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Start Time *</label>
                <input type="datetime-local" name="start_time" required>
            </div>
            <div class="form-group">
                <label>End Time *</label>
                <input type="datetime-local" name="end_time" required>
            </div>
            <div class="form-group">
                <label>Result Deadline</label>
                <input type="datetime-local" name="result_deadline">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeModal('schedule-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Schedule</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-backdrop" id="ai-generator-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:540px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">🤖 Generate Test from Book with AI</h3>
            <button onclick="closeModal('ai-generator-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>
        <p class="muted" style="margin-bottom:20px">Select a subject, enter the chapter/topic, and the AI will extract questions from the uploaded textbook RAG context.</p>
        <form id="ai-gen-form" onsubmit="submitAiGenerator(event)">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Subject *</label>
                <select id="ai-subject-id" required>
                    <option value="">Select subject</option>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"><?php echo e($s->subject_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Academic Term *</label>
                <select id="ai-term-id" required>
                    <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>" <?php if($t->is_active): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Class Section *</label>
                <select id="ai-section-id" required>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sec->id); ?>"><?php echo e($sec->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($sec->section_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Topic / Chapter Title *</label>
                <input type="text" id="ai-topic" required placeholder="e.g., Chapter 3: Laws of Motion">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>MCQs Count</label>
                    <input type="number" id="ai-mcqs" value="5" min="0" max="20">
                </div>
                <div class="form-group">
                    <label>Short Qs Count</label>
                    <input type="number" id="ai-short" value="3" min="0" max="10">
                </div>
                <div class="form-group">
                    <label>Long Qs Count</label>
                    <input type="number" id="ai-long" value="1" min="0" max="5">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Marks per MCQ *</label>
                    <input type="number" id="ai-mcq-marks" value="2" min="1" max="50">
                </div>
                <div class="form-group">
                    <label>Marks per Short *</label>
                    <input type="number" id="ai-short-marks" value="5" min="1" max="50">
                </div>
                <div class="form-group">
                    <label>Marks per Long *</label>
                    <input type="number" id="ai-long-marks" value="10" min="1" max="100">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Save Marksheet of Students?</label>
                    <select id="ai-save-marksheet">
                        <option value="1">Yes (Auto-Save Marksheet)</option>
                        <option value="0">No (Do Not Auto-Save)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Enable Time Limit</label>
                    <select id="ai-enable-timer" onchange="document.getElementById('ai-duration-group').style.display = this.value == '1' ? 'block' : 'none'">
                        <option value="0">No Time Limit</option>
                        <option value="1">Enable Time Limit</option>
                    </select>
                </div>
            </div>
            <div class="form-group" id="ai-duration-group" style="display:none">
                <label>Time Limit (Minutes)</label>
                <input type="number" id="ai-duration-minutes" value="30" min="1" max="300">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">
                <button type="button" class="btn btn-ghost" onclick="closeModal('ai-generator-modal')">Cancel</button>
                <button type="submit" id="ai-gen-btn" class="btn btn-primary">⚡ Generate & Publish</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-backdrop" id="practice-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:540px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.5)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px">🎯 Self-Study Practice Quiz</h3>
            <button onclick="closeModal('practice-modal')" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">✕</button>
        </div>
        <p class="muted" style="margin-bottom:20px">Generate an instant practice quiz from your subject book to test your knowledge anytime.</p>
        <form id="practice-form" onsubmit="submitPracticeQuiz(event)">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Subject *</label>
                <select id="prac-subject-id" required>
                    <option value="">Select subject</option>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"><?php echo e($s->subject_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Topic / Chapter (Optional)</label>
                <input type="text" id="prac-topic" placeholder="e.g. Chapter 1 revision">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">
                <button type="button" class="btn btn-ghost" onclick="closeModal('practice-modal')">Cancel</button>
                <button type="submit" id="prac-btn" class="btn btn-primary">🚀 Start Practice Quiz</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('create-modal').style.display = 'flex';
    }
    function openQuestionsModal(id, title) {
        document.getElementById('questions-modal-title').textContent = title;
        const form = document.getElementById('questions-form');
        form.action = form.action.replace('/QUESTION_ID', '/' + id).replace('QUESTION_ID', id.toString());
        document.getElementById('questions-list').innerHTML = '';
        addQuestionRow();
        document.getElementById('questions-modal').style.display = 'flex';
    }
    function openScheduleModal(id, title) {
        document.getElementById('schedule-modal-title').textContent = title;
        const form = document.getElementById('schedule-form');
        form.action = form.action.replace('/SCHEDULE_ID', '/' + id).replace('SCHEDULE_ID', id.toString());
        document.getElementById('schedule-modal').style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    function addQuestionRow() {
        const container = document.getElementById('questions-list');
        const row = document.createElement('div');
        row.className = 'question-row';
        row.innerHTML = `
            <div style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-bottom:10px">
                <b style="font-size:13px">Question ${container.children.length + 1}</b>
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.question-row').remove()">✕</button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 120px;gap:10px">
                <div class="form-group">
                    <label>Type</label>
                    <select name="questions[${container.children.length}][question_type]">
                        <option value="mcq">MCQ</option>
                        <option value="short">Short Answer</option>
                        <option value="long">Long Answer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marks</label>
                    <input type="number" name="questions[${container.children.length}][marks]" min="1" required value="1">
                </div>
                <div class="form-group">
                    <label>Chapter Ref.</label>
                    <input type="text" name="questions[${container.children.length}][chapter_reference]" placeholder="e.g. Ch 3">
                </div>
            </div>
            <div class="form-group">
                <label>Question Statement *</label>
                <textarea name="questions[${container.children.length}][statement]" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Options (MCQ, comma-separated)</label>
                <input type="text" name="questions[${container.children.length}][options]" placeholder="A, B, C, D">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Correct / Model Answer</label>
                <textarea name="questions[${container.children.length}][correct_answer]" rows="2" placeholder="Required for MCQs (exact option). Recommended for AI evaluation."></textarea>
            </div>`;
        container.appendChild(row);
    }
    function openAiGeneratorModal() {
        document.getElementById('ai-generator-modal').style.display = 'flex';
    }
    function openPracticeModal() {
        document.getElementById('practice-modal').style.display = 'flex';
    }
    async function submitAiGenerator(e) {
        e.preventDefault();
        const btn = document.getElementById('ai-gen-btn');
        btn.disabled = true;
        btn.textContent = '⏳ Generating Test from Book...';

        try {
            const res = await fetch('<?php echo e(route('lms.assessments.generateAi')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    subject_id:         document.getElementById('ai-subject-id').value,
                    academic_term_id:   document.getElementById('ai-term-id').value,
                    class_section_id:   document.getElementById('ai-section-id').value,
                    topic:              document.getElementById('ai-topic').value,
                    mcq_count:          parseInt(document.getElementById('ai-mcqs').value),
                    short_count:        parseInt(document.getElementById('ai-short').value),
                    long_count:         parseInt(document.getElementById('ai-long').value),
                    mcq_marks:          parseInt(document.getElementById('ai-mcq-marks').value),
                    short_marks:        parseInt(document.getElementById('ai-short-marks').value),
                    long_marks:         parseInt(document.getElementById('ai-long-marks').value),
                    is_marksheet_saved: parseInt(document.getElementById('ai-save-marksheet').value),
                    has_time_limit:     parseInt(document.getElementById('ai-enable-timer').value),
                    duration_minutes:   parseInt(document.getElementById('ai-duration-minutes').value),
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to generate test');

            alert('✅ ' + data.message);
            window.location.reload();
        } catch (err) {
            alert('❌ ' + err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = '⚡ Generate & Publish';
        }
    }

    async function submitPracticeQuiz(e) {
        e.preventDefault();
        const btn = document.getElementById('prac-btn');
        btn.disabled = true;
        btn.textContent = '⏳ Creating Quiz...';

        try {
            const res = await fetch('<?php echo e(route('lms.assessments.practiceQuiz')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    subject_id: document.getElementById('prac-subject-id').value,
                    topic:      document.getElementById('prac-topic').value,
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to create quiz');

            alert('🎯 Practice Quiz Generated!\nQuestions: ' + (data.quiz?.questions?.length || 0));
            closeModal('practice-modal');
        } catch (err) {
            alert('❌ ' + err.message);
        } finally {
            btn.disabled = false;
            btn.textContent = '🚀 Start Practice Quiz';
        }
    }
<?php $__env->stopSection(); ?>
<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\assessments\index.blade.php ENDPATH**/ ?>
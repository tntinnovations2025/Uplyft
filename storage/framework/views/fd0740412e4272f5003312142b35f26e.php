<?php $__env->startSection('title', 'AI Practice Tests'); ?>
<?php $__env->startSection('breadcrumb', 'AI Practice Tests'); ?>

<?php $__env->startSection('content'); ?>
<!-- Top Hero Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 liquid-glass-card bg-gradient-to-r from-indigo-500/[0.04] via-blue-500/[0.02] to-violet-500/[0.04] p-6 border border-slate-200/90 shadow-2xs relative overflow-hidden glass-specular-top">
    <div class="relative z-10">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-700 via-indigo-600 to-violet-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-indigo-500/25 border border-white/60">
                🎯
            </div>
            <h1 class="font-extrabold text-2xl md:text-3xl text-slate-900 tracking-tight font-display">
                AI Practice Test Creator
            </h1>
        </div>
        <p class="text-slate-500 font-medium text-xs md:text-sm max-w-2xl leading-relaxed">
            Generate custom practice quizzes from your course materials and notes. Practice with instant feedback and grading.
        </p>
    </div>

    <div class="flex items-center gap-2 relative z-10">
        <span class="badge badge-indigo text-xs font-bold shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
            RAG Vector Engine Active
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <div class="lg:col-span-7">
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs relative">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60 flex items-center justify-center font-bold text-sm">
                        ⚡
                    </span>
                    <h3 class="text-lg font-extrabold text-slate-900 font-display">Configure AI Practice Test</h3>
                </div>
                <span class="badge badge-indigo text-xs font-bold">
                    Instant Quiz
                </span>
            </div>

            <form method="POST" action="<?php echo e(route('lms.practice-test.generate')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>

                
                <?php if(!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1)): ?>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-school text-indigo-600"></i>
                                <span>Step 1: Select Class / Grade *</span>
                            </span>
                            <span class="text-[11px] font-bold text-indigo-600 capitalize">
                                <?php echo e($classes->count()); ?> Classes available
                            </span>
                        </label>
                        <div class="relative">
                            <select name="class_id" id="practice-class-selector" onchange="onPracticeClassChange(this.value)" class="styled-select pr-10">
                                <option value="">— Select a Class to proceed —</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cls->id); ?>" <?php echo e((string)$selectedClassId === (string)$cls->id ? 'selected' : ''); ?>>
                                        <?php echo e($cls->name); ?> (<?php echo e($cls->subjects_count ?? $cls->subjects->count()); ?> Subjects)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div id="practice-no-class-prompt" style="<?php echo e((!$selectedClassId) ? 'display:block;' : 'display:none;'); ?>" class="p-4 rounded-xl bg-indigo-50/50 border border-dashed border-indigo-300 text-center">
                        <div class="text-xl mb-1">👆</div>
                        <div class="text-xs font-bold text-slate-800">Please select a class first</div>
                        <div class="text-[11px] text-slate-500 font-medium">Select a class from the dropdown above to view its registered subjects and generate a practice test.</div>
                    </div>
                <?php elseif(auth()->user()->isStudent()): ?>
                    <?php if($classes->isNotEmpty()): ?>
                        <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-200/70 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">🎓</span>
                                <div>
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Your Enrolled Class</div>
                                    <div class="text-sm font-bold text-indigo-700"><?php echo e($classes->pluck('name')->implode(', ')); ?></div>
                                </div>
                            </div>
                            <span class="badge badge-indigo text-xs font-bold">
                                <?php echo e($subjects->count()); ?> Assigned <?php echo e(Str::plural('Subject', $subjects->count())); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                <?php elseif($classes->isNotEmpty()): ?>
                    <div class="p-3.5 rounded-xl bg-indigo-50 border border-indigo-200/70 flex items-center gap-3">
                        <span class="text-xl">🎓</span>
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Your Assigned Class</div>
                            <div class="text-sm font-bold text-indigo-700"><?php echo e($classes->pluck('name')->implode(', ')); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if(auth()->user()->isStudent() && $subjects->isEmpty()): ?>
                    <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-center space-y-1">
                        <div class="text-2xl">🎓</div>
                        <div class="text-sm font-extrabold text-rose-800">You are not currently enrolled in any class or subjects</div>
                        <div class="text-xs text-rose-600 font-medium">Please contact campus administration to allocate your class section and subjects.</div>
                    </div>
                <?php endif; ?>

                
                <div id="practice-step2-subject-section" style="<?php echo e((!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1) && !$selectedClassId) || (auth()->user()->isStudent() && $subjects->isEmpty()) ? 'display:none;' : 'display:block;'); ?>" class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-book text-indigo-600"></i>
                        <span><?php echo e((!auth()->user()->isStudent() && ($isAdministration || $classes->count() > 1)) ? 'Step 2: Choose Subject *' : 'Select Enrolled Subject *'); ?></span>
                    </label>
                    <div class="relative">
                        <select name="subject_id" id="practice-subject-selector" required class="styled-select pr-10">
                            <option value="">— Select a Subject —</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-indigo-600"></i>
                        <span>Question Mix &amp; Weightage *</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-800">MCQs</span>
                                <span class="badge badge-indigo text-[10px] font-bold">2 pts ea</span>
                            </div>
                            <select name="mcq_count" class="styled-select">
                                <option value="0">0 Questions</option>
                                <option value="3" selected>3 Questions</option>
                                <option value="5">5 Questions</option>
                                <option value="7">7 Questions</option>
                                <option value="10">10 Questions</option>
                            </select>
                        </div>

                        
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-800">Short Ans</span>
                                <span class="badge badge-indigo text-[10px] font-bold">5 pts ea</span>
                            </div>
                            <select name="short_count" class="styled-select">
                                <option value="0">0 Questions</option>
                                <option value="1">1 Question</option>
                                <option value="3" selected>3 Questions</option>
                                <option value="5">5 Questions</option>
                                <option value="7">7 Questions</option>
                            </select>
                        </div>

                        
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-800">Long Ans</span>
                                <span class="badge badge-purple text-[10px] font-bold">10 pts ea</span>
                            </div>
                            <select name="long_count" class="styled-select">
                                <option value="0" selected>0 Questions</option>
                                <option value="1">1 Question</option>
                                <option value="2">2 Questions</option>
                                <option value="3">3 Questions</option>
                                <option value="5">5 Questions</option>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="space-y-4 pt-2 border-t border-slate-200">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-bullseye text-indigo-600"></i>
                            <span>Topic / Specific Focus (Optional)</span>
                        </label>
                        <input type="text" name="topic" placeholder="e.g. Chapter 1, Newton's Laws, Kinematics..." class="styled-input">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Book Portion</label>
                            <select name="portion" class="styled-select">
                                <option value="complete">Complete Book</option>
                                <option value="first_half">First Half (50%)</option>
                                <option value="second_half">Second Half (50%)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Chapter Number</label>
                            <input type="number" name="chapter_number" min="1" placeholder="e.g. 1, 2, 3" class="styled-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Page Start</label>
                            <input type="number" name="page_start" min="1" placeholder="e.g. 1" class="styled-input">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Page End</label>
                            <input type="number" name="page_end" min="1" placeholder="e.g. 30" class="styled-input">
                        </div>
                    </div>
                </div>

                
                <div class="bg-slate-50/90 border border-slate-200 rounded-xl p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">⏱️</span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Enable Time Limit</h4>
                                <p class="text-xs text-slate-500 font-medium">Set a countdown timer or exact exam schedule window</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enable_timer" value="1" id="timer-toggle" onchange="toggleTimerOptions(this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 shadow-md"></div>
                        </label>
                    </div>

                    <div id="timer-settings" class="hidden pt-4 border-t border-slate-200 space-y-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Timer Mode</label>
                            <select name="timer_mode" id="timer-mode-select" onchange="switchTimerMode(this.value)" class="styled-select">
                                <option value="duration">Fixed Duration (Minutes)</option>
                                <option value="scheduled">Scheduled Time Window (e.g. 1:00 PM – 1:30 PM)</option>
                            </select>
                        </div>

                        <div id="mode-duration" class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Time Limit (Minutes)</label>
                            <select name="time_limit_minutes" class="styled-select">
                                <option value="10">10 Minutes</option>
                                <option value="15" selected>15 Minutes</option>
                                <option value="30">30 Minutes</option>
                                <option value="45">45 Minutes</option>
                                <option value="60">60 Minutes</option>
                            </select>
                        </div>

                        <div id="mode-scheduled" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Start Time</label>
                                <input type="time" name="scheduled_start" value="13:00" class="styled-input">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Exact End Time</label>
                                <input type="time" name="scheduled_end" value="13:30" class="styled-input">
                            </div>
                        </div>
                    </div>
                </div>

                
                <button type="submit" class="w-full btn-primary py-4 px-6 text-sm font-extrabold flex items-center justify-center gap-3 shadow-md">
                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-100"></i>
                    <span>Generate AI Practice Test from Book</span>
                </button>
            </form>
        </div>
    </div>

    
    <div class="lg:col-span-5">
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60 flex items-center justify-center font-bold text-sm">
                            📊
                        </span>
                        <h3 class="text-lg font-extrabold text-slate-900 font-display">Your Recent Practice Tests</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/90 hover:border-indigo-300 transition-all flex items-center justify-between gap-4 group">
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-900 group-hover:text-indigo-600 transition-colors">
                                    <?php echo e($test->title); ?>

                                </h4>
                                <div class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-2">
                                    <span class="text-indigo-700 font-bold"><?php echo e($test->subject->subject_name ?? 'Subject'); ?></span>
                                    <span>•</span>
                                    <span><?php echo e(count($test->questions ?? [])); ?> questions</span>
                                    <span>•</span>
                                    <span><?php echo e($test->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <?php if($test->status === 'evaluated'): ?>
                                    <span class="badge badge-emerald text-xs font-bold block mb-1">
                                        <?php echo e($test->obtained_marks); ?> / <?php echo e($test->total_marks); ?> pts
                                    </span>
                                    <a href="<?php echo e(route('lms.practice-test.result', $test->id)); ?>" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700">
                                        <span>Scorecard</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('lms.practice-test.take', $test->id)); ?>" class="btn-primary px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                        <span>Continue</span>
                                        <i class="fa-solid fa-bolt text-[10px]"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-12 px-4 border border-dashed border-slate-300 rounded-xl bg-slate-50/50">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200/60 flex items-center justify-center text-2xl mx-auto mb-3">
                                🎯
                            </div>
                            <h4 class="font-extrabold text-sm text-slate-800 mb-1">No Practice Tests Taken Yet</h4>
                            <p class="text-xs text-slate-500 font-medium max-w-xs mx-auto">
                                Configure your subject and scope on the left to generate your first AI practice test!
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-medium">
                    💡 All test questions are dynamically cross-referenced with your uploaded subject textbooks.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sleek Modern Daylight Input & Select Design System */
    .styled-input, .styled-select {
        width: 100%;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 11px 14px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 500;
        font-family: inherit;
        transition: all 0.2s ease;
        outline: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .styled-input:hover, .styled-select:hover {
        border-color: #94a3b8;
    }

    .styled-input:focus, .styled-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .styled-select option {
        background-color: #ffffff;
        color: #0f172a;
        padding: 8px;
    }
</style>

<script>
    const isPracticeAdmin = <?php echo e($isAdministration ? 'true' : 'false'); ?>;
    let currentPracticeClassId = <?php echo e($selectedClassId ? (int)$selectedClassId : 'null'); ?>;
    const allPracticeSubjects = <?php echo json_encode($subjectsJson ?? [], 15, 512) ?>;

    function onPracticeClassChange(classId) {
        currentPracticeClassId = classId ? parseInt(classId) : null;
        applyPracticeClassFilter(currentPracticeClassId);
    }

    function applyPracticeClassFilter(classId) {
        const step2Section = document.getElementById('practice-step2-subject-section');
        const subjectSelect = document.getElementById('practice-subject-selector');
        const noClassPrompt = document.getElementById('practice-no-class-prompt');

        const isStudent = <?php echo e(auth()->user()->isStudent() ? 'true' : 'false'); ?>;

        if (!isStudent && !classId && (isPracticeAdmin || <?php echo e($classes->count() > 1 ? 'true' : 'false'); ?>)) {
            if (step2Section) step2Section.style.display = 'none';
            if (noClassPrompt) noClassPrompt.style.display = 'block';
            if (subjectSelect) {
                subjectSelect.innerHTML = '<option value="">— Select a Class first —</option>';
            }
            return;
        }

        const filtered = allPracticeSubjects.filter(s => !classId || s.class_id === classId);

        if (step2Section) step2Section.style.display = 'block';
        if (noClassPrompt) noClassPrompt.style.display = 'none';

        if (subjectSelect) {
            subjectSelect.innerHTML = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = filtered.length > 0 ? '— Choose your subject —' : '— No subjects registered for this class —';
            subjectSelect.appendChild(defaultOpt);

            filtered.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.materials_count > 0 ? ` (${s.materials_count} indexed materials)` : '');
                subjectSelect.appendChild(opt);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        applyPracticeClassFilter(currentPracticeClassId);
    });

    function toggleTimerOptions(checked) {
        document.getElementById('timer-settings').style.display = checked ? 'block' : 'none';
    }

    function switchTimerMode(mode) {
        document.getElementById('mode-duration').style.display = mode === 'duration' ? 'block' : 'none';
        document.getElementById('mode-scheduled').style.display = mode === 'scheduled' ? 'grid' : 'none';
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\practice_test\index.blade.php ENDPATH**/ ?>
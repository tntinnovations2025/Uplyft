

<?php $__env->startSection('title', 'Take Assessment'); ?>
<?php $__env->startSection('breadcrumb', 'Take Assessment'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isExpired = $assessment->end_time && now()->greaterThan($assessment->end_time);
    $hasSubmitted = $existingAnswers->isNotEmpty();
    $questionIndex = 0;
?>

<div style="max-width:900px;margin:0 auto">

    
    <div class="card" style="background:linear-gradient(135deg,rgba(212,138,46,0.12),rgba(212,138,46,0.06));border-color:var(--accent)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div>
                <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
                    <span class="badge badge-purple"><?php echo e(ucfirst($assessment->type)); ?></span>
                    <?php if($assessment->evaluation_mode === 'ai'): ?>
                        <span class="badge badge-green">🤖 AI Evaluation</span>
                    <?php endif; ?>
                    <?php if($hasSubmitted): ?>
                        <span class="badge badge-green">✅ Submitted</span>
                    <?php elseif($isExpired): ?>
                        <span class="badge badge-red">⏰ Expired</span>
                    <?php else: ?>
                        <span class="badge badge-yellow">⏳ In Progress</span>
                    <?php endif; ?>
                </div>
                <h1 style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;margin-bottom:4px">
                    <?php echo e($assessment->title); ?>

                </h1>
                <p class="muted">
                    <?php echo e($assessment->subject->subject_name ?? '—'); ?> · <?php echo e($assessment->classSection->section_name ?? '—'); ?> · Total: <?php echo e($assessment->total_marks); ?> marks
                </p>
                <?php if($assessment->instructions): ?>
                    <div style="margin-top:10px;padding:10px 14px;background:rgba(255,165,2,0.1);border:1px solid rgba(255,165,2,0.25);border-radius:8px;font-size:13px;color:var(--warning)">
                        📋 <?php echo e($assessment->instructions); ?>

                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <?php if($assessment->end_time): ?>
                    <div style="font-size:12px;color:var(--text-muted)">Deadline</div>
                    <div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px" id="countdown-timer">
                        <?php echo e($assessment->end_time->format('M d, Y h:i A')); ?>

                    </div>
                <?php endif; ?>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px">
                    <?php echo e($assessment->questions->count()); ?> questions
                </div>
            </div>
        </div>
    </div>

    <?php if($hasSubmitted): ?>
        
        <div class="alert alert-success">
            ✅ You have already submitted this assessment. Your answers are shown below.
        </div>

        <?php $__currentLoopData = $assessment->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $answer = $existingAnswers->firstWhere('assessment_question_id', $question->id);
            ?>
            <div class="card">
                <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;color:var(--accent)">
                        <?php echo e($loop->iteration); ?>

                    </div>
                    <div style="flex:1">
                        <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
                            <span class="badge badge-purple" style="font-size:10px"><?php echo e(ucfirst($question->question_type)); ?></span>
                            <span class="muted"><?php echo e($question->marks); ?> marks</span>
                        </div>
                        <div style="font-weight:600;line-height:1.5"><?php echo e($question->statement); ?></div>
                    </div>
                </div>

                <div style="padding:12px;background:var(--surface2);border-radius:8px;margin-bottom:8px">
                    <div style="font-size:11px;font-weight:600;color:var(--text-muted);margin-bottom:4px">YOUR ANSWER</div>
                    <div style="font-size:14px"><?php echo e($answer->answer_text ?? 'Not answered'); ?></div>
                </div>

                <?php if($answer && $answer->is_graded): ?>
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                        <span class="badge <?php echo e($answer->marks_awarded > 0 ? 'badge-green' : 'badge-red'); ?>">
                            Score: <?php echo e($answer->marks_awarded); ?> / <?php echo e($question->marks); ?>

                        </span>
                        <?php if($answer->feedback): ?>
                            <span class="muted"><?php echo e($answer->feedback); ?></span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="badge badge-yellow">⏳ Awaiting grading</span>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php elseif($isExpired): ?>
        <div class="alert alert-error">
            ⏰ This assessment's deadline has passed. You can no longer submit answers.
        </div>

    <?php else: ?>
        
        <form method="POST" action="<?php echo e(route('lms.assessments.submit', $assessment)); ?>" id="assessment-form" onsubmit="return confirmSubmit()">
            <?php echo csrf_field(); ?>

            <?php $__currentLoopData = $assessment->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card question-card" data-question="<?php echo e($question->id); ?>">
                    <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:14px">
                        <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-hover));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;color:var(--accent-on)">
                            <?php echo e($loop->iteration); ?>

                        </div>
                        <div style="flex:1">
                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;flex-wrap:wrap">
                                <span class="badge badge-purple" style="font-size:10px"><?php echo e(ucfirst($question->question_type)); ?></span>
                                <span class="muted"><?php echo e($question->marks); ?> marks</span>
                                <?php if($question->chapter_reference): ?>
                                    <span class="muted">· Ch: <?php echo e($question->chapter_reference); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="font-weight:600;font-size:15px;line-height:1.6"><?php echo e($question->statement); ?></div>
                        </div>
                    </div>

                    <?php if($question->question_type === 'mcq'): ?>
                        
                        <?php $options = is_array($question->options) ? $question->options : explode(',', $question->options ?? ''); ?>
                        <div style="display:grid;gap:8px;margin-left:48px">
                            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $optTrimmed = trim($opt); ?>
                                <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;cursor:pointer;transition:all 0.15s" class="mcq-option">
                                    <input type="radio"
                                           name="answers[<?php echo e($question->id); ?>]"
                                           value="<?php echo e($optTrimmed); ?>"
                                           style="accent-color:var(--accent);width:18px;height:18px">
                                    <span style="font-size:14px"><?php echo e($optTrimmed); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php elseif($question->question_type === 'short'): ?>
                        <div style="margin-left:48px">
                            <textarea name="answers[<?php echo e($question->id); ?>]" rows="3"
                                      placeholder="Type your short answer here..."
                                      style="width:100%;padding:12px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;outline:none;resize:vertical"></textarea>
                        </div>
                    <?php else: ?>
                        <div style="margin-left:48px">
                            <textarea name="answers[<?php echo e($question->id); ?>]" rows="6"
                                      placeholder="Type your detailed answer here..."
                                      style="width:100%;padding:12px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;outline:none;resize:vertical"></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="card" style="background:var(--surface);position:sticky;bottom:0;z-index:50;display:flex;justify-content:space-between;align-items:center">
                <div class="muted">
                    <span id="answered-count">0</span> / <?php echo e($assessment->questions->count()); ?> questions answered
                </div>
                <button type="submit" class="btn btn-primary">
                    📤 Submit Assessment
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
    .mcq-option:has(input:checked) {
        border-color: var(--accent) !important;
        background: rgba(212,138,46,0.1) !important;
    }
    .question-card { transition: border-color 0.15s; }
    .question-card.answered { border-left: 3px solid var(--success); }
</style>

<script>
    function confirmSubmit() {
        const total = document.querySelectorAll('.question-card').length;
        const answered = countAnswered();
        if (answered < total) {
            return confirm(`You have answered ${answered} out of ${total} questions. Submit anyway?`);
        }
        return confirm('Are you sure you want to submit? This cannot be undone.');
    }

    function countAnswered() {
        let count = 0;
        document.querySelectorAll('.question-card').forEach(card => {
            const radios = card.querySelectorAll('input[type=radio]:checked');
            const textareas = card.querySelectorAll('textarea');
            let hasAnswer = radios.length > 0;
            textareas.forEach(ta => { if (ta.value.trim()) hasAnswer = true; });
            if (hasAnswer) {
                count++;
                card.classList.add('answered');
            } else {
                card.classList.remove('answered');
            }
        });
        document.getElementById('answered-count').textContent = count;
        return count;
    }

    // Track answering progress
    document.addEventListener('input', countAnswered);
    document.addEventListener('change', countAnswered);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\assessments\take.blade.php ENDPATH**/ ?>
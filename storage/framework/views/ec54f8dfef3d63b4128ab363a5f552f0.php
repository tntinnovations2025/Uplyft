
<?php $__env->startSection('title', 'Academic Class & Student Promotion Studio'); ?>
<?php $__env->startSection('breadcrumb', 'Class Promotion'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:1100px;margin:0 auto;padding-bottom:60px">
    
    <!-- Top Action Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
        <div>
            <div style="display:flex;align-items:center;gap:10px">
                <a href="<?php echo e(route('principal.classes-subjects.index')); ?>" class="btn btn-ghost btn-sm" style="color:var(--text-muted);border-color:rgba(255,255,255,0.1)">
                    &larr; Back to Classes &amp; Subjects
                </a>
                <span style="color:var(--text-muted);font-size:13px">&bull;</span>
                <span style="color:#10b981;font-weight:700;font-size:13px">Multi-Session Roster Engine</span>
            </div>
            <h1 style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700;margin-top:6px;color:#fff;display:flex;align-items:center;gap:10px">
                🎓 Academic Class &amp; Student Promotion Studio
            </h1>
            <p style="color:var(--text-muted);font-size:14px;margin-top:4px">
                Rollover class rosters into a new academic session with zero manual data entry. Configure handling for failed students, preserve faculty allocations, and retain textbook AI materials.
            </p>
        </div>

        <div style="display:flex;gap:12px">
            <a href="<?php echo e(route('principal.classes-subjects.index')); ?>" class="btn btn-ghost">
                Cancel
            </a>
            <button type="button" onclick="document.getElementById('promotionForm').submit()" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);border:none;box-shadow:0 4px 18px rgba(16,185,129,0.35);padding:10px 20px;font-weight:700">
                🚀 Confirm &amp; Execute Promotion
            </button>
        </div>
    </div>

    <?php if(session('error')): ?>
    <div style="background:rgba(255,71,87,0.12);border:1px solid rgba(255,71,87,0.3);color:#ff4757;padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:10px">
        <span style="font-size:18px">⚠️</span>
        <span style="font-weight:600"><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?>

    <form id="promotionForm" method="POST" action="<?php echo e(route('principal.classes.promote')); ?>">
        <?php echo csrf_field(); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
            
            <!-- STEP 1: SOURCE SESSION & CLASS -->
            <div class="card" style="background:#121827;border:1px solid rgba(16,185,129,0.3);border-radius:16px;padding:24px">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(16,185,129,0.2);color:#10b981;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px">1</div>
                    <div>
                        <h2 style="font-size:16px;font-weight:700;color:#10b981;margin:0">Source Academic Session &amp; Class</h2>
                        <div style="font-size:12px;color:var(--text-muted)">Select the existing session and registered class to promote from.</div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label for="promote_source_session_id" style="color:#10b981;font-weight:700">1. Source Session *</label>
                    <select id="promote_source_session_id" name="source_session_id" onchange="filterPromoteSourceClasses(this.value)" style="border-color:rgba(16,185,129,0.4);background:#0b0f19">
                        <?php $__currentLoopData = $allTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php echo e($activeTerm && $activeTerm->id == $t->id ? 'selected' : ''); ?>>
                                <?php echo e($t->name); ?> <?php echo e($t->is_active ? '(Active Session)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label for="promote_source_class_id" style="color:#10b981;font-weight:700">2. Registered Class to Promote *</label>
                    <select id="promote_source_class_id" name="source_class_id" required onchange="previewFailedStudents()" style="border-color:rgba(16,185,129,0.4);background:#0b0f19">
                        <option value="">-- Select Class to Promote --</option>
                        <?php $__currentLoopData = $allClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" data-term="<?php echo e($c->academic_term_id); ?>" <?php echo e($selectedSourceClassId == $c->id ? 'selected' : ''); ?>>
                                🎓 <?php echo e($c->custom_name); ?> (<?php echo e($c->sections->count()); ?> Sections, <?php echo e($c->students_count ?? 0); ?> Students)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- STEP 2: DESTINATION SESSION & TARGET CLASS -->
            <div class="card" style="background:#121827;border:1px solid rgba(56,189,248,0.3);border-radius:16px;padding:24px">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(56,189,248,0.2);color:#38bdf8;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px">2</div>
                    <div>
                        <h2 style="font-size:16px;font-weight:700;color:#38bdf8;margin:0">Destination Academic Session &amp; Class</h2>
                        <div style="font-size:12px;color:var(--text-muted)">Select target session and existing or new custom class name.</div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label for="promote_target_session_id" style="color:#38bdf8;font-weight:700">3. Target Destination Session *</label>
                    <select id="promote_target_session_id" name="target_session_id" onchange="filterPromoteTargetClasses(this.value)" required style="border-color:rgba(56,189,248,0.4);background:#0b0f19">
                        <option value="">-- Select Target Session --</option>
                        <?php $__currentLoopData = $allTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>">
                                🚀 <?php echo e($t->name); ?> <?php echo e($t->is_active ? '(Active Session)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label for="promote_target_class_id" style="color:#38bdf8;font-weight:700">4. Select Existing Target Class</label>
                    <select id="promote_target_class_id" name="target_class_id" style="border-color:rgba(56,189,248,0.4);background:#0b0f19">
                        <option value="">-- Select Target Class OR Type Custom Name Below --</option>
                        <?php $__currentLoopData = $allClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ac->id); ?>" data-term="<?php echo e($ac->academic_term_id); ?>">
                                <?php echo e($ac->custom_name); ?> (Session: <?php echo e($ac->academicTerm ? $ac->academicTerm->name : 'N/A'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label for="custom_target_class_name" style="color:#c084fc;font-weight:700">OR Enter Custom Target Class Name</label>
                    <input id="custom_target_class_name" type="text" name="custom_target_class_name" placeholder="e.g. Grade 10 - Science, FMA, 1st Year Pre-Med" style="border-color:rgba(192,132,252,0.4);background:#0b0f19">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                        If class is not created yet in the new session, enter a custom name here and it will be provisioned automatically.
                    </div>
                </div>
            </div>

        </div>

        <!-- STEP 3: PROMOTION DECISION RULES & FAILED STUDENT ENGINE -->
        <div class="card" style="background:#121827;border:1px solid rgba(139,92,246,0.3);border-radius:16px;padding:24px;margin-bottom:24px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(139,92,246,0.2);color:#c084fc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px">3</div>
                <div>
                    <h2 style="font-size:16px;font-weight:700;color:#c084fc;margin:0">Promotion Rules &amp; Failed Student Strategy</h2>
                    <div style="font-size:12px;color:var(--text-muted)">Configure how automated rollover and subject-wise failures are handled.</div>
                </div>
            </div>

            <!-- Rollover Options Checkboxes -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:14px;margin-bottom:20px">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.2);border-radius:10px">
                    <input type="checkbox" name="promote_students" value="1" checked style="width:18px;height:18px;accent-color:#10b981;margin-top:2px">
                    <div>
                        <span style="font-size:13px;color:#fff;font-weight:700">Promote Class Roster</span>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:2px">Transfer enrolled student records to the new session's class.</div>
                    </div>
                </label>

                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:rgba(56,189,248,0.06);border:1px solid rgba(56,189,248,0.2);border-radius:10px">
                    <input type="checkbox" name="copy_faculty_allocations" value="1" checked style="width:18px;height:18px;accent-color:#38bdf8;margin-top:2px">
                    <div>
                        <span style="font-size:13px;color:#fff;font-weight:700">Preserve Faculty Allocations</span>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:2px">Retain teacher subject section authorities and class incharge roles.</div>
                    </div>
                </label>

                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:rgba(192,132,252,0.06);border:1px solid rgba(192,132,252,0.2);border-radius:10px">
                    <input type="checkbox" name="copy_textbook_materials" value="1" checked style="width:18px;height:18px;accent-color:#c084fc;margin-top:2px">
                    <div>
                        <span style="font-size:13px;color:#fff;font-weight:700">Retain Textbook PDFs &amp; RAG AI Chunks</span>
                        <div style="font-size:11.5px;color:#94a3b8;margin-top:2px">Copy centralized course materials &amp; vector index chunks to target subjects.</div>
                    </div>
                </label>
            </div>

            <!-- 3-Way Failed Student Action Menu -->
            <div style="padding:18px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.25);border-radius:12px">
                <div style="font-size:14px;font-weight:800;color:#f87171;margin-bottom:12px;display:flex;align-items:center;gap:8px">
                    ⚠️ How should FAILED students be handled during promotion?
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:12px">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px;background:rgba(15,23,42,0.7);border:1px solid rgba(239,68,68,0.3);border-radius:10px" id="failedAction_retain_label">
                        <input type="radio" name="failed_student_action" value="retain" checked style="accent-color:#f87171;margin-top:3px;width:16px;height:16px" onchange="updateFailedActionHighlight()">
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#fff">🔁 Retain in Same Class (Repeat Year)</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:3px">
                                Failed students stay in current level (e.g. 9A) in the new session alongside students promoted from previous class (e.g. 8A &rarr; 9A).
                            </div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px;background:rgba(15,23,42,0.7);border:1px solid rgba(245,158,11,0.3);border-radius:10px" id="failedAction_promote_with_label">
                        <input type="radio" name="failed_student_action" value="promote_with_failed_subjects" style="accent-color:#f59e0b;margin-top:3px;width:16px;height:16px" onchange="updateFailedActionHighlight()">
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#fff">📋 Promote to Next Class + Carry Failed Subjects</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:3px">
                                Failed students move to target class (e.g. 9A &rarr; 10A), but their specific failed subject records are carried forward for retakes.
                            </div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px;background:rgba(15,23,42,0.7);border:1px solid rgba(16,185,129,0.3);border-radius:10px" id="failedAction_promote_fully_label">
                        <input type="radio" name="failed_student_action" value="promote_fully" style="accent-color:#10b981;margin-top:3px;width:16px;height:16px" onchange="updateFailedActionHighlight()">
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#fff">✅ Promote Fully (Ignore Failures)</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:3px">
                                All students advance to target class regardless of result status. Failed subject flags are cleared for new session.
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- LIVE ROSTER & FAILED STUDENTS PREVIEW PANEL -->
        <div class="card" style="background:#121827;border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:24px;margin-bottom:32px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <div>
                    <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;display:flex;align-items:center;gap:8px">
                        📊 Live Class Roster &amp; Failed Students Preview
                    </h2>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                        View pass/fail status breakdown of students in the selected source class.
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="previewFailedStudents()" style="border-color:rgba(239,68,68,0.3);color:#f87171">
                    🔄 Refresh Roster Preview
                </button>
            </div>

            <div id="failedStudentsPreviewContent" style="font-size:13px;color:#cbd5e1;padding:16px;background:#0b0f19;border-radius:12px;border:1px solid rgba(255,255,255,0.06)">
                <div style="text-align:center;padding:20px;color:var(--text-muted)">
                    👆 Please select a <strong>Registered Class to Promote</strong> above to load the live student roster preview.
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:20px;background:#121827;border:1px solid var(--border);border-radius:16px">
            <a href="<?php echo e(route('principal.classes-subjects.index')); ?>" class="btn btn-ghost" style="color:var(--text-muted)">
                &larr; Cancel &amp; Return to Classes
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);border:none;box-shadow:0 4px 18px rgba(16,185,129,0.35);padding:12px 28px;font-weight:700;font-size:15px">
                🚀 Confirm &amp; Promote Class Roster Now
            </button>
        </div>

    </form>
</div>

<script>
    function filterPromoteSourceClasses(sessionId) {
        const sourceSelect = document.getElementById('promote_source_class_id');
        const options = sourceSelect.options;
        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            const termId = opt.getAttribute('data-term');
            if (!termId) continue;
            if (!sessionId || termId === sessionId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }
        sourceSelect.value = '';
        document.getElementById('failedStudentsPreviewContent').innerHTML = `
            <div style="text-align:center;padding:20px;color:var(--text-muted)">
                👆 Please select a <strong>Registered Class to Promote</strong> above to load the live student roster preview.
            </div>
        `;
    }

    function filterPromoteTargetClasses(sessionId) {
        const targetSelect = document.getElementById('promote_target_class_id');
        const options = targetSelect.options;
        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            const termId = opt.getAttribute('data-term');
            if (!termId) continue;
            if (!sessionId || termId === sessionId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }
        targetSelect.value = '';
    }

    function updateFailedActionHighlight() {
        const labels = ['failedAction_retain_label', 'failedAction_promote_with_label', 'failedAction_promote_fully_label'];
        const colors = ['rgba(239,68,68,0.15)', 'rgba(245,158,11,0.15)', 'rgba(16,185,129,0.15)'];
        const borders = ['rgba(239,68,68,0.4)', 'rgba(245,158,11,0.4)', 'rgba(16,185,129,0.4)'];

        labels.forEach((labelId, idx) => {
            const label = document.getElementById(labelId);
            if (!label) return;
            const radio = label.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                label.style.background = colors[idx];
                label.style.borderColor = borders[idx];
            } else {
                label.style.background = 'rgba(15,23,42,0.7)';
                label.style.borderColor = 'rgba(255,255,255,0.08)';
            }
        });
    }

    function previewFailedStudents() {
        const sourceClassId = document.getElementById('promote_source_class_id')?.value;
        const content = document.getElementById('failedStudentsPreviewContent');
        if (!sourceClassId) {
            content.innerHTML = `
                <div style="text-align:center;padding:20px;color:var(--text-muted)">
                    👆 Please select a <strong>Registered Class to Promote</strong> above to load the live student roster preview.
                </div>
            `;
            return;
        }

        content.innerHTML = '<div style="color:#94a3b8;text-align:center;padding:16px">⏳ Loading live student roster &amp; result preview...</div>';

        const prefix = window.location.pathname.startsWith('/teacher') ? '/teacher' : '/principal';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

        fetch(`${prefix}/classes/promote/preview`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ source_class_id: sourceClassId }),
        })
        .then(res => res.json())
        .then(data => {
            let html = `
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.1)">
                    <div style="font-weight:700;color:#fff;font-size:14px">Class Roster: ${data.class_name}</div>
                    <div style="display:flex;gap:8px">
                        <span style="font-size:11px;padding:3px 8px;border-radius:10px;background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.3);font-weight:700">
                            ✅ ${data.passed_count} Passed
                        </span>
                        <span style="font-size:11px;padding:3px 8px;border-radius:10px;background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3);font-weight:700">
                            ⚠️ ${data.failed_count} Failed
                        </span>
                        <span style="font-size:11px;padding:3px 8px;border-radius:10px;background:rgba(56,189,248,0.15);color:#38bdf8;border:1px solid rgba(56,189,248,0.3);font-weight:700">
                            👥 ${data.total_students} Total
                        </span>
                    </div>
                </div>
            `;

            if (data.failed_count === 0) {
                html += `
                    <div style="color:#34d399;font-weight:700;padding:12px;background:rgba(16,185,129,0.08);border-radius:8px">
                        🎉 Great News! All ${data.total_students} students in ${data.class_name} have passed their annual evaluations.
                        <div style="font-size:11.5px;color:#94a3b8;font-weight:400;margin-top:4px">
                            All students will advance cleanly to the selected target class in the new session.
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div style="margin-bottom:10px;color:#f87171;font-weight:700;font-size:13px">
                        Students Requiring Special Promotion Decisions (${data.failed_count}):
                    </div>
                    <div style="display:grid;gap:8px">
                `;

                data.failed_students.forEach(s => {
                    let subjList = '';
                    if (s.failed_subjects && s.failed_subjects.length > 0) {
                        subjList = s.failed_subjects.map(fs =>
                            `<span style="display:inline-block;padding:2px 8px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:6px;font-size:11px;color:#f87171">
                                ❌ ${fs.subject_name} (${fs.marks_obtained}/${fs.total_marks})
                            </span>`
                        ).join('');
                    } else {
                        subjList = '<span style="font-size:11px;color:#94a3b8">Overall status: Failed</span>';
                    }

                    html += `
                        <div style="padding:10px 14px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.15);border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                            <div>
                                <strong style="color:#fff;font-size:13px">${s.name}</strong> 
                                <span style="color:#94a3b8;font-size:12px">(${s.roll_number} — Section ${s.section})</span>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:4px">
                                ${subjList}
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;
            }

            content.innerHTML = html;
        })
        .catch(err => {
            content.innerHTML = '<div style="color:#f87171">❌ Error loading preview: ' + err.message + '</div>';
        });
    }

    // Auto-trigger initial setup on page load if source class is pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const srcSession = document.getElementById('promote_source_session_id')?.value;
        if (srcSession) filterPromoteSourceClasses(srcSession);
        
        const srcClass = document.getElementById('promote_source_class_id')?.value;
        if (srcClass) previewFailedStudents();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\classes-subjects\promote.blade.php ENDPATH**/ ?>
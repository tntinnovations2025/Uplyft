

<?php $__env->startSection('title', 'Grading Scales & Weightages'); ?>
<?php $__env->startSection('breadcrumb', 'Grading Weightages'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $types = \App\Models\Assessment::TYPES;
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">⚖️ Grading Scales &amp; Weightages</h1>
        <p class="muted" style="margin-top:4px">Browse classes, view subjects, configure assessment total marks, weightages, grade scale rules, and student display settings.</p>
    </div>
</div>


<div class="card" style="margin-bottom:24px">
    <div class="card-header">
        <h3 class="card-title">🏫 Institute Classes &amp; Assigned Subjects</h3>
        <span class="badge badge-purple"><?php echo e(count($classes)); ?> Classes Registered</span>
    </div>

    <?php if($classes->isEmpty()): ?>
        <div style="text-align:center;padding:40px" class="muted">
            <div style="font-size:32px;margin-bottom:8px">🏫</div>
            <p>No institute classes or subjects found. Please register classes and subjects in the Principal Portal.</p>
        </div>
    <?php else: ?>
        <div style="display:grid;gap:16px" id="classes-accordion">
            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $cName = $cls->custom_name ?? "Class #{$cls->id}";
                    $subCount = $cls->subjects->count();
                ?>
                <div class="class-card-item" style="border:1px solid var(--border);border-radius:12px;overflow:hidden;background:var(--surface2)">
                    
                    <div onclick="toggleClassAccordion('class-subs-<?php echo e($cls->id); ?>')" 
                         style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:rgba(255,255,255,0.02);user-select:none"
                         class="class-header-hover">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="width:36px;height:36px;border-radius:10px;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px">
                                🎓
                            </div>
                            <div>
                                <h3 style="font-size:16px;font-weight:700;margin:0;color:var(--text)"><?php echo e($cName); ?></h3>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                                    <?php echo e($subCount); ?> <?php echo e($subCount === 1 ? 'Subject' : 'Subjects'); ?> Assigned &bull; <?php echo e($cls->sections->count()); ?> Sections
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <span class="badge badge-green" style="font-size:12px"><?php echo e($subCount); ?> Subjects</span>
                            <span id="arrow-class-subs-<?php echo e($cls->id); ?>" style="transition:transform 0.3s ease;font-size:14px;color:var(--text-muted)">▼</span>
                        </div>
                    </div>

                    
                    <div id="class-subs-<?php echo e($cls->id); ?>" style="display:none;border-t:1px solid var(--border);padding:16px;background:var(--surface)">
                        <?php if($cls->subjects->isEmpty()): ?>
                            <div class="muted" style="padding:12px;font-size:13px;text-align:center">No subjects registered for this class yet.</div>
                        <?php else: ?>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px">
                                <?php $__currentLoopData = $cls->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div style="padding:16px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);display:flex;flex-direction:column;justify-content:space-between;gap:12px">
                                        <div>
                                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                                                <h4 style="font-size:15px;font-weight:700;margin:0;color:var(--text)">📖 <?php echo e($sub->subject_name); ?></h4>
                                                <span class="badge badge-purple" style="font-size:11px"><?php echo e($sub->subject_code ?? 'SUB'); ?></span>
                                            </div>
                                            <div style="font-size:12px;color:var(--text-muted);display:flex;gap:12px;margin-bottom:10px">
                                                <span>🎯 Total Marks: <strong><?php echo e($sub->total_marks ?? 100); ?></strong></span>
                                                <span>⏱️ <?php echo e($sub->formatted_lecture_duration); ?></span>
                                            </div>

                                            
                                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                                <span id="badge-marks-<?php echo e($sub->id); ?>" class="badge <?php echo e(($sub->show_marks_to_student ?? true) ? 'badge-green' : 'badge-red'); ?>" style="font-size:11px">
                                                    📊 Marks: <?php echo e(($sub->show_marks_to_student ?? true) ? 'Visible' : 'Hidden'); ?>

                                                </span>
                                                <span id="badge-grade-<?php echo e($sub->id); ?>" class="badge <?php echo e(($sub->show_grade_to_student ?? true) ? 'badge-green' : 'badge-red'); ?>" style="font-size:11px">
                                                    🏆 Grade: <?php echo e(($sub->show_grade_to_student ?? true) ? 'Visible' : 'Hidden'); ?>

                                                </span>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:4px"
                                                onclick="openSubjectMenu(<?php echo e($sub->id); ?>, '<?php echo e(addslashes($sub->subject_name)); ?>', '<?php echo e(addslashes($cName)); ?>', <?php echo e($cls->id); ?>)">
                                            ⚙️ Configure Grading &amp; Weightages
                                        </button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>


<div id="subject-menu-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:20px;overflow-y:auto">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;width:100%;max-width:860px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,0.5);overflow:hidden">
        
        
        <div style="padding:20px 24px;border-bottom:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between">
            <div>
                <h2 id="sm-title" style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin:0;color:var(--text)">
                    📖 Subject Grading &amp; Weightage Menu
                </h2>
                <p id="sm-subtitle" class="muted" style="margin-top:2px;font-size:12px">Configure total marks, dual assessment sliders (Marks &amp; Weightage %), grade thresholds, and student portal display toggles.</p>
            </div>
            <button type="button" style="background:transparent;border:none;color:var(--text-muted);font-size:24px;cursor:pointer;padding:4px" onclick="closeSubjectMenu()">&times;</button>
        </div>

        
        <div style="padding:24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:24px">
            
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;background:var(--surface2);padding:14px;border-radius:10px;border:1px solid var(--border)">
                <div>
                    <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;display:block;margin-bottom:4px">Academic Term *</label>
                    <select id="sm-term" style="width:100%;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:13px" onchange="reloadSubjectWeightages()">
                        <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php if($t->is_active): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;display:block;margin-bottom:4px">Class Section *</label>
                    <select id="sm-section" style="width:100%;padding:8px 12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:13px" onchange="reloadSubjectWeightages()">
                        <option value="">Loading Sections...</option>
                    </select>
                </div>
            </div>

            
            <div style="border:1px solid var(--border);border-radius:12px;padding:20px;background:var(--surface2)">
                <h3 style="font-size:16px;font-weight:700;margin-top:0;margin-bottom:16px;display:flex;align-items:center;gap:8px">
                    🎯 1. Subject Total Marks &amp; Assessment Sliders
                </h3>

                
                <div style="margin-bottom:20px;display:flex;align-items:center;gap:16px;background:var(--surface);padding:14px;border-radius:10px;border:1px solid var(--border)">
                    <div style="flex:1">
                        <label style="font-weight:700;font-size:13px;color:var(--text);display:block">Subject Overall Max Marks</label>
                        <span class="muted" style="font-size:11px">The base total marks allocated to this course (e.g. 100, 50).</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <input type="number" id="sm-total-marks" min="1" max="1000" value="100" 
                               style="width:90px;padding:8px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-weight:700;font-size:16px;text-align:center">
                        <span style="font-size:12px;font-weight:600;color:var(--text-muted)">Pts</span>
                    </div>
                </div>

                
                <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface);display:flex;align-items:center;justify-content:space-between" id="sm-total-bar">
                    <div>
                        <span style="font-weight:600;font-size:13px">Total Weightage Allocated:</span>
                        <span id="sm-total-percentage" style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;margin-left:8px;color:var(--accent)">0%</span>
                    </div>
                    <div id="sm-total-status" class="badge badge-yellow">Incomplete</div>
                </div>

                
                <div style="display:grid;gap:12px" id="sm-weightage-rows">
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="padding:14px 18px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;flex-direction:column;gap:12px" data-type="<?php echo e($type); ?>">
                            
                            
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <div style="font-weight:700;text-transform:capitalize;font-size:14px;display:flex;align-items:center;gap:8px;color:var(--text)">
                                    <?php if(in_array($type, \App\Models\Assessment::MANDATORY_TYPES)): ?>
                                        <span style="color:var(--danger);font-size:10px">●</span>
                                    <?php endif; ?>
                                    <?php echo e($type); ?>

                                    <?php if(in_array($type, \App\Models\Assessment::MANDATORY_TYPES)): ?>
                                        <span style="font-size:11px;color:var(--danger);font-weight:600">(Mandatory)</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);display:flex;gap:12px">
                                    <span>Marks: <strong class="sm-marks-val-<?php echo e($type); ?>" style="color:var(--accent2)">100 Pts</strong></span>
                                    <span>Weightage: <strong class="sm-w-val-<?php echo e($type); ?>" style="color:var(--accent)">0%</strong></span>
                                </div>
                            </div>

                            
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                                
                                
                                <div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                                        <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase">🎯 Slider 1: Marks</label>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <input type="range" class="sm-marks-slider" data-type="<?php echo e($type); ?>" min="0" max="100" value="100"
                                               style="flex:1;accent-color:var(--accent2);height:6px"
                                               oninput="updateSmMarksValue(this)">
                                        <div style="display:flex;align-items:center;gap:4px">
                                            <input type="number" class="sm-marks-input" data-type="<?php echo e($type); ?>" min="0" max="1000" value="100"
                                                   style="width:58px;padding:5px 6px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);text-align:center;font-size:13px;font-weight:700"
                                                   oninput="updateSmMarksSlider(this)">
                                            <span style="color:var(--text-muted);font-size:11px;font-weight:700">Pts</span>
                                        </div>
                                    </div>
                                </div>

                                
                                <div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                                        <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase">⚖️ Slider 2: Weightage %</label>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <input type="range" class="sm-w-slider" data-type="<?php echo e($type); ?>" min="0" max="100" value="0"
                                               style="flex:1;accent-color:var(--accent);height:6px"
                                               oninput="updateSmWeightageValue(this)">
                                        <div style="display:flex;align-items:center;gap:4px">
                                            <input type="number" class="sm-w-input" data-type="<?php echo e($type); ?>" min="0" max="100" value="0"
                                                   style="width:58px;padding:5px 6px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);text-align:center;font-size:13px;font-weight:700"
                                                   oninput="updateSmWeightageSlider(this)">
                                            <span style="color:var(--text-muted);font-size:11px;font-weight:700">%</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div style="border:1px solid var(--border);border-radius:12px;padding:20px;background:var(--surface2)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <h3 style="font-size:16px;font-weight:700;margin:0">
                        🏆 2. Grade Criteria &amp; Threshold Rules
                    </h3>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="resetDefaultGradeScale()">🔄 Reset Standard Scale</button>
                </div>
                <p class="muted" style="font-size:12px;margin-bottom:16px">Define the minimum marks percentage required to achieve each letter grade for this subject.</p>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px">
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-green" style="font-size:14px;font-weight:700;margin-bottom:6px">A+</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Min %</div>
                        <input type="number" id="gs-A-plus" value="90" min="0" max="100" style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                    </div>
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-green" style="font-size:14px;font-weight:700;margin-bottom:6px">A</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Min %</div>
                        <input type="number" id="gs-A" value="80" min="0" max="100" style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                    </div>
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-purple" style="font-size:14px;font-weight:700;margin-bottom:6px">B</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Min %</div>
                        <input type="number" id="gs-B" value="70" min="0" max="100" style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                    </div>
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-yellow" style="font-size:14px;font-weight:700;margin-bottom:6px">C</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Min %</div>
                        <input type="number" id="gs-C" value="60" min="0" max="100" style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                    </div>
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-yellow" style="font-size:14px;font-weight:700;margin-bottom:6px">D</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Min %</div>
                        <input type="number" id="gs-D" value="50" min="0" max="100" style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-weight:700;font-size:13px">
                    </div>
                    <div style="padding:10px;background:var(--surface);border:1px solid var(--border);border-radius:8px;text-align:center">
                        <div class="badge badge-red" style="font-size:14px;font-weight:700;margin-bottom:6px">F</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px">Below %</div>
                        <input type="number" value="50" disabled style="width:100%;text-align:center;padding:4px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;color:var(--text-muted);font-weight:700;font-size:13px;opacity:0.7">
                    </div>
                </div>
            </div>

            
            <div style="border:1px solid var(--border);border-radius:12px;padding:20px;background:linear-gradient(135deg,rgba(212,138,46,0.08),rgba(232,206,170,0.2))">
                <h3 style="font-size:16px;font-weight:700;margin-top:0;margin-bottom:6px;display:flex;align-items:center;gap:8px;color:var(--accent2)">
                    👁️ 3. Student Portal Display Controls
                </h3>
                <p class="muted" style="font-size:12px;margin-bottom:18px">
                    Toggle options below to control what students can see on their portal: <strong>Marks Achieved per subject</strong>, <strong>Grade of that subject</strong>, or both.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    
                    <div style="padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:12px">
                        <div>
                            <div style="font-weight:700;font-size:14px;color:var(--text);display:flex;align-items:center;gap:6px">
                                📊 Display Marks to Student
                            </div>
                            <div class="muted" style="font-size:11px;margin-top:2px">Shows total &amp; test marks achieved on student dashboard.</div>
                        </div>
                        <label class="switch-toggle" style="position:relative;display:inline-block;width:48px;height:26px;shrink-0">
                            <input type="checkbox" id="sm-toggle-marks" checked style="opacity:0;width:0;height:0">
                            <span class="slider-round"></span>
                        </label>
                    </div>

                    
                    <div style="padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:12px">
                        <div>
                            <div style="font-weight:700;font-size:14px;color:var(--text);display:flex;align-items:center;gap:6px">
                                🏆 Display Grade to Student
                            </div>
                            <div class="muted" style="font-size:11px;margin-top:2px">Shows letter grade (e.g. A+, B) for this subject to student.</div>
                        </div>
                        <label class="switch-toggle" style="position:relative;display:inline-block;width:48px;height:26px;shrink-0">
                            <input type="checkbox" id="sm-toggle-grade" checked style="opacity:0;width:0;height:0">
                            <span class="slider-round"></span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

        
        <div style="padding:16px 24px;border-top:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:space-between">
            <button type="button" class="btn btn-ghost" onclick="applySmDefaults()">🔄 Apply Default Weightages</button>
            <div style="display:flex;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeSubjectMenu()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAllSubjectSettings()">💾 Save Subject Settings</button>
            </div>
        </div>

    </div>
</div>

<style>
    .class-header-hover:hover {
        background: rgba(255,255,255,0.05) !important;
    }
    .switch-toggle input:checked + .slider-round {
        background-color: var(--accent);
    }
    .switch-toggle input:checked + .slider-round:before {
        transform: translateX(22px);
    }
    .slider-round {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--border);
        transition: .3s;
        border-radius: 26px;
    }
    .slider-round:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
</style>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let activeSubjectId = null;
    let activeClassId = null;
    const classSectionsMap = <?php echo json_encode($classes->keyBy('id')->map(fn($c) => $c->sections), 15, 512) ?>;

    function toggleClassAccordion(id) {
        const el = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        if (el.style.display === 'none' || !el.style.display) {
            el.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            el.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function openSubjectMenu(subId, subName, className, classId) {
        activeSubjectId = subId;
        activeClassId = classId;

        document.getElementById('sm-title').textContent = `📖 ${className} — ${subName}`;
        document.getElementById('sm-subtitle').textContent = `Configuring dual assessment sliders (Marks & Weightage %), grade rules & student portal visibility for ${subName}.`;

        // Populate sections for this class
        const secSelect = document.getElementById('sm-section');
        secSelect.innerHTML = '';
        const secs = classSectionsMap[classId] || [];
        if (secs.length === 0) {
            secSelect.innerHTML = '<option value="">No sections found for class</option>';
        } else {
            secs.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `Section ${s.section_name}`;
                secSelect.appendChild(opt);
            });
        }

        document.getElementById('subject-menu-overlay').style.display = 'flex';
        reloadSubjectWeightages();
    }

    function closeSubjectMenu() {
        document.getElementById('subject-menu-overlay').style.display = 'none';
    }

    function updateSmMarksValue(slider) {
        const type = slider.dataset.type;
        const val = slider.value;
        const input = document.querySelector(`.sm-marks-input[data-type="${type}"]`);
        if (input) input.value = val;
        const label = document.querySelector(`.sm-marks-val-${type}`);
        if (label) label.textContent = val + ' Pts';
    }

    function updateSmMarksSlider(input) {
        const type = input.dataset.type;
        const val = input.value;
        const slider = document.querySelector(`.sm-marks-slider[data-type="${type}"]`);
        if (slider) slider.value = val;
        const label = document.querySelector(`.sm-marks-val-${type}`);
        if (label) label.textContent = val + ' Pts';
    }

    function updateSmWeightageValue(slider) {
        const type = slider.dataset.type;
        const val = slider.value;
        const input = document.querySelector(`.sm-w-input[data-type="${type}"]`);
        if (input) input.value = val;
        const label = document.querySelector(`.sm-w-val-${type}`);
        if (label) label.textContent = val + '%';
        recalcSmTotal();
    }

    function updateSmWeightageSlider(input) {
        const type = input.dataset.type;
        const val = input.value;
        const slider = document.querySelector(`.sm-w-slider[data-type="${type}"]`);
        if (slider) slider.value = val;
        const label = document.querySelector(`.sm-w-val-${type}`);
        if (label) label.textContent = val + '%';
        recalcSmTotal();
    }

    function recalcSmTotal() {
        let total = 0;
        document.querySelectorAll('.sm-w-input').forEach(inp => {
            total += parseFloat(inp.value) || 0;
        });

        document.getElementById('sm-total-percentage').textContent = total.toFixed(1) + '%';

        const statusEl = document.getElementById('sm-total-status');
        const totalBar = document.getElementById('sm-total-bar');
        if (Math.abs(total - 100) < 0.01) {
            statusEl.className = 'badge badge-green';
            statusEl.textContent = '✅ Perfect (100%)';
            totalBar.style.borderColor = 'var(--success)';
        } else if (total > 100) {
            statusEl.className = 'badge badge-red';
            statusEl.textContent = '⚠️ Over 100%';
            totalBar.style.borderColor = 'var(--danger)';
        } else {
            statusEl.className = 'badge badge-yellow';
            statusEl.textContent = `${(100 - total).toFixed(1)}% remaining`;
            totalBar.style.borderColor = 'var(--border)';
        }
    }

    function resetDefaultGradeScale() {
        document.getElementById('gs-A-plus').value = 90;
        document.getElementById('gs-A').value = 80;
        document.getElementById('gs-B').value = 70;
        document.getElementById('gs-C').value = 60;
        document.getElementById('gs-D').value = 50;
    }

    async function reloadSubjectWeightages() {
        if (!activeSubjectId) return;

        const termId = document.getElementById('sm-term').value;
        const sectionId = document.getElementById('sm-section').value;

        try {
            const url = `/lms/grades/weightages?subject_id=${activeSubjectId}&academic_term_id=${termId}&class_section_id=${sectionId || 0}`;
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();

            // Reset inputs
            document.querySelectorAll('.sm-marks-input').forEach(inp => { inp.value = 100; });
            document.querySelectorAll('.sm-marks-slider').forEach(sl => { sl.value = 100; });
            document.querySelectorAll('.sm-w-input').forEach(inp => { inp.value = 0; });
            document.querySelectorAll('.sm-w-slider').forEach(sl => { sl.value = 0; });

            // Populate weightages and marks per assessment type
            (data.weightages || []).forEach(w => {
                const type = w.assessment_type;
                const mInp = document.querySelector(`.sm-marks-input[data-type="${type}"]`);
                const mSl = document.querySelector(`.sm-marks-slider[data-type="${type}"]`);
                const mLabel = document.querySelector(`.sm-marks-val-${type}`);

                const wInp = document.querySelector(`.sm-w-input[data-type="${type}"]`);
                const wSl = document.querySelector(`.sm-w-slider[data-type="${type}"]`);
                const wLabel = document.querySelector(`.sm-w-val-${type}`);

                const totalMarks = w.total_marks !== undefined ? w.total_marks : 100;
                const weightagePct = parseFloat(w.weightage_percentage) || 0;

                if (mInp) mInp.value = totalMarks;
                if (mSl) mSl.value = totalMarks;
                if (mLabel) mLabel.textContent = totalMarks + ' Pts';

                if (wInp) wInp.value = weightagePct;
                if (wSl) wSl.value = weightagePct;
                if (wLabel) wLabel.textContent = weightagePct + '%';
            });

            // Populate Subject model fields
            if (data.subject) {
                document.getElementById('sm-total-marks').value = data.subject.total_marks || 100;
                document.getElementById('sm-toggle-marks').checked = data.subject.show_marks_to_student !== false;
                document.getElementById('sm-toggle-grade').checked = data.subject.show_grade_to_student !== false;

                const gs = data.subject.grade_scale_json || { 'A+': 90, 'A': 80, 'B': 70, 'C': 60, 'D': 50 };
                if (gs['A+'] !== undefined) document.getElementById('gs-A-plus').value = gs['A+'];
                if (gs['A'] !== undefined) document.getElementById('gs-A').value = gs['A'];
                if (gs['B'] !== undefined) document.getElementById('gs-B').value = gs['B'];
                if (gs['C'] !== undefined) document.getElementById('gs-C').value = gs['C'];
                if (gs['D'] !== undefined) document.getElementById('gs-D').value = gs['D'];
            }

            recalcSmTotal();
        } catch (err) {
            console.error('Failed to load weightages:', err);
        }
    }

    async function saveAllSubjectSettings() {
        if (!activeSubjectId) return;

        const termId = document.getElementById('sm-term').value;
        const sectionId = document.getElementById('sm-section').value;
        const totalMarks = parseInt(document.getElementById('sm-total-marks').value) || 100;
        const showMarks = document.getElementById('sm-toggle-marks').checked;
        const showGrade = document.getElementById('sm-toggle-grade').checked;

        const gradeScale = {
            'A+': parseFloat(document.getElementById('gs-A-plus').value) || 90,
            'A': parseFloat(document.getElementById('gs-A').value) || 80,
            'B': parseFloat(document.getElementById('gs-B').value) || 70,
            'C': parseFloat(document.getElementById('gs-C').value) || 60,
            'D': parseFloat(document.getElementById('gs-D').value) || 50,
            'F': 0
        };

        // Collect weightages with both total_marks and weightage_percentage
        const weightages = [];
        document.querySelectorAll('.sm-w-input').forEach(inp => {
            const type = inp.dataset.type;
            const wVal = parseFloat(inp.value) || 0;
            const mVal = parseInt(document.querySelector(`.sm-marks-input[data-type="${type}"]`)?.value) || 100;
            
            if (wVal > 0 || mVal > 0) {
                weightages.push({
                    assessment_type: type,
                    total_marks: mVal,
                    weightage_percentage: wVal
                });
            }
        });

        try {
            // 1. Save Subject Model Config
            await fetch('/lms/grades/subject-config', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    subject_id: activeSubjectId,
                    total_marks: totalMarks,
                    show_marks_to_student: showMarks,
                    show_grade_to_student: showGrade,
                    grade_scale_json: gradeScale,
                })
            });

            // 2. Save Assessment Weightages & Marks (if section is selected)
            if (sectionId && weightages.length > 0) {
                await fetch('/lms/grades/weightages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        subject_id: activeSubjectId,
                        academic_term_id: termId,
                        class_section_id: sectionId,
                        weightages: weightages,
                    }),
                });
            }

            // Update UI badges for this subject
            const badgeMarks = document.getElementById(`badge-marks-${activeSubjectId}`);
            if (badgeMarks) {
                badgeMarks.className = `badge ${showMarks ? 'badge-green' : 'badge-red'}`;
                badgeMarks.textContent = `📊 Marks: ${showMarks ? 'Visible' : 'Hidden'}`;
            }

            const badgeGrade = document.getElementById(`badge-grade-${activeSubjectId}`);
            if (badgeGrade) {
                badgeGrade.className = `badge ${showGrade ? 'badge-green' : 'badge-red'}`;
                badgeGrade.textContent = `🏆 Grade: ${showGrade ? 'Visible' : 'Hidden'}`;
            }

            alert('✅ Subject grading rules, dual assessment sliders & student display settings saved successfully!');
            closeSubjectMenu();

        } catch (err) {
            alert('⚠️ Failed to save settings: ' + err.message);
        }
    }

    async function applySmDefaults() {
        if (!activeSubjectId) return;
        const termId = document.getElementById('sm-term').value;
        const sectionId = document.getElementById('sm-section').value;

        if (!sectionId) {
            alert('Please select a class section first.');
            return;
        }

        try {
            const res = await fetch('/lms/grades/weightages/defaults', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ subject_id: activeSubjectId, academic_term_id: termId, class_section_id: sectionId }),
            });

            const data = await res.json();
            alert(data.message || 'Defaults applied!');
            reloadSubjectWeightages();
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\grades\weightages.blade.php ENDPATH**/ ?>
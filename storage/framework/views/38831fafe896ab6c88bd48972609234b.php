<?php $__env->startSection('title', 'Classes, Grades & Sections'); ?>
<?php $__env->startSection('breadcrumb', 'Classes & Sections'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* Modal Backdrop & Dialog */
    .modal-backdrop {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .modal-backdrop.active { display: flex; }
    
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        width: 100%;
        max-width: 580px;
        padding: 24px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        animation: modalSlide 0.2s ease-out;
        max-height: 90vh;
        overflow-y: auto;
        color: #0f172a;
    }
    @keyframes modalSlide {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }
    .modal-body {
        color: #475569;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 24px;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }

    /* Accordion Style */
    .class-card {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 14px;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease;
    }
    .class-card.open {
        border-color: rgba(225, 48, 108, 0.4);
    }
    .class-card-header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        background: #ffffff;
        transition: background 0.15s ease;
    }
    .class-card-header:hover {
        background: #f8fafc;
    }
    .class-card-body {
        padding: 0 24px 24px 24px;
        display: none;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .class-card.open .class-card-body {
        display: block;
    }

    .capacity-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .capacity-green { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .capacity-red { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">🏫 Classes, Grades &amp; Sections</h1>
        <p style="color:#64748b;font-size:13px;margin-top:4px;font-weight:500">
            Configure academic classes, section divisions, student capacities, and student promotions.
        </p>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="<?php echo e(route('principal.rooms.index')); ?>" class="btn btn-ghost">
            🏢 Campus Classrooms
        </a>
        <?php if($activeTerm && $activeTerm->isPromotionWindowOpen()): ?>
            <a href="<?php echo e(route('principal.classes.promote.page')); ?>" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);border:none;box-shadow:0 4px 15px rgba(16,185,129,0.3);display:inline-flex;align-items:center;gap:6px">
                🎓 Student Promotion Desk
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" onclick="openAddClassModal()" style="background:linear-gradient(135deg, #8b5cf6, #6366f1);border:none;box-shadow:0 4px 15px rgba(139,92,246,0.3)">
            ➕ Add New Class
        </button>
    </div>
</div>

<?php if($activeTerm && $activeTerm->promotion_deadline && !$activeTerm->isPromotionWindowOpen()): ?>
<div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;padding:12px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;justify-content:space-between">
    <span style="display:flex;align-items:center;gap:8px">
        <span>⏳</span>
        <span>Class Promotion window for term <strong><?php echo e($activeTerm->name); ?></strong> closed on <?php echo e($activeTerm->promotion_deadline->format('d M Y, h:i A')); ?>.</span>
    </span>
    <a href="<?php echo e(route('principal.academic-terms.index')); ?>" style="color:#fbbf24;text-decoration:underline;font-weight:700">Update Deadline &rarr;</a>
</div>
<?php endif; ?>

<?php if(session('success')): ?>
<div style="background:rgba(46,213,115,0.12);border:1px solid rgba(46,213,115,0.3);color:#2ed573;padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:10px">
    <span style="font-size:18px">✓</span>
    <span style="font-weight:600"><?php echo e(session('success')); ?></span>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div style="background:rgba(255,71,87,0.12);border:1px solid rgba(255,71,87,0.3);color:#ff4757;padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:10px">
    <span style="font-size:18px">⚠️</span>
    <span style="font-weight:600"><?php echo e(session('error')); ?></span>
</div>
<?php endif; ?>

<!-- Full Width Classes Accordion List -->
<div>
    <?php if($classes->count() > 0): ?>
        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="class-card" id="class-card-<?php echo e($class->id); ?>">
            <!-- Card Header -->
            <div class="class-card-header" onclick="toggleClassCard(<?php echo e($class->id); ?>)">
                <div style="display:flex;align-items:center;gap:14px">
                    <span id="chevron-<?php echo e($class->id); ?>" style="font-size:14px;color:var(--text-muted);transition:transform 0.2s;display:inline-block">▶</span>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff">
                                <?php echo e($class->custom_name); ?>

                            </div>
                            <?php if($class->systemClass): ?>
                                <span class="badge badge-purple" style="font-size:10px">
                                    Master: <?php echo e($class->systemClass->name); ?> (<?php echo e(strtoupper($class->systemClass->education_type)); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                            <?php echo e($class->sections->count()); ?> Section(s) • <?php echo e($class->subjects->count()); ?> Subject(s) Provisioned
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <span class="badge badge-green" style="font-weight:700">🎓 <?php echo e($class->students_count ?? 0); ?> Students</span>
                    <span class="badge badge-purple"><?php echo e($class->sections->count()); ?> Sections</span>
                    <span class="badge badge-purple"><?php echo e($class->subjects->count()); ?> Subjects</span>

                    <!-- Rename Class Button -->
                    <button type="button" class="btn btn-ghost btn-sm" onclick="event.stopPropagation(); openEditClassModal(<?php echo e($class->id); ?>, '<?php echo e(addslashes($class->custom_name)); ?>')" style="padding:4px 10px;font-size:12px;color:#38bdf8;border:1px solid rgba(56,189,248,0.3)">
                        ✏️ Rename Class
                    </button>

                    <?php if($activeTerm && $activeTerm->isPromotionWindowOpen()): ?>
                        <a href="<?php echo e(route('principal.classes.promote.page', ['source_class_id' => $class->id])); ?>" onclick="event.stopPropagation()" class="btn btn-ghost btn-sm" style="padding:4px 10px;font-size:12px;color:#10b981;border:1px solid rgba(16,185,129,0.3)">
                            🎓 Promote Class
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); triggerDeleteModal('class', '<?php echo e(route('principal.classes.destroy', $class)); ?>', '<?php echo e(addslashes($class->custom_name)); ?>')" style="padding:4px 10px;font-size:12px">
                        🗑️ Delete
                    </button>
                </div>
            </div>

            <!-- Card Body -->
            <div class="class-card-body">
                
                <!-- 1. SECTIONS & AUTOMATED ROOM ALLOCATIONS SUB-PANEL -->
                <div style="margin-top:20px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
                    <span style="font-weight:600;font-size:14px;color:var(--accent2)">🏫 Sections, Auto-Allocated Rooms &amp; Enrolled Student Roster</span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="openAddSectionModal(<?php echo e($class->id); ?>, '<?php echo e(addslashes($class->custom_name)); ?>')">
                        ➕ Add Section
                    </button>
                </div>

                <?php if($class->sections->count() > 0): ?>
                <table style="margin-bottom:24px;background:var(--surface);border-radius:10px;overflow:hidden;border:1px solid var(--border)">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Auto-Assigned Room / Facility</th>
                            <th>Class Incharge Teacher</th>
                            <th>Enrolled Students / Seating Capacity</th>
                            <th>Enrolled Roster</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php 
                            $actualCount = $sec->students_count ?? ($sec->students ? $sec->students->count() : 0);
                            $enrolled = max($actualCount, $sec->enrolled_students ?? 0); 
                            $capacity = $sec->capacity ?? 40;
                            $isFull = $enrolled >= $capacity;
                            $roomName = $sec->room ? $sec->room->room_number : ($sec->room_number ?? 'Auto-Assigned');
                        ?>
                        <tr>
                            <td><strong style="color:#fff"><?php echo e($sec->section_name); ?></strong></td>
                            <td>
                                <span class="badge badge-purple" style="font-size:12px;font-weight:600">
                                    📍 Room: <?php echo e($roomName); ?>

                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?php echo e(route('principal.sections.incharge', $sec)); ?>" style="display:flex;align-items:center;gap:6px">
                                    <?php echo csrf_field(); ?>
                                    <select name="class_incharge_id" onchange="this.form.submit()" style="padding:4px 8px;background:#070b14;border:1px solid rgba(0,206,209,0.3);border-radius:6px;color:#fff;font-size:12px;outline:none">
                                        <option value="">-- Assign Incharge --</option>
                                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($t->id); ?>" <?php echo e($sec->class_incharge_id == $t->id ? 'selected' : ''); ?>>
                                                👤 <?php echo e($t->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="capacity-badge <?php echo e($isFull ? 'capacity-red' : 'capacity-green'); ?>">
                                    👥 <?php echo e($enrolled); ?> / <?php echo e($capacity); ?> Seats Enrolled
                                    <?php if($isFull): ?>(FULL)<?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($sec->students && $sec->students->count() > 0): ?>
                                    <details style="cursor:pointer">
                                        <summary style="font-size:12px;color:#10b981;font-weight:700">
                                            👁️ View Enrolled Students (<?php echo e($sec->students->count()); ?>)
                                        </summary>
                                        <div style="margin-top:8px;padding:8px;background:#070b14;border-radius:8px;border:1px solid rgba(16,185,129,0.2)">
                                            <?php $__currentLoopData = $sec->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 8px;border-bottom:1px solid rgba(255,255,255,0.05);font-size:12px">
                                                    <div>
                                                        <strong style="color:#fff"><?php echo e($st->full_name); ?></strong> 
                                                        <span style="color:#94a3b8;font-family:monospace">(<?php echo e($st->roll_number); ?>)</span>
                                                    </div>
                                                    <a href="<?php echo e(route('principal.students.show', $st)); ?>" target="_blank" style="color:#38bdf8;text-decoration:none;font-weight:600">
                                                        Profile &rarr;
                                                    </a>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span style="font-size:12px;color:#64748b">No students registered in section</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;display:flex;justify-content:flex-end;gap:8px">
                                <button type="button" 
                                        class="btn btn-ghost btn-sm"
                                        onclick="openEditSectionModal(<?php echo e($sec->id); ?>, '<?php echo e(addslashes($sec->section_name)); ?>', <?php echo e($capacity); ?>, '<?php echo e(route('principal.sections.update', $sec)); ?>')"
                                        style="font-size:12px;color:#38bdf8;border:1px solid rgba(56,189,248,0.3)">
                                    ✏️ Edit Section
                                </button>
                                <button type="button" 
                                        class="btn btn-danger btn-sm"
                                        onclick="triggerDeleteModal('section', '<?php echo e(route('principal.sections.destroy', $sec)); ?>', '<?php echo e(addslashes($sec->section_name)); ?>')">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color:var(--text-muted);font-size:13px;margin-bottom:24px;font-style:italic">No sections provisioned yet. Click "Add Section" above.</p>
                <?php endif; ?>

                <!-- 2. SUBJECTS QUICK LINK BAR -->
                <div style="margin-top:20px;padding:14px 18px;background:rgba(139,92,246,0.08);border-radius:12px;border:1px solid rgba(139,92,246,0.25);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-weight:700;font-size:14px;color:#c084fc">📖 <?php echo e($class->subjects->count()); ?> Subject(s) Enabled for <?php echo e($class->custom_name); ?></div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px">
                            <?php if($class->subjects->count() > 0): ?>
                                Enabled: <?php echo e($class->subjects->pluck('subject_name')->take(5)->implode(', ')); ?><?php echo e($class->subjects->count() > 5 ? '...' : ''); ?>

                            <?php else: ?>
                                No subjects enabled yet for this class.
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="openAddSubjectModal(<?php echo e($class->id); ?>, '<?php echo e(addslashes($class->custom_name)); ?>')" style="font-size:12px;color:#c084fc">
                            ➕ Add Custom Subject
                        </button>
                        <a href="<?php echo e(route('principal.subjects.index')); ?>" class="btn btn-ghost btn-sm" style="font-size:12px;color:var(--accent2)">
                            Open Subject Catalog &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
    <div class="card" style="text-align:center;padding:48px 24px">
        <p style="color:var(--text-muted);font-size:16px;margin-bottom:16px">No classes added yet for this active academic session.</p>
        <div style="display:flex;justify-content:center;gap:12px">
            <button type="button" class="btn btn-primary" onclick="openAddClassModal()" style="background:linear-gradient(135deg, #8b5cf6, #6366f1);border:none">
                ➕ Provision New Class
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>



<!-- MODAL: Provision Class with Dynamic SaaS Education System Filter -->
<div class="modal-backdrop" id="addClassModal">
    <div class="modal-box" style="border-color:rgba(139,92,246,0.4)">
        <div class="modal-header">
            <div>
                <div class="modal-title" style="color:#c084fc">➕ Provision New Academic Class</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Select an enabled institute education system, pick or customize a class, and auto-allocate optimal facilities.
                </div>
            </div>
            <button type="button" onclick="closeAddClassModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('principal.classes.store')); ?>">
            <?php echo csrf_field(); ?>

            <!-- Step 1: Education System Selector Dropdown (Filtered by Global Admin Institute Config) -->
            <div class="form-group">
                <label for="add_education_type" style="color:#c084fc;font-weight:700">1. Select Education System *</label>
                <select id="add_education_type" onchange="filterAddSystemClasses(this.value)" style="border-color:rgba(139,92,246,0.3)">
                    <option value="">-- All Enabled Systems --</option>
                    <?php $__currentLoopData = $educationTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $typeLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($typeKey); ?>"><?php echo e($typeLabel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                    Shows education systems configured &amp; enabled for this institute by Global Admin.
                </div>
            </div>

            <!-- Step 2: Pre-defined Class Selector Dropdown -->
            <div class="form-group">
                <label for="add_system_class_id" style="color:#c084fc;font-weight:700">2. Select Class from Catalog (Optional)</label>
                <select id="add_system_class_id" name="system_class_id" onchange="onAddSystemClassSelected(this)" style="border-color:rgba(139,92,246,0.3)">
                    <option value="">-- Custom / Select Pre-defined Class --</option>
                    <?php $__currentLoopData = $systemClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sysClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $subCount = count($sysClass->default_subjects ?? []);
                        ?>
                        <option value="<?php echo e($sysClass->id); ?>" 
                                data-education="<?php echo e($sysClass->education_type); ?>" 
                                data-name="<?php echo e($sysClass->name); ?>"
                                data-subjects='<?php echo e(json_encode($sysClass->default_subjects ?? [])); ?>'>
                            <?php echo e($sysClass->name); ?> (<?php echo e($subCount); ?> Standard Subjects)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Step 3: Class Name / Alias Input -->
            <div class="form-group">
                <label for="custom_name" style="color:#38bdf8;font-weight:700">3. Class Display Name / Alias *</label>
                <input id="custom_name" type="text" name="custom_name" placeholder="e.g. Grade 9, Grade 10, FMMA, FBT" required value="<?php echo e(old('custom_name')); ?>" style="border-color:rgba(56,189,248,0.4)">
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                    You can rename or alias any class for this academic session.
                </div>
            </div>

            <!-- Step 4: Section Name & Capacity -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label for="section_name">Section Name *</label>
                    <input id="section_name" type="text" name="section_name" placeholder="e.g. A, Morning, Blue" required value="<?php echo e(old('section_name', 'A')); ?>">
                </div>

                <div class="form-group">
                    <label for="capacity">Student Capacity *</label>
                    <input id="capacity" type="number" name="capacity" class="section-capacity-input" placeholder="e.g. 40" min="1" max="1000" required value="<?php echo e(old('capacity', 40)); ?>">
                </div>
            </div>

            <!-- Automated Intelligent Room Allocation Badge -->
            <div style="padding:10px 14px;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.25);border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="font-size:16px">⚡</span>
                <div style="font-size:12px;color:#38bdf8;font-weight:600">
                    <strong>Intelligent Room Allocation:</strong> Room auto-assigned based on optimal seating capacity &amp; facility load balancing.
                </div>
            </div>

            <!-- Live Subjects Preview Box -->
            <div id="add_subjects_preview_box" style="display:none;padding:12px 14px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.25);border-radius:10px;margin-bottom:16px">
                <div style="font-size:11px;font-weight:800;color:#c084fc;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px">
                    ⚡ Standard Default Subjects Catalog to Enable:
                </div>
                <div id="add_subjects_preview_pills" style="display:flex;flex-wrap:wrap;gap:6px"></div>
            </div>

            <div class="form-group">
                <label for="enrolled_students">Current Enrolled Students</label>
                <input id="enrolled_students" type="number" name="enrolled_students" placeholder="e.g. 0 or 35" min="0" value="<?php echo e(old('enrolled_students', 0)); ?>">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddClassModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #8b5cf6, #6366f1);border:none;box-shadow:0 4px 15px rgba(139,92,246,0.35)">
                    🚀 Provision Class
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit/Rename Class -->
<div class="modal-backdrop" id="editClassModal">
    <div class="modal-box" style="border-color:rgba(56,189,248,0.4)">
        <div class="modal-header">
            <div>
                <div class="modal-title" style="color:#38bdf8">✏️ Rename Class</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Update the display name or session alias for this class.
                </div>
            </div>
            <button type="button" onclick="closeEditClassModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form id="editClassForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="form-group">
                <label for="edit_custom_name" style="color:#38bdf8;font-weight:700">Class Name / Alias *</label>
                <input id="edit_custom_name" type="text" name="custom_name" required placeholder="e.g. Grade 9 - Science, FMMA, 10th">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeEditClassModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #0284c7, #0369a1);border:none">
                    Save New Class Name
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Section -->
<div class="modal-backdrop" id="editSectionModal">
    <div class="modal-box" style="border-color:rgba(56,189,248,0.4)">
        <div class="modal-header">
            <div>
                <div class="modal-title" style="color:#38bdf8">✏️ Edit Section Details</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Update section name or required seating capacity.
                </div>
            </div>
            <button type="button" onclick="closeEditSectionModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form id="editSectionForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="form-group">
                <label for="edit_section_name" style="color:#38bdf8;font-weight:700">Section Name *</label>
                <input id="edit_section_name" type="text" name="section_name" required placeholder="e.g. Section A, Blue, Morning">
            </div>

            <div class="form-group">
                <label for="edit_capacity" style="color:#38bdf8;font-weight:700">Expected Student Capacity *</label>
                <input id="edit_capacity" type="number" name="capacity" min="1" max="1000" required>
            </div>

            <!-- Automated Intelligent Room Allocation Badge -->
            <div style="padding:10px 14px;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.25);border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="font-size:16px">⚡</span>
                <div style="font-size:12px;color:#38bdf8;font-weight:600">
                    <strong>Facility Auto-Balancing:</strong> Room allocation updates automatically if capacity requirement increases.
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeEditSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #0284c7, #0369a1);border:none">
                    Save Section Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add Additional Section -->
<div class="modal-backdrop" id="addSectionModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="addSectionModalTitle">➕ Add Section</div>
            <button type="button" onclick="closeAddSectionModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('principal.sections.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="institute_class_id" id="sec_class_id">

            <div class="form-group">
                <label for="sec_section_name">Section Name *</label>
                <input id="sec_section_name" type="text" name="section_name" placeholder="e.g. Section B, Green" required>
            </div>

            <div class="form-group">
                <label for="sec_capacity">Expected Student Capacity *</label>
                <input id="sec_capacity" type="number" name="capacity" class="section-capacity-input" placeholder="e.g. 40" min="1" max="1000" value="40" required>
            </div>

            <!-- Automated Intelligent Room Allocation Badge -->
            <div style="padding:10px 14px;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.25);border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="font-size:16px">⚡</span>
                <div style="font-size:12px;color:#38bdf8;font-weight:600">
                    <strong>Intelligent Room Allocation:</strong> Room auto-assigned based on optimal seating capacity &amp; facility load balancing.
                </div>
            </div>

            <div class="form-group">
                <label for="sec_enrolled">Current Enrolled Students</label>
                <input id="sec_enrolled" type="number" name="enrolled_students" value="0" min="0" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Section</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add Subject -->
<div class="modal-backdrop" id="addSubjectModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="addSubjectModalTitle">➕ Add Subject</div>
            <button type="button" onclick="closeAddSubjectModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('principal.subjects.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="institute_class_id" id="sub_class_id">

            <div class="form-group">
                <label for="sub_subject_name">Subject Name *</label>
                <input id="sub_subject_name" type="text" name="subject_name" placeholder="e.g. Physics, Mathematics" required>
            </div>

            <div class="form-group">
                <label for="sub_subject_code">Subject Code (Optional)</label>
                <input id="sub_subject_code" type="text" name="subject_code" placeholder="e.g. PHY-101">
            </div>

            <div class="form-group">
                <label for="sub_credit_hours">Credit Hours *</label>
                <input id="sub_credit_hours" type="number" name="credit_hours" value="3" min="1" max="10" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeAddSubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal Popup -->
<div class="modal-backdrop" id="deleteConfirmationModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">⚠️ Confirm Deletion</div>
            <button type="button" onclick="closeDeleteModal()" style="background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer">&times;</button>
        </div>
        <div class="modal-body" id="modalMessage">
            Are you sure you want to delete this item? This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            
            <form id="deleteModalForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    function filterPromoteSourceClasses(sessionId) {
        const sourceSelect = document.getElementById('promote_source_class_id');
        if (!sourceSelect) return;
        const options = sourceSelect.options;

        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            const termId = opt.getAttribute('data-term');
            if (!termId) {
                opt.style.display = 'block';
                continue;
            }
            if (!sessionId || termId === sessionId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }
        sourceSelect.value = '';
    }

    function filterPromoteTargetClasses(sessionId) {
        const targetSelect = document.getElementById('promote_target_class_id');
        if (!targetSelect) return;
        const options = targetSelect.options;

        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            const termId = opt.getAttribute('data-term');
            if (!termId) {
                opt.style.display = 'block';
                continue;
            }
            if (!sessionId || termId === sessionId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }
        targetSelect.value = '';
    }

    function openPromoteClassModal(sourceClassId = null) {
        const modal = document.getElementById('promoteClassModal');
        const sourceSessionSelect = document.getElementById('promote_source_session_id');

        if (sourceSessionSelect && sourceSessionSelect.value) {
            filterPromoteSourceClasses(sourceSessionSelect.value);
        }

        if (sourceClassId) {
            const sourceClassSelect = document.getElementById('promote_source_class_id');
            if (sourceClassSelect) sourceClassSelect.value = sourceClassId;
        }
        modal.classList.add('active');
    }
    function closePromoteClassModal() {
        document.getElementById('promoteClassModal').classList.remove('active');
    }

    function openEditClassModal(classId, className) {
        const form = document.getElementById('editClassForm');
        const input = document.getElementById('edit_custom_name');
        form.action = '/principal/classes/' + classId;
        input.value = className;
        document.getElementById('editClassModal').classList.add('active');
    }
    function closeEditClassModal() {
        document.getElementById('editClassModal').classList.remove('active');
    }

    function openEditSectionModal(sectionId, sectionName, capacity, targetUrl) {
        const form = document.getElementById('editSectionForm');
        const nameInput = document.getElementById('edit_section_name');
        const capInput = document.getElementById('edit_capacity');
        form.action = targetUrl;
        nameInput.value = sectionName;
        capInput.value = capacity;
        document.getElementById('editSectionModal').classList.add('active');
    }
    function closeEditSectionModal() {
        document.getElementById('editSectionModal').classList.remove('active');
    }

    function filterAddSystemClasses(educationType) {
        const classSelect = document.getElementById('add_system_class_id');
        const options = classSelect.options;

        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            const optEdu = opt.getAttribute('data-education');

            if (!optEdu) {
                opt.style.display = 'block';
                continue;
            }

            if (!educationType || optEdu === educationType || (educationType === 'acca' && optEdu === 'professional')) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        }

        classSelect.value = '';
        document.getElementById('add_subjects_preview_box').style.display = 'none';
    }

    function onAddSystemClassSelected(selectElem) {
        const opt = selectElem.options[selectElem.selectedIndex];
        if (!opt || !opt.value) return;

        const defaultName = opt.getAttribute('data-name');
        const customInput = document.getElementById('custom_name');
        if (defaultName && customInput) {
            customInput.value = defaultName;
        }

        const subjectsJson = opt.getAttribute('data-subjects');
        const previewBox = document.getElementById('add_subjects_preview_box');
        const previewPills = document.getElementById('add_subjects_preview_pills');

        if (subjectsJson && previewBox && previewPills) {
            try {
                const subjects = JSON.parse(subjectsJson);
                if (Array.isArray(subjects) && subjects.length > 0) {
                    previewPills.innerHTML = subjects.map(s => `
                        <span style="font-size:11px;padding:3px 8px;border-radius:12px;background:rgba(139,92,246,0.2);color:#c084fc;border:1px solid rgba(139,92,246,0.3);font-weight:600">
                            📘 ${s.name} (${s.code || 'N/A'})
                        </span>
                    `).join('');
                    previewBox.style.display = 'block';
                } else {
                    previewBox.style.display = 'none';
                }
            } catch(e) {
                previewBox.style.display = 'none';
            }
        }
    }

    function toggleClassCard(classId) {
        const card = document.getElementById('class-card-' + classId);
        const chevron = document.getElementById('chevron-' + classId);
        if (card.classList.contains('open')) {
            card.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            card.classList.add('open');
            chevron.style.transform = 'rotate(90deg)';
        }
    }

    // Modal Control Functions
    function openAddClassModal() {
        document.getElementById('addClassModal').classList.add('active');
    }
    function closeAddClassModal() {
        document.getElementById('addClassModal').classList.remove('active');
    }

    function openAddSectionModal(classId, className) {
        document.getElementById('sec_class_id').value = classId;
        document.getElementById('addSectionModalTitle').innerText = '➕ Add Section to ' + className;
        document.getElementById('addSectionModal').classList.add('active');
    }
    function closeAddSectionModal() {
        document.getElementById('addSectionModal').classList.remove('active');
    }

    function openAddSubjectModal(classId, className) {
        document.getElementById('sub_class_id').value = classId;
        document.getElementById('addSubjectModalTitle').innerText = '➕ Add Subject to ' + className;
        document.getElementById('addSubjectModal').classList.add('active');
    }
    function closeAddSubjectModal() {
        document.getElementById('addSubjectModal').classList.remove('active');
    }

    function triggerDeleteModal(type, targetUrl, itemName) {
        const modal = document.getElementById('deleteConfirmationModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalForm = document.getElementById('deleteModalForm');

        modalForm.action = targetUrl;

        if (type === 'subject') {
            modalTitle.innerText = '🗑️ Delete Subject';
            modalMessage.innerHTML = 'Are you sure you want to delete subject <strong style="color:#fff">' + itemName + '</strong>?<br>This will remove it from class subjects.';
        } else if (type === 'section') {
            modalTitle.innerText = '🗑️ Delete Section';
            modalMessage.innerHTML = 'Are you sure you want to delete section <strong style="color:#fff">' + itemName + '</strong>?<br>This will remove it from class sections.';
        } else if (type === 'class') {
            modalTitle.innerText = '🗑️ Delete Class';
            modalMessage.innerHTML = 'Are you sure you want to delete class <strong style="color:#fff">' + itemName + '</strong>?<br>This will also remove all associated subjects and sections.';
        }

        modal.classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmationModal').classList.remove('active');
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePromoteClassModal();
            closeEditClassModal();
            closeEditSectionModal();
            closeAddClassModal();
            closeAddSectionModal();
            closeAddSubjectModal();
            closeDeleteModal();
        }
    });

    // === FAILED STUDENT ACTION HIGHLIGHT ===
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
                label.style.background = 'rgba(15,23,42,0.5)';
                label.style.borderColor = 'rgba(255,255,255,0.08)';
            }
        });
    }

    // === AJAX PREVIEW: Load Failed Students Before Confirmation ===
    function previewFailedStudents() {
        const sourceClassId = document.getElementById('promote_source_class_id')?.value;
        if (!sourceClassId) {
            alert('Please select a Source Class first to preview failed students.');
            return;
        }

        const panel = document.getElementById('failedStudentsPreviewPanel');
        const content = document.getElementById('failedStudentsPreviewContent');
        panel.style.display = 'block';
        content.innerHTML = '<div style="color:#94a3b8">⏳ Loading failed students preview...</div>';

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
            if (data.failed_count === 0) {
                content.innerHTML = `
                    <div style="color:#34d399;font-weight:700">✅ All ${data.total_students} students in ${data.class_name} have PASSED.</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px">No students will be retained or flagged during promotion.</div>
                `;
                return;
            }

            let html = `
                <div style="margin-bottom:8px;color:#f87171;font-weight:700">
                    ⚠️ ${data.failed_count} out of ${data.total_students} students marked as FAILED in ${data.class_name}:
                </div>
            `;

            data.failed_students.forEach(s => {
                let subjList = '';
                if (s.failed_subjects && s.failed_subjects.length > 0) {
                    subjList = s.failed_subjects.map(fs =>
                        `<span style="display:inline-block;padding:1px 6px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:4px;font-size:10px;color:#f87171;margin-right:3px">
                            ❌ ${fs.subject_name} (${fs.marks_obtained}/${fs.total_marks})
                        </span>`
                    ).join('');
                } else {
                    subjList = '<span style="font-size:10px;color:#94a3b8">Overall status: Failed</span>';
                }

                html += `
                    <div style="padding:6px 8px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.12);border-radius:6px;margin-bottom:6px">
                        <div style="font-weight:700;color:#fff;font-size:12px">
                            ${s.name} <span style="color:#94a3b8;font-weight:400">(${s.roll_number} — Sec ${s.section})</span>
                        </div>
                        <div style="margin-top:3px;display:flex;flex-wrap:wrap;gap:3px">
                            ${subjList}
                        </div>
                    </div>
                `;
            });

            content.innerHTML = html;
        })
        .catch(err => {
            content.innerHTML = '<div style="color:#f87171">❌ Error loading preview: ' + err.message + '</div>';
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\classes-subjects\index.blade.php ENDPATH**/ ?>
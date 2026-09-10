<?php $__env->startSection('title', 'Subject Catalog & Curriculum'); ?>
<?php $__env->startSection('breadcrumb', 'Subject Catalog'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .subject-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .class-subject-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 16px;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 4px 16px -2px rgba(15, 23, 42, 0.03);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .class-subject-card.open {
        border-color: #c7d2fe;
        box-shadow: 0 4px 20px -2px rgba(79, 70, 229, 0.08);
    }
    .class-subject-header {
        padding: 16px 22px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease;
    }
    .class-subject-header:hover {
        background: #f8fafc;
    }
    .class-subject-body {
        display: none;
        padding: 0;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .class-subject-card.open .class-subject-body {
        display: block;
    }
    .chevron-icon {
        font-size: 12px;
        color: #4f46e5;
        transition: transform 0.2s ease;
        display: inline-block;
        width: 16px;
        text-align: center;
    }
    .subject-table {
        width: 100%;
        border-collapse: collapse;
    }
    .subject-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }
    .subject-table td {
        padding: 14px 16px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
        background: #ffffff;
    }
    .subject-table tr:hover td {
        background: #f8fafc;
    }
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(8px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.active { display: flex; }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        width: 100%;
        max-width: 620px;
        padding: 26px;
        box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.2);
        color: #0f172a;
    }
</style>

<div class="subject-header-actions">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            📚 Subject Catalog &amp; Syllabus
        </h1>
        <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500">
            Manage course curriculum, lecture durations, passing criteria, and evaluation weightages by class.
        </div>
    </div>

    <?php if($classes->count() > 0): ?>
        <div>
            <button type="button" class="btn btn-primary" onclick="openAddSubjectModal()">
                ➕ Add New Subject
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if(session('success')): ?>
    <div style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;color:#059669;font-size:13px;font-weight:600">
        ✅ <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#dc2626;font-size:13px;font-weight:600">
        ⚠️ <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<!-- CLASS ACCORDION SUBJECT CATALOG -->
<div>
    <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="class-subject-card" id="class-subject-card-<?php echo e($cls->id); ?>">
            <!-- Class Card Header (Click to Expand) -->
            <div class="class-subject-header" onclick="toggleClassSubjectCard(<?php echo e($cls->id); ?>)">
                <div style="display:flex;align-items:center;gap:14px">
                    <span id="chevron-<?php echo e($cls->id); ?>" class="chevron-icon">▶</span>
                    <div>
                        <div style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a">
                            🏫 <?php echo e($cls->custom_name); ?>

                        </div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                            <?php echo e($cls->subjects->count()); ?> Subject(s) Provisioned • Click to view details
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px" onclick="event.stopPropagation()">
                    <span class="badge badge-purple" style="font-weight:700">
                        📖 <?php echo e($cls->subjects->count()); ?> Subject(s)
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddSubjectModal(<?php echo e($cls->id); ?>, '<?php echo e(addslashes($cls->custom_name)); ?>')" style="font-size:12px;padding:6px 12px">
                        ➕ Register Subject
                    </button>
                </div>
            </div>

            <!-- Class Card Body (Dropdown Subjects Details) -->
            <div class="class-subject-body">
                <div style="overflow-x:auto">
                    <table class="subject-table">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th>Subject Code</th>
                                <th>Lecture Duration</th>
                                <th>Total &amp; Passing Marks</th>
                                <th>Evaluation Weightage Criteria</th>
                                <th>Credit Hours</th>
                                <th style="text-align:right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_2 = true; $__currentLoopData = $cls->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <tr>
                                    <td style="font-weight:800;color:#0f172a;font-size:14px">
                                        <?php echo e($sub->subject_name); ?>

                                        <?php if($sub->room): ?>
                                            <div style="font-size:11px;color:#059669;margin-top:2px;font-weight:600">
                                                📍 <?php echo e($sub->room->room_number); ?> (Max: <?php echo e($sub->room->capacity); ?> seats)
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code style="color:#7c3aed;background:#f5f3ff;border:1px solid #ddd6fe;padding:4px 8px;border-radius:6px;font-family:monospace;font-size:12px;font-weight:700">
                                            <?php echo e($sub->subject_code ?? 'N/A'); ?>

                                        </code>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:#d97706;font-size:13px">
                                            ⏱️ <?php echo e($sub->formatted_lecture_duration); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight:800;color:#0284c7;font-size:13px">
                                            🎯 <?php echo e($sub->total_marks ?? 100); ?> Total Marks
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;font-weight:500">
                                            Pass: <span style="color:#059669;font-weight:700"><?php echo e($sub->passing_marks ?? 33); ?> Marks</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                                            <span class="badge badge-purple" style="font-size:10px" title="MCQs Criteria Weightage">
                                                📝 MCQs: <?php echo e($sub->mcq_weightage ?? 20); ?>%
                                            </span>
                                            <span class="badge badge-blue" style="font-size:10px" title="Short Answers Criteria Weightage">
                                                ✍️ Short: <?php echo e($sub->short_answer_weightage ?? 30); ?>%
                                            </span>
                                            <span class="badge badge-yellow" style="font-size:10px" title="Long Answers Criteria Weightage">
                                                📄 Long: <?php echo e($sub->long_answer_weightage ?? 30); ?>%
                                            </span>
                                            <span class="badge badge-green" style="font-size:10px" title="Assignments & Quizzes Weightage">
                                                📊 Quiz/Assign: <?php echo e($sub->assignment_quiz_weightage ?? 20); ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td style="color:#334155;font-weight:700">
                                        <?php echo e($sub->credit_hours); ?> Hours
                                    </td>
                                    <td style="text-align:right">
                                        <?php
                                            $updateRoute = request()->routeIs('teacher.*') 
                                                ? route('teacher.subjects.update', $sub) 
                                                : route('principal.subjects.update', $sub);
                                            $destroyRoute = request()->routeIs('teacher.*') 
                                                ? route('teacher.subjects.destroy', $sub) 
                                                : route('principal.subjects.destroy', $sub);
                                        ?>
                                        <button type="button" class="btn btn-ghost btn-sm" style="padding:6px 12px;font-size:12px;margin-right:4px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff" onclick='openEditSubjectModal(<?php echo json_encode($sub, 15, 512) ?>, "<?php echo e($updateRoute); ?>")'>
                                            ✏️ Edit
                                        </button>
                                        <form action="<?php echo e($destroyRoute); ?>" method="POST" onsubmit="return confirm('Delete subject <?php echo e(addslashes($sub->subject_name)); ?> from <?php echo e(addslashes($cls->custom_name)); ?>?')" style="display:inline-block">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding:6px 12px;font-size:12px">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:32px;color:#64748b">
                                        <div style="font-size:24px;margin-bottom:6px">📖</div>
                                        <div style="font-size:14px;font-weight:800;color:#0f172a">No Subjects Registered for <?php echo e($cls->custom_name); ?></div>
                                        <div style="font-size:12px;margin-top:2px;margin-bottom:12px;color:#64748b">Click below to add subjects and assign specialized laboratories or classrooms.</div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddSubjectModal(<?php echo e($cls->id); ?>, '<?php echo e(addslashes($cls->custom_name)); ?>')">
                                            ➕ Register First Subject for <?php echo e($cls->custom_name); ?>

                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card" style="text-align:center;padding:48px;color:#64748b">
            <div style="font-size:36px;margin-bottom:12px">🏫</div>
            <div style="font-size:18px;font-weight:800;color:#0f172a">No Classes Provisioned</div>
            <div style="font-size:13px;margin-top:4px;margin-bottom:20px;color:#64748b">Please create classes first in the Classes &amp; Sections module before registering subjects.</div>
            <?php
                $classesRoute = request()->routeIs('teacher.*') 
                    ? route('teacher.classes-subjects.index') 
                    : route('principal.classes-subjects.index');
            ?>
            <a href="<?php echo e($classesRoute); ?>" class="btn btn-primary">
                ➕ Provision Classes &amp; Sections
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Register Subject for Selected Class -->
<div class="modal-backdrop" id="addSubjectModal">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
            <h3 id="addSubjectModalTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                ➕ Register New Subject &amp; Evaluation Criteria
            </h3>
            <button type="button" onclick="closeAddSubjectModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <?php
            $storeRoute = request()->routeIs('teacher.*') 
                ? route('teacher.subjects.store') 
                : route('principal.subjects.store');
        ?>
        <form method="POST" action="<?php echo e($storeRoute); ?>">
            <?php echo csrf_field(); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Target Class *</label>
                    <select id="modalClassSelect" name="institute_class_id" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                        <option value="">-- Select Class --</option>
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cls->id); ?>"><?php echo e($cls->custom_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Subject Code (Optional)</label>
                    <input type="text" name="subject_code" placeholder="e.g. PHY-101, CS-201, ENG-9" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                </div>
            </div>

            <div class="form-group mb-3">
                <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Subject Name *</label>
                <input type="text" name="subject_name" required placeholder="e.g. Physics, Computer Science Lab, Financial Accounting" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
            </div>

            <!-- Subject Total Marks, Passing Marks & Lecture Duration -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px" class="mb-3">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase">Duration (Mins) *</label>
                    <input type="number" name="lecture_duration_minutes" value="60" min="15" max="480" step="15" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #fde68a;border-radius:10px;color:#d97706;font-weight:800;outline:none" placeholder="60">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase">Total Marks *</label>
                    <input type="number" name="total_marks" value="100" min="1" max="1000" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #bae6fd;border-radius:10px;color:#0284c7;font-weight:800;outline:none">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase">Passing Marks *</label>
                    <input type="number" name="passing_marks" value="33" min="1" max="1000" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #a7f3d0;border-radius:10px;color:#059669;font-weight:800;outline:none">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Credit Hours *</label>
                    <input type="number" name="credit_hours" value="3" min="1" max="10" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                </div>
            </div>

            <!-- Evaluation Criteria Breakdown -->
            <div style="padding:16px;background:#fdf4ff;border:1px solid #f5d0fe;border-radius:14px;margin-bottom:16px">
                <div style="font-size:12px;font-weight:800;color:#9333ea;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <span>📊 Evaluation Criteria &amp; Assessment Weightage Breakdown (%):</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px">
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">MCQs (%)</label>
                        <input type="number" step="0.01" name="mcq_weightage" value="20" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Short Answers (%)</label>
                        <input type="number" step="0.01" name="short_answer_weightage" value="30" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Long Answers (%)</label>
                        <input type="number" step="0.01" name="long_answer_weightage" value="30" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Quiz &amp; Assign (%)</label>
                        <input type="number" step="0.01" name="assignment_quiz_weightage" value="20" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Specialized Classroom / Lab Allocation</label>
                <select name="room_id" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                    <option value="">🏫 Default Class Homeroom</option>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($rm->id); ?>">📍 <?php echo e($rm->room_number); ?> (Max Capacity: <?php echo e($rm->capacity); ?> Seats)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeAddSubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject &amp; Criteria</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Subject & Lecture Duration -->
<div class="modal-backdrop" id="editSubjectModal">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #e2e8f0">
            <h3 id="editSubjectModalTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                ✏️ Edit Subject &amp; Lecture Duration
            </h3>
            <button type="button" onclick="closeEditSubjectModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="editSubjectForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Subject Name *</label>
                    <input type="text" id="edit_subject_name" name="subject_name" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                </div>

                <div class="form-group mb-3">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Subject Code (Optional)</label>
                    <input type="text" id="edit_subject_code" name="subject_code" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                </div>
            </div>

            <!-- Subject Total Marks, Passing Marks & Lecture Duration -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px" class="mb-3">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase">Duration (Mins) *</label>
                    <input type="number" id="edit_lecture_duration_minutes" name="lecture_duration_minutes" value="60" min="15" max="480" step="15" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #fde68a;border-radius:10px;color:#d97706;font-weight:800;outline:none">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase">Total Marks *</label>
                    <input type="number" id="edit_total_marks" name="total_marks" value="100" min="1" max="1000" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #bae6fd;border-radius:10px;color:#0284c7;font-weight:800;outline:none">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase">Passing Marks *</label>
                    <input type="number" id="edit_passing_marks" name="passing_marks" value="33" min="1" max="1000" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #a7f3d0;border-radius:10px;color:#059669;font-weight:800;outline:none">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Credit Hours *</label>
                    <input type="number" id="edit_credit_hours" name="credit_hours" value="3" min="1" max="10" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;outline:none">
                </div>
            </div>

            <!-- Evaluation Criteria Breakdown -->
            <div style="padding:16px;background:#fdf4ff;border:1px solid #f5d0fe;border-radius:14px;margin-bottom:16px">
                <div style="font-size:12px;font-weight:800;color:#9333ea;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <span>📊 Evaluation Criteria &amp; Assessment Weightage Breakdown (%):</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px">
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">MCQs (%)</label>
                        <input type="number" step="0.01" id="edit_mcq_weightage" name="mcq_weightage" value="20" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Short Answers (%)</label>
                        <input type="number" step="0.01" id="edit_short_answer_weightage" name="short_answer_weightage" value="30" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Long Answers (%)</label>
                        <input type="number" step="0.01" id="edit_long_answer_weightage" name="long_answer_weightage" value="30" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:700;color:#475569">Quiz &amp; Assign (%)</label>
                        <input type="number" step="0.01" id="edit_assignment_quiz_weightage" name="assignment_quiz_weightage" value="20" min="0" max="100" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;font-weight:700;outline:none">
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeEditSubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Subject &amp; Re-generate Timetable</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleClassSubjectCard(classId) {
        const card = document.getElementById('class-subject-card-' + classId);
        const chevron = document.getElementById('chevron-' + classId);
        if (card.classList.contains('open')) {
            card.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            card.classList.add('open');
            chevron.style.transform = 'rotate(90deg)';
        }
    }

    function openAddSubjectModal(classId = null, className = null) {
        const modalTitle = document.getElementById('addSubjectModalTitle');
        const classSelect = document.getElementById('modalClassSelect');
        
        if (classId) {
            classSelect.value = classId;
            modalTitle.innerText = '➕ Register New Subject for ' + className;
            // Also ensure the target class accordion is expanded
            const card = document.getElementById('class-subject-card-' + classId);
            const chevron = document.getElementById('chevron-' + classId);
            if (card && !card.classList.contains('open')) {
                card.classList.add('open');
                if (chevron) chevron.style.transform = 'rotate(90deg)';
            }
        } else {
            classSelect.value = '';
            modalTitle.innerText = '➕ Register New Subject';
        }
        
        document.getElementById('addSubjectModal').classList.add('active');
    }

    function closeAddSubjectModal() {
        document.getElementById('addSubjectModal').classList.remove('active');
    }

    function openEditSubjectModal(subject, actionUrl) {
        document.getElementById('editSubjectForm').action = actionUrl;
        document.getElementById('edit_subject_name').value = subject.subject_name || '';
        document.getElementById('edit_subject_code').value = subject.subject_code || '';
        document.getElementById('edit_lecture_duration_minutes').value = subject.lecture_duration_minutes || 60;
        document.getElementById('edit_total_marks').value = subject.total_marks || 100;
        document.getElementById('edit_passing_marks').value = subject.passing_marks || 33;
        document.getElementById('edit_credit_hours').value = subject.credit_hours || 3;
        document.getElementById('edit_mcq_weightage').value = subject.mcq_weightage || 20;
        document.getElementById('edit_short_answer_weightage').value = subject.short_answer_weightage || 30;
        document.getElementById('edit_long_answer_weightage').value = subject.long_answer_weightage || 30;
        document.getElementById('edit_assignment_quiz_weightage').value = subject.assignment_quiz_weightage || 20;
        
        document.getElementById('editSubjectModalTitle').innerText = '✏️ Edit ' + subject.subject_name;
        document.getElementById('editSubjectModal').classList.add('active');
    }

    function closeEditSubjectModal() {
        document.getElementById('editSubjectModal').classList.remove('active');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\subjects\index.blade.php ENDPATH**/ ?>
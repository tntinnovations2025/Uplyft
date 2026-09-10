
<?php $__env->startSection('title', 'Timetable & Master Matrix'); ?>
<?php $__env->startSection('breadcrumb', 'Timetable Matrix'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $todayName = strtolower(date('l'));
    $defaultDay = in_array($todayName, $days) ? $todayName : 'monday';
?>

<style>
    /* Modal Backdrop & Centered Pop-up */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        width: 100%;
        max-width: 540px;
        padding: 28px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.15);
        max-height: 90vh;
        overflow-y: auto;
        color: #0f172a;
    }

    /* Per-Section Day Selector Pills */
    .sec-day-pill {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .sec-day-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .sec-day-pill.active {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }

    /* Table Grid Styling */
    .timetable-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .timetable-grid th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 12px;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #f1f5f9;
        text-align: center;
    }
    .timetable-grid td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        vertical-align: top;
        background: #ffffff;
        min-height: 80px;
    }
    .timetable-grid td:last-child, .timetable-grid th:last-child {
        border-right: none;
    }
    .timetable-grid tr:last-child td {
        border-bottom: none;
    }

    /* Slot Card Styling */
    .slot-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 6px;
        position: relative;
        transition: all 0.2s ease;
    }
    .slot-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    .slot-card .subject {
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
    }
    .slot-card .teacher {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }
    .slot-card .room {
        font-size: 10px;
        font-weight: 700;
        color: #059669;
        margin-top: 2px;
    }
    .slot-card .delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 4px;
        width: 18px;
        height: 18px;
        font-size: 11px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slot-card .delete-btn:hover { background: #dc2626; color: #fff; }

    /* Accordion Header */
    .grade-accordion-header {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 20px;
        margin-top: 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }
    .grade-accordion-header:hover {
        background: #fdf2f8;
        border-color: #fbcfe8;
    }
    .grade-accordion-title {
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<?php
    $routePrefix = request()->routeIs('teacher.*') ? 'teacher.' : 'principal.';
?>

<!-- HEADER TITLE & SINGLE UNIFIED TOP MENU BAR -->
<div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">🗓️ Timetable &amp; Master Matrix</h1>
            <p style="color:#64748b;font-size:13.5px;margin-top:2px;font-weight:500">
                Conflict-free master schedule adhering to teacher availability, room capacity, and active academic term rules in <strong><?php echo e($activeTerm?->name); ?></strong>.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12.5px;color:#64748b;font-weight:700">
                Active View: <strong style="color:#4f46e5"><?php echo e($viewType === 'teacher' ? 'Teacher Schedules' : 'Class Schedules'); ?></strong>
            </span>
        </div>
    </div>

<!-- SINGLE UNIFIED TOP NAVIGATION & ACTION MENU -->
    <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <a href="<?php echo e(route($routePrefix . 'timetables.index', ['view_type' => 'class'])); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;<?php echo e($viewType === 'class' ? 'background:linear-gradient(135deg, #4f46e5, #4338ca);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,0.35)' : 'background:#f8fafc;color:#475569;border:1px solid #cbd5e1'); ?>">
                🏫 Class Schedules
            </a>
            <a href="<?php echo e(route($routePrefix . 'timetables.index', ['view_type' => 'teacher'])); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;<?php echo e($viewType === 'teacher' ? 'background:linear-gradient(135deg, #4f46e5, #4338ca);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,0.35)' : 'background:#f8fafc;color:#475569;border:1px solid #cbd5e1'); ?>">
                👨‍🏫 Teacher Schedules
            </a>
            <a href="<?php echo e(route($routePrefix . 'timetables.grid')); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                🏢 Campus Master Grid
            </a>
            <a href="<?php echo e(route($routePrefix . 'timetables.days-and-hours')); ?>" 
               class="btn" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                ⚙️ Bell Timings &amp; Days
            </a>
        </div>

            <a href="<?php echo e(route($routePrefix . 'timetables.export')); ?>" class="btn btn-ghost" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;border:1px solid #cbd5e1;background:#ffffff">
                📗 Download Excel (.xlsx)
            </a>
            <?php if(auth()->user()->hasPermission('timetables', 'edit')): ?>
                <button type="button" onclick="openAddSlotModal()" class="btn btn-primary" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700">
                    ➕ Add Class Slot
                </button>
                <form id="generateTimetableForm" method="POST" action="<?php echo e(route($routePrefix . 'timetables.generate')); ?>" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <button type="button" onclick="openClassyConfirmModal()" class="btn btn-primary" style="border-radius:10px;padding:9px 16px;font-size:12px;font-weight:700;background:linear-gradient(135deg, #10b981, #059669);border:none">
                        ⚡ Auto-Generate Timetable
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>



<?php if($viewType === 'teacher'): ?>
    <!-- TEACHER SELECTOR & TEACHER-WISE SCHEDULE VIEW -->
    <div class="card" style="margin-bottom:24px;padding:20px;background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div>
                <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
                    👨‍🏫 Timetable Teacher-Wise
                </h2>
                <p style="color:#64748b;font-size:12.5px;margin-top:2px;font-weight:500">
                    Click on any teacher's name card to display their weekly timetable matrix schedule.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <!-- Quick Search Input -->
                <div style="position:relative;min-width:200px">
                    <input type="text" 
                           id="teacherSearchInput" 
                           onkeyup="filterTeacherCards()" 
                           placeholder="🔍 Search teacher name..." 
                           style="width:100%;padding:8px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:12.5px;outline:none">
                </div>

                <!-- Teacher Select Dropdown -->
                <div style="min-width:220px">
                    <select onchange="window.location.href='<?php echo e(route($routePrefix . 'timetables.index')); ?>?view_type=teacher&teacher_id=' + this.value" 
                            style="width:100%;padding:8px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:12.5px;font-weight:600;outline:none">
                        <option value="">-- All Teachers (<?php echo e($teachers->count()); ?>) --</option>
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php echo e((string)$selectedTeacherId === (string)$t->id ? 'selected' : ''); ?>>
                                👨‍🏫 <?php echo e($t->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Expand / Collapse All Buttons -->
                <div style="display:flex;align-items:center;gap:8px">
                    <button type="button" onclick="expandAllTeachers()" class="btn btn-ghost btn-sm" style="font-size:11.5px;padding:6px 12px">
                        📂 Expand All
                    </button>
                    <button type="button" onclick="collapseAllTeachers()" class="btn btn-ghost btn-sm" style="font-size:11.5px;padding:6px 12px">
                        📁 Collapse All
                    </button>
                </div>
            </div>
        </div>

        <?php
            $displayTeachers = $selectedTeacherId ? $teachers->where('id', $selectedTeacherId) : $teachers;
            $allDaysList = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'];
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $displayTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $rawTSlots = $allSlots->filter(fn($s) => $s->teacher_id == $t->id);
                $mergedTSlots = \App\Models\Timetable::mergeContiguousSlots($rawTSlots);
                $isInitiallyExpanded = $selectedTeacherId == $t->id;
            ?>
            <div class="teacher-schedule-card" data-teacher-name="<?php echo e(strtolower($t->name)); ?>" style="margin-bottom:16px;background:rgba(30,41,59,0.7);border:1px solid rgba(0,206,209,0.25);border-radius:14px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.25);transition:all 0.2s">
                <!-- Teacher Name Clickable Header Bar -->
                <div onclick="toggleTeacherTimetable('<?php echo e($t->id); ?>')" 
                     style="background:linear-gradient(135deg, rgba(30,41,59,0.95), rgba(15,23,42,0.95));padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;cursor:pointer;user-select:none;border-bottom:<?php echo e($isInitiallyExpanded ? '1px solid rgba(255,255,255,0.08)' : 'none'); ?>"
                     onmouseover="this.style.background='linear-gradient(135deg, rgba(30,41,59,1), rgba(15,23,42,1))'"
                     onmouseout="this.style.background='linear-gradient(135deg, rgba(30,41,59,0.95), rgba(15,23,42,0.95))'">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg, #00ced1, #3b82f6);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:16px;box-shadow:0 4px 12px rgba(0,206,209,0.3)">
                            <?php echo e(strtoupper(substr($t->name, 0, 1))); ?>

                        </div>
                        <div>
                            <div style="font-weight:800;color:#fff;font-size:16px;font-family:'Space Grotesk',sans-serif;display:inline-flex;align-items:center;gap:8px">
                                <span>👨‍🏫 <?php echo e($t->name); ?></span>
                                <button type="button" 
                                        onclick="event.stopPropagation(); openTeacherModalFromId(<?php echo e($t->id); ?>)" 
                                        style="font-size:10px;background:rgba(0,206,209,0.15);color:#00ced1;padding:2px 7px;border-radius:12px;border:1px solid rgba(0,206,209,0.3);font-weight:700;cursor:pointer"
                                        title="Click to view full profile details">
                                    🔍 Details
                                </button>
                            </div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:2px"><?php echo e($t->email); ?> &bull; <?php echo e($mergedTSlots->count()); ?> Total Weekly Lecture(s)</div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge" style="background:rgba(0,206,209,0.15);color:#00ced1;border:1px solid rgba(0,206,209,0.3);padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700">
                            📚 <?php echo e($mergedTSlots->count()); ?> Weekly Block(s)
                        </span>

                        <span id="teacher-acc-icon-<?php echo e($t->id); ?>" style="font-size:12px;font-weight:800;color:<?php echo e($isInitiallyExpanded ? '#fff' : '#00ced1'); ?>;background:<?php echo e($isInitiallyExpanded ? 'rgba(0,206,209,0.25)' : 'rgba(0,206,209,0.1)'); ?>;padding:6px 14px;border-radius:8px;border:1px solid rgba(0,206,209,0.3);transition:all 0.2s">
                            <?php echo e($isInitiallyExpanded ? '▲ Collapse Schedule' : '▼ Click to View Schedule'); ?>

                        </span>
                    </div>
                </div>

                <!-- Teacher Timetable Table Container (Expanded on Click) -->
                <div id="teacher-timetable-body-<?php echo e($t->id); ?>" style="padding:18px;display:<?php echo e($isInitiallyExpanded ? 'block' : 'none'); ?>">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                        <div style="font-size:13px;font-weight:700;color:#00ced1">
                            📅 Weekly Schedule Table for <?php echo e($t->name); ?>

                        </div>
                        <!-- View Toggle Buttons (Grid vs List) -->
                        <div style="display:inline-flex;background:rgba(15,23,42,0.8);padding:3px;border-radius:8px;border:1px solid rgba(255,255,255,0.08)">
                            <button type="button" 
                                    id="view-toggle-grid-<?php echo e($t->id); ?>" 
                                    onclick="switchTeacherViewMode('<?php echo e($t->id); ?>', 'grid')" 
                                    style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:#00ced1;color:#0f172a;border:none">
                                🗓️ Weekly Matrix Grid
                            </button>
                            <button type="button" 
                                    id="view-toggle-list-<?php echo e($t->id); ?>" 
                                    onclick="switchTeacherViewMode('<?php echo e($t->id); ?>', 'list')" 
                                    style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:transparent;color:#94a3b8;border:none">
                                📋 List View
                            </button>
                        </div>
                    </div>

                    <?php if($mergedTSlots->isEmpty()): ?>
                        <div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px">
                            No scheduled lectures for <?php echo e($t->name); ?> in active term <?php echo e($activeTerm?->name); ?>.
                        </div>
                    <?php else: ?>
                        <!-- MODE 1: WEEKLY MATRIX GRID (DEFAULT - COMPACT 6 COLUMN LAYOUT) -->
                        <div id="teacher-view-grid-<?php echo e($t->id); ?>" style="overflow-x:auto">
                            <table class="horiz-matrix-table" style="width:100%;min-width:720px;border-collapse:collapse;border:1px solid rgba(255,255,255,0.08);border-radius:12px;overflow:hidden">
                                <thead>
                                    <tr style="background:rgba(15,23,42,0.9)">
                                        <?php $__currentLoopData = $allDaysList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dKey => $dName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $daySlotsCount = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); ?>
                                            <th style="width:16.66%;padding:10px 8px;font-size:12px;font-weight:700;color:#fff;border-bottom:1px solid rgba(255,255,255,0.1);border-right:1px solid rgba(255,255,255,0.06);text-align:center">
                                                <?php echo e($dName); ?>

                                                <?php if($daySlotsCount > 0): ?>
                                                    <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 6px;border-radius:4px;margin-left:4px"><?php echo e($daySlotsCount); ?></span>
                                                <?php endif; ?>
                                            </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php $__currentLoopData = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php 
                                                $daySlots = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->sortBy('start_time');
                                            ?>
                                            <td style="vertical-align:top;padding:8px;background:rgba(15,23,42,0.4);border-right:1px solid rgba(255,255,255,0.06);border-bottom:none">
                                                <?php $__empty_2 = true; $__currentLoopData = $daySlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <?php
                                                        $rNum = $slot->room?->room_number ?? '';
                                                        $cleanRoom = Str::startsWith(strtolower($rNum), 'room') ? $rNum : ($rNum ? 'Room ' . $rNum : 'Hall');
                                                    ?>
                                                    <div style="margin-bottom:8px;background:linear-gradient(135deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95));border:1px solid rgba(0,206,209,0.3);border-radius:10px;padding:10px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">
                                                        <div style="font-size:11px;font-weight:800;color:#38bdf8;margin-bottom:4px">
                                                            ⏰ <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                                        </div>
                                                        <div style="font-size:12.5px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.3;cursor:pointer"
                                                             onmouseover="this.style.color='#00ced1'"
                                                             onmouseout="this.style.color='#fff'"
                                                             onclick="showSubjectDetailsModal('<?php echo e(addslashes($slot->subject?->subject_name ?: 'Subject')); ?>', '<?php echo e(addslashes($slot->subject?->subject_code ?: '')); ?>', '<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: '')); ?>', '<?php echo e(addslashes($t->name)); ?>', '<?php echo e(addslashes($cleanRoom)); ?>')">
                                                            📘 <?php echo e($slot->subject?->subject_name ?: 'Subject'); ?>

                                                            <?php if($slot->subject?->subject_code): ?>
                                                                <span style="font-size:9.5px;color:#00ced1;background:rgba(0,206,209,0.15);padding:1px 5px;border-radius:4px;margin-left:2px"><?php echo e($slot->subject->subject_code); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#cbd5e1;margin-top:6px;padding-top:6px;border-top:1px solid rgba(255,255,255,0.06)">
                                                            <span style="font-weight:700;cursor:pointer;color:#38bdf8"
                                                                  onmouseover="this.style.textDecoration='underline'"
                                                                  onmouseout="this.style.textDecoration='none'"
                                                                  onclick="showSectionDetailsModal('<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: 'Class')); ?>', '<?php echo e(addslashes($slot->section?->section_name ?: 'A')); ?>', '<?php echo e($mergedTSlots->count()); ?>')">
                                                                🏫 <?php echo e($slot->section?->instituteClass?->custom_name ?: 'Class'); ?>–<?php echo e($slot->section?->section_name ?: 'A'); ?>

                                                            </span>
                                                            <span style="color:#2ed573;font-weight:700">🚪 <?php echo e($cleanRoom); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <div style="font-size:11px;color:#64748b;text-align:center;padding:16px 0;font-style:italic">— Off —</div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- MODE 2: STREAMLINED LIST VIEW (COMPACT SLOTS ALWAYS SORTED CHRONOLOGICALLY) -->
                        <div id="teacher-view-list-<?php echo e($t->id); ?>" style="display:none">
                            <!-- CLICKABLE DAY SELECTOR TABS -->
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:8px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.05)">
                                <span style="font-size:12px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                                <button type="button" 
                                        id="day-filter-btn-<?php echo e($t->id); ?>-all" 
                                        class="day-filter-btn-<?php echo e($t->id); ?>"
                                        onclick="filterDayLectures('all', '<?php echo e($t->id); ?>')" 
                                        style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none">
                                    🗓️ All Days
                                </button>
                                <?php $__currentLoopData = $allDaysList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dKey => $dName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $dayCount = $mergedTSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); ?>
                                    <button type="button" 
                                            id="day-filter-btn-<?php echo e($t->id); ?>-<?php echo e($dKey); ?>" 
                                            class="day-filter-btn-<?php echo e($t->id); ?>"
                                            onclick="filterDayLectures('<?php echo e($dKey); ?>', '<?php echo e($t->id); ?>')" 
                                            style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08)">
                                        <?php echo e($dName); ?> <?php if($dayCount > 0): ?> <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 5px;border-radius:4px;margin-left:2px"><?php echo e($dayCount); ?></span> <?php endif; ?>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div style="display:flex;flex-direction:column;gap:8px">
                                <?php $__currentLoopData = $mergedTSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $dKey = strtolower($slot->day_of_week);
                                        $rNum = $slot->room?->room_number ?? '';
                                        $cleanRoom = Str::startsWith(strtolower($rNum), 'room') ? $rNum : ($rNum ? 'Room ' . $rNum : 'Hall');
                                    ?>
                                    <div class="lecture-card-<?php echo e($t->id); ?>" data-day="<?php echo e($dKey); ?>" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.25);border-radius:10px;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                                        <div style="display:flex;align-items:center;gap:12px">
                                            <span style="font-size:11px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:3px 8px;border-radius:6px">
                                                🗓️ <?php echo e(ucfirst($slot->day_of_week)); ?>

                                            </span>
                                            <span style="font-size:13px;font-weight:800;color:#38bdf8">
                                                ⏰ <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                            </span>
                                            <span style="font-size:13px;font-weight:800;color:#fff;cursor:pointer"
                                                  onmouseover="this.style.color='#00ced1'"
                                                  onmouseout="this.style.color='#fff'"
                                                  onclick="showSubjectDetailsModal('<?php echo e(addslashes($slot->subject?->subject_name ?: 'Subject')); ?>', '<?php echo e(addslashes($slot->subject?->subject_code ?: '')); ?>', '<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: '')); ?>', '<?php echo e(addslashes($t->name)); ?>', '<?php echo e(addslashes($cleanRoom)); ?>')">
                                                📘 <?php echo e($slot->subject?->subject_name); ?> <?php if($slot->subject?->subject_code): ?><span style="font-size:10px;color:#00ced1">(<?php echo e($slot->subject->subject_code); ?>)</span><?php endif; ?>
                                            </span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:14px;font-size:12px;color:#cbd5e1">
                                            <span style="font-weight:700;cursor:pointer;color:#38bdf8"
                                                  onmouseover="this.style.textDecoration='underline'"
                                                  onmouseout="this.style.textDecoration='none'"
                                                  onclick="showSectionDetailsModal('<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: 'Class')); ?>', '<?php echo e(addslashes($slot->section?->section_name ?: 'A')); ?>', '<?php echo e($mergedTSlots->count()); ?>')">
                                                🏫 Class <?php echo e($slot->section?->instituteClass?->custom_name); ?> — Section <?php echo e($slot->section?->section_name); ?>

                                            </span>
                                            <span style="color:#2ed573;font-weight:700">🚪 <?php echo e($cleanRoom); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                No teachers found.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($viewType === 'time'): ?>
    <!-- TIME SLOT SELECTOR & TIME-WISE SCHEDULE VIEW WITH CLICKABLE DAYS & HORIZONTAL BARS -->
    <div class="card" style="margin-bottom:24px;padding:20px;background:rgba(15,23,42,0.85);border:1px solid rgba(0,206,209,0.25);border-radius:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:14px">
            <div>
                <h2 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px">
                    ⏰ View Timetable Time-Slot-Wise
                </h2>
                <p style="color:#94a3b8;font-size:12.5px;margin-top:2px">
                    Inspect scheduled lectures by specific time slots across all weekdays and classrooms.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;min-width:280px">
                <label style="font-size:12px;color:#94a3b8;font-weight:700;white-space:nowrap">Select Time Slot:</label>
                <select onchange="window.location.href='<?php echo e(route($routePrefix . 'timetables.index')); ?>?view_type=time&time_slot=' + this.value" 
                        style="width:100%;padding:9px 14px;background:#0f172a;border:1px solid rgba(0,206,209,0.4);border-radius:10px;color:#fff;font-size:13px;font-weight:600;outline:none">
                    <option value="">-- All Time Slots --</option>
                    <?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ts['key']); ?>" <?php echo e((string)$selectedTimeSlot === (string)$ts['key'] ? 'selected' : ''); ?>>
                            ⏰ <?php echo e($ts['start']); ?> - <?php echo e($ts['end']); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <?php
            $mergedTimeSlots = \App\Models\Timetable::mergeContiguousSlots($timeFilteredSlots);
            $allDaysList = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday'];
        ?>

        <?php if($mergedTimeSlots->isEmpty()): ?>
            <div style="text-align:center;padding:32px;color:#94a3b8;font-size:13px">
                No scheduled lectures match the selected time slot.
            </div>
        <?php else: ?>
            <!-- CLICKABLE DAY SELECTOR TABS -->
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:10px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.05)">
                <span style="font-size:12px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                <button type="button" 
                        id="day-filter-btn-timeslot-all" 
                        class="day-filter-btn-timeslot"
                        onclick="filterDayLectures('all', 'timeslot')" 
                        style="padding:6px 14px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none;transition:all 0.2s">
                    🗓️ All Days
                </button>
                <?php $__currentLoopData = $allDaysList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dKey => $dName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $dayCount = $mergedTimeSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); ?>
                    <button type="button" 
                            id="day-filter-btn-timeslot-<?php echo e($dKey); ?>" 
                            class="day-filter-btn-timeslot"
                            onclick="filterDayLectures('<?php echo e($dKey); ?>', 'timeslot')" 
                            style="padding:6px 14px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);transition:all 0.2s">
                        <?php echo e($dName); ?> <?php if($dayCount > 0): ?> <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 6px;border-radius:4px;margin-left:2px"><?php echo e($dayCount); ?></span> <?php endif; ?>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- HORIZONTAL LECTURE BARS LIST ALWAYS SORTED CHRONOLOGICALLY -->
            <div style="display:flex;flex-direction:column;gap:12px">
                <?php $__currentLoopData = $mergedTimeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dKey = strtolower($slot->day_of_week);
                        $startSec = strtotime($slot->start_time);
                        $endSec = strtotime($slot->end_time);
                        $diffMins = max(15, ($endSec - $startSec) / 60);
                        $hoursDecimal = round($diffMins / 60, 2);
                        $barWidthPercent = min(100, max(15, round(($diffMins / 180) * 100)));
                    ?>
                    <div class="lecture-card-timeslot" data-day="<?php echo e($dKey); ?>" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.3);border-radius:12px;padding:14px 18px;box-shadow:0 6px 18px rgba(0,0,0,0.3)">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <span style="font-size:11.5px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:4px 10px;border-radius:6px">
                                    🗓️ <?php echo e(ucfirst($slot->day_of_week)); ?>

                                </span>
                                <span style="font-size:13.5px;font-weight:800;color:#38bdf8">
                                    ⏰ <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:12px;font-weight:800;color:#2ed573;background:rgba(46,213,115,0.15);border:1px solid rgba(46,213,115,0.3);padding:4px 12px;border-radius:8px">
                                    ⏱️ Duration: <?php echo e($hoursDecimal); ?> <?php echo e(Str::plural('Hour', $hoursDecimal)); ?> (<?php echo e($diffMins); ?> mins)
                                </span>
                            </div>
                        </div>

                        <!-- Horizontal Duration Progress Bar -->
                        <div style="background:rgba(255,255,255,0.06);height:8px;border-radius:4px;overflow:hidden;margin-bottom:12px">
                            <div style="width:<?php echo e($barWidthPercent); ?>%;height:100%;background:linear-gradient(90deg, #00ced1, #6c63ff);border-radius:4px"></div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;align-items:center">
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Subject</span>
                                <span style="font-size:13.5px;font-weight:800;color:#fff;display:flex;align-items:center;gap:6px;margin-top:2px;cursor:pointer"
                                      onmouseover="this.style.color='#00ced1'"
                                      onmouseout="this.style.color='#fff'"
                                      onclick="showSubjectDetailsModal('<?php echo e(addslashes($slot->subject?->subject_name ?: 'Subject')); ?>', '<?php echo e(addslashes($slot->subject?->subject_code ?: '')); ?>', '<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: '')); ?>', '<?php echo e(addslashes($slot->teacher?->name ?: 'Unassigned')); ?>', '<?php echo e(addslashes($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall')); ?>')">
                                    📘 <?php echo e($slot->subject?->subject_name ?: 'Subject'); ?>

                                    <?php if($slot->subject?->subject_code): ?>
                                        <code style="font-size:10px;color:#38bdf8;background:rgba(56,189,248,0.12);padding:2px 6px;border-radius:4px"><?php echo e($slot->subject->subject_code); ?></code>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Class &amp; Section</span>
                                <span style="font-size:13px;font-weight:700;color:#cbd5e1;margin-top:2px;display:block;cursor:pointer"
                                      onmouseover="this.style.color='#38bdf8'"
                                      onmouseout="this.style.color='#cbd5e1'"
                                      onclick="showSectionDetailsModal('<?php echo e(addslashes($slot->section?->instituteClass?->custom_name ?: 'Class')); ?>', '<?php echo e(addslashes($slot->section?->section_name ?: 'A')); ?>', 1)">
                                    🏫 <?php echo e($slot->section?->instituteClass?->custom_name ?: 'Class'); ?> — Section <?php echo e($slot->section?->section_name ?: 'A'); ?>

                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Faculty Teacher</span>
                                <span style="font-size:13px;font-weight:700;color:#38bdf8;margin-top:2px;display:block;cursor:pointer"
                                      onmouseover="this.style.textDecoration='underline'"
                                      onmouseout="this.style.textDecoration='none'"
                                      onclick="openTeacherModalFromId(<?php echo e($slot->teacher_id ?: 0); ?>)">
                                    👨‍🏫 <?php echo e($slot->teacher?->name ?: 'Unassigned'); ?> 🔍
                                </span>
                            </div>
                            <div>
                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Room / Facility</span>
                                <span style="font-size:13px;font-weight:700;color:#2ed573;margin-top:2px;display:block">
                                    🚪 <?php echo e($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall'); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($viewType === 'class'): ?>
<!-- CONTROL BAR: SEARCH SECTION & EXPAND/COLLAPSE BUTTONS -->
<div class="card" style="margin-bottom:24px;padding:16px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:400px">
            <span style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap">🔍 Search Section:</span>
            <input type="text" id="sectionSearchInput" onkeyup="filterSections()" placeholder="Type section e.g. 9A, 9-A, Grade 10..." style="padding:9px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:#fff;font-size:13px;width:100%;outline:none">
        </div>

        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:320px">
            <span style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap">Filter Section:</span>
            <select onchange="window.location.href='<?php echo e(route($routePrefix . 'timetables.index')); ?>?view_type=class&section_id=' + this.value" 
                    style="width:100%;padding:9px 14px;background:#0f172a;border:1px solid rgba(0,206,209,0.4);border-radius:10px;color:#fff;font-size:13px;font-weight:600;outline:none">
                <option value="">-- All Sections --</option>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $cName = $sec->instituteClass?->custom_name ?: 'Grade 10';
                        $sName = $sec->section_name ?: 'A';
                        $sClean = trim(str_replace(['Sec', 'Section', 'sec', 'section'], '', $sName));
                        if (str_contains($sClean, '-')) {
                            $parts = explode('-', $sClean);
                            $sClean = trim(end($parts));
                        }
                        preg_match_all('/\d+/', $cName, $cMatches);
                        foreach ($cMatches[0] ?? [] as $num) {
                            if (str_starts_with($sClean, $num)) {
                                $sClean = trim(substr($sClean, strlen($num)));
                            }
                        }
                        $sClean = trim($sClean, ' -_');
                        $classSecLabel = !empty($sClean) ? ($cName . ' - ' . strtoupper($sClean)) : $cName;
                    ?>
                    <option value="<?php echo e($sec->id); ?>" <?php echo e((string)$selectedSectionId === (string)$sec->id ? 'selected' : ''); ?>>
                        🏫 <?php echo e($classSecLabel); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <button type="button" onclick="expandAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📂 Expand All Grades
            </button>
            <button type="button" onclick="collapseAllGrades()" class="btn btn-ghost btn-sm" style="font-size:12px">
                📁 Collapse All Grades
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SECTION 3: GRADE ACCORDIONS WITH PER-SECTION CLICKABLE DAY SELECTORS & HORIZONTAL DURATION BARS -->
<?php if($viewType === 'class'): ?>
<?php if($groupedSections->isEmpty()): ?>
<div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:var(--text-muted);font-size:15px">No classes or sections configured yet.</p>
</div>
<?php else: ?>
    <?php $__currentLoopData = $groupedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $classSections): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php 
        $classSlug = Str::slug($className); 
        $displaySections = $selectedSectionId ? $classSections->where('id', $selectedSectionId) : $classSections;
    ?>
    <?php if($displaySections->isNotEmpty()): ?>
    <div class="grade-wrapper-block grade-group-<?php echo e($classSlug); ?>" style="margin-bottom:20px">
        <div class="grade-accordion-header" onclick="toggleGradeAccordion('<?php echo e($classSlug); ?>')">
            <div class="grade-accordion-title">
                🎓 <?php echo e($className); ?>

                <span class="badge badge-purple" style="font-size:11px;padding:3px 10px"><?php echo e($displaySections->count()); ?> Section(s)</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <button type="button" onclick="event.stopPropagation(); openAddSlotModal()" class="btn btn-ghost btn-sm" style="font-size:11px">
                    ➕ Add Slot
                </button>
                <span class="grade-accordion-icon grade-icon-<?php echo e($classSlug); ?>" style="font-size:14px;color:var(--text-muted)">▼</span>
            </div>
        </div>

        <div id="grade-body-<?php echo e($classSlug); ?>" class="grade-body-container" style="display:none">
            <div class="card" style="padding:18px;background:rgba(15,23,42,0.85);border:1px solid rgba(0,206,209,0.25);border-radius:14px">
                <?php $__currentLoopData = $displaySections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $secSlots = $allSlots->filter(fn($s) => $s->class_section_id == $sec->id);
                        $mergedSecSlots = \App\Models\Timetable::mergeContiguousSlots($secSlots);

                        $sClean = trim(str_replace(['Sec', 'Section', 'sec', 'section'], '', $sec->section_name));
                        if (str_contains($sClean, '-')) {
                            $parts = explode('-', $sClean);
                            $sClean = trim(end($parts));
                        }
                        preg_match_all('/\d+/', $className, $cMatches);
                        foreach ($cMatches[0] ?? [] as $num) {
                            if (str_starts_with($sClean, $num)) {
                                $sClean = trim(substr($sClean, strlen($num)));
                            }
                        }
                        $sClean = trim($sClean, ' -_');
                        $classSecTitle = !empty($sClean) ? ($className . ' - ' . strtoupper($sClean)) : $className;
                    ?>
                    <div style="margin-bottom:20px;background:rgba(30,41,59,0.6);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,0.06);flex-wrap:wrap;gap:10px">
                            <div style="font-weight:800;color:#fff;font-size:15px;display:flex;align-items:center;gap:8px;cursor:pointer"
                                 onclick="showSectionDetailsModal('<?php echo e(addslashes($className)); ?>', '<?php echo e(addslashes($sec->section_name)); ?>', '<?php echo e($mergedSecSlots->count()); ?>')">
                                🏫 <?php echo e($classSecTitle); ?> <span style="font-size:11px;color:#00ced1;font-weight:600">(Click for details 🔍)</span>
                            </div>
                            <span style="font-size:12px;color:#00ced1;font-weight:700;background:rgba(0,206,209,0.12);border:1px solid rgba(0,206,209,0.25);padding:4px 10px;border-radius:6px">
                                📚 <?php echo e($mergedSecSlots->count()); ?> Weekly Lecture Block(s)
                            </span>
                        </div>

                        <!-- CLICKABLE DAY FILTER TABS FOR THIS SECTION -->
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;background:rgba(15,23,42,0.6);padding:8px 12px;border-radius:10px">
                            <span style="font-size:11.5px;font-weight:700;color:#cbd5e1;margin-right:4px">🗓️ Filter Day:</span>
                            <button type="button" 
                                    id="day-filter-btn-sec-<?php echo e($sec->id); ?>-all" 
                                    class="day-filter-btn-sec-<?php echo e($sec->id); ?>"
                                    onclick="filterDayLectures('all', 'sec-<?php echo e($sec->id); ?>')" 
                                    style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:linear-gradient(135deg, #00ced1, #3b82f6);color:#fff;box-shadow:0 4px 12px rgba(0,206,209,0.3);border:none;transition:all 0.2s">
                                🗓️ All Days
                            </button>
                            <?php $__currentLoopData = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dKey => $dName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $dayCount = $mergedSecSlots->filter(fn($s) => strtolower($s->day_of_week) === $dKey)->count(); ?>
                                <button type="button" 
                                        id="day-filter-btn-sec-<?php echo e($sec->id); ?>-<?php echo e($dKey); ?>" 
                                        class="day-filter-btn-sec-<?php echo e($sec->id); ?>"
                                        onclick="filterDayLectures('<?php echo e($dKey); ?>', 'sec-<?php echo e($sec->id); ?>')" 
                                        style="padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;background:rgba(30,41,59,0.8);color:#94a3b8;border:1px solid rgba(255,255,255,0.08);transition:all 0.2s">
                                    <?php echo e($dName); ?> <?php if($dayCount > 0): ?> <span style="font-size:10px;background:rgba(0,206,209,0.2);color:#00ced1;padding:1px 5px;border-radius:4px;margin-left:2px"><?php echo e($dayCount); ?></span> <?php endif; ?>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($mergedSecSlots->isEmpty()): ?>
                            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12.5px">
                                No scheduled lectures for Section <?php echo e($sec->section_name); ?>.
                            </div>
                        <?php else: ?>
                            <!-- HORIZONTAL LECTURE BARS LIST ALWAYS SORTED CHRONOLOGICALLY -->
                            <div style="display:flex;flex-direction:column;gap:10px">
                                <?php $__currentLoopData = $mergedSecSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $dKey = strtolower($slot->day_of_week);
                                        $startSec = strtotime($slot->start_time);
                                        $endSec = strtotime($slot->end_time);
                                        $diffMins = max(15, ($endSec - $startSec) / 60);
                                        $hoursDecimal = round($diffMins / 60, 2);
                                        $barWidthPercent = min(100, max(15, round(($diffMins / 180) * 100)));
                                    ?>
                                    <div class="lecture-card-sec-<?php echo e($sec->id); ?>" data-day="<?php echo e($dKey); ?>" style="background:rgba(15,23,42,0.9);border:1px solid rgba(0,206,209,0.3);border-radius:10px;padding:12px 16px;box-shadow:0 4px 14px rgba(0,0,0,0.25)">
                                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px">
                                            <div style="display:flex;align-items:center;gap:10px">
                                                <span style="font-size:11px;font-weight:800;color:#00ced1;background:rgba(0,206,209,0.15);padding:3px 8px;border-radius:6px">
                                                    🗓️ <?php echo e(ucfirst($slot->day_of_week)); ?>

                                                </span>
                                                <span style="font-size:13px;font-weight:800;color:#38bdf8">
                                                    ⏰ <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('g:i A')); ?> – <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('g:i A')); ?>

                                                </span>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px">
                                                <span style="font-size:11.5px;font-weight:800;color:#2ed573;background:rgba(46,213,115,0.15);border:1px solid rgba(46,213,115,0.3);padding:3px 10px;border-radius:6px">
                                                    ⏱️ Duration: <?php echo e($hoursDecimal); ?> <?php echo e(Str::plural('Hour', $hoursDecimal)); ?> (<?php echo e($diffMins); ?> mins)
                                                </span>
                                                <form method="POST" action="<?php echo e(route('principal.timetables.destroy', $slot)); ?>" onsubmit="return classyConfirmForm(this, 'Remove Slot?', 'Are you sure you want to remove this timetable slot?', {danger: true, icon: '🗑️', confirmText: 'Yes, Remove'})" style="margin:0">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="delete-btn" style="background:rgba(239,68,68,0.2);color:#ef4444;border:none;border-radius:6px;width:24px;height:24px;font-size:14px;cursor:pointer" title="Delete Slot">&times;</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Horizontal Duration Progress Bar -->
                                        <div style="background:rgba(255,255,255,0.06);height:6px;border-radius:3px;overflow:hidden;margin-bottom:10px">
                                            <div style="width:<?php echo e($barWidthPercent); ?>%;height:100%;background:linear-gradient(90deg, #00ced1, #6c63ff);border-radius:3px"></div>
                                        </div>

                                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:10px;align-items:center">
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Subject</span>
                                                <span style="font-size:13px;font-weight:800;color:#fff;display:flex;align-items:center;gap:6px;margin-top:2px;cursor:pointer"
                                                      onmouseover="this.style.color='#00ced1'"
                                                      onmouseout="this.style.color='#fff'"
                                                      onclick="showSubjectDetailsModal('<?php echo e(addslashes($slot->subject?->subject_name ?: 'Subject')); ?>', '<?php echo e(addslashes($slot->subject?->subject_code ?: '')); ?>', '<?php echo e(addslashes($className)); ?>', '<?php echo e(addslashes($slot->teacher?->name ?: 'Unassigned')); ?>', '<?php echo e(addslashes($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall')); ?>')">
                                                    📘 <?php echo e($slot->subject?->subject_name ?: 'Subject'); ?>

                                                    <?php if($slot->subject?->subject_code): ?>
                                                        <code style="font-size:10px;color:#38bdf8;background:rgba(56,189,248,0.12);padding:2px 5px;border-radius:4px"><?php echo e($slot->subject->subject_code); ?></code>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Faculty Teacher</span>
                                                <span style="font-size:12.5px;font-weight:700;color:#38bdf8;margin-top:2px;display:block;cursor:pointer"
                                                      onmouseover="this.style.textDecoration='underline'"
                                                      onmouseout="this.style.textDecoration='none'"
                                                      onclick="openTeacherModalFromId(<?php echo e($slot->teacher_id ?: 0); ?>)">
                                                    👨‍🏫 <?php echo e($slot->teacher?->name ?: 'Unassigned'); ?> 🔍
                                                </span>
                                            </div>
                                            <div>
                                                <span style="font-size:10px;color:#94a3b8;font-weight:800;text-transform:uppercase;display:block">Room / Facility</span>
                                                <span style="font-size:12.5px;font-weight:700;color:#2ed573;margin-top:2px;display:block">
                                                    🚪 <?php echo e($slot->room?->room_number ? 'Room ' . $slot->room->room_number : 'Assigned Hall'); ?>

                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php endif; ?>

<!-- CONVERSATIONAL TIMETABLE ADJUSTER -->
<?php echo $__env->make('principal.timetables._chat', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
let globalDay = '<?php echo e($defaultDay); ?>';
const expandedGrades = new Set();
const sectionDays = {};

function filterTeacherCards() {
    const input = document.getElementById('teacherSearchInput');
    if (!input) return;
    const q = input.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.teacher-schedule-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-teacher-name') || '';
        if (!q || name.includes(q)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function switchTeacherViewMode(teacherId, mode) {
    const gridDiv = document.getElementById('teacher-view-grid-' + teacherId);
    const listDiv = document.getElementById('teacher-view-list-' + teacherId);
    const gridBtn = document.getElementById('view-toggle-grid-' + teacherId);
    const listBtn = document.getElementById('view-toggle-list-' + teacherId);

    if (mode === 'grid') {
        if (gridDiv) gridDiv.style.display = 'block';
        if (listDiv) listDiv.style.display = 'none';
        if (gridBtn) {
            gridBtn.style.background = '#00ced1';
            gridBtn.style.color = '#0f172a';
        }
        if (listBtn) {
            listBtn.style.background = 'transparent';
            listBtn.style.color = '#94a3b8';
        }
    } else {
        if (gridDiv) gridDiv.style.display = 'none';
        if (listDiv) listDiv.style.display = 'block';
        if (listBtn) {
            listBtn.style.background = '#00ced1';
            listBtn.style.color = '#0f172a';
        }
        if (gridBtn) {
            gridBtn.style.background = 'transparent';
            gridBtn.style.color = '#94a3b8';
        }
    }
}

function toggleViewModeDropdown() {
    const dropdown = document.getElementById('viewModeDropdown');
    if (dropdown) {
        dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const container = document.getElementById('viewModeDropdownContainer');
    const dropdown = document.getElementById('viewModeDropdown');
    if (container && dropdown && !container.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

function filterDayLectures(day, scopeId = 'all') {
    document.querySelectorAll('.day-filter-btn-' + scopeId).forEach(btn => {
        btn.style.background = 'rgba(30,41,59,0.8)';
        btn.style.color = '#94a3b8';
        btn.style.border = '1px solid rgba(255,255,255,0.08)';
        btn.style.boxShadow = 'none';
    });
    const activeBtn = document.getElementById('day-filter-btn-' + scopeId + '-' + day);
    if (activeBtn) {
        activeBtn.style.background = 'linear-gradient(135deg, #00ced1, #3b82f6)';
        activeBtn.style.color = '#fff';
        activeBtn.style.boxShadow = '0 4px 12px rgba(0,206,209,0.3)';
        activeBtn.style.border = 'none';
    }

    document.querySelectorAll('.lecture-card-' + scopeId).forEach(card => {
        if (day === 'all' || card.getAttribute('data-day') === day) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function selectGlobalDay(day) {
    globalDay = day;
    document.querySelectorAll('.day-pill').forEach(el => el.classList.remove('active'));
    const targetPill = document.getElementById('day-pill-' + day);
    if (targetPill) targetPill.classList.add('active');

    // Update day for all section accordions simultaneously
    document.querySelectorAll('.sec-day-pill').forEach(btn => {
        if (btn.id.endsWith('-' + day)) {
            btn.click();
        }
    });
}

function selectSectionDay(classSlug, day) {
    sectionDays[classSlug] = day;

    // Update active pill for this section
    document.querySelectorAll('.sec-day-pill-' + classSlug).forEach(el => el.classList.remove('active'));
    const pill = document.getElementById('sec-day-pill-' + classSlug + '-' + day);
    if (pill) pill.classList.add('active');

    // Update day label text
    const label = document.getElementById('sec-day-label-' + classSlug);
    if (label) label.innerText = day;

    // Show matrix table for this section and day
    document.querySelectorAll('.sec-matrix-' + classSlug).forEach(el => el.style.display = 'none');
    const targetTable = document.getElementById('sec-matrix-' + classSlug + '-' + day);
    if (targetTable) targetTable.style.display = 'block';
}

function filterClass(slug) {
    document.querySelectorAll('.class-pill').forEach(el => el.classList.remove('active'));
    document.getElementById('class-pill-' + slug).classList.add('active');

    if (slug === 'all') {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'block');
    } else {
        document.querySelectorAll('.grade-wrapper-block').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.grade-group-' + slug).forEach(el => el.style.display = 'block');
    }
}

function toggleGradeAccordion(classSlug) {
    if (expandedGrades.has(classSlug)) {
        expandedGrades.delete(classSlug);
    } else {
        expandedGrades.add(classSlug);
    }
    applyGradeAccordionStates();
}

function applyGradeAccordionStates() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');

        if (classSlug) {
            const isExpanded = expandedGrades.has(classSlug);
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');

            if (bodyContainer) {
                bodyContainer.style.display = isExpanded ? 'block' : 'none';
            }
            if (icon) {
                icon.innerText = isExpanded ? '▲' : '▼';
            }
        }
    });
}

function expandAllGrades() {
    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        const classSlug = Array.from(wrapper.classList)
            .find(c => c.startsWith('grade-group-'))
            ?.replace('grade-group-', '');
        if (classSlug) expandedGrades.add(classSlug);
    });
    applyGradeAccordionStates();
}

function collapseAllGrades() {
    expandedGrades.clear();
    applyGradeAccordionStates();
}

function filterSections() {
    const rawQuery = document.getElementById('sectionSearchInput').value.trim().toLowerCase();
    const cleanQuery = rawQuery.replace(/[^a-z0-9]/g, '');

    if (!rawQuery) {
        document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
            wrapper.style.display = 'block';
        });
        document.querySelectorAll('.section-row').forEach(row => {
            row.style.display = '';
        });
        applyGradeAccordionStates();
        return;
    }

    document.querySelectorAll('.grade-wrapper-block').forEach(wrapper => {
        let hasMatchInGrade = false;
        const rows = wrapper.querySelectorAll('.section-row');

        rows.forEach(row => {
            const searchText = (row.getAttribute('data-section-name') || '').toLowerCase();
            const cleanSearchText = searchText.replace(/[^a-z0-9]/g, '');

            const isMatch = searchText.includes(rawQuery) || 
                            (cleanQuery.length > 0 && cleanSearchText.includes(cleanQuery));

            if (isMatch) {
                row.style.display = '';
                hasMatchInGrade = true;
            } else {
                row.style.display = 'none';
            }
        });

        if (hasMatchInGrade) {
            wrapper.style.display = 'block';
            // Automatically expand matching class accordion to reveal the searched section instantly
            const bodyContainer = wrapper.querySelector('.grade-body-container');
            const icon = wrapper.querySelector('.grade-accordion-icon');
            if (bodyContainer) bodyContainer.style.display = 'block';
            if (icon) icon.innerText = '▲';
        } else {
            wrapper.style.display = 'none';
        }
    });
}
</script>

<!-- POP-UP MODAL FOR MANUAL SLOT ADDITION -->
<div id="addSlotModal" class="modal-backdrop" onclick="if(event.target===this) closeAddSlotModal()">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:12px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#fff;margin:0">➕ Add Timetable Slot Manually</h3>
            <button type="button" onclick="closeAddSlotModal()" style="background:none;border:none;color:var(--text-muted);font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form method="POST" action="<?php echo e(route('principal.timetables.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="modal_class_section_id">Class Section *</label>
                <select id="modal_class_section_id" name="class_section_id" required>
                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sec->id); ?>" <?php echo e($selectedSectionId == $sec->id ? 'selected' : ''); ?>>
                        <?php echo e($sec->instituteClass->custom_name); ?> — <?php echo e($sec->section_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_subject_id">Subject *</label>
                <select id="modal_subject_id" name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sub->id); ?>"><?php echo e($sub->subject_name); ?> (<?php echo e($sub->instituteClass->custom_name); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="modal_teacher_id">Teacher / Faculty *</label>
                <select id="modal_teacher_id" name="teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->name); ?> (<?php echo e($teacher->identifier ?? $teacher->email); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['teacher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="modal_day_of_week">Day of Week *</label>
                <select id="modal_day_of_week" name="day_of_week" required>
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($d); ?>" <?php echo e(old('day_of_week', $defaultDay) == $d ? 'selected' : ''); ?>><?php echo e(ucfirst($d)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_room_id">Assign Room (Optional)</label>
                <select id="modal_room_id" name="room_id">
                    <option value="">-- No Room --</option>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rm->id); ?>" <?php echo e(old('room_id') == $rm->id ? 'selected' : ''); ?>>📍 <?php echo e($rm->room_number); ?> (<?php echo e($rm->capacity); ?> seats)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_start_time">Start Time *</label>
                <input id="modal_start_time" type="time" name="start_time" required value="<?php echo e(old('start_time', '08:00')); ?>">
                <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="modal_end_time">End Time *</label>
                <input id="modal_end_time" type="time" name="end_time" required value="<?php echo e(old('end_time', '09:00')); ?>">
                <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                <button type="button" onclick="closeAddSlotModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Slot</button>
            </div>
        </form>
    </div>
</div>

<!-- CLASSY GENERATION CONFIRMATION MODAL -->
<div id="classyConfirmModal" class="modal-backdrop" style="display:none">
    <div class="modal-box" style="max-width:480px;text-align:center;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.15);animation:modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards">
        <div style="width:68px;height:68px;margin:0 auto 20px;background:#fdf2f8;border:1px solid #fbcfe8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 0 25px rgba(225,48,108,0.2)">
            ⚡
        </div>
        <h3 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin-bottom:10px">
            Generate Optimistic Timetable?
        </h3>
        <p style="color:#64748b;font-size:13.5px;line-height:1.6;margin-bottom:28px;font-weight:500">
            This action will automatically calculate &amp; schedule conflict-free timetable slots based on active teacher allocations, faculty working hours, subject durations, and room capacities.
        </p>
        <div style="display:flex;gap:12px;justify-content:center">
            <button type="button" onclick="closeClassyConfirmModal()" class="btn" style="flex:1;background:#ffffff;color:#475569;border:1px solid #cbd5e1;border-radius:12px;padding:12px;font-weight:700;font-size:13px;transition:all 0.2s">
                Cancel
            </button>
            <button type="button" onclick="submitGenerateForm()" class="btn btn-primary" style="flex:1.4;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);color:#fff;border:none;border-radius:12px;padding:12px;font-weight:700;font-size:13px;box-shadow:0 6px 20px rgba(225,48,108,0.35);transition:all 0.2s">
                ⚡ Yes, Generate Timetable
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    0% { opacity: 0; transform: scale(0.9) translateY(10px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<!-- MODAL: QUICK ASSIGN DAYS & HOURS -->
<div id="configDaysHoursModal" class="modal-backdrop" onclick="if(event.target===this) closeConfigDaysHoursModal()">
    <div class="modal-box" style="max-width:540px;border:1px solid #e2e8f0;box-shadow:0 25px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px">
                ⚙️ Quick Assign Days &amp; Hours
            </h3>
            <button type="button" onclick="closeConfigDaysHoursModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer">&times;</button>
        </div>

        <form id="configDaysHoursForm" method="POST" action="">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="form-group mb-3">
                <label style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;display:block;margin-bottom:4px">Select Subject Allocation *</label>
                <select id="modal_allocation_id" onchange="onModalAllocationSelectChange(this)" required style="width:100%;padding:10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:13px;outline:none">
                    <option value="">-- Select Class &amp; Subject Allocation --</option>
                    <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $cName = $a->section->instituteClass->custom_name ?? 'Class';
                            $sName = $a->section->section_name ?? 'Sec';
                            $subName = $a->subject->subject_name ?? 'Subject';
                            $tName = $a->teacher->name ?? 'Unassigned';
                            $updateUrl = route('principal.timetables.allocations.update', $a->id);
                        ?>
                        <option value="<?php echo e($a->id); ?>" 
                                data-update-url="<?php echo e($updateUrl); ?>"
                                data-hours="<?php echo e($a->duration_hours); ?>"
                                data-minutes="<?php echo e($a->duration_remaining_minutes); ?>"
                                data-periods="<?php echo e($a->periods_per_week ?: 3); ?>"
                                data-days='<?php echo json_encode($a->allowed_days_list, 15, 512) ?>'
                                data-avails='<?php echo json_encode($a->teacher ? $a->teacher->availabilities->keyBy(fn($av)=>strtolower($av->day_of_week))->map(fn($av)=>(bool)$av->is_available) : [], 15, 512) ?>'>
                            <?php echo e($cName); ?> — Section <?php echo e($sName); ?> | <?php echo e($subName); ?> (Faculty: <?php echo e($tName); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Duration & Periods Input Row -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Hours</label>
                    <select id="modal_alloc_hours" name="hours" style="width:100%;padding:7px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                        <?php for($h = 0; $h <= 4; $h++): ?>
                            <option value="<?php echo e($h); ?>"><?php echo e($h); ?> <?php echo e(Str::plural('Hour', $h)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Minutes</label>
                    <select id="modal_alloc_minutes" name="minutes" style="width:100%;padding:7px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                        <option value="0">0 Mins</option>
                        <option value="15">15 Mins</option>
                        <option value="30">30 Mins</option>
                        <option value="45">45 Mins</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Periods / Wk</label>
                    <input type="number" id="modal_alloc_periods" name="periods_per_week" value="3" min="1" max="20" style="width:100%;padding:6px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                </div>
            </div>

            <!-- Allowed Days Checkboxes Strip -->
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:18px">
                <div style="font-size:11px;font-weight:800;color:#0f172a;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
                    <span>🗓️ Allowed Lecture Days:</span>
                    <span style="font-size:9.5px;color:#059669">🟢 Teacher Avail &nbsp;|&nbsp; 🔴 Unavail</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px">
                    <?php $__currentLoopData = ['monday'=>'Mon', 'tuesday'=>'Tue', 'wednesday'=>'Wed', 'thursday'=>'Thu', 'friday'=>'Fri', 'saturday'=>'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayKey => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:8px;background:#ffffff;border:1px solid #e2e8f0;cursor:pointer">
                            <input type="checkbox" class="modal-day-checkbox" name="allowed_days[]" value="<?php echo e($dayKey); ?>" checked style="accent-color:#e1306c">
                            <span style="font-size:12px;font-weight:700;color:#0f172a"><?php echo e($dayLabel); ?></span>
                            <span class="modal-day-avail-badge" id="modal_avail_badge_<?php echo e($dayKey); ?>" style="font-size:9px;margin-left:auto">🟢</span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="closeConfigDaysHoursModal()" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);font-weight:700">
                    💾 Save &amp; Re-generate Timetable
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TEACHER / FACULTY DETAILS POPUP MODAL -->
<div id="teacherDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeTeacherDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:540px;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:28px;box-shadow:0 25px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div style="display:flex;align-items:center;gap:14px">
                <div id="tdm_avatar" style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:22px;box-shadow:0 4px 16px rgba(225,48,108,0.35)">
                    T
                </div>
                <div>
                    <h3 id="tdm_name" style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:#0f172a;margin:0">
                        Teacher Name
                    </h3>
                    <div id="tdm_empid" style="font-size:12px;color:#e1306c;font-weight:700;margin-top:2px">
                        Employee ID: EMP-00
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeTeacherDetailsModal()" style="background:none;border:none;color:#64748b;font-size:28px;cursor:pointer;line-height:1">&times;</button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Email Address</span>
                <span id="tdm_email" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;display:block;word-break:break-all">email@domain.com</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Phone Number</span>
                <span id="tdm_phone" style="font-size:13px;font-weight:700;color:#0284c7;margin-top:2px;display:block">N/A</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Highest Qualification</span>
                <span id="tdm_qual" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;display:block">Faculty Member</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Teaching Experience</span>
                <span id="tdm_exp" style="font-size:13px;font-weight:700;color:#059669;margin-top:2px;display:block">N/A</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Monthly Basic Salary</span>
                <span id="tdm_salary" style="font-size:13px;font-weight:700;color:#059669;margin-top:2px;display:block">PKR 0</span>
            </div>
            <div style="background:#f8fafc;padding:12px 14px;border-radius:12px;border:1px solid #e2e8f0">
                <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block">Emergency Contact</span>
                <span id="tdm_emergency" style="font-size:13px;font-weight:700;color:#d97706;margin-top:2px;display:block">N/A</span>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px">
            <a id="tdm_profile_link" href="#" class="btn btn-primary" style="padding:9px 16px;font-size:12.5px;font-weight:700;text-decoration:none">
                👁️ Open Full Profile &amp; Directory Ledger &rarr;
            </a>
            <button type="button" onclick="closeTeacherDetailsModal()" class="btn btn-ghost" style="padding:9px 16px">
                Close
            </button>
        </div>
    </div>
</div>

<!-- SUBJECT DETAILS POPUP MODAL -->
<div id="subjectDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeSubjectDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:480px;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px">
            <h3 id="sdm_title" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Subject Details
            </h3>
            <button type="button" onclick="closeSubjectDetailsModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1">&times;</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Subject Name &amp; Code</span>
                <span id="sdm_name_code" style="font-size:14px;font-weight:800;color:#0284c7;margin-top:2px;display:block">Subject</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Class Grade</span>
                <span id="sdm_class" style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;display:block">Class</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Faculty Teacher In-Charge</span>
                <span id="sdm_teacher" style="font-size:13px;font-weight:800;color:#059669;margin-top:2px;display:block">Teacher</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Room / Facility Location</span>
                <span id="sdm_room" style="font-size:13px;font-weight:800;color:#d97706;margin-top:2px;display:block">Room</span>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <button type="button" onclick="closeSubjectDetailsModal()" class="btn btn-ghost">Close</button>
        </div>
    </div>
</div>

<!-- SECTION DETAILS POPUP MODAL -->
<div id="sectionDetailsModal" class="modal-backdrop" onclick="if(event.target===this) closeSectionDetailsModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.5);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(8px)">
    <div class="modal-box" style="width:90%;max-width:460px;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,0.15)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px">
            <h3 id="sec_dm_title" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Class Section Details
            </h3>
            <button type="button" onclick="closeSectionDetailsModal()" style="background:none;border:none;color:#64748b;font-size:24px;cursor:pointer;line-height:1">&times;</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Class Name</span>
                <span id="sec_dm_class" style="font-size:14px;font-weight:800;color:#0284c7;margin-top:2px;display:block">Class</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Section</span>
                <span id="sec_dm_sec" style="font-size:13px;font-weight:800;color:#0f172a;margin-top:2px;display:block">Section</span>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;display:block">Weekly Lecture Workload</span>
                <span id="sec_dm_count" style="font-size:13px;font-weight:800;color:#059669;margin-top:2px;display:block">0 Lecture Blocks</span>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:20px">
            <button type="button" onclick="closeSectionDetailsModal()" class="btn btn-ghost">Close</button>
        </div>
    </div>
</div>

<script>
const teachersData = {
    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        "<?php echo e($t->id); ?>": {
            id: <?php echo e($t->id); ?>,
            name: <?php echo json_encode($t->name, 15, 512) ?>,
            email: <?php echo json_encode($t->email, 15, 512) ?>,
            phone: <?php echo json_encode($t->phone ?? 'N/A', 15, 512) ?>,
            empid: <?php echo json_encode($t->employee_id ?? ('EMP-' . $t->id), 15, 512) ?>,
            qualification: <?php echo json_encode($t->qualification ?? 'Faculty Member', 15, 512) ?>,
            experience: <?php echo json_encode($t->years_of_experience ? ($t->years_of_experience . ' Years') : 'N/A', 15, 512) ?>,
            salary: <?php echo json_encode(number_format($t->basic_salary_pkr ?? 0), 15, 512) ?>,
            emergency: <?php echo json_encode($t->emergency_contact_phone ?? 'N/A', 15, 512) ?>,
            profileUrl: <?php echo json_encode(route($routePrefix . 'directory.teacher', $t->id), 512) ?>
        },
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
};

function openTeacherModalFromId(teacherId) {
    if (!teacherId || !teachersData[teacherId]) return;
    const t = teachersData[teacherId];
    
    document.getElementById('tdm_avatar').innerText = (t.name.charAt(0) || 'T').toUpperCase();
    document.getElementById('tdm_name').innerText = t.name;
    document.getElementById('tdm_empid').innerText = 'Employee ID: ' + t.empid;
    document.getElementById('tdm_email').innerText = t.email;
    document.getElementById('tdm_phone').innerText = t.phone;
    document.getElementById('tdm_qual').innerText = t.qualification;
    document.getElementById('tdm_exp').innerText = t.experience;
    document.getElementById('tdm_salary').innerText = 'PKR ' + t.salary;
    document.getElementById('tdm_emergency').innerText = t.emergency;
    document.getElementById('tdm_profile_link').href = t.profileUrl;

    const modal = document.getElementById('teacherDetailsModal');
    modal.style.display = 'flex';
}

function closeTeacherDetailsModal() {
    document.getElementById('teacherDetailsModal').style.display = 'none';
}

function showSubjectDetailsModal(name, code, className, teacherName, room) {
    document.getElementById('sdm_title').innerText = '📘 ' + name + (code ? ' (' + code + ')' : '');
    document.getElementById('sdm_name_code').innerText = name + (code ? ' [' + code + ']' : '');
    document.getElementById('sdm_class').innerText = className || 'All Classes';
    document.getElementById('sdm_teacher').innerText = teacherName || 'Unassigned';
    document.getElementById('sdm_room').innerText = room || 'Campus Room';

    document.getElementById('subjectDetailsModal').style.display = 'flex';
}

function closeSubjectDetailsModal() {
    document.getElementById('subjectDetailsModal').style.display = 'none';
}

function showSectionDetailsModal(className, sectionName, count) {
    document.getElementById('sec_dm_title').innerText = '🏫 Class ' + className + ' — Section ' + sectionName;
    document.getElementById('sec_dm_class').innerText = className;
    document.getElementById('sec_dm_sec').innerText = 'Section ' + sectionName;
    document.getElementById('sec_dm_count').innerText = count + ' Lecture Block(s)';

    document.getElementById('sectionDetailsModal').style.display = 'flex';
}

function closeSectionDetailsModal() {
    document.getElementById('sectionDetailsModal').style.display = 'none';
}

function openConfigDaysHoursModal(allocationId = null) {
    const modal = document.getElementById('configDaysHoursModal');
    modal.classList.add('active');
    modal.style.display = 'flex';
    
    if (allocationId) {
        const select = document.getElementById('modal_allocation_id');
        select.value = allocationId;
        onModalAllocationSelectChange(select);
    }
}

function closeConfigDaysHoursModal() {
    const modal = document.getElementById('configDaysHoursModal');
    modal.classList.remove('active');
    modal.style.display = 'none';
}

function onModalAllocationSelectChange(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    const updateUrl = selectedOption.getAttribute('data-update-url');
    const hours = selectedOption.getAttribute('data-hours') || 1;
    const minutes = selectedOption.getAttribute('data-minutes') || 0;
    const periods = selectedOption.getAttribute('data-periods') || 3;
    const days = JSON.parse(selectedOption.getAttribute('data-days') || '[]');
    const avails = JSON.parse(selectedOption.getAttribute('data-avails') || '{}');

    document.getElementById('configDaysHoursForm').action = updateUrl;
    document.getElementById('modal_alloc_hours').value = hours;
    document.getElementById('modal_alloc_minutes').value = minutes;
    document.getElementById('modal_alloc_periods').value = periods;

    const dayCheckboxes = document.querySelectorAll('.modal-day-checkbox');
    dayCheckboxes.forEach(cb => {
        const dayVal = cb.value;
        const badge = document.getElementById('modal_avail_badge_' + dayVal);
        const isWorking = avails[dayVal] !== undefined ? avails[dayVal] : true;
        const parentLabel = cb.closest('label');

        if (!isWorking) {
            cb.disabled = true;
            cb.checked = false;
            if (parentLabel) {
                parentLabel.style.opacity = '0.45';
                parentLabel.style.cursor = 'not-allowed';
            }
            if (badge) badge.innerText = '🔴 Off';
        } else {
            cb.disabled = false;
            cb.checked = days.length === 0 || days.includes(dayVal);
            if (parentLabel) {
                parentLabel.style.opacity = '1';
                parentLabel.style.cursor = 'pointer';
            }
            if (badge) badge.innerText = '🟢';
        }
    });
}

function openAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'flex';
}
function closeAddSlotModal() {
    document.getElementById('addSlotModal').style.display = 'none';
}
function openClassyConfirmModal() {
    document.getElementById('classyConfirmModal').style.display = 'flex';
}
function closeClassyConfirmModal() {
    document.getElementById('classyConfirmModal').style.display = 'none';
}
function submitGenerateForm() {
    closeClassyConfirmModal();
    document.getElementById('generateTimetableForm').submit();
}
function toggleDurationAccordion(accId) {
    const el = document.getElementById(accId);
    const icon = document.getElementById(accId.replace('acc_', 'acc_icon_'));
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        if (icon) icon.innerHTML = '▲ Collapse Subjects';
    } else {
        el.style.display = 'none';
        if (icon) icon.innerHTML = '▼ Click to Expand';
    }
}

function toggleTeacherTimetable(teacherId) {
    const body = document.getElementById('teacher-timetable-body-' + teacherId);
    const icon = document.getElementById('teacher-acc-icon-' + teacherId);
    if (!body) return;

    if (body.style.display === 'none' || body.style.display === '') {
        body.style.display = 'block';
        if (icon) {
            icon.innerHTML = '▲ Collapse Schedule';
            icon.style.background = 'rgba(0,206,209,0.25)';
            icon.style.color = '#fff';
        }
    } else {
        body.style.display = 'none';
        if (icon) {
            icon.innerHTML = '▼ Click to View Schedule';
            icon.style.background = 'rgba(0,206,209,0.1)';
            icon.style.color = '#00ced1';
        }
    }
}

function expandAllTeachers() {
    document.querySelectorAll('[id^="teacher-timetable-body-"]').forEach(el => {
        el.style.display = 'block';
    });
    document.querySelectorAll('[id^="teacher-acc-icon-"]').forEach(icon => {
        icon.innerHTML = '▲ Collapse Schedule';
        icon.style.background = 'rgba(0,206,209,0.25)';
        icon.style.color = '#fff';
    });
}

function collapseAllTeachers() {
    document.querySelectorAll('[id^="teacher-timetable-body-"]').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('[id^="teacher-acc-icon-"]').forEach(icon => {
        icon.innerHTML = '▼ Click to View Schedule';
        icon.style.background = 'rgba(0,206,209,0.1)';
        icon.style.color = '#00ced1';
    });
}

<?php if($viewType === 'section'): ?>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof expandAllGrades === 'function') {
        expandAllGrades();
    }
});
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\timetables\index.blade.php ENDPATH**/ ?>
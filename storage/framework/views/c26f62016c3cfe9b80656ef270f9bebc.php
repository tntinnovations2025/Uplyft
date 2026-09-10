<?php $__env->startSection('title', 'Class Days & Work Hours Allocation'); ?>
<?php $__env->startSection('breadcrumb', 'Days & Hours Allocation Management'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }
?>

<!-- HEADER TITLE & SINGLE UNIFIED TOP MENU BAR -->
<div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:10px;letter-spacing:-0.5px">
                ⚙️ Class Days &amp; Work Hours Allocation
            </h1>
            <p style="color:#64748b;font-size:13.5px;margin-top:2px;font-weight:500">
                Configure permitted lecture days, duration hours/minutes, and weekly period frequency per subject assignment in <strong><?php echo e($activeTerm?->name); ?></strong>.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12.5px;color:#64748b;font-weight:700">
                Active View: <strong style="color:#e1306c">Days &amp; Hours Allocation</strong>
            </span>
        </div>
    </div>

    <!-- SINGLE UNIFIED TOP NAVIGATION & ACTION MENU -->
    <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <a href="<?php echo e(route($routePrefix . 'timetables.index', ['view_type' => 'my'])); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                👨‍🏫 My Assigned Lectures
            </a>
            <a href="<?php echo e(route($routePrefix . 'timetables.index', ['view_type' => 'section'])); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                📂 View Section Wise
            </a>

            <a href="<?php echo e(route($routePrefix . 'timetables.grid')); ?>" 
               class="btn" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;background:#f8fafc;color:#475569;border:1px solid #cbd5e1">
                📊 Master Tabular Grid
            </a>
            <a href="<?php echo e(route($routePrefix . 'timetables.days-and-hours')); ?>" 
               class="btn btn-primary" style="border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700">
                ⚙️ Days &amp; Work Hours Allocation
            </a>
        </div>

            <a href="<?php echo e(route($routePrefix . 'timetables.export')); ?>" class="btn btn-ghost" style="border-radius:10px;padding:9px 14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;border:1px solid #cbd5e1;background:#ffffff">
                📗 Download Excel (.xlsx)
            </a>
            <?php if(auth()->user()->hasPermission('timetables', 'edit')): ?>
                <form id="generateTimetableForm" method="POST" action="<?php echo e(route($routePrefix . 'timetables.generate')); ?>" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <button type="button" onclick="openClassyConfirmModal()" class="btn btn-primary" style="border-radius:10px;padding:9px 16px;font-size:12px;font-weight:700">
                        ⚡ Generate
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom:24px;padding:16px 22px;background:#ffffff;border:1px solid rgba(226,232,240,0.85)">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:500px">
            <span style="font-size:13px;font-weight:800;color:#0f172a;white-space:nowrap">🔍 Filter Allocation:</span>
            <input type="text" id="allocationSearchInput" onkeyup="filterAllocations()" placeholder="Search class, section, subject, or faculty name..." style="padding:10px 16px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px;width:100%;outline:none">
        </div>
        <div style="font-size:12.5px;color:#059669;font-weight:700">
            💡 Unavailable days for teachers are automatically disabled (🔴 Off)
        </div>
    </div>
</div>

<?php
    $groupedAssignments = $assignments->groupBy(function($a) {
        $cName = $a->section->instituteClass->custom_name ?? 'Class';
        $sName = $a->section->section_name ?? 'Section';
        return "{$cName} — Section {$sName}";
    });
?>

<?php $__empty_1 = true; $__currentLoopData = $groupedAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $classAllocations): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="allocation-class-card" data-class-title="<?php echo e(strtolower($className)); ?>" style="margin-bottom:20px;background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;overflow:hidden;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <!-- Class Accordion Header -->
        <div onclick="toggleAllocationAccordion('alloc_acc_<?php echo e($loop->index); ?>')" 
             style="background:#f8fafc;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;transition:background 0.2s;border-bottom:1px solid #e2e8f0">
            <div style="display:flex;align-items:center;gap:14px">
                <span style="font-size:22px">🏫</span>
                <div>
                    <div style="font-weight:800;color:#0f172a;font-size:16px"><?php echo e($className); ?></div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500"><?php echo e($classAllocations->count()); ?> Allocated Subject(s)</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                <span class="badge badge-purple" style="font-size:11px;padding:4px 12px;font-weight:700">
                    <?php echo e($classAllocations->count()); ?> Subject(s)
                </span>
                <span id="alloc_acc_icon_<?php echo e($loop->index); ?>" style="color:#e1306c;font-size:13px;font-weight:800;transition:transform 0.2s">
                    ▲ Hide Subjects
                </span>
            </div>
        </div>

        <!-- Subjects Cards Grid -->
        <div id="alloc_acc_<?php echo e($loop->index); ?>" style="display:block;padding:20px;background:#ffffff">
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(380px, 1fr));gap:18px">
                <?php $__currentLoopData = $classAllocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $tAvails = $alloc->teacher ? $alloc->teacher->availabilities->keyBy(fn($a) => strtolower($a->day_of_week)) : collect();
                        $allWeekdays = [
                            'monday' => 'Mon',
                            'tuesday' => 'Tue',
                            'wednesday' => 'Wed',
                            'thursday' => 'Thu',
                            'friday' => 'Fri',
                            'saturday' => 'Sat'
                        ];
                        $searchKeyword = strtolower("{$alloc->subject->subject_name} {$alloc->subject->subject_code} " . ($alloc->teacher->name ?? ''));
                    ?>
                    <div class="allocation-item-card" data-keyword="<?php echo e($searchKeyword); ?>" style="background:#ffffff;border:1px solid #cbd5e1;border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:14px;box-shadow:0 2px 10px rgba(0,0,0,0.03)">
                        <form method="POST" action="<?php echo e(route($routePrefix . 'timetables.allocations.update', $alloc->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>

                            <!-- Header: Subject & Teacher Info -->
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:14px">
                                <div>
                                    <div style="font-weight:800;color:#0f172a;font-size:15px;display:flex;align-items:center;gap:8px">
                                        📘 <?php echo e($alloc->subject->subject_name); ?>

                                        <code style="font-size:11px;color:#0284c7;background:#eff6ff;padding:2px 7px;border-radius:5px;font-weight:700;border:1px solid #bfdbfe">
                                            <?php echo e($alloc->subject->subject_code ?: 'SUB'); ?>

                                        </code>
                                    </div>
                                    <div style="font-size:12px;color:#64748b;margin-top:4px;font-weight:600">
                                        👨‍🏫 Faculty: <?php echo e($alloc->teacher->name ?? 'Unassigned'); ?>

                                    </div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
                                    <span style="font-size:11px;font-weight:800;color:#059669;background:#ecfdf5;padding:4px 10px;border-radius:6px;border:1px solid #a7f3d0">
                                        ⏱️ <?php echo e($alloc->formatted_duration); ?>

                                    </span>
                                    <span style="font-size:11px;color:#475569;font-weight:600">
                                        🗓️ <?php echo e($alloc->formatted_allowed_days); ?>

                                    </span>
                                </div>
                            </div>

                            <!-- Duration & Periods Input Row -->
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                                <div>
                                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Duration (Hours)</label>
                                    <select name="hours" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                                        <?php for($h = 0; $h <= 4; $h++): ?>
                                            <option value="<?php echo e($h); ?>" <?php echo e($alloc->duration_hours === $h ? 'selected' : ''); ?>><?php echo e($h); ?> <?php echo e(Str::plural('Hour', $h)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Minutes</label>
                                    <select name="minutes" style="width:100%;padding:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                                        <option value="0" <?php echo e($alloc->duration_remaining_minutes === 0 ? 'selected' : ''); ?>>0 Mins</option>
                                        <option value="15" <?php echo e($alloc->duration_remaining_minutes === 15 ? 'selected' : ''); ?>>15 Mins</option>
                                        <option value="30" <?php echo e($alloc->duration_remaining_minutes === 30 ? 'selected' : ''); ?>>30 Mins</option>
                                        <option value="45" <?php echo e($alloc->duration_remaining_minutes === 45 ? 'selected' : ''); ?>>45 Mins</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;margin-bottom:4px">Periods / Wk</label>
                                    <input type="number" name="periods_per_week" value="<?php echo e($alloc->periods_per_week ?: 3); ?>" min="1" max="20" style="width:100%;padding:7px 8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                                </div>
                            </div>

                            <!-- Allowed Lecture Days Selection Strip -->
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:14px">
                                <div style="font-size:11px;font-weight:800;color:#0f172a;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
                                    <span>🗓️ Assign Allowed Days:</span>
                                    <span style="font-size:10px;color:#059669;font-weight:700">🟢 Teacher Avail &nbsp;|&nbsp; 🔴 Unavailable (Disabled)</span>
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px">
                                    <?php $__currentLoopData = $allWeekdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayKey => $dayLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isAvailRecord = $tAvails->get($dayKey);
                                            $isTeacherWorking = $isAvailRecord ? (bool)$isAvailRecord->is_available : true;
                                            $isDayChecked = $isTeacherWorking && $alloc->isDayAllowed($dayKey);
                                        ?>
                                        <label style="display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:8px;background:<?php echo e($isTeacherWorking ? '#ecfdf5' : '#fef2f2'); ?>;border:1px solid <?php echo e($isTeacherWorking ? '#a7f3d0' : '#fecaca'); ?>;cursor:<?php echo e($isTeacherWorking ? 'pointer' : 'not-allowed'); ?>;opacity:<?php echo e($isTeacherWorking ? '1' : '0.5'); ?>" title="<?php echo e($isTeacherWorking ? 'Teacher available on '.$dayLabel : 'Teacher unavailable on '.$dayLabel.' (Disabled)'); ?>">
                                            <input type="checkbox" name="allowed_days[]" value="<?php echo e($dayKey); ?>" <?php echo e($isDayChecked ? 'checked' : ''); ?> <?php echo e($isTeacherWorking ? '' : 'disabled'); ?> style="accent-color:#e1306c">
                                            <span style="font-size:12px;font-weight:700;color:<?php echo e($isTeacherWorking ? '#0f172a' : '#94a3b8'); ?>">
                                                <?php echo e($dayLabel); ?>

                                            </span>
                                            <span style="font-size:9.5px;margin-left:auto">
                                                <?php echo e($isTeacherWorking ? '🟢' : '🔴 Off'); ?>

                                            </span>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;font-size:12.5px;font-weight:700;border:none;border-radius:10px">
                                💾 Save Settings &amp; Re-generate Timetable
                            </button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="card" style="text-align:center;padding:48px 24px">
        <p style="color:var(--text-muted);font-size:15px">No subject allocations found for the active academic term.</p>
    </div>
<?php endif; ?>

<!-- CLASSY GENERATION CONFIRMATION MODAL -->
<div id="classyConfirmModal" class="modal-backdrop" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div class="modal-box" style="max-width:480px;text-align:center;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.15);animation:modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards">
        <div style="width:68px;height:68px;margin:0 auto 20px;background:#fdf4ff;border:1px solid #f5d0fe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 4px 14px rgba(225,48,108,0.15)">
            ⚡
        </div>
        <h3 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin-bottom:10px">
            Generate Optimistic Timetable?
        </h3>
        <p style="color:#64748b;font-size:13.5px;line-height:1.6;margin-bottom:28px;font-weight:500">
            This action will automatically calculate &amp; schedule conflict-free timetable slots based on active teacher allocations, faculty working hours, subject durations, and room capacities.
        </p>
        <div style="display:flex;gap:12px;justify-content:center">
            <button type="button" onclick="closeClassyConfirmModal()" class="btn btn-ghost" style="flex:1;border-radius:12px;padding:12px;font-weight:700;font-size:13px">
                Cancel
            </button>
            <button type="button" onclick="submitGenerateForm()" class="btn btn-primary" style="flex:1.4;border-radius:12px;padding:12px;font-weight:700;font-size:13px">
                ⚡ Yes, Generate Timetable
            </button>
        </div>
    </div>
</div>

<script>
function toggleAllocationAccordion(accId) {
    const el = document.getElementById(accId);
    const icon = document.getElementById(accId.replace('acc_', 'acc_icon_'));
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        if (icon) icon.innerHTML = '▲ Hide Subjects';
    } else {
        el.style.display = 'none';
        if (icon) icon.innerHTML = '▼ Show Subjects';
    }
}

function filterAllocations() {
    const query = document.getElementById('allocationSearchInput').value.toLowerCase().trim();
    const classCards = document.querySelectorAll('.allocation-class-card');

    classCards.forEach(card => {
        const classTitle = card.getAttribute('data-class-title') || '';
        const itemCards = card.querySelectorAll('.allocation-item-card');
        let hasMatch = false;

        itemCards.forEach(item => {
            const keyword = item.getAttribute('data-keyword') || '';
            if (classTitle.includes(query) || keyword.includes(query)) {
                item.style.display = 'flex';
                hasMatch = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (hasMatch || !query) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
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
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\timetables\days_and_hours.blade.php ENDPATH**/ ?>
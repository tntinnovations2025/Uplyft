

<?php $__env->startSection('title', 'Exam Datesheet Builder'); ?>
<?php $__env->startSection('breadcrumb', 'Exam Datesheets'); ?>

<?php $__env->startSection('content'); ?>
<style>
    @media print {
        body { background: #fff !important; color: #000 !important; }
        .no-print, header, sidebar, .btn, form, .modal-backdrop, .filter-card { display: none !important; }
        .card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; margin-bottom: 20px !important; }
        body.printing-single-class .class-datesheet-card { display: none !important; }
        body.printing-single-class .class-datesheet-card.active-print-target { display: block !important; }
        .class-card-body { display: block !important; }
        table { border-collapse: collapse !important; width: 100% !important; }
        th, td { border: 1px solid #cbd5e1 !important; padding: 8px !important; color: #0f172a !important; font-size: 11px !important; }
    }
    .class-datesheet-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .class-datesheet-card:hover {
        border-color: #334155;
    }
    .class-header-row {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
    }
    .datesheet-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }
    .datesheet-table th {
        background: #1e293b;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #334155;
    }
    .datesheet-table td {
        padding: 12px;
        border-bottom: 1px solid #1e293b;
        vertical-align: middle;
        font-size: 12px;
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-complete { background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .badge-pending { background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }
    .btn-outline { background: transparent; border-color: #334155; color: #cbd5e1; }
    .btn-outline:hover { background: #1e293b; color: #ffffff; }
    .btn-primary-custom { background: #6366f1; color: #ffffff; }
    .btn-primary-custom:hover { background: #4f46e5; }
    .btn-danger-custom { background: transparent; color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }
    .btn-danger-custom:hover { background: rgba(239, 68, 68, 0.1); }
</style>

<?php
    $roomsList = $registeredRooms ?? collect();
    $roomOptionsHtml = '<option value="" selected>-- Select Room / Hall --</option>';
    if ($roomsList->isNotEmpty()) {
        foreach($roomsList as $rm) {
            $rawRoomNo = trim($rm->room_number ?? '');
            $cleanRoomNo = preg_replace('/^(room\s*)+/i', '', $rawRoomNo);
            $rName = 'Room '.$cleanRoomNo.($rm->building_block ? ' ('.$rm->building_block.')' : '');
            $roomOptionsHtml .= '<option value="'.e($rName).'">'.e($rName).'</option>';
        }
    } else {
        $roomOptionsHtml .= '<option value="Room 1">Room 1</option><option value="Room 2">Room 2</option><option value="Room 3">Room 3</option><option value="Hall A">Hall A</option><option value="Hall B">Hall B</option><option value="Auditorium">Auditorium</option>';
    }
?>


<div class="no-print" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;color:#f8fafc;margin:0">
            Examination Datesheets
        </h1>
        <p style="color:#94a3b8;font-size:13px;margin:4px 0 0">
            <?php if(auth()->user()->isAdministration()): ?>
                Manage, schedule and publish official examination datesheets across institute classes.
            <?php else: ?>
                Official examination schedule directory.
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="btn-action btn-outline" onclick="printWholeInstitute()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Print Institute Schedule
        </button>
        <?php if(auth()->user()->isAdministration()): ?>
            <button class="btn-action btn-primary-custom" onclick="openModal('create-datesheet-modal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Exam Schedule
            </button>
        <?php endif; ?>
    </div>
</div>


<?php if(auth()->user()->isAdministration()): ?>
    <div id="datesheet-builder-workspace" class="card no-print" style="display:none;margin-bottom:24px;border:1px solid #6366f1;background:#0f172a;border-radius:12px">
        <div style="background:#1e293b;padding:16px 20px;border-bottom:1px solid #334155;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <h3 id="ws-exam-title-display" style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;color:#f8fafc;margin:0">
                    Midterm Examination
                </h3>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">
                    Configure dates, timing, rooms and marks for selected classes.
                </p>
            </div>
            <button type="button" class="btn-action btn-outline" onclick="openModal('create-datesheet-modal')">
                Configure Exam Settings
            </button>
        </div>

        <form method="POST" action="<?php echo e(route('lms.datesheet.store')); ?>" onsubmit="return validateDatesheetForm(event)" style="padding:20px">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="ws-form-title" name="title" value="Midterm Examination">
            <input type="hidden" id="ws-form-type" name="type" value="Midterm Examination">
            <input type="hidden" id="ws-form-term-id" name="academic_term_id" value="<?php echo e($academicTerms->first()?->id ?? ''); ?>">

            <div style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:700;color:#94a3b8;margin-bottom:8px;display:block;text-transform:uppercase;letter-spacing:0.05em">
                    Select Class Section to Schedule:
                </label>
                <div id="class-tabs-container" style="display:flex;flex-wrap:wrap;gap:8px"></div>
            </div>

            <div style="margin-bottom:20px">
                <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $cName = $cs->instituteClass?->custom_name ?? 'Class';
                        $csSubjects = $cs->subjects ?? collect();
                        $subOptsHtml = '';
                        foreach($csSubjects as $optSub) {
                            $subOptsHtml .= '<option value="'.$optSub->id.'">'.e($optSub->subject_name).'</option>';
                        }
                    ?>
                    <div id="class-block-<?php echo e($cs->id); ?>" class="class-schedule-block" style="display:none;padding:16px;background:#1e293b;border:1px solid #334155;border-radius:10px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #334155;flex-wrap:wrap;gap:10px">
                            <div>
                                <h4 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:15px;color:#f8fafc;margin:0">
                                    <?php echo e($cName); ?> — Section <?php echo e($cs->section_name); ?>

                                </h4>
                                <span style="font-size:12px;color:#94a3b8"><?php echo e($csSubjects->count()); ?> Subjects Enrolled</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px">
                                <div style="display:flex;align-items:center;gap:10px;background:#0f172a;padding:5px 10px;border-radius:6px;border:1px solid #334155">
                                    <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:11px;color:#cbd5e1;font-weight:600">
                                        <input type="hidden" name="class_visibility[<?php echo e($cs->id); ?>][is_published_teacher]" value="0">
                                        <input type="checkbox" name="class_visibility[<?php echo e($cs->id); ?>][is_published_teacher]" value="1" checked style="accent-color:#6366f1">
                                        <span>Teacher Portal</span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:11px;color:#cbd5e1;font-weight:600">
                                        <input type="hidden" name="class_visibility[<?php echo e($cs->id); ?>][is_published_student]" value="0">
                                        <input type="checkbox" name="class_visibility[<?php echo e($cs->id); ?>][is_published_student]" value="1" checked style="accent-color:#6366f1">
                                        <span>Student Portal</span>
                                    </label>
                                </div>
                                <span style="font-size:12px;color:#94a3b8">Start Date:</span>
                                <input type="date" id="auto-date-<?php echo e($cs->id); ?>" value="<?php echo e(date('Y-m-d')); ?>" style="padding:5px 8px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:12px">
                                <button type="button" class="btn-action btn-outline" onclick="autoFillDatesForClass(<?php echo e($cs->id); ?>)" style="font-size:11px">
                                    Auto-Assign Sequential Dates
                                </button>
                            </div>
                        </div>

                        <div id="paper-rows-container-<?php echo e($cs->id); ?>" style="display:flex;flex-direction:column;gap:12px">
                            <?php if($csSubjects->isNotEmpty()): ?>
                                <?php $__currentLoopData = $csSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="paper-row-item-<?php echo e($cs->id); ?>" style="background:#0f172a;border:1px solid #334155;border-radius:8px;padding:12px">
                                        <div style="display:grid;grid-template-columns:2.5fr 1.5fr 2fr 1fr 30px;gap:10px;align-items:center;margin-bottom:8px">
                                            <div>
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">SUBJECT</label>
                                                <select name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][subject_id]" style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#ffffff;font-weight:600;font-size:12px">
                                                    <?php echo str_replace('value="'.$sub->id.'"', 'value="'.$sub->id.'" selected', $subOptsHtml); ?>

                                                </select>
                                            </div>
                                            <div>
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">EXAM DATE</label>
                                                <input type="date" name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][exam_date]" class="cs-exam-date-<?php echo e($cs->id); ?>" value="<?php echo e(date('Y-m-d', strtotime("+{$idx} days"))); ?>" style="width:100%;padding:7px 8px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600;font-size:12px">
                                            </div>
                                            <div>
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">TIME SLOT</label>
                                                <div style="display:flex;gap:4px">
                                                    <input type="time" name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][start_time]" value="09:00" style="width:50%;padding:6px 4px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                                                    <input type="time" name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][end_time]" value="11:30" style="width:50%;padding:6px 4px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                                                </div>
                                            </div>
                                            <div>
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">MARKS</label>
                                                <input type="number" name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][total_marks]" value="100" min="1" max="1000" style="width:100%;padding:7px 8px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600;font-size:12px">
                                            </div>
                                            <div style="text-align:center;padding-top:14px">
                                                <button type="button" onclick="this.closest('.paper-row-item-<?php echo e($cs->id); ?>').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px">✕</button>
                                            </div>
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:center;padding:8px 10px;background:#1e293b;border-radius:6px">
                                            <div style="display:flex;align-items:center;gap:6px">
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8">ROOM:</label>
                                                <select name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][room]" class="cs-room-select-<?php echo e($cs->id); ?>" style="width:100%;padding:5px 8px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#ffffff;font-size:11px">
                                                    <?php echo $roomOptionsHtml; ?>

                                                </select>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:6px">
                                                <label style="font-size:10px;font-weight:700;color:#94a3b8">DEADLINE:</label>
                                                <input type="date" name="class_schedules[<?php echo e($cs->id); ?>][<?php echo e($idx); ?>][result_deadline]" class="cs-res-date-<?php echo e($cs->id); ?>" value="<?php echo e(date('Y-m-d', strtotime("+".($idx+7)." days"))); ?>" style="width:100%;padding:5px 6px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top:12px">
                            <button type="button" class="btn-action btn-outline" onclick="addPaperRowForClass(<?php echo e($cs->id); ?>)" style="font-size:11px">
                                + Add Additional Subject Row
                            </button>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:12px;border-top:1px solid #334155">
                <button type="button" class="btn-action btn-outline" onclick="closeWorkspace()">Close</button>
                <button type="submit" class="btn-action btn-primary-custom">
                    Publish Exam Datesheet
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>


<div class="card no-print" style="margin-bottom:20px;padding:14px;background:#0f172a;border:1px solid #1e293b;border-radius:10px">
    <form method="GET" action="<?php echo e(route('lms.datesheet.index')); ?>" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:260px">
            <span style="font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Class Filter:</span>
            <select name="class_section_id" onchange="this.form.submit()" style="padding:7px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:13px;flex:1;max-width:320px">
                <option value="">All Institute Classes</option>
                <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cs->id); ?>" <?php if(request('class_section_id') == $cs->id): echo 'selected'; endif; ?>>
                        <?php echo e($cs->instituteClass?->custom_name ?? 'Class'); ?> - <?php echo e($cs->section_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php if(request()->has('class_section_id')): ?>
            <a href="<?php echo e(route('lms.datesheet.index')); ?>" class="btn-action btn-outline" style="font-size:11px">Reset Filter</a>
        <?php endif; ?>
    </form>
</div>


<?php
    $displayedSections = $classSections;
    if (request()->filled('class_section_id')) {
        $displayedSections = $classSections->where('id', request('class_section_id'));
    }
?>

<div id="classes-directory-container">
    <?php $__empty_1 = true; $__currentLoopData = $displayedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $cName = trim(($cs->instituteClass?->custom_name ?? 'Class').' - '.$cs->section_name);
            $exams = $groupedDatesheet->get($cName, collect());
            $csSubjects = $cs->subjects ?? collect();
            $scheduledSubIds = $exams->pluck('subject_id')->filter()->toArray();
            $unscheduledSubjects = $csSubjects->filter(fn($sub) => !in_array($sub->id, $scheduledSubIds));
            $isComplete = $unscheduledSubjects->isEmpty() && $exams->isNotEmpty();
        ?>

        <div id="class-card-display-<?php echo e($cs->id); ?>" class="class-datesheet-card">
            
            <div class="class-header-row" onclick="toggleClassBody(<?php echo e($cs->id); ?>)">
                <div style="display:flex;align-items:center;gap:14px">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:#f8fafc;margin:0">
                                <?php echo e($cName); ?>

                            </h3>
                            <?php if($isComplete): ?>
                                <span class="badge-status badge-complete">Complete</span>
                            <?php else: ?>
                                <span class="badge-status badge-pending"><?php echo e($unscheduledSubjects->count()); ?> Subject(s) Missing</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px">
                            <?php echo e($exams->count()); ?> Exam Papers Scheduled
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px" onclick="event.stopPropagation()">
                    <?php if($exams->isNotEmpty()): ?>
                        <?php
                            $classPubTeacher = $exams->every(fn($e) => $e->is_published_teacher);
                            $classPubStudent = $exams->every(fn($e) => $e->is_published_student);
                        ?>
                        <span class="badge-status <?php echo e($classPubTeacher ? 'badge-complete' : 'badge-pending'); ?>" style="font-size:11px; <?php echo e($classPubTeacher ? 'background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.3)' : 'background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3)'); ?>">
                            Teacher Portal: <?php echo e($classPubTeacher ? 'Visible' : 'Hidden'); ?>

                        </span>
                        <span class="badge-status <?php echo e($classPubStudent ? 'badge-complete' : 'badge-pending'); ?>" style="font-size:11px; <?php echo e($classPubStudent ? 'background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.3)' : 'background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3)'); ?>">
                            Student Portal: <?php echo e($classPubStudent ? 'Visible' : 'Hidden'); ?>

                        </span>
                    <?php endif; ?>

                    <button type="button" class="btn-action btn-outline no-print" onclick="printClassDatesheet(<?php echo e($cs->id); ?>)">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        Print
                    </button>
                    <?php if(auth()->user()->isAdministration() && $exams->isNotEmpty()): ?>
                        <form method="POST" action="<?php echo e(route('lms.datesheet.destroyClass', $cs->id)); ?>" onsubmit="return confirm('Delete entire examination datesheet for <?php echo e($cName); ?>?')" class="no-print" style="margin:0">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-action btn-danger-custom">Delete</button>
                        </form>
                    <?php endif; ?>
                    <div id="accordion-arrow-<?php echo e($cs->id); ?>" style="color:#94a3b8;margin-left:6px;transition:transform 0.2s ease">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            
            <div id="class-card-body-<?php echo e($cs->id); ?>" class="class-card-body" style="display:none;border-top:1px solid #1e293b;padding:16px 20px">
                
                
                <?php if($unscheduledSubjects->isNotEmpty()): ?>
                    <div style="margin-bottom:16px;padding:12px 16px;background:rgba(245, 158, 11, 0.08);border:1px solid rgba(245, 158, 11, 0.2);border-radius:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                        <div>
                            <div style="font-weight:700;color:#f59e0b;font-size:12px;text-transform:uppercase;letter-spacing:0.05em">
                                Pending Subject Datesheets:
                            </div>
                            <div style="font-size:12px;color:#cbd5e1;margin-top:2px">
                                <?php $__currentLoopData = $unscheduledSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unSub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span style="display:inline-block;margin-right:12px"><?php echo e($unSub->subject_name); ?> (No datesheet created)</span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php if(auth()->user()->isAdministration()): ?>
                            <button type="button" class="btn-action btn-outline" onclick="quickCreateSubjectExam(<?php echo e($cs->id); ?>)" style="font-size:11px;border-color:rgba(245, 158, 11, 0.4);color:#f59e0b">
                                + Schedule Missing Papers
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                
                <?php if(auth()->user()->isAdministration() && $exams->isNotEmpty()): ?>
                    <div style="background:#1e293b;border:1px solid #334155;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="no-print">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#f8fafc;text-transform:uppercase;letter-spacing:0.05em">
                                Class Datesheet Portal Visibility Actions
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px">
                                Turn ON/OFF datesheet visibility for all subjects of <?php echo e($cName); ?> at once on Teacher and Student portals.
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <form method="POST" action="<?php echo e(route('lms.datesheet.toggleVisibility', $cs->id)); ?>" style="margin:0">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="target" value="teacher">
                                <button type="submit" class="btn-action <?php echo e($classPubTeacher ? 'btn-danger-custom' : 'btn-primary-custom'); ?>" style="font-size:11px;padding:6px 14px">
                                    <?php echo e($classPubTeacher ? 'Turn OFF Teacher Portal Visibility' : 'Turn ON Teacher Portal Visibility'); ?>

                                </button>
                            </form>

                            <form method="POST" action="<?php echo e(route('lms.datesheet.toggleVisibility', $cs->id)); ?>" style="margin:0">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="target" value="student">
                                <button type="submit" class="btn-action <?php echo e($classPubStudent ? 'btn-danger-custom' : 'btn-primary-custom'); ?>" style="font-size:11px;padding:6px 14px">
                                    <?php echo e($classPubStudent ? 'Turn OFF Student Portal Visibility' : 'Turn ON Student Portal Visibility'); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if($exams->isNotEmpty()): ?>
                    <table class="datesheet-table">
                        <thead>
                            <tr>
                                <th style="width:22%">Subject</th>
                                <th style="width:28%">Date &amp; Session</th>
                                <th style="width:16%">Room</th>
                                <th style="width:18%">Title / Marks</th>
                                <th style="width:16%">Deadline</th>
                                <?php if(auth()->user()->isAdministration()): ?>
                                    <th class="no-print" style="width:10%;text-align:right">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $startDate = $exam->start_time ? $exam->start_time->format('D, M d, Y') : '—';
                                    $rawExamDate = $exam->start_time ? $exam->start_time->format('Y-m-d') : '';
                                    $rawStartTime = $exam->start_time ? $exam->start_time->format('H:i') : '09:00';
                                    $rawEndTime = $exam->end_time ? $exam->end_time->format('H:i') : '11:30';
                                    $rawDeadline = $exam->result_deadline ? $exam->result_deadline->format('Y-m-d') : '';
                                    $startTime = $exam->start_time ? $exam->start_time->format('h:i A') : '—';
                                    $endTime = $exam->end_time ? $exam->end_time->format('h:i A') : '—';
                                    $resultDeadline = $exam->result_deadline ? $exam->result_deadline->format('M d, Y') : '—';
                                    $rawRoomStr = trim($exam->room ?: 'Unassigned');
                                    $roomName = preg_replace('/^(room\s*)+/i', 'Room ', $rawRoomStr);
                                    $pubTeacher = $exam->is_published_teacher ?? true;
                                    $pubStudent = $exam->is_published_student ?? true;
                                    
                                    $startHour = $exam->start_time ? (int)$exam->start_time->format('H') : 9;
                                    $sessionLabel = $startHour < 12 ? 'Morning Session' : ($startHour < 15 ? 'Afternoon Session' : 'Evening Session');
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700;color:#f8fafc"><?php echo e($exam->subject->subject_name ?? 'Subject'); ?></div>
                                        <div style="font-size:11px;color:#64748b"><?php echo e($exam->subject->subject_code ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#f8fafc"><?php echo e($startDate); ?></div>
                                        <div style="font-size:11px;color:#818cf8"><?php echo e($startTime); ?> - <?php echo e($endTime); ?></div>
                                        <div style="font-size:10px;color:#64748b"><?php echo e($sessionLabel); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size:11px;font-weight:600;color:#cbd5e1;background:#1e293b;padding:3px 8px;border-radius:4px">
                                            <?php echo e($roomName); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#f8fafc"><?php echo e($exam->title); ?></div>
                                        <div style="font-size:11px;color:#64748b"><?php echo e($exam->total_marks); ?> Marks</div>
                                    </td>
                                    <td>
                                        <div style="font-size:11px;color:#f59e0b"><?php echo e($resultDeadline); ?></div>
                                    </td>
                                    <?php if(auth()->user()->isAdministration()): ?>
                                        <td class="no-print" style="text-align:right">
                                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                                <button type="button" class="btn-action btn-outline" onclick="openEditModal(<?php echo e($exam->id); ?>, '<?php echo e(addslashes($exam->title)); ?>', '<?php echo e($exam->type); ?>', <?php echo e($exam->academic_term_id ?? 'null'); ?>, '<?php echo e($rawExamDate); ?>', '<?php echo e($rawStartTime); ?>', '<?php echo e($rawEndTime); ?>', <?php echo e($exam->total_marks); ?>, '<?php echo e($rawDeadline); ?>', '<?php echo e(addslashes($exam->instructions ?? '')); ?>', '<?php echo e(addslashes($roomName)); ?>', <?php echo e($pubTeacher ? 1 : 0); ?>, <?php echo e($pubStudent ? 1 : 0); ?>)" style="padding:3px 8px;font-size:11px">
                                                    Edit
                                                </button>
                                                <form method="POST" action="<?php echo e(route('lms.datesheet.destroy', $exam->id)); ?>" onsubmit="return confirm('Remove this exam paper?')" style="margin:0">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn-action btn-danger-custom" style="padding:3px 8px;font-size:11px">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align:center;padding:24px;color:#64748b;font-size:13px">
                        No exam papers scheduled for <?php echo e($cName); ?> yet.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card" style="text-align:center;padding:48px 20px;background:#0f172a;border:1px solid #1e293b;border-radius:12px">
            <h3 style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;color:#f8fafc;margin-bottom:6px">
                No Examination Schedules Found
            </h3>
            <p style="color:#64748b;font-size:13px;max-width:400px;margin:0 auto 16px">
                No active exam datesheets have been scheduled yet.
            </p>
            <?php if(auth()->user()->isAdministration()): ?>
                <button class="btn-action btn-primary-custom" onclick="openModal('create-datesheet-modal')">
                    Create Exam Schedule
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


<?php if(auth()->user()->isAdministration()): ?>
    <div class="modal-backdrop" id="create-datesheet-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
        <div style="background:#0f172a;border:1px solid #334155;border-radius:14px;width:100%;max-width:720px;box-shadow:0 25px 50px rgba(0,0,0,0.5);display:flex;flex-direction:column;max-height:85vh;overflow:hidden">
            <div style="background:#1e293b;padding:16px 20px;border-bottom:1px solid #334155;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;color:#f8fafc;margin:0">
                        Create Exam Schedule
                    </h3>
                    <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">Select title and target class sections.</p>
                </div>
                <button onclick="closeModal('create-datesheet-modal')" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer">✕</button>
            </div>

            <div style="padding:20px;overflow-y:auto;flex:1">
                <div style="margin-bottom:16px">
                    <label style="font-size:11px;font-weight:700;color:#94a3b8;margin-bottom:4px;display:block;text-transform:uppercase;letter-spacing:0.05em">Exam Title / Type *</label>
                    <input type="text" id="modal-exam-name" required value="Midterm Examination" style="width:100%;padding:9px 12px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600;font-size:13px">
                </div>

                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <label style="font-size:12px;font-weight:700;color:#f8fafc">Select Target Classes:</label>
                        <button type="button" class="btn-action btn-outline" onclick="toggleAllClassCheckboxes(this)" style="font-size:11px;padding:3px 8px">
                            Select All
                        </button>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:8px;padding:12px;background:#1e293b;border:1px solid #334155;border-radius:8px">
                        <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $cName = $cs->instituteClass?->custom_name ?? 'Class';
                            ?>
                            <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:#f8fafc;cursor:pointer;padding:8px;border-radius:6px;background:#0f172a;border:1px solid #334155">
                                <input type="checkbox" id="class-chk-<?php echo e($cs->id); ?>" value="<?php echo e($cs->id); ?>" data-class-name="<?php echo e($cName); ?> - <?php echo e($cs->section_name); ?>" class="class-builder-checkbox" style="accent-color:#6366f1">
                                <div>
                                    <div style="font-weight:600"><?php echo e($cName); ?> - <?php echo e($cs->section_name); ?></div>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div style="background:#1e293b;padding:14px 20px;border-top:1px solid #334155;display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn-action btn-outline" onclick="closeModal('create-datesheet-modal')">Cancel</button>
                <button type="button" class="btn-action btn-primary-custom" onclick="saveClassSelectionAndOpenWorkspace()">
                    Open Schedule Builder
                </button>
            </div>
        </div>
    </div>

    
    <div class="modal-backdrop" id="edit-datesheet-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.85);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:20px">
        <div style="background:#0f172a;border:1px solid #334155;border-radius:14px;width:100%;max-width:540px;padding:20px;box-shadow:0 20px 40px rgba(0,0,0,0.5)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #334155">
                <h3 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:16px;color:#f8fafc;margin:0">
                    Edit Schedule Entry
                </h3>
                <button onclick="closeModal('edit-datesheet-modal')" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer">✕</button>
            </div>

            <form id="edit-datesheet-form" method="POST" action="">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" id="edit-term-id" name="academic_term_id" value="">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="grid-column:1/-1">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Exam Title *</label>
                        <input type="text" id="edit-title" name="title" required style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Exam Date *</label>
                        <input type="date" id="edit-exam-date" name="exam_date" required style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Room *</label>
                        <select id="edit-room" name="room" style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600">
                            <?php echo $roomOptionsHtml; ?>

                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Start Time *</label>
                        <input type="time" id="edit-start-time" name="start_time" required style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">End Time *</label>
                        <input type="time" id="edit-end-time" name="end_time" required style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Total Marks *</label>
                        <input type="number" id="edit-total-marks" name="total_marks" required min="1" max="1000" style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px;font-weight:600;color:#94a3b8;margin-bottom:3px;display:block">Result Deadline</label>
                        <input type="date" id="edit-result-deadline" name="result_deadline" style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;background:#1e293b;padding:10px;border-radius:6px">
                        <label style="font-size:11px;font-weight:700;color:#f8fafc;margin-bottom:6px;display:block">Portal Visibility</label>
                        <div style="display:flex;gap:16px">
                            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:12px;color:#cbd5e1">
                                <input type="hidden" name="is_published_teacher" value="0">
                                <input type="checkbox" id="edit-pub-teacher" name="is_published_teacher" value="1" style="accent-color:#6366f1">
                                <span>Teacher Portal</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:12px;color:#cbd5e1">
                                <input type="hidden" name="is_published_student" value="0">
                                <input type="checkbox" id="edit-pub-student" name="is_published_student" value="1" style="accent-color:#6366f1">
                                <span>Student Portal</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                    <button type="button" class="btn-action btn-outline" onclick="closeModal('edit-datesheet-modal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary-custom">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleClassBody(sectionId) {
        const body = document.getElementById(`class-card-body-${sectionId}`);
        const arrow = document.getElementById(`accordion-arrow-${sectionId}`);
        if (body.style.display === 'none' || !body.style.display) {
            body.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            body.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function printWholeInstitute() {
        document.body.classList.remove('printing-single-class');
        document.querySelectorAll('.class-card-body').forEach(b => b.style.display = 'block');
        window.print();
    }

    function printClassDatesheet(sectionId) {
        document.body.classList.add('printing-single-class');
        document.querySelectorAll('.class-datesheet-card').forEach(c => c.classList.remove('active-print-target'));
        const targetCard = document.getElementById(`class-card-display-${sectionId}`);
        const targetBody = document.getElementById(`class-card-body-${sectionId}`);
        if (targetCard) targetCard.classList.add('active-print-target');
        if (targetBody) targetBody.style.display = 'block';
        window.print();
        document.body.classList.remove('printing-single-class');
    }

    function quickCreateSubjectExam(sectionId) {
        const chk = document.getElementById(`class-chk-${sectionId}`);
        if (chk) chk.checked = true;
        saveClassSelectionAndOpenWorkspace();
        selectClassTab(sectionId);
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function closeWorkspace() { document.getElementById('datesheet-builder-workspace').style.display = 'none'; }

    function openEditModal(id, title, type, termId, examDate, startTime, endTime, totalMarks, resultDeadline, instructions, room, pubTeacher, pubStudent) {
        const form = document.getElementById('edit-datesheet-form');
        form.action = "<?php echo e(url('/lms/datesheet')); ?>/" + id;
        document.getElementById('edit-title').value = title;
        if (termId) document.getElementById('edit-term-id').value = termId;
        document.getElementById('edit-exam-date').value = examDate;
        document.getElementById('edit-start-time').value = startTime;
        document.getElementById('edit-end-time').value = endTime;
        document.getElementById('edit-total-marks').value = totalMarks;
        document.getElementById('edit-result-deadline').value = resultDeadline;
        document.getElementById('edit-room').value = room || '';
        document.getElementById('edit-pub-teacher').checked = !!pubTeacher;
        document.getElementById('edit-pub-student').checked = !!pubStudent;
        openModal('edit-datesheet-modal');
    }

    function toggleAllClassCheckboxes(btn) {
        const checkboxes = document.querySelectorAll('.class-builder-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        btn.textContent = !allChecked ? 'Deselect All' : 'Select All';
    }

    function saveClassSelectionAndOpenWorkspace() {
        const examNameVal = document.getElementById('modal-exam-name').value.trim() || 'Midterm Examination';
        document.getElementById('ws-form-title').value = examNameVal;
        document.getElementById('ws-form-type').value = examNameVal;
        document.getElementById('ws-exam-title-display').textContent = examNameVal;

        const checkedBoxes = document.querySelectorAll('.class-builder-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one class checkbox.');
            return;
        }

        const tabsContainer = document.getElementById('class-tabs-container');
        tabsContainer.innerHTML = '';
        document.querySelectorAll('.class-schedule-block').forEach(b => b.style.display = 'none');

        let firstSectionId = null;
        checkedBoxes.forEach((cb, idx) => {
            const sectionId = cb.value;
            const className = cb.getAttribute('data-class-name') || `Class #${sectionId}`;
            if (idx === 0) firstSectionId = sectionId;

            const tabBtn = document.createElement('button');
            tabBtn.type = 'button';
            tabBtn.id = `class-tab-btn-${sectionId}`;
            tabBtn.className = 'class-nav-tab btn-action btn-outline';
            tabBtn.style.fontSize = '12px';
            tabBtn.innerHTML = className;
            tabBtn.onclick = () => selectClassTab(sectionId);
            tabsContainer.appendChild(tabBtn);
        });

        closeModal('create-datesheet-modal');
        const workspace = document.getElementById('datesheet-builder-workspace');
        workspace.style.display = 'block';

        if (firstSectionId) selectClassTab(firstSectionId);
        workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function selectClassTab(sectionId) {
        document.querySelectorAll('.class-nav-tab').forEach(tab => {
            tab.style.background = 'transparent';
            tab.style.borderColor = '#334155';
            tab.style.color = '#cbd5e1';
        });
        const activeTab = document.getElementById(`class-tab-btn-${sectionId}`);
        if (activeTab) {
            activeTab.style.background = '#6366f1';
            activeTab.style.borderColor = '#6366f1';
            activeTab.style.color = '#ffffff';
        }
        document.querySelectorAll('.class-schedule-block').forEach(b => b.style.display = 'none');
        const activeBlock = document.getElementById(`class-block-${sectionId}`);
        if (activeBlock) activeBlock.style.display = 'block';
    }

    function autoFillDatesForClass(sectionId) {
        const startDateInput = document.getElementById(`auto-date-${sectionId}`);
        if (!startDateInput || !startDateInput.value) return;
        let currDate = new Date(startDateInput.value);
        const examDateInputs = document.querySelectorAll(`.cs-exam-date-${sectionId}`);
        const resDateInputs = document.querySelectorAll(`.cs-res-date-${sectionId}`);
        
        examDateInputs.forEach((inp, idx) => {
            if (currDate.getDay() === 0) currDate.setDate(currDate.getDate() + 1);
            let dateStr = currDate.toISOString().split('T')[0];
            inp.value = dateStr;
            if (resDateInputs[idx]) {
                let deadline = new Date(currDate);
                deadline.setDate(deadline.getDate() + 7);
                resDateInputs[idx].value = deadline.toISOString().split('T')[0];
            }
            currDate.setDate(currDate.getDate() + 1);
        });
    }

    function addPaperRowForClass(sectionId) {
        const container = document.getElementById(`paper-rows-container-${sectionId}`);
        if (!container) return;
        const firstSelect = container.querySelector('select');
        let optionsHtml = firstSelect ? firstSelect.innerHTML : '<option value="">Select Subject</option>';
        const roomSelect = container.querySelector('.cs-room-select-' + sectionId);
        let roomOptionsHtml = roomSelect ? roomSelect.innerHTML : '<option value="" selected>-- Select Room / Hall --</option>';

        const idx = container.children.length;
        let targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + idx);
        if (targetDate.getDay() === 0) targetDate.setDate(targetDate.getDate() + 1);
        
        const dateStr = targetDate.toISOString().split('T')[0];
        const deadlineDate = new Date(targetDate);
        deadlineDate.setDate(deadlineDate.getDate() + 7);
        const deadlineStr = deadlineDate.toISOString().split('T')[0];

        const rowDiv = document.createElement('div');
        rowDiv.className = `paper-row-item-${sectionId}`;
        rowDiv.style.cssText = 'background:#0f172a;border:1px solid #334155;border-radius:8px;padding:12px';
        rowDiv.innerHTML = `
            <div style="display:grid;grid-template-columns:2.5fr 1.5fr 2fr 1fr 30px;gap:10px;align-items:center;margin-bottom:8px">
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">SUBJECT</label>
                    <select name="class_schedules[${sectionId}][${idx}][subject_id]" style="width:100%;padding:7px 10px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#ffffff;font-weight:600;font-size:12px">
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">EXAM DATE</label>
                    <input type="date" name="class_schedules[${sectionId}][${idx}][exam_date]" class="cs-exam-date-${sectionId}" value="${dateStr}" style="width:100%;padding:7px 8px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600;font-size:12px">
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">TIME SLOT</label>
                    <div style="display:flex;gap:4px">
                        <input type="time" name="class_schedules[${sectionId}][${idx}][start_time]" value="09:00" style="width:50%;padding:6px 4px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                        <input type="time" name="class_schedules[${sectionId}][${idx}][end_time]" value="11:30" style="width:50%;padding:6px 4px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                    </div>
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;display:block;margin-bottom:2px">MARKS</label>
                    <input type="number" name="class_schedules[${sectionId}][${idx}][total_marks]" value="100" min="1" max="1000" style="width:100%;padding:7px 8px;background:#1e293b;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-weight:600;font-size:12px">
                </div>
                <div style="text-align:center;padding-top:14px">
                    <button type="button" onclick="this.closest('.paper-row-item-${sectionId}').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px">✕</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:center;padding:8px 10px;background:#1e293b;border-radius:6px">
                <div style="display:flex;align-items:center;gap:6px">
                    <label style="font-size:10px;font-weight:700;color:#94a3b8">ROOM:</label>
                    <select name="class_schedules[${sectionId}][${idx}][room]" class="cs-room-select-${sectionId}" style="width:100%;padding:5px 8px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#ffffff;font-size:11px">
                        ${roomOptionsHtml}
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <label style="font-size:10px;font-weight:700;color:#94a3b8">DEADLINE:</label>
                    <input type="date" name="class_schedules[${sectionId}][${idx}][result_deadline]" class="cs-res-date-${sectionId}" value="${deadlineStr}" style="width:100%;padding:5px 6px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f8fafc;font-size:11px">
                </div>
            </div>
        `;
        container.appendChild(rowDiv);
    }

    function validateDatesheetForm(event) {
        const workspace = document.getElementById('datesheet-builder-workspace');
        if (!workspace || workspace.style.display === 'none') return true;

        const classBlocks = workspace.querySelectorAll('.class-schedule-block');

        for (let block of classBlocks) {
            const classTitleEl = block.querySelector('h4');
            const className = classTitleEl ? classTitleEl.textContent.trim() : 'Class';
            const rows = block.querySelectorAll('[class^="paper-row-item-"]');

            const paperData = [];
            rows.forEach(row => {
                const subSel = row.querySelector('select[name*="[subject_id]"]');
                const dateInp = row.querySelector('input[name*="[exam_date]"]');
                const startInp = row.querySelector('input[name*="[start_time]"]');
                const endInp = row.querySelector('input[name*="[end_time]"]');

                if (subSel && dateInp && startInp && endInp && dateInp.value) {
                    const subText = subSel.options[subSel.selectedIndex] ? subSel.options[subSel.selectedIndex].text : 'Subject';
                    paperData.push({
                        subject: subText,
                        date: dateInp.value,
                        startTime: startInp.value,
                        endTime: endInp.value
                    });
                }
            });

            const dateGroups = {};
            paperData.forEach(p => {
                if (!dateGroups[p.date]) dateGroups[p.date] = [];
                dateGroups[p.date].push(p);
            });

            for (let dStr in dateGroups) {
                const group = dateGroups[dStr];
                if (group.length > 1) {
                    for (let i = 0; i < group.length; i++) {
                        for (let j = i + 1; j < group.length; j++) {
                            const p1 = group[i];
                            const p2 = group[j];
                            if (p1.startTime < p2.endTime && p2.startTime < p1.endTime) {
                                alert(`Schedule Conflict Error!\n\nFor ${className} on ${dStr}:\n• "${p1.subject}" (${p1.startTime} - ${p1.endTime})\n• "${p2.subject}" (${p2.startTime} - ${p2.endTime})\n\nExams scheduled on the same day must have non-overlapping timings.`);
                                event.preventDefault();
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\datesheet\index.blade.php ENDPATH**/ ?>
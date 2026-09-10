<?php $__env->startSection('title', 'Student Dashboard'); ?>
<?php $__env->startSection('page-header', 'Student Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* =========================================================================
       STUDENT DASHBOARD: INK & AMBER DESIGN SYSTEM
       ========================================================================= */
    .student-dash-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 100%;
        margin: 0 auto;
    }

    /* 1. Welcome & Identity Header Bar */
    .dash-welcome-strip {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 14px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: none;
        position: relative;
    }

    .dash-welcome-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .dash-avatar {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #17191C;
        color: #F0B45D;
        border: 1px solid #2A2C30;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Manrope', sans-serif;
        font-size: 19px;
        font-weight: 800;
        box-shadow: none;
        flex-shrink: 0;
    }

    .dash-welcome-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dash-welcome-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dash-welcome-heading {
        font-family: 'Manrope', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: #1B1A17;
        letter-spacing: -0.4px;
        margin: 0;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-pill-active {
        background: #E3EFE2;
        color: #2E6E42;
        border: 1px solid #C7DEC5;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #2E6E42;
    }

    .dash-meta-pills {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .meta-pill {
        font-size: 12px;
        color: #68665D;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .meta-pill strong {
        color: #1B1A17;
        font-weight: 700;
    }

    .dash-welcome-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dash-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        border-radius: 8px;
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        font-size: 12px;
        font-weight: 700;
        color: #1B1A17;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .dash-btn-link:hover {
        background: #EFEEEA;
        color: #1B1A17;
        border-color: #D5D2C7;
    }

    .dash-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 15px;
        border-radius: 8px;
        background: #D48A2E;
        border: 1px solid #C07A22;
        font-size: 12px;
        font-weight: 800;
        color: #1A1200;
        text-decoration: none;
        box-shadow: none;
        transition: all 0.2s ease;
    }

    .dash-btn-primary:hover {
        background: #C07A22;
        color: #1A1200;
    }

    /* 2. Top 4-Metric Grid (Balanced, Symmetrical, Aligned Footers) */
    .metrics-top-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    @media (max-width: 1200px) {
        .metrics-top-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .metrics-top-grid { grid-template-columns: 1fr; }
    }

    .metric-box {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 12px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 175px;
        box-shadow: none;
        transition: border-color 0.2s ease;
    }

    .metric-box:hover {
        border-color: #D48A2E;
    }

    .metric-box-unpaid {
        background: #F6E4E1 !important;
        border: 1px solid #EAC8C1 !important;
    }

    .metric-box-paid {
        background: #E3EFE2 !important;
        border: 1px solid #C7DEC5 !important;
    }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .metric-tag {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #68665D;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .metric-body {
        margin: 10px 0;
    }

    .metric-value {
        font-family: 'Manrope', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: #1B1A17;
        line-height: 1.1;
    }

    .metric-subtext {
        font-size: 12px;
        color: #68665D;
        font-weight: 500;
        margin-top: 4px;
    }

    .metric-footer-btn {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 7px 11px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .footer-btn-unpaid {
        background: #A2412C;
        color: #ffffff;
    }
    .footer-btn-unpaid:hover { background: #8E3420; }

    .footer-btn-paid {
        background: #2E6E42;
        color: #ffffff;
    }
    .footer-btn-paid:hover { background: #255835; }

    .footer-btn-secondary {
        background: #EFEEEA;
        color: #1B1A17;
        border: 1px solid #E1DFD7;
    }
    .footer-btn-secondary:hover {
        background: #E5E3DC;
        color: #1B1A17;
    }

    .footer-btn-accent {
        background: #F8E9D3;
        color: #8A5A10;
        border: 1px solid #E8CEAA;
    }
    .footer-btn-accent:hover {
        background: #EED8B9;
        color: #8A5A10;
    }

    /* 3. Main 2-Column Workspace Grid */
    .dash-workspace-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    @media (max-width: 1100px) {
        .dash-workspace-grid { grid-template-columns: 1fr; }
    }

    .panel-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 14px;
        padding: 22px;
        box-shadow: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 18px;
    }

    .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px solid #E1DFD7;
    }

    .panel-title {
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #1B1A17;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    /* Modern Subject Table List */
    .course-table-header {
        display: grid;
        grid-template-columns: 1.4fr 1.6fr 0.9fr;
        gap: 10px;
        padding: 8px 12px;
        background: #EFEEEA;
        border: 1px solid #E1DFD7;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        color: #68665D;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .course-row {
        display: grid;
        grid-template-columns: 1.4fr 1.6fr 0.9fr;
        gap: 10px;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #EAE8E1;
        transition: all 0.2s ease;
        border-radius: 8px;
    }

    .course-row:last-child {
        border-bottom: none;
    }

    .course-row:hover {
        background: #F2EFEB;
    }

    .course-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .course-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .course-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #1B1A17;
        line-height: 1.2;
    }

    .course-code {
        font-size: 11.5px;
        color: #68665D;
        font-weight: 500;
        margin-top: 2px;
    }

    .course-progress-cell {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding-right: 8px;
    }

    .course-progress-text {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        color: #68665D;
        font-weight: 600;
    }

    .course-bar-track {
        width: 100%;
        height: 6px;
        background: #E1DFD7;
        border-radius: 999px;
        overflow: hidden;
    }

    .course-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.8s ease;
    }

    .course-status-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    /* Timetable Slot Row */
    .timetable-slot-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background: #F2EFEB;
        border: 1px solid #E1DFD7;
        border-radius: 10px;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .timetable-slot-row:hover {
        background: #EAE8E1;
    }

    .slot-timing-badge {
        font-size: 11.5px;
        font-weight: 700;
        background: #F8E9D3;
        color: #8A5A10;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #E8CEAA;
        white-space: nowrap;
    }

    /* Deadlines List Item */
    .deadline-item {
        padding: 12px 14px;
        background: #F2EFEB;
        border: 1px solid #E1DFD7;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .deadline-item:hover {
        background: #EAE8E1;
    }

    /* Circular Donut Gauge */
    .circular-chart {
        display: block;
        margin: 8px auto;
        max-width: 150px;
        max-height: 150px;
    }
    .circle-bg { fill: none; stroke: #E1DFD7; stroke-width: 3.2; }
    .circle-progress { fill: none; stroke-width: 3.2; stroke-linecap: round; transition: stroke-dasharray 1s ease; }
    .circle-percentage { fill: #1B1A17; font-family: 'Manrope', sans-serif; font-size: 0.52em; font-weight: 800; text-anchor: middle; }
    .circle-sublabel { fill: #68665D; font-family: 'Inter', sans-serif; font-size: 0.19em; font-weight: 700; text-anchor: middle; }
</style>

<div class="student-dash-wrapper">

    <!-- 1. INTEGRATED HERO IDENTITY BAR -->
    <div class="dash-welcome-strip">
        <div class="dash-welcome-left">
            <div class="dash-avatar">
                <?php echo e(strtoupper(substr($student->first_name ?? auth()->user()->name ?? 'S', 0, 1))); ?>

            </div>
            <div class="dash-welcome-text">
                <div class="dash-welcome-title-row">
                    <h1 class="dash-welcome-heading">
                        Welcome back, <?php echo e($student->first_name ?: auth()->user()->first_name); ?>

                    </h1>
                    <span class="status-pill status-pill-active">
                        <span class="status-dot"></span> Active Student
                    </span>
                </div>
                <div class="dash-meta-pills">
                    <span class="meta-pill">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'id-card','class' => 'w-3.5 h-3.5 text-neutral-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'id-card','class' => 'w-3.5 h-3.5 text-neutral-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <span>Roll: <strong><?php echo e($student->roll_number); ?></strong></span>
                    </span>
                    <span class="meta-pill">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'school','class' => 'w-3.5 h-3.5 text-neutral-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'school','class' => 'w-3.5 h-3.5 text-neutral-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <span>Class: <strong><?php echo e($student->classSection?->instituteClass?->class_name ?? 'Class 10'); ?> (<?php echo e($student->classSection?->section_name ?? 'Sec A'); ?>)</strong></span>
                    </span>
                    <span class="meta-pill">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'calendar-days','class' => 'w-3.5 h-3.5 text-neutral-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'w-3.5 h-3.5 text-neutral-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <span>Session: <strong><?php echo e($student->academicTerm?->name ?? 'Spring 2026'); ?></strong></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="dash-welcome-actions">
            <a href="<?php echo e(route('student.timetable')); ?>" class="dash-btn-link">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'calendar-days','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Timetable
            </a>
            <a href="<?php echo e(route('student.invoices')); ?>" class="dash-btn-link">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'receipt','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'receipt','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Invoices
            </a>
            <a href="<?php echo e(route('student.lms')); ?>" class="dash-btn-primary">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'graduation-cap','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'graduation-cap','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> LMS Portal
            </a>
        </div>
    </div>

    <!-- 2. TOP METRIC STRIP (4 SYMMETRICAL CARDS) -->
    <div class="metrics-top-grid">
        
        <!-- CARD 1: FEE ACCOUNT STANDING -->
        <?php if($hasPendingFee): ?>
            <div class="metric-box metric-box-unpaid">
                <div class="metric-header">
                    <span class="metric-tag" style="color:#A2412C"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'credit-card','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'credit-card','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Fee &amp; Dues</span>
                    <span style="font-size:11px;font-weight:800;background:#F6E4E1;color:#A2412C;border:1px solid #EAC8C1;padding:2px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => ''.e($isOverdue ? 'triangle-exclamation' : 'hourglass-half').'','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($isOverdue ? 'triangle-exclamation' : 'hourglass-half').'','class' => 'w-3 h-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <?php echo e($daysRemainingText); ?>

                    </span>
                </div>

                <div class="metric-body">
                    <div class="metric-value" style="color:#A2412C">
                        PKR <?php echo e(number_format($totalPendingAmount)); ?>

                    </div>
                    <div class="metric-subtext" style="color:#A2412C">
                        Due Date: <strong><?php echo e($formattedDueDate); ?></strong>
                    </div>
                </div>

                <a href="<?php echo e(route('student.invoices')); ?>" class="metric-footer-btn footer-btn-unpaid">
                    <span>View &amp; Pay Voucher</span>
                    <span>&rarr;</span>
                </a>
            </div>
        <?php else: ?>
            <div class="metric-box metric-box-paid">
                <div class="metric-header">
                    <span class="metric-tag" style="color:#2E6E42"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'credit-card','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'credit-card','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Fee Account</span>
                    <span style="font-size:11px;font-weight:800;background:#E3EFE2;color:#2E6E42;border:1px solid #C7DEC5;padding:2px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'w-3 h-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> All Cleared
                    </span>
                </div>

                <div class="metric-body">
                    <div class="metric-value" style="color:#2E6E42">
                        PKR 0.00
                    </div>
                    <div class="metric-subtext" style="color:#2E6E42">
                        Account in good standing
                    </div>
                </div>

                <a href="<?php echo e(route('student.invoices')); ?>" class="metric-footer-btn footer-btn-paid">
                    <span>Payment History</span>
                    <span>&rarr;</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- CARD 2: OVERALL ATTENDANCE % -->
        <div class="metric-box">
            <div class="metric-header">
                <span class="metric-tag"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-trend-up','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-trend-up','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Attendance</span>
                <span class="badge" style="font-size:10.5px;font-weight:800;padding:2px 8px;border-radius:6px;border:1px solid;background:#E3EFE2;color:#2E6E42;border-color:#C7DEC5">
                    <?php echo e($gaugeStatus ?? 'Good'); ?>

                </span>
            </div>

            <div class="metric-body">
                <div class="metric-value" style="color:<?php echo e((int)$overallAttendancePct < 75 ? '#A2412C' : '#2E6E42'); ?>">
                    <?php echo e((int)$overallAttendancePct); ?>%
                </div>
                <div class="metric-subtext">
                    <?php echo e($presentDays); ?> Present of <?php echo e($totalDays); ?> Total Days
                </div>
            </div>

            <a href="<?php echo e(route('student.attendance')); ?>" class="metric-footer-btn footer-btn-secondary">
                <span>Detailed Record</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- CARD 3: TODAY'S LECTURES -->
        <div class="metric-box">
            <div class="metric-header">
                <span class="metric-tag"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'calendar-days','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Today's Classes</span>
                <span style="font-size:10.5px;font-weight:800;color:#8A5A10;background:#F8E9D3;padding:2px 8px;border-radius:6px;border:1px solid #E8CEAA">
                    <?php echo e(ucfirst($todayDay)); ?>

                </span>
            </div>

            <div class="metric-body">
                <div class="metric-value">
                    <?php echo e($todaySlots->count() > 0 ? $todaySlots->count() : 3); ?> <span style="font-size:14px;color:#68665D;font-weight:600">Lectures</span>
                </div>
                <div class="metric-subtext">
                    Starts at <strong><?php echo e($todaySlots->first()?->start_time ? substr($todaySlots->first()->start_time, 0, 5) : '08:00 AM'); ?></strong>
                </div>
            </div>

            <a href="<?php echo e(route('student.timetable')); ?>" class="metric-footer-btn footer-btn-secondary">
                <span>View Class Timetable</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- CARD 4: LMS & AI ASSISTANT -->
        <div class="metric-box">
            <div class="metric-header">
                <span class="metric-tag"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sparkles','class' => 'w-3.5 h-3.5 text-amber-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'w-3.5 h-3.5 text-amber-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> LMS &amp; AI</span>
                <span style="font-size:10.5px;font-weight:800;color:#8A5A10;background:#F8E9D3;padding:2px 8px;border-radius:6px;border:1px solid #E8CEAA">
                    Studio Ready
                </span>
            </div>

            <div class="metric-body">
                <div class="metric-value">
                    <?php echo e($upcomingAssessments->count() > 0 ? $upcomingAssessments->count() : 1); ?> <span style="font-size:14px;color:#68665D;font-weight:600">Due Soon</span>
                </div>
                <div class="metric-subtext">
                    Practice tests &amp; AI assistant ready
                </div>
            </div>

            <?php if(Route::has('lms.practice-test.index')): ?>
                <a href="<?php echo e(route('lms.practice-test.index')); ?>" class="metric-footer-btn footer-btn-accent">
                    <span>Open AI Practice Studio</span>
                    <span>&rarr;</span>
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('student.lms')); ?>" class="metric-footer-btn footer-btn-accent">
                    <span>Open Learning Suite</span>
                    <span>&rarr;</span>
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- 3. BALANCED 2-COLUMN MAIN WORKSPACE -->
    <div class="dash-workspace-grid">
        
        <!-- LEFT COLUMN: COURSE ATTENDANCE & PERFORMANCE DATA GRID -->
        <div class="panel-card">
            <div>
                <div class="panel-header">
                    <div class="panel-title">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'book-open','class' => 'w-4 h-4 text-amber-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'book-open','class' => 'w-4 h-4 text-amber-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <span>Course Catalog &amp; Attendance</span>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="font-size:11.5px;color:#68665D;font-weight:600">
                            Min. Required: <strong style="color:#1B1A17"><?php echo e((int)$minAttendancePct); ?>%</strong>
                        </span>
                        <a href="<?php echo e(route('student.attendance')); ?>" style="font-size:12px;font-weight:800;color:#8A5A10;text-decoration:none">
                            Full History &rarr;
                        </a>
                    </div>
                </div>

                <!-- MODE A: SUBJECT-WISE ATTENDANCE BARS -->
                <?php if($attendanceMode === 'subject'): ?>
                    <!-- Table Header Strip -->
                    <div class="course-table-header" style="margin-top:12px;margin-bottom:6px">
                        <span>Course &amp; Code</span>
                        <span>Lectures &amp; Progress</span>
                        <span style="text-align:right">Standing</span>
                    </div>

                    <!-- Course Data Rows -->
                    <div style="display:flex;flex-direction:column">
                        <?php $__empty_1 = true; $__currentLoopData = $subjectAttendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $badges = [
                                    ['bg' => '#F8E9D3', 'color' => '#8A5A10', 'icon' => 'book-open'],
                                    ['bg' => '#E3EFE2', 'color' => '#2E6E42', 'icon' => 'shapes'],
                                    ['bg' => '#E7ECF6', 'color' => '#3A529C', 'icon' => 'flask'],
                                    ['bg' => '#F2EFEB', 'color' => '#1B1A17', 'icon' => 'laptop-code'],
                                ];
                                $iconStyle = $badges[$index % count($badges)];
                                $barColor = $sub['percentage'] >= 85 ? '#2E6E42' : ($sub['percentage'] >= 75 ? '#D48A2E' : '#A2412C');
                            ?>
                            <div class="course-row">
                                <!-- Col 1: Subject Name & Code -->
                                <div class="course-info">
                                    <div class="course-icon-badge" style="background:<?php echo e($iconStyle['bg']); ?>;color:<?php echo e($iconStyle['color']); ?>">
                                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => ''.e($iconStyle['icon']).'','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($iconStyle['icon']).'','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="course-title"><?php echo e($sub['name']); ?></div>
                                        <div class="course-code">Code: <strong><?php echo e($sub['code']); ?></strong></div>
                                    </div>
                                </div>

                                <!-- Col 2: Mini Progress Bar + Lectures Attended -->
                                <div class="course-progress-cell">
                                    <div class="course-progress-text">
                                        <span><?php echo e($sub['presents']); ?> of <?php echo e($sub['total']); ?> Lectures</span>
                                        <strong style="color:<?php echo e($barColor); ?>"><?php echo e($sub['percentage']); ?>%</strong>
                                    </div>
                                    <div class="course-bar-track">
                                        <div class="course-bar-fill" style="width:<?php echo e(min(100, $sub['percentage'])); ?>%;background:<?php echo e($barColor); ?>"></div>
                                    </div>
                                </div>

                                <!-- Col 3: Status Badge -->
                                <div class="course-status-cell">
                                    <span class="badge <?php echo e($sub['percentage'] >= 75 ? 'badge-success' : 'badge-danger'); ?>" style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:6px;border:1px solid">
                                        <?php echo e($sub['status_label']); ?>

                                    </span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div style="text-align:center;padding:30px;color:#A19E92">
                                No enrolled subjects found for this session.
                            </div>
                        <?php endif; ?>
                    </div>

                <!-- MODE B: CIRCULAR DAILY ATTENDANCE GAUGE -->
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:12px 0">
                        <svg viewBox="0 0 36 36" class="circular-chart">
                            <path class="circle-bg"
                                d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                            />
                            <path class="circle-progress"
                                stroke="<?php echo e((int)$overallAttendancePct >= 75 ? '#2E6E42' : '#A2412C'); ?>"
                                stroke-dasharray="<?php echo e($overallAttendancePct); ?>, 100"
                                d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                            />
                            <text x="18" y="19" class="circle-percentage"><?php echo e($overallAttendancePct); ?>%</text>
                            <text x="18" y="24" class="circle-sublabel">ATTENDANCE</text>
                        </svg>

                        <span class="badge <?php echo e((int)$overallAttendancePct >= 75 ? 'badge-success' : 'badge-danger'); ?>" style="font-size:11px;font-weight:800;padding:3px 12px;border-radius:999px;border:1px solid">
                            <?php echo e($gaugeStatus); ?>

                        </span>

                        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;width:100%;margin-top:4px">
                            <div style="background:#EFEEEA;border:1px solid #E1DFD7;border-radius:10px;padding:10px;text-align:center">
                                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#68665D">Total</div>
                                <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#1B1A17;margin-top:2px"><?php echo e($totalDays); ?></div>
                            </div>

                            <div style="background:#E3EFE2;border:1px solid #C7DEC5;border-radius:10px;padding:10px;text-align:center">
                                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#2E6E42">Present</div>
                                <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#2E6E42;margin-top:2px"><?php echo e($presentDays); ?></div>
                            </div>

                            <div style="background:#F6E4E1;border:1px solid #EAC8C1;border-radius:10px;padding:10px;text-align:center">
                                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#A2412C">Absent</div>
                                <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#A2412C;margin-top:2px"><?php echo e($absentDays); ?></div>
                            </div>

                            <div style="background:#F8E9D3;border:1px solid #E8CEAA;border-radius:10px;padding:10px;text-align:center">
                                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#8A5A10">Leave</div>
                                <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#8A5A10;margin-top:2px"><?php echo e($leaveDays); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sleek Footer Legend -->
            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid #E1DFD7;padding-top:12px;font-size:11px;color:#68665D;font-weight:600;flex-wrap:wrap;gap:8px">
                <span style="color:#2E6E42;font-weight:700">● ≥85% Excellent</span>
                <span style="color:#8A5A10;font-weight:700">● ≥75% On Track</span>
                <span style="color:#D48A2E;font-weight:700">● 60-74% Low</span>
                <span style="color:#A2412C;font-weight:700">● &lt;60% Critical</span>
            </div>
        </div>

        <!-- RIGHT COLUMN: INTEGRATED SCHEDULE & ACADEMIC TASKS -->
        <div class="panel-card">
            <div>
                <!-- TODAY'S SCHEDULE -->
                <div class="panel-header">
                    <div class="panel-title">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'calendar-days','class' => 'w-4 h-4 text-amber-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'w-4 h-4 text-amber-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <span>Today's Schedule</span>
                    </div>
                    <span style="font-size:11px;font-weight:800;color:#8A5A10;background:#F8E9D3;padding:2px 8px;border-radius:6px;border:1px solid #E8CEAA">
                        <?php echo e(ucfirst($todayDay)); ?>

                    </span>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px">
                    <?php $__empty_1 = true; $__currentLoopData = $todaySlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $roomNum = $slot->room?->room_number ?? '101';
                            $cleanRoom = preg_match('/^room\b/i', $roomNum) ? $roomNum : 'Room ' . $roomNum;
                        ?>
                        <div class="timetable-slot-row">
                            <div style="display:flex;flex-direction:column;gap:1px">
                                <span style="font-size:13.5px;font-weight:800;color:#1B1A17">
                                    <?php echo e($slot->subject?->subject_name ?? 'Subject Lecture'); ?>

                                </span>
                                <span style="font-size:11.5px;color:#68665D;font-weight:500">
                                    <?php echo e($slot->teacher?->full_name ?? 'Faculty Member'); ?> • <?php echo e($cleanRoom); ?>

                                </span>
                            </div>
                            <span class="slot-timing-badge">
                                <?php echo e(substr($slot->start_time, 0, 5)); ?> - <?php echo e(substr($slot->end_time, 0, 5)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="timetable-slot-row">
                            <div style="display:flex;flex-direction:column;gap:1px">
                                <span style="font-size:13.5px;font-weight:800;color:#1B1A17">Physics &amp; Mechanics</span>
                                <span style="font-size:11.5px;color:#68665D;font-weight:500">Faculty Member • Room 101</span>
                            </div>
                            <span class="slot-timing-badge">08:00 - 09:00</span>
                        </div>

                        <div class="timetable-slot-row">
                            <div style="display:flex;flex-direction:column;gap:1px">
                                <span style="font-size:13.5px;font-weight:800;color:#1B1A17">Urdu Language &amp; Literature</span>
                                <span style="font-size:11.5px;color:#68665D;font-weight:500">Faculty Member • Room 102</span>
                            </div>
                            <span class="slot-timing-badge">09:00 - 10:00</span>
                        </div>

                        <div class="timetable-slot-row">
                            <div style="display:flex;flex-direction:column;gap:1px">
                                <span style="font-size:13.5px;font-weight:800;color:#1B1A17">English Grammar &amp; Composition</span>
                                <span style="font-size:11.5px;color:#68665D;font-weight:500">Faculty Member • Room 103</span>
                            </div>
                            <span class="slot-timing-badge">10:00 - 11:00</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- LMS UPCOMING DEADLINES -->
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #E1DFD7">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <div style="font-family:'Manrope',sans-serif;font-size:14.5px;font-weight:800;color:#1B1A17;display:flex;align-items:center;gap:6px">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'hourglass-half','class' => 'w-3.5 h-3.5 text-amber-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'hourglass-half','class' => 'w-3.5 h-3.5 text-amber-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Upcoming Deadlines &amp; Tests
                        </div>
                        <span style="background:#F8E9D3;color:#8A5A10;border:1px solid #E8CEAA;font-size:10.5px;font-weight:800;padding:1px 6px;border-radius:4px">
                            Active
                        </span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php $__empty_1 = true; $__currentLoopData = $upcomingAssessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="deadline-item">
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#1B1A17"><?php echo e($assm->title); ?></div>
                                    <div style="font-size:11.5px;color:#68665D;font-weight:500"><?php echo e($assm->subject?->subject_name ?? 'LMS Course'); ?></div>
                                </div>
                                <span style="font-size:11px;font-weight:800;color:#A2412C;background:#F6E4E1;padding:2px 8px;border-radius:6px">
                                    <?php echo e($assm->due_date ? \Carbon\Carbon::parse($assm->due_date)->format('M d') : 'Soon'); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="deadline-item">
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#1B1A17">Midterm Preparation Quiz</div>
                                    <div style="font-size:11.5px;color:#68665D;font-weight:500">Physics &amp; Mechanics</div>
                                </div>
                                <span style="font-size:11px;font-weight:800;color:#8A5A10;background:#F8E9D3;border:1px solid #E8CEAA;padding:2px 8px;border-radius:6px">
                                    Due in 3 Days
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Studio Buttons -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid #E1DFD7">
                <?php if(Route::has('lms.practice-test.index')): ?>
                    <a href="<?php echo e(route('lms.practice-test.index')); ?>" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#F8E9D3;color:#8A5A10;border:1px solid #E8CEAA;padding:9px 12px;border-radius:8px;font-size:12px;font-weight:800;text-decoration:none;transition:all 0.2s ease">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sparkles','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Practice Studio
                    </a>
                <?php endif; ?>
                <?php if(Route::has('lms.chatbot.index')): ?>
                    <a href="<?php echo e(route('lms.chatbot.index')); ?>" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#EFEEEA;color:#1B1A17;border:1px solid #E1DFD7;padding:9px 12px;border-radius:8px;font-size:12px;font-weight:800;text-decoration:none;transition:all 0.2s ease">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'message-square','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'message-square','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> AI Tutor Chat
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\student\dashboard.blade.php ENDPATH**/ ?>
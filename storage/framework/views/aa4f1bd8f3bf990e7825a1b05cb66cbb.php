

<?php $__env->startSection('title', $class->name . ' — Subject Materials'); ?>
<?php $__env->startSection('page-header', $class->name . ' — Subjects & RAG Materials'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .subj-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #68665D;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .subj-breadcrumb a {
        color: #D48A2E;
        text-decoration: none;
        font-weight: 700;
    }
    .subj-breadcrumb a:hover { text-decoration: underline; }
    .subj-breadcrumb .sep { color: #A19E92; }

    .class-hero {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 18px;
        padding: 24px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .class-hero-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #17191C;
        border: 1px solid #2A2C30;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 800;
        color: #F0B45D;
    }

    .subject-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }
    .subject-card:hover {
        transform: translateY(-3px);
        border-color: #D48A2E;
        box-shadow: 0 10px 24px rgba(212, 138, 46, 0.12);
    }

    .subject-card-top {
        padding: 20px 20px 12px;
    }
    .subject-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .subject-card-code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        font-weight: 700;
        color: #68665D;
        background: #F2EFEB;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #E1DFD7;
    }
    .subject-card-files {
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .subject-card-name {
        font-family: 'Outfit', sans-serif;
        font-size: 17px;
        font-weight: 800;
        color: #1B1A17;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    .subject-card-class {
        font-size: 11.5px;
        color: #68665D;
        font-weight: 600;
    }
    .subject-card-stats {
        padding: 12px 20px;
        display: flex;
        gap: 8px;
        background: #F4F3EE;
        border-top: 1px solid #EAE8E1;
        border-bottom: 1px solid #EAE8E1;
    }
    .subject-stat {
        flex: 1;
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 8px;
        padding: 8px 10px;
        text-align: center;
    }
    .subject-stat-num {
        font-size: 16px;
        font-weight: 800;
        color: #1B1A17;
    }
    .subject-stat-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #68665D;
        margin-top: 2px;
    }
    .subject-card-actions {
        padding: 14px 20px 16px;
        background: #F9F8F5;
        display: flex;
        gap: 8px;
    }
    .subject-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
    }
    .subject-btn-primary {
        background: #D48A2E;
        color: #1A1200;
        border: 1px solid #C07A22;
        box-shadow: none;
    }
    .subject-btn-primary:hover {
        background: #C07A22;
        transform: translateY(-1px);
    }
</style>

<div>
    <!-- Breadcrumb -->
    <div class="subj-breadcrumb">
        <a href="<?php echo e(route('lms.subjects.list')); ?>">📚 All Classes</a>
        <span class="sep">›</span>
        <span style="color: #1B1A17; font-weight: 700;"><?php echo e($class->name); ?></span>
    </div>

    <!-- Class Hero -->
    <div class="class-hero">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="class-hero-icon"><?php echo e(strtoupper(substr($class->name, 0, 1))); ?></div>
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 21px; font-weight: 800; color: #1B1A17; margin: 0 0 4px;">
                    <?php echo e($class->name); ?> — Subject Library
                </h1>
                <p style="font-size: 12.5px; color: #68665D; margin: 0; font-weight: 500;">
                    <?php echo e($subjects->count()); ?> <?php echo e(Str::plural('subject', $subjects->count())); ?> registered 
                    <?php if($class->sections->count() > 0): ?>
                        • <?php echo e($class->sections->count()); ?> <?php echo e(Str::plural('section', $class->sections->count())); ?>

                        (<?php echo e($class->sections->pluck('section_name')->join(', ')); ?>)
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <a href="<?php echo e(route('lms.subjects.list')); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:#F2EFEB;border:1px solid #E1DFD7;color:#68665D;font-size:12px;font-weight:700;text-decoration:none;transition:all 0.2s">
            ← Back to Classes
        </a>
    </div>

    <!-- Subjects Grid -->
    <?php if($subjects->isEmpty()): ?>
        <div style="text-align: center; padding: 64px 24px; background: #F9F8F5; border: 1px dashed #E1DFD7; border-radius: 16px;">
            <div style="display: inline-flex; width: 64px; height: 64px; border-radius: 14px; align-items: center; justify-content: center; font-size: 28px; background: #F2EFEB; border: 1px solid #E1DFD7; margin-bottom: 14px; color: #8A877E;">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'book-bookmark','class' => 'w-7 h-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'book-bookmark','class' => 'w-7 h-7']); ?>
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
            <h3 style="font-size: 17px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">No Subjects Registered</h3>
            <p style="font-size: 12.5px; color: #68665D; max-width: 400px; margin: 0 auto; line-height: 1.5;">
                This class has no subjects configured. Ask your Principal to add subjects from the Academic Suite.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px;">
            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $fileCount = $subject->materials->count();
                    $textbooks = $subject->materials->where('document_type', 'textbook')->count();
                    $notes = $subject->materials->where('document_type', 'notes')->count();
                    $others = $fileCount - $textbooks - $notes;
                ?>
                <div class="subject-card">
                    <div class="subject-card-top">
                        <div class="subject-card-meta">
                            <span class="subject-card-code"><?php echo e($subject->subject_code ?? 'SUB-'.$subject->id); ?></span>
                            <span class="subject-card-files" style="color: <?php echo e($fileCount > 0 ? '#2E6E42' : '#8A877E'); ?>; font-weight: 700;">
                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'file-lines','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'file-lines','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($fileCount); ?> <?php echo e(Str::plural('File', $fileCount)); ?>

                            </span>
                        </div>
                        <div class="subject-card-name"><?php echo e($subject->subject_name ?? 'Subject #'.$subject->id); ?></div>
                        <div class="subject-card-class">
                            <?php echo e($class->name); ?>

                            <?php if($subject->credit_hours): ?>
                                • <?php echo e($subject->credit_hours); ?> Credit <?php echo e(Str::plural('Hour', $subject->credit_hours)); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="subject-card-stats">
                        <div class="subject-stat">
                            <div class="subject-stat-num" style="color: #D48A2E;"><?php echo e($textbooks); ?></div>
                            <div class="subject-stat-label">Textbooks</div>
                        </div>
                        <div class="subject-stat">
                            <div class="subject-stat-num" style="color: #8A5A10;"><?php echo e($notes); ?></div>
                            <div class="subject-stat-label">Notes</div>
                        </div>
                        <div class="subject-stat">
                            <div class="subject-stat-num" style="color: #68665D;"><?php echo e(max($others, 0)); ?></div>
                            <div class="subject-stat-label">Reference</div>
                        </div>
                    </div>
                    <div class="subject-card-actions">
                        <a href="<?php echo e(route('lms.materials.index', $subject->id)); ?>" class="subject-btn subject-btn-primary">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'cloud-arrow-up','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cloud-arrow-up','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Upload / Manage Books
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\materials\class_subjects.blade.php ENDPATH**/ ?>
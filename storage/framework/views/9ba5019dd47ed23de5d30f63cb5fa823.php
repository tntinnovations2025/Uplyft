

<?php $__env->startSection('title', 'LMS Course Materials — Select Class'); ?>
<?php $__env->startSection('page-header', 'Subject Materials & RAG Document Uploads'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .lms-hero {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 18px;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
        box-shadow: none;
    }
    .class-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
        box-shadow: none;
    }
    .class-card:hover {
        transform: translateY(-3px);
        border-color: #D48A2E;
        box-shadow: 0 10px 24px rgba(212, 138, 46, 0.12);
    }
    .class-card-header {
        padding: 22px 24px 0;
    }
    .class-card-body {
        padding: 16px 24px;
    }
    .class-card-footer {
        padding: 14px 24px 18px;
        border-top: 1px solid #EAE8E1;
        background: #F4F3EE;
    }
    .class-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        flex-shrink: 0;
        background: #17191C;
        color: #F0B45D;
        border: 1px solid #2A2C30;
    }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .class-card .arrow-indicator {
        transition: transform 0.2s ease, color 0.2s ease;
        color: #8A877E;
    }
    .class-card:hover .arrow-indicator {
        transform: translateX(4px);
        color: #D48A2E;
    }
</style>

<div class="space-y-6">
    <!-- Hero Section -->
    <div class="lms-hero">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">
                    📚 LMS Course Materials &amp; RAG Library
                </h1>
                <p style="font-size: 13.5px; color: #68665D; margin: 0; max-width: 620px; line-height: 1.5;">
                    Select a class below to view and manage subject materials. Upload PDF textbooks and Word notes for AI-powered RAG indexing.
                </p>
            </div>
            <?php if(isset($activeTerm)): ?>
                <div class="stat-pill" style="background: #F8E9D3; border: 1px solid #E8CEAA; color: #8A5A10;">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'calendar-check','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-check','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Active: <?php echo e($activeTerm->name ?? 'Current Term'); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Classes Grid -->
    <?php if($classes->isEmpty()): ?>
        <div style="text-align: center; padding: 64px 24px; background: #F9F8F5; border: 1px dashed #E1DFD7; border-radius: 16px;">
            <div style="display: inline-flex; width: 64px; height: 64px; border-radius: 14px; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 14px; background: #F2EFEB; border: 1px solid #E1DFD7; color: #8A877E;">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'building-columns','class' => 'w-7 h-7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'building-columns','class' => 'w-7 h-7']); ?>
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
            <h3 style="font-size: 17px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">No Classes with Subjects Found</h3>
            <p style="font-size: 13px; color: #68665D; max-width: 420px; margin: 0 auto; line-height: 1.5;">
                There are no classes with registered subjects in the active academic term. Configure subjects from Academic Suite.
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px;">
            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $sectionsCount = $class->sections->count();
                ?>
                <a href="<?php echo e(route('lms.subjects.class', $class->id)); ?>" class="class-card">
                    <div class="class-card-header">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div class="class-icon">
                                <?php echo e(strtoupper(substr($class->name, 0, 1))); ?>

                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 16.5px; font-weight: 800; color: #1B1A17; margin: 0 0 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo e($class->name); ?>

                                </h3>
                                <div style="font-size: 11.5px; color: #68665D; font-weight: 600;">
                                    <?php if($class->systemClass): ?>
                                        <?php echo e($class->systemClass->name); ?> •
                                    <?php endif; ?>
                                    <?php echo e($sectionsCount); ?> <?php echo e(Str::plural('Section', $sectionsCount)); ?>

                                </div>
                            </div>
                            <div class="arrow-indicator" style="font-size: 16px; margin-top: 2px;">→</div>
                        </div>
                    </div>
                    <div class="class-card-body">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="stat-pill" style="background: #F8E9D3; color: #8A5A10; border: 1px solid #E8CEAA;">
                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'book-bookmark','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'book-bookmark','class' => 'w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($class->subjects_count); ?> <?php echo e(Str::plural('Subject', $class->subjects_count)); ?>

                            </span>
                            <span class="stat-pill" style="background: #EAE8E1; color: #68665D; border: 1px solid #E1DFD7;">
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
<?php endif; ?> <?php echo e($class->materials_count ?? 0); ?> <?php echo e(Str::plural('File', $class->materials_count ?? 0)); ?> Uploaded
                            </span>
                        </div>
                    </div>
                    <div class="class-card-footer">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 6px; background: #E1DFD7; border-radius: 999px; overflow: hidden;">
                                <?php
                                    $maxFiles = max($class->subjects_count * 3, 1);
                                    $progress = min(($class->materials_count ?? 0) / $maxFiles * 100, 100);
                                ?>
                                <div style="height: 100%; width: <?php echo e($progress); ?>%; background: #D48A2E; border-radius: 999px; transition: width 0.4s ease;"></div>
                            </div>
                            <span style="font-size: 11px; color: #8A5A10; font-weight: 800; white-space: nowrap;">
                                <?php echo e(round($progress)); ?>% coverage
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('lms.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\lms\materials\subject_list.blade.php ENDPATH**/ ?>
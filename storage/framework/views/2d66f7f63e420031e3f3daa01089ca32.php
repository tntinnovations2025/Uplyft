<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Account Details')); ?> — <?php echo e($user->name); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <?php if(session('success')): ?>
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Full Name</dt>
                            <dd class="text-gray-900"><?php echo e($user->name); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Role</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($user->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                    <?php echo e(ucfirst($user->role)); ?>

                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Identifier</dt>
                            <dd class="text-gray-900 font-mono"><?php echo e($user->identifier ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Email</dt>
                            <dd class="text-gray-900"><?php echo e($user->email ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Created At</dt>
                            <dd class="text-gray-900"><?php echo e($user->created_at->format('M d, Y h:i A')); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Created By</dt>
                            <dd class="text-gray-900"><?php echo e($user->creator->name ?? 'System'); ?></dd>
                        </div>
                        <?php if($user->isTeacher()): ?>
                            <div>
                                <dt class="font-medium text-gray-500">Delegated Admin</dt>
                                <dd>
                                    <?php if($user->is_delegated_admin): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Yes — Active</span>
                                    <?php else: ?>
                                        <span class="text-gray-400">No</span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <div class="mt-6 flex space-x-4">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $user)): ?>
                            <a href="<?php echo e(route('principal.accounts.edit', $user)); ?>" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition text-sm">
                                Edit Account
                            </a>
                        <?php endif; ?>

                        <?php if($user->isTeacher() && auth()->user()->isPrincipal()): ?>
                            <form method="POST" action="<?php echo e(route('principal.delegation.toggle', $user)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="inline-flex items-center px-4 py-2 <?php echo e($user->is_delegated_admin ? 'bg-red-500 hover:bg-red-600' : 'bg-purple-500 hover:bg-purple-600'); ?> text-white rounded-md transition text-sm">
                                    <?php echo e($user->is_delegated_admin ? 'Revoke Delegation' : 'Grant Delegation'); ?>

                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $user)): ?>
                            <form method="POST" action="<?php echo e(route('principal.accounts.destroy', $user)); ?>" onsubmit="return confirm('Are you sure you want to deactivate this account?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition text-sm">
                                    Deactivate
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\principal\accounts\show.blade.php ENDPATH**/ ?>
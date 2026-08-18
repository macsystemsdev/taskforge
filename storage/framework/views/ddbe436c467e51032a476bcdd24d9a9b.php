<div>
    
    <div class="border-b border-zinc-200 px-4 py-3 dark:border-white/10">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-semibold text-zinc-950 dark:text-white">
                    <?php echo e(__('Notifications')); ?>

                </p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadCount): ?>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        <?php echo e($this->unreadCount); ?>

                        <?php echo e(\Illuminate\Support\Str::plural(__('unread'), $this->unreadCount)); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <a
                href="<?php echo e(route('notifications.index')); ?>"
                wire:navigate
                class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"
            >
                <?php echo e(__('View all')); ?>

            </a>
        </div>
    </div>

    
    <div class="max-h-96 overflow-y-auto">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal5027d420cfeeb03dd925cfc08ae44851 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.item','data' => ['as' => 'a','href' => ''.e(route('notifications.redirect', $notification->id)).'','wire:navigate' => true,'class' => 'block border-b border-zinc-200 px-4 py-3 !p-0 last:border-0 dark:border-white/10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'a','href' => ''.e(route('notifications.redirect', $notification->id)).'','wire:navigate' => true,'class' => 'block border-b border-zinc-200 px-4 py-3 !p-0 last:border-0 dark:border-white/10']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="flex gap-3 px-4 py-3">
                    <div class="mt-2 size-2 shrink-0 rounded-full <?php echo e($notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-rose-600'); ?>"></div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">
                                <?php echo e($notification->data['title'] ?? class_basename($notification->type)); ?>

                            </p>

                            <span class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400">
                                <?php echo e($notification->created_at->diffForHumans()); ?>

                            </span>
                        </div>

                        <p class="mt-1 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <?php echo e($notification->data['message'] ?? ($notification->data['body'] ?? __('Notification received.'))); ?>

                        </p>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $attributes = $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $component = $__componentOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="px-4 py-8 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    <?php echo e(__('No notifications yet.')); ?>

                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="border-t border-zinc-200 p-2 dark:border-white/10">
        <a
            href="<?php echo e(route('notifications.index')); ?>"
            wire:navigate
            class="block rounded-lg px-3 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5"
        >
            <?php echo e(__('View all notifications')); ?>

        </a>
    </div>
</div><?php /**PATH /var/www/html/resources/views/livewire/notifications/notification-dropdown.blade.php ENDPATH**/ ?>
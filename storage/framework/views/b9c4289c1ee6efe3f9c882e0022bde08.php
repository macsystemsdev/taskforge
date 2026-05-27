<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


    <div class="max-w-5xl mx-auto py-8 space-y-6">

        
        <div class="rounded-2xl border bg-dark p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>

                    <h1 class="text-3xl font-bold">
                        <?php echo e($task->title); ?>

                    </h1>

                    <p class="text-zinc-500 mt-3">
                        <?php echo e($task->description); ?>

                    </p>

                </div>

                <div class="flex flex-col gap-2 items-end">

                    <span class="rounded-full border px-3 py-1 text-sm">
                        <?php echo e(str($task->status->value)->headline()); ?>

                    </span>

                    <span class="rounded-full border px-3 py-1 text-sm">
                        <?php echo e(str($task->priority->value)->headline()); ?>

                    </span>

                </div>

            </div>

            <div class="mt-6 grid grid-cols-2 gap-6 text-sm">

                <div>

                    <p class="text-zinc-500">
                        Assigned To
                    </p>

                    <p class="font-medium mt-1">
                        <?php echo e($task->assignee?->name ?? 'Unassigned'); ?>

                    </p>

                </div>

                <div>

                    <p class="text-zinc-500">
                        Due Date
                    </p>

                    <p class="font-medium mt-1">
                        <?php echo e($task->due_date?->format('M d, Y') ?? 'N/A'); ?>

                    </p>

                </div>

            </div>

        </div>

        
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('comments.comment-section', [
            'commentable' => $task,
        ]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1612645519-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH D:\Code\taskforge\resources\views/pages/tasks/show.blade.php ENDPATH**/ ?>
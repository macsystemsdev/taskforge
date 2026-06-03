<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">

<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="min-h-screen bg-zinc-100 dark:bg-zinc-900">

    <div class="flex min-h-screen">

        
        <?php echo $__env->make('layouts.app.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 flex flex-col">

            
            <?php echo $__env->make('layouts.app.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <main class="flex-1 p-6 lg:p-10">

                <?php echo e($slot); ?>


            </main>

        </div>

    </div>

    <?php app("livewire")->forceAssetInjection(); ?><div x-persist="<?php echo e('toast'); ?>">
        <?php if (isset($component)) { $__componentOriginal9e52f305f7cdd22fd6be350cf248d973 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9e52f305f7cdd22fd6be350cf248d973 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::toast.group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::toast.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginal6e0689304ed9fe6f1f826bea0820c41b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6e0689304ed9fe6f1f826bea0820c41b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::toast.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6e0689304ed9fe6f1f826bea0820c41b)): ?>
<?php $attributes = $__attributesOriginal6e0689304ed9fe6f1f826bea0820c41b; ?>
<?php unset($__attributesOriginal6e0689304ed9fe6f1f826bea0820c41b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6e0689304ed9fe6f1f826bea0820c41b)): ?>
<?php $component = $__componentOriginal6e0689304ed9fe6f1f826bea0820c41b; ?>
<?php unset($__componentOriginal6e0689304ed9fe6f1f826bea0820c41b); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9e52f305f7cdd22fd6be350cf248d973)): ?>
<?php $attributes = $__attributesOriginal9e52f305f7cdd22fd6be350cf248d973; ?>
<?php unset($__attributesOriginal9e52f305f7cdd22fd6be350cf248d973); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9e52f305f7cdd22fd6be350cf248d973)): ?>
<?php $component = $__componentOriginal9e52f305f7cdd22fd6be350cf248d973; ?>
<?php unset($__componentOriginal9e52f305f7cdd22fd6be350cf248d973); ?>
<?php endif; ?>
    </div>

    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>


</body>

</html>
<?php /**PATH D:\Code\taskforge\resources\views/layouts/app.blade.php ENDPATH**/ ?>
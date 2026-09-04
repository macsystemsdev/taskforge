<?php if (isset($component)) { $__componentOriginalf755b28e1a732ff956ecb1d7ff522fd7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf755b28e1a732ff956ecb1d7ff522fd7 = $attributes; } ?>
<?php $component = App\View\Components\SubscriptionPlanPreview::resolve(['plan' => $plan,'metadata' => $metadata] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('subscription-plan-preview'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubscriptionPlanPreview::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf755b28e1a732ff956ecb1d7ff522fd7)): ?>
<?php $attributes = $__attributesOriginalf755b28e1a732ff956ecb1d7ff522fd7; ?>
<?php unset($__attributesOriginalf755b28e1a732ff956ecb1d7ff522fd7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf755b28e1a732ff956ecb1d7ff522fd7)): ?>
<?php $component = $__componentOriginalf755b28e1a732ff956ecb1d7ff522fd7; ?>
<?php unset($__componentOriginalf755b28e1a732ff956ecb1d7ff522fd7); ?>
<?php endif; ?><?php /**PATH /var/www/html/resources/views/filament/resources/subscription-plans/preview.blade.php ENDPATH**/ ?>
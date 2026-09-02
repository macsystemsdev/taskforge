
<div class="mx-auto max-w-2xl">

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <div class="text-center">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/10">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-amber-600 dark:text-amber-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M22 12A10 10 0 112 12a10 10 0 0120 0z" />

                </svg>

            </div>

            <h1 class="mt-6 text-2xl font-semibold">
                Payment Cancelled
            </h1>

            <p class="mt-3 tf-muted">
                Your payment was cancelled before completion.
            </p>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left">

                <p class="text-sm text-amber-800">

                    No payment was processed and your current subscription remains active.
                    You can return to billing and try again whenever you're ready.

                </p>

            </div>

            <div class="mt-8 flex justify-center gap-3">

                <a
                    href="<?php echo e(route('dashboard')); ?>"
                    class="tf-button-secondary">

                    Dashboard

                </a>

                <a
                    href="<?php echo e(route('organizations.billing', $organization ?? null)); ?>"
                    class="tf-button-primary">

                    Back to Billing

                </a>

            </div>

        </div>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

</div><?php /**PATH /var/www/html/resources/views/livewire/billing/show-billing-cancel.blade.php ENDPATH**/ ?>
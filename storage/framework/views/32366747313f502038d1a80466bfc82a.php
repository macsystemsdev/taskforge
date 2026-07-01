<?php

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Computed;

new class extends Component {
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load(['subscription.plan']);
    }

    #[Computed]
    public function plans()
    {
        return SubscriptionPlan::query()->where('is_active', true)->orderBy('price')->get();
    }

    public function render()
    {
        return view('livewire.billing.show-billing');
    }
};

?>

<div class="space-y-8">

    
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


        <div class="flex items-start justify-between gap-6">

            <div>

                <p class="tf-panel-title">
                    Current Subscription
                </p>

                <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($organization->subscription->plan->name); ?>

                </h2>

                <p class="mt-1 tf-muted">
                    <?php echo e($organization->subscription->status->value); ?>

                </p>

            </div>

            <span
                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                Active
            </span>

        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-3">

            <div>

                <p class="tf-muted">
                    Price
                </p>

                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($organization->subscription->plan->formattedPrice()); ?>


                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($organization->subscription->plan->isFree()): ?>
                        <span class="font-normal text-zinc-500">
                            / <?php echo e($organization->subscription->plan->billingLabel()); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>

            </div>

            <div>

                <p class="tf-muted">
                    Billing
                </p>

                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($organization->subscription->plan->billingIntervalLabel()); ?>

                </p>

            </div>

            <div>

                <p class="tf-muted">
                    Renewal
                </p>

                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($organization->subscription->ends_at?->format('M d, Y') ?? 'No active renewal'); ?>

                </p>

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

    
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $current = $organization->subscription->plan->is($plan);
            ?>

            <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'flex h-full flex-col '.e($current ? 'border-indigo-500 ring-2 ring-indigo-500/10' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex h-full flex-col '.e($current ? 'border-indigo-500 ring-2 ring-indigo-500/10' : '').'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


                <div class="flex items-start justify-between">

                    <div>

                        <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">
                            <?php echo e($plan->name); ?>

                        </h3>

                        <p class="mt-1 text-sm text-zinc-500">
                            <?php echo e($plan->billingIntervalLabel()); ?>

                        </p>

                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($current): ?>
                        <span
                            class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                            Current
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                </div>

                <div class="mt-6">

                    <p class="text-4xl font-bold text-zinc-950 dark:text-white">
                        <?php echo e($plan->formattedPrice()); ?>


                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->price > 0): ?>
                            <span class="text-base font-normal text-zinc-500">
                                / <?php echo e($plan->billingLabel()); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>

                </div>

                <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-white/10">

                    <ul class="space-y-3 text-sm text-zinc-700 dark:text-zinc-300">

                        <li class="flex items-center gap-2">
                            ✓ <?php echo e($plan->workspaceLimitLabel()); ?> Workspaces
                        </li>

                        <li class="flex items-center gap-2">
                            ✓ <?php echo e($plan->projectLimitLabel()); ?> Projects
                        </li>

                        <li class="flex items-center gap-2">
                            ✓ <?php echo e($plan->memberLimitLabel()); ?> Members
                        </li>

                    </ul>

                </div>

                <div class="mt-auto pt-8">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($current): ?>
                        <button disabled class="tf-button-secondary w-full cursor-not-allowed opacity-70">

                            Current Plan

                        </button>
                    <?php else: ?>
                        <button wire:click="changePlan(<?php echo e($plan->id); ?>)" class="tf-button-primary w-full">

                            Choose Plan

                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

    </div>

</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/billing/show-billing.blade.php ENDPATH**/ ?>
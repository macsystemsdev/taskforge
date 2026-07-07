<?php

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Computed;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CreateCheckoutService;

new class extends Component {
    public ?SubscriptionPlan $selectedPlan = null;

    public Organization $organization;

    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load(['subscription.plan']);
    }

    #[Computed]
    public function plans()
    {
        return SubscriptionPlan::query()->purchasable()->orderBy('price')->get();
    }

    public function selectPlan(SubscriptionPlan $plan): void
    {
        $this->selectedPlan = $plan;

        Flux::modal('confirm-subscription')->show();
    }

    public function resetSelectedPlan(): void
    {
        $this->selectedPlan = null;

        Flux::modal('confirm-subscription')->close();
    }

    public function confirmPlanChange(): mixed
    {
        if (!$this->selectedPlan) {
            return null;
        }

        $response = app(CreateCheckoutService::class)->handle(new CheckoutData(organization: $this->organization, plan: $this->selectedPlan, provider: PaymentProvider::STRIPE));

        return redirect()->away($response->url);
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
                    <?php echo e($organization->subscription?->plan?->name ?? 'No Active Plan'); ?>

                </h2>

                <p class="mt-1 tf-muted">
                    <?php echo e($organization->subscription?->status?->value ?? '-'); ?>

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
                    Next Billing Date
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($organization->subscription->hasPendingPlan()): ?>
        <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


            <div class="flex items-start justify-between">

                <div>

                    <p class="tf-panel-title">
                        Upcoming Subscription
                    </p>

                    <h3 class="mt-2 text-xl font-semibold">
                        <?php echo e($organization->subscription->pendingPlan->name); ?>

                    </h3>

                    <p class="tf-muted mt-1">
                        Scheduled for
                        <?php echo e($organization->subscription->pending_effective_at->format('M d, Y')); ?>

                    </p>

                </div>

                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                    Scheduled
                </span>

            </div>

            <div class="mt-6 rounded-lg bg-blue-50 p-4">

                <p class="text-sm text-blue-800">

                    Your payment has been received.

                    Your subscription will automatically change to
                    <strong><?php echo e($organization->subscription->pendingPlan->name); ?></strong>
                    on
                    <?php echo e($organization->subscription->pending_effective_at->format('F j, Y')); ?>.

                </p>

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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $current = $organization->subscription->plan->is($plan);
                $scheduled = $organization->subscription->pending_subscription_plan_id === $plan->id;
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
                    <?php elseif($scheduled): ?>
                        <button disabled class="tf-button-secondary w-full cursor-not-allowed opacity-70">

                            Scheduled Plan

                        </button>
                    <?php else: ?>
                        <button wire:click="selectPlan(<?php echo e($plan->id); ?>)" class="tf-button-primary w-full">

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
    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['name' => 'confirm-subscription','xOn:close' => '$wire.resetSelectedPlan()']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-subscription','x-on:close' => '$wire.resetSelectedPlan()']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPlan): ?>
            <div class="space-y-8">

                <div>

                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">
                        Confirm Subscription Change
                    </h2>

                    <p class="mt-2 tf-muted">
                        Review your new subscription before continuing to our secure payment provider.
                    </p>

                </div>

                <div class="grid gap-4 md:grid-cols-2">

                    
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-white/10">

                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            Current Plan
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            <?php echo e($organization->subscription?->plan?->name ?? 'None'); ?>

                        </h3>

                        <p class="mt-1 tf-muted">
                            <?php echo e($organization->subscription?->plan?->formattedPrice() ?? 'Free'); ?>

                        </p>

                        <div class="mt-6 space-y-2 text-sm">

                            <div class="flex justify-between">
                                <span>Workspaces</span>
                                <span><?php echo e($organization->subscription?->plan?->workspaceLimitLabel() ?? '-'); ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Projects</span>
                                <span><?php echo e($organization->subscription?->plan?->projectLimitLabel() ?? '-'); ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Members</span>
                                <span><?php echo e($organization->subscription?->plan?->memberLimitLabel() ?? '-'); ?></span>
                            </div>

                        </div>

                    </div>

                    
                    <div
                        class="rounded-xl border-2 border-indigo-500 bg-indigo-50/40 p-5 dark:border-indigo-400 dark:bg-indigo-500/10">

                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            New Plan
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            <?php echo e($selectedPlan->name); ?>

                        </h3>

                        <p class="mt-1 font-medium text-indigo-600">
                            <?php echo e($selectedPlan->formattedPrice()); ?>


                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($selectedPlan->isFree())): ?>
                                / <?php echo e($selectedPlan->billingLabel()); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>

                        <div class="mt-6 space-y-2 text-sm">

                            <div class="flex justify-between">
                                <span>Workspaces</span>
                                <span><?php echo e($selectedPlan->workspaceLimitLabel()); ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Projects</span>
                                <span><?php echo e($selectedPlan->projectLimitLabel()); ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Members</span>
                                <span><?php echo e($selectedPlan->memberLimitLabel()); ?></span>
                            </div>

                        </div>

                    </div>

                </div>

                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/20">

                    <h4 class="font-medium text-amber-900 dark:text-amber-300">
                        Before you continue
                    </h4>

                    <ul class="mt-3 space-y-2 text-sm text-amber-800 dark:text-amber-200">

                        <li>• Your payment secures your next subscription plan immediately.</li>

                        <li>• Your current plan remains active until the end of the current billing period.</li>

                        <li>• The new plan will automatically become active on your next renewal date.</li>

                    </ul>

                </div>

                <div class="flex justify-end gap-3">

                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'ghost','xOn:click' => '$dispatch(\'close\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','x-on:click' => '$dispatch(\'close\')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


                        Cancel

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'primary','wire:click' => 'confirmPlanChange']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','wire:click' => 'confirmPlanChange']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


                        Continue to Payment

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>

                </div>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/billing/show-billing.blade.php ENDPATH**/ ?>
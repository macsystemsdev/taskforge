<?php

use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Livewire\Component;
use Flux\Flux;
use Livewire\Attributes\Computed;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Domain\Billing\Services\StartTrialService;
use App\Exceptions\Billing\TrialUnavailableException;

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

    public function startTrial(StartTrialService $service)
    {
        try {
            $service->handle($this->organization->subscription);

            $this->organization->refresh();

            $this->dispatch('notify', type: 'success', message: 'Your free trial has started.');
        } catch (TrialUnavailableException) {
            $this->dispatch('notify', type: 'error', message: 'Free trial is no longer available.');
        }
    }

    public function render()
    {
        return view('livewire.billing.show-billing');
    }
};

?>

<div class="space-y-8">
    @php
        $subscription = $organization->subscription;
        $currentAccessLabel = $subscription->isTrial() ? 'Pro Trial' : ($subscription?->plan?->name ?? 'No Active Plan');
        $currentAccessDescription = $subscription->isTrial() ? '14-day trial active' : ($subscription?->plan?->billingIntervalLabel() ?? 'Free');
        $currentAccessBadge = $subscription->isTrial() ? 'Trial' : 'Active';
    @endphp

    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-500 p-8 text-white shadow-2xl shadow-indigo-950/10">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-white/90">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Billing & plans
                </div>

                <h1 class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">
                    Scale your organization with confidence.
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-50 sm:text-base">
                    Unlock more workspaces, projects, and collaboration power as your team grows. Start a free trial in one click or switch to a plan that fits your momentum.
                </p>
            </div>

            <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                <p class="text-sm font-medium text-white/70">
                    Current access
                </p>
                <p class="mt-2 text-xl font-semibold">
                    {{ $currentAccessLabel }}
                </p>
                <p class="mt-1 text-sm text-white/80">
                    {{ $currentAccessDescription }}
                </p>
            </div>
        </div>
    </div>

    @if ($organization->subscription->isTrial())
        <x-ui.card class="border-amber-200 bg-amber-50/80 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-300">
                        Pro Trial Active
                    </p>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">
                        {{ $organization->subscription->trialDaysRemaining() }} days remaining on your trial.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                    Trial
                </span>
            </div>
        </x-ui.card>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <x-ui.card class="border-zinc-200/80 bg-white/80 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        Current Access
                    </p>
                    <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $currentAccessLabel }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                        {{ $subscription->isTrial() ? 'Trial is active right now.' : ($subscription?->status?->value ?? '-') }}
                    </p>
                </div>

                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ $currentAccessBadge }}
                </span>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-sm font-medium text-zinc-500">Price</p>
                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                        @if ($subscription->isTrial())
                            Included with trial
                        @else
                            {{ $subscription?->plan?->formattedPrice() ?? 'Free' }}

                            @if ($subscription?->plan?->isFree())
                                <span class="text-sm font-normal text-zinc-500">
                                    / {{ $subscription?->plan?->billingLabel() }}
                                </span>
                            @endif
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-zinc-500">Billing</p>
                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                        {{ $subscription->isTrial() ? 'Trial' : ($subscription?->plan?->billingIntervalLabel() ?? 'Free') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-zinc-500">Next Billing Date</p>
                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                        {{ $subscription->isTrial() ? 'Ends after 14 days' : ($subscription->ends_at?->format('M d, Y') ?? 'No active renewal') }}
                    </p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="border-zinc-200/80 bg-zinc-950 text-white shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                Why teams upgrade
            </p>
            <ul class="mt-5 space-y-3 text-sm text-zinc-300">
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                    Add more workspaces and projects without limits.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                    Give every collaborator the visibility they need.
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-400"></span>
                    Keep finance and operations aligned with predictable billing.
                </li>
            </ul>
        </x-ui.card>
    </div>

    @if ($organization->subscription->hasPendingPlan())
        <x-ui.card class="border-blue-200 bg-blue-50/80 shadow-sm dark:border-blue-500/20 dark:bg-blue-500/10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-700">
                        Upcoming Subscription
                    </p>
                    <h3 class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">
                        {{ $organization->subscription->pendingPlan->name }}
                    </h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                        Scheduled for {{ $organization->subscription->pending_effective_at->format('M d, Y') }}.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">
                    Scheduled
                </span>
            </div>
        </x-ui.card>
    @endif

    @if ($organization->subscription->canStartTrial())
        <x-ui.card class="border-indigo-200 bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-xl shadow-indigo-950/10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-indigo-100">
                        Free Trial
                    </p>
                    <h3 class="mt-2 text-2xl font-semibold">
                        Start your 14-day Pro trial
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-indigo-50 sm:text-base">
                        Get full access to premium collaboration features, higher limits, and a polished workspace experience without entering a credit card.
                    </p>
                </div>

                <button wire:click="startTrial" wire:loading.attr="disabled" wire:target="startTrial"
                    class="inline-flex items-center justify-center rounded-full bg-white px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                    <span wire:loading.remove wire:target="startTrial">
                        Start Free Trial
                    </span>
                    <span wire:loading wire:target="startTrial">
                        Starting...
                    </span>
                </button>
            </div>
        </x-ui.card>
    @endif

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->plans as $plan)
            @php
                $current = $organization->subscription?->accessPlan()?->is($plan) ?? false;
                $scheduled = $organization->subscription?->pending_subscription_plan_id === $plan->id;
            @endphp

            <x-ui.card class="flex h-full flex-col border-zinc-200/80 bg-white/90 shadow-sm {{ $current ? 'ring-2 ring-indigo-500/20' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">
                            {{ $plan->name }}
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $plan->billingIntervalLabel() }}
                        </p>
                    </div>

                    @if ($current)
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                            Current
                        </span>
                    @endif
                </div>

                <div class="mt-6">
                    <p class="text-4xl font-semibold text-zinc-950 dark:text-white">
                        {{ $plan->formattedPrice() }}

                        @if ($plan->price > 0)
                            <span class="text-base font-normal text-zinc-500">
                                / {{ $plan->billingLabel() }}
                            </span>
                        @endif
                    </p>
                </div>

                <div class="mt-6 rounded-2xl border border-zinc-200 bg-slate-50 p-4 shadow-inner">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-500">
                            Plan details
                        </p>
                        <span class="rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-zinc-600">
                            Included
                        </span>
                    </div>
                    <ul class="space-y-3 text-sm text-zinc-700 dark:text-zinc-300">
                        @foreach ($plan->featureHighlights() as $feature)
                            <li class="flex items-center justify-between rounded-lg border border-zinc-200/80 bg-white/70 px-3 py-2">
                                <span>{{ $feature['label'] }}</span>
                                <span class="font-semibold text-zinc-950">{{ $feature['value'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-auto pt-8">
                    @if ($current && $organization->subscription->isTrial())
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                            Trial Active
                        </span>
                    @elseif ($current)
                        <button disabled class="w-full cursor-not-allowed rounded-full border border-zinc-200 bg-zinc-100 px-4 py-3 text-sm font-semibold text-zinc-500">
                            Current Plan
                        </button>
                    @elseif ($scheduled)
                        <button disabled class="w-full cursor-not-allowed rounded-full border border-zinc-200 bg-zinc-100 px-4 py-3 text-sm font-semibold text-zinc-500">
                            Scheduled Plan
                        </button>
                    @else
                        <button wire:click="selectPlan({{ $plan->id }})" class="w-full rounded-full bg-zinc-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                            Choose Plan
                        </button>
                    @endif
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <flux:modal name="confirm-subscription" x-on:close="$wire.resetSelectedPlan()">
        @if ($selectedPlan)
            <div class="space-y-8">
                <div>
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">
                        Confirm Subscription Change
                    </h2>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                        Review your new subscription before continuing to our secure payment provider.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-zinc-200 p-5 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-zinc-500">
                            Current Plan
                        </p>
                        <h3 class="mt-2 text-xl font-semibold">
                            {{ $organization->subscription?->plan?->name ?? 'None' }}
                        </h3>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $organization->subscription?->plan?->formattedPrice() ?? 'Free' }}
                        </p>

                        <div class="mt-6 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Workspaces</span>
                                <span>{{ $organization->subscription?->plan?->workspaceLimitLabel() ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Projects</span>
                                <span>{{ $organization->subscription?->plan?->projectLimitLabel() ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Members</span>
                                <span>{{ $organization->subscription?->plan?->memberLimitLabel() ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border-2 border-indigo-500 bg-indigo-50/60 p-5 dark:border-indigo-400 dark:bg-indigo-500/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-600 dark:text-indigo-300">
                            New Plan
                        </p>
                        <h3 class="mt-2 text-xl font-semibold">
                            {{ $selectedPlan->name }}
                        </h3>
                        <p class="mt-1 font-medium text-indigo-600 dark:text-indigo-300">
                            {{ $selectedPlan->formattedPrice() }}
                            @unless ($selectedPlan->isFree())
                                / {{ $selectedPlan->billingLabel() }}
                            @endunless
                        </p>

                        <div class="mt-6 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Workspaces</span>
                                <span>{{ $selectedPlan->workspaceLimitLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Projects</span>
                                <span>{{ $selectedPlan->projectLimitLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Members</span>
                                <span>{{ $selectedPlan->memberLimitLabel() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/20">
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
                    <flux:button variant="ghost" x-on:click="$dispatch('close')">
                        Cancel
                    </flux:button>

                    <flux:button variant="primary" wire:click="confirmPlanChange">
                        Continue to Payment
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

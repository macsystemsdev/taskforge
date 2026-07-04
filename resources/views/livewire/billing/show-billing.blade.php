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
        return SubscriptionPlan::query()->where('is_active', true)->orderBy('price')->get();
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

    {{-- Current Subscription --}}
    <x-ui.card>

        <div class="flex items-start justify-between gap-6">

            <div>

                <p class="tf-panel-title">
                    Current Subscription
                </p>

                <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                    {{ $organization->subscription?->plan?->name ?? 'No Active Plan' }}
                </h2>

                <p class="mt-1 tf-muted">
                    {{ $organization->subscription?->status?->value ?? '-' }}
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
                    {{ $organization->subscription->plan->formattedPrice() }}

                    @if ($organization->subscription->plan->isFree())
                        <span class="font-normal text-zinc-500">
                            / {{ $organization->subscription->plan->billingLabel() }}
                        </span>
                    @endif
                </p>

            </div>

            <div>

                <p class="tf-muted">
                    Billing
                </p>

                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">
                    {{ $organization->subscription->plan->billingIntervalLabel() }}
                </p>

            </div>

            <div>

                <p class="tf-muted">
                    Renewal
                </p>

                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">
                    {{ $organization->subscription->ends_at?->format('M d, Y') ?? 'No active renewal' }}
                </p>

            </div>

        </div>

    </x-ui.card>

    {{-- Available Plans --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

        @foreach ($this->plans as $plan)
            @php
                $current = $organization->subscription->plan->is($plan);
            @endphp

            <x-ui.card class="flex h-full flex-col {{ $current ? 'border-indigo-500 ring-2 ring-indigo-500/10' : '' }}">

                <div class="flex items-start justify-between">

                    <div>

                        <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">
                            {{ $plan->name }}
                        </h3>

                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $plan->billingIntervalLabel() }}
                        </p>

                    </div>

                    @if ($current)
                        <span
                            class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                            Current
                        </span>
                    @endif

                </div>

                <div class="mt-6">

                    <p class="text-4xl font-bold text-zinc-950 dark:text-white">
                        {{ $plan->formattedPrice() }}

                        @if ($plan->price > 0)
                            <span class="text-base font-normal text-zinc-500">
                                / {{ $plan->billingLabel() }}
                            </span>
                        @endif
                    </p>

                </div>

                <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-white/10">

                    <ul class="space-y-3 text-sm text-zinc-700 dark:text-zinc-300">

                        <li class="flex items-center gap-2">
                            ✓ {{ $plan->workspaceLimitLabel() }} Workspaces
                        </li>

                        <li class="flex items-center gap-2">
                            ✓ {{ $plan->projectLimitLabel() }} Projects
                        </li>

                        <li class="flex items-center gap-2">
                            ✓ {{ $plan->memberLimitLabel() }} Members
                        </li>

                    </ul>

                </div>

                <div class="mt-auto pt-8">

                    @if ($current)
                        <button disabled class="tf-button-secondary w-full cursor-not-allowed opacity-70">

                            Current Plan

                        </button>
                    @else
                        <button wire:click="selectPlan({{ $plan->id }})" class="tf-button-primary w-full">

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

                    <p class="mt-2 tf-muted">
                        Review your new subscription before continuing to our secure payment provider.
                    </p>

                </div>

                <div class="grid gap-4 md:grid-cols-2">

                    {{-- Current Plan --}}
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-white/10">

                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            Current Plan
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            {{ $organization->subscription?->plan?->name ?? 'None' }}
                        </h3>

                        <p class="mt-1 tf-muted">
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

                    {{-- New Plan --}}
                    <div
                        class="rounded-xl border-2 border-indigo-500 bg-indigo-50/40 p-5 dark:border-indigo-400 dark:bg-indigo-500/10">

                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            New Plan
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            {{ $selectedPlan->name }}
                        </h3>

                        <p class="mt-1 font-medium text-indigo-600">
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

                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/20">

                    <h4 class="font-medium text-amber-900 dark:text-amber-300">
                        Before you continue
                    </h4>

                    <ul class="mt-3 space-y-2 text-sm text-amber-800 dark:text-amber-200">

                        <li>• Your current subscription remains active until payment is successfully completed.</li>

                        <li>• The selected plan becomes active immediately after payment confirmation.</li>

                        <li>• Future renewals will use the selected subscription plan.</li>

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

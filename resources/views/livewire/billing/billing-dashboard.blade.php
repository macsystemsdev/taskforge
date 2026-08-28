<div class="space-y-6">
    {{-- Header --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-500 p-6 text-white shadow-2xl shadow-indigo-950/10 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    {{ __('Billing & Plans') }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-indigo-50 sm:text-base">
                    {{ __('Select an organization, choose a plan, and pay securely.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Step 1: Select Organization --}}
    <section aria-label="Select organization">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">
            {{ __('1. Select Organization') }}
        </h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Choose which organization you want to manage billing for.') }}
        </p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->organizations as $org)
                <button 
                    wire:click="selectOrganization({{ $org->id }})"
                    class="group rounded-2xl border p-5 text-left transition-all
                        {{ $selectedOrganization?->id === $org->id 
                            ? 'border-indigo-500 bg-indigo-50/60 ring-2 ring-indigo-500/20 dark:border-indigo-400 dark:bg-indigo-500/10' 
                            : 'border-zinc-200 bg-white hover:border-zinc-300 hover:shadow-sm dark:border-white/10 dark:bg-zinc-900/70 dark:hover:border-white/20' }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-zinc-950 dark:text-white">
                                {{ $org->name }}
                            </h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $org->subscription?->plan?->name ?? 'No Plan' }}
                            </p>
                        </div>
                        
                        @if ($org->subscription?->isTrial())
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                Trial
                            </span>
                        @elseif ($org->subscription?->hasPendingPlan())
                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                Pending
                            </span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {{ $org->subscription?->plan?->billingIntervalLabel() ?? 'Free' }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400">
                            {{ $org->subscription?->plan?->formattedPrice() ?? 'Free' }}
                        </span>
                        
                        @if ($selectedOrganization?->id === $org->id)
                            <span class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                Selected
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Step 2: Select Plan --}}
    @if ($selectedOrganization)
        <section aria-label="Select plan">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">
                {{ __('2. Choose a Plan') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Select a plan for :name.', ['name' => $selectedOrganization->name]) }}
            </p>

            {{-- Trial banner --}}
            @if ($selectedOrganization->subscription?->canStartTrial())
                <div class="mt-4 rounded-2xl border border-indigo-200 bg-indigo-50/60 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-indigo-900 dark:text-indigo-300">
                                {{ __('Start your 14-day Pro trial') }}
                            </p>
                            <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-200">
                                {{ __('Full access to premium features — no credit card required.') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="primary" wire:click="startTrial">
                            {{ __('Start Free Trial') }}
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Plan cards --}}
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->plans as $plan)
                    @php
                        $isCurrent = $selectedOrganization->subscription?->plan?->is($plan) ?? false;
                        $isPending = $selectedOrganization->subscription?->pending_subscription_plan_id === $plan->id;
                        $isDisabled = $isCurrent || $isPending;
                    @endphp

                    <div class="flex h-full flex-col rounded-2xl border p-5 transition-all
                        {{ $isCurrent 
                            ? 'border-indigo-500 bg-indigo-50/60 ring-2 ring-indigo-500/20' 
                            : 'border-zinc-200 bg-white shadow-sm hover:shadow-md dark:border-white/10 dark:bg-zinc-900/70' }}"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $plan->name }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->billingIntervalLabel() }}</p>
                            </div>
                            
                            @if ($isCurrent)
                                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">Current</span>
                            @elseif ($isPending)
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Scheduled</span>
                            @endif
                        </div>

                        <div class="mt-4">
                            <p class="text-3xl font-semibold text-zinc-950 dark:text-white">
                                {{ $plan->formattedPrice() }}
                                @if (!$plan->isFree())
                                    <span class="text-base font-normal text-zinc-500">/ {{ $plan->billingLabel() }}</span>
                                @endif
                            </p>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex justify-between"><span>Workspaces</span><span class="font-medium">{{ $plan->workspaceLimitLabel() }}</span></li>
                            <li class="flex justify-between"><span>Projects</span><span class="font-medium">{{ $plan->projectLimitLabel() }}</span></li>
                            <li class="flex justify-between"><span>Members</span><span class="font-medium">{{ $plan->memberLimitLabel() }}</span></li>
                        </ul>

                        <div class="mt-auto pt-6">
                            @if ($isDisabled)
                                <flux:button disabled class="w-full">{{ $isCurrent ? 'Current Plan' : 'Already Scheduled' }}</flux:button>
                            @else
                                <flux:button variant="primary" wire:click="selectPlan({{ $plan->id }})" class="w-full">
                                    {{ __('Choose Plan') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/50 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
            <p class="text-zinc-500 dark:text-zinc-400">
                {{ __('Select an organization above to view available plans.') }}
            </p>
        </div>
    @endif

    {{-- Checkout Modal --}}
    <flux:modal wire:model="showCheckoutModal" class="max-w-lg">
        @if ($selectedPlan && $selectedOrganization)
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">
                        {{ __('Confirm Subscription') }}
                    </h2>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                        {{ __('Review your subscription before proceeding to payment.') }}
                    </p>
                </div>

                {{-- Summary --}}
                <div class="rounded-2xl border border-zinc-200 p-5 dark:border-white/10">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Organization</span>
                            <span class="font-medium">{{ $selectedOrganization->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Current Plan</span>
                            <span class="font-medium">{{ $selectedOrganization->subscription?->plan?->name ?? 'None' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">New Plan</span>
                            <span class="font-medium text-indigo-600">{{ $selectedPlan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Price</span>
                            <span class="font-medium">{{ $selectedPlan->formattedPrice() }}/{{ $selectedPlan->billingLabel() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Provider Selection --}}
                <div>
                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Select payment method') }}
                    </p>
                    <div class="mt-3 space-y-2">
                        @foreach ($this->availableProviders as $provider)
                            <label class="flex items-start gap-3 rounded-xl border p-4 transition
                                {{ $provider['available'] 
                                    ? 'cursor-pointer border-zinc-200 hover:border-indigo-400 dark:border-white/10 dark:hover:border-indigo-400' 
                                    : 'cursor-not-allowed opacity-50 border-zinc-200 dark:border-white/10' }}"
                            >
                                <input 
                                    type="radio" 
                                    wire:model="paymentProvider" 
                                    value="{{ $provider['value'] }}" 
                                    {{ !$provider['available'] ? 'disabled' : '' }}
                                    class="mt-1"
                                >
                                <div class="flex-1">
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $provider['label'] }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $provider['description'] }}</p>
                                </div>
                                @if (!$provider['available'])
                                    <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-500">Soon</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeCheckoutModal">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" wire:click="confirmPlanChange" wire:loading.attr="disabled">
                        {{ __('Continue to Payment') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>

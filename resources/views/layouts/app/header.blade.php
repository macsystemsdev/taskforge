<flux:header container class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
    @php
        $routeOrganization = request()->route('organization');
        $billingRoute = $routeOrganization
            ? route('organizations.billing', ['organization' => $routeOrganization])
            : route('billing.index');
        $isBillingRoute = request()->routeIs('organizations.billing') || request()->routeIs('billing.*') || request()->routeIs('billing.index');
    @endphp

    <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

    <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navbar.item>

        <flux:navbar.item icon="building-office-2" :href="route('organizations.index')" :current="request()->routeIs('organizations.*')" wire:navigate>
            {{ __('Organizations') }}
        </flux:navbar.item>

        <flux:navbar.item icon="folder" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
            {{ __('Projects') }}
        </flux:navbar.item>

        <flux:navbar.item icon="check-circle" :href="route('tasks.index')" :current="request()->routeIs('tasks.*')" wire:navigate>
            {{ __('Tasks') }}
        </flux:navbar.item>

        @if ($billingRoute)
            <flux:navbar.item icon="credit-card" :href="$billingRoute" :current="$isBillingRoute" wire:navigate>
                {{ __('Billing') }}
            </flux:navbar.item>
        @endif
    </flux:navbar>

    <flux:spacer />

    {{-- Global Search --}}
    <livewire:search.global-search />

    {{-- Notification Bell --}}
    <flux:dropdown position="bottom" align="end" class="max-w-sm">
        <button type="button"
            class="relative flex size-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-blue-50 hover:text-blue-600 dark:text-zinc-400 dark:hover:bg-blue-950/30 dark:hover:text-blue-400">
            <flux:icon.bell class="size-5" />
            <livewire:notifications.unread-count />
        </button>

        <flux:menu class="!p-0 overflow-hidden rounded-xl border border-zinc-200 shadow-xl dark:border-white/10">
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50/50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                <p class="text-sm font-semibold text-zinc-950 dark:text-white">Notifications</p>
                <a href="{{ route('notifications.index') }}" wire:navigate
                    class="text-xs font-medium text-zinc-500 hover:text-blue-600 dark:text-zinc-400 dark:hover:text-blue-400">
                    View all
                </a>
            </div>

            <div class="max-h-80 overflow-y-auto">
                @php
                    $notifications = auth()->user()->notifications()->latest()->take(3)->get();
                @endphp

                @forelse ($notifications as $notification)
                    <a href="{{ route('notifications.redirect', $notification->id) }}" wire:navigate
                        class="group flex items-start gap-3 border-b border-zinc-100 px-4 py-3 transition hover:bg-zinc-50 dark:border-white/5 dark:hover:bg-white/[0.02]">
                        <div class="mt-1">
                            @if (!$notification->read_at)
                                <span class="block size-2 rounded-full bg-blue-500"></span>
                            @else
                                <span class="block size-2 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">
                                {{ $notification->data['title'] ?? class_basename($notification->type) }}
                            </p>
                            <p class="mt-0.5 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $notification->data['message'] ?? ($notification->data['body'] ?? __('Notification received.')) }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-zinc-500">No notifications yet</p>
                    </div>
                @endforelse
            </div>
        </flux:menu>
    </flux:dropdown>

    <x-desktop-user-menu :showTeam="false" />
</flux:header>

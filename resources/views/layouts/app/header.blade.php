<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    @php
        $unreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
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
    </flux:navbar>

    <flux:spacer />

    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <flux:tooltip :content="__('Search')" position="bottom">
            <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
        </flux:tooltip>
        <flux:tooltip :content="__('Notifications')" position="bottom">
            <flux:navbar.item class="h-10 max-lg:hidden [&>div>svg]:size-5" icon="bell" :href="route('notifications.index')" wire:navigate>
                {{ __('Notifications') }}
                @if ($unreadNotifications)
                    <span class="ml-2 inline-flex items-center justify-center rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                        {{ $unreadNotifications }}
                    </span>
                @endif
            </flux:navbar.item>
        </flux:tooltip>
    </flux:navbar>

    <x-desktop-user-menu :showTeam="false" />
</flux:header>

<!-- Mobile Menu -->
<flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Platform')">
            <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard')  }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="building-office-2" :href="route('organizations.index')" :current="request()->routeIs('organizations.*')" wire:navigate>
                {{ __('Organizations') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="folder" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                {{ __('Projects') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="check-circle" :href="route('tasks.index')" :current="request()->routeIs('tasks.*')" wire:navigate>
                {{ __('Tasks') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                {{ __('Notifications') }}
                @if ($unreadNotifications)
                    <span class="ml-2 inline-flex items-center justify-center rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                        {{ $unreadNotifications }}
                    </span>
                @endif
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Administration')">
            <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit') || request()->routeIs('security.edit') || request()->routeIs('appearance.edit')" wire:navigate>
                {{ __('Settings') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="plus" :href="route('organizations.create')" :current="request()->routeIs('organizations.create')" wire:navigate>
            {{ __('New Organization') }}
        </flux:sidebar.item>
    </flux:sidebar.nav>
</flux:sidebar>

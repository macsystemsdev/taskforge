<flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-zinc-950/95">
    <flux:sidebar.header class="border-b border-zinc-200/70 pb-3 dark:border-white/10">
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <div class="px-2 py-3">
        <livewire:team-switcher />
    </div>

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Workspace')" class="grid gap-1">
            <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
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
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Administration')" class="grid gap-1">
            <flux:sidebar.item icon="users" :href="route('teams.index')" :current="request()->routeIs('teams.*')" wire:navigate>
                {{ __('Teams') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit') || request()->routeIs('security.edit') || request()->routeIs('appearance.edit')" wire:navigate>
                {{ __('Settings') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />

    <flux:sidebar.nav>
        <flux:modal.trigger name="create-team-switcher">
            <flux:sidebar.item icon="user-plus" href="#">
                {{ __('New Team') }}
            </flux:sidebar.item>
        </flux:modal.trigger>

        <flux:sidebar.item icon="plus" :href="route('organizations.create')" :current="request()->routeIs('organizations.create')" wire:navigate>
            {{ __('New Organization') }}
        </flux:sidebar.item>
    </flux:sidebar.nav>

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>

<flux:sidebar sticky collapsible="mobile"
    class="border-e border-blue-200 bg-gradient-to-b from-white via-blue-50 to-blue-100 shadow-sm backdrop-blur-xl dark:border-blue-800/40 dark:from-zinc-950 dark:via-blue-950/40 dark:to-blue-900/40">
    
    <flux:sidebar.header class="relative border-b border-zinc-200/60 pb-3 dark:border-white/10">
        <div class="flex items-center justify-between gap-3">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </div>
    </flux:sidebar.header>

    @php
        $currentProject = request()->route('project');
    @endphp

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Workspace')" class="grid gap-1">
            <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="building-office-2" :href="route('organizations.index')" :current="request()->routeIs('organizations.*')" wire:navigate>
                {{ __('Organizations') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="squares-2x2" :href="route('workspaces.index')" :current="request()->routeIs('workspaces.*')" wire:navigate>
                {{ __('Workspaces') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="folder" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                {{ __('Projects') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="check-circle" :href="route('tasks.index')" :current="request()->routeIs('tasks.*')" wire:navigate>
                {{ __('Tasks') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="users" :href="route('teams.index')" :current="request()->routeIs('teams.index')" wire:navigate>
                {{ __('Teams') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="credit-card" :href="route('billing.index')" :current="request()->routeIs('billing.*')" wire:navigate>
                {{ __('Billing') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                {{ __('Reports') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        @if ($currentProject)
            <flux:sidebar.group :heading="__('Current project')" class="grid gap-1 mt-4">
                <flux:sidebar.item icon="folder-open" :href="route('projects.show', $currentProject)" :current="request()->routeIs('projects.show')" wire:navigate>
                    {{ $currentProject->name }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            @if ($team = $currentProject?->team)
                <flux:sidebar.group :heading="__('Team')" class="grid gap-1 mt-4">
                    <flux:sidebar.item icon="users" :href="route('teams.edit', $team)" :current="request()->routeIs('teams.edit') && request()->route('team')->is($team)" wire:navigate>
                        {{ $team->name }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif
        @endif

        <flux:sidebar.group :heading="__('Administration')" class="grid gap-1 mt-4">
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

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>

@props(['showTeam' => true])

<flux:dropdown position="bottom" align="start">
    <button type="button" class="group flex w-full items-center rounded-lg p-1 hover:bg-zinc-800/5 dark:hover:bg-white/10" data-test="sidebar-menu-button">
        <flux:avatar :initials="auth()->user()->initials()" size="sm" />
        <div class="in-data-flux-sidebar-collapsed-desktop:hidden mx-2 grid flex-1 text-start text-sm leading-tight">
            <span class="truncate font-medium text-zinc-500 group-hover:text-zinc-800 dark:text-white/80 dark:group-hover:text-white">{{ auth()->user()->name }}</span>
            @if($showTeam && auth()->user()->currentTeam)
                <span class="truncate text-xs text-zinc-400 dark:text-zinc-500">{{ auth()->user()->currentTeam->name }}</span>
            @endif
        </div>
        <flux:icon name="chevrons-up-down" variant="micro" class="in-data-flux-sidebar-collapsed-desktop:hidden ms-auto size-4 text-zinc-400 group-hover:text-zinc-800 dark:text-white/80 dark:group-hover:text-white" />
    </button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                    x-bind:disabled="submitting"
                >
                    <span x-show="!submitting">{{ __('Log out') }}</span>
                    <span x-show="submitting" class="inline-flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z"></path>
                        </svg>
                        <span>{{ __('Logging out...') }}</span>
                    </span>
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>

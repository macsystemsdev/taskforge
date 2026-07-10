<div class="flex items-start gap-6 max-md:flex-col">
    <div class="w-full rounded-2xl border border-zinc-200 bg-white/80 p-3 shadow-sm backdrop-blur md:w-[240px] dark:border-white/10 dark:bg-zinc-900/70">
        <flux:navlist aria-label="{{ __('Settings') }}" class="space-y-1">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('teams.index')" :current="request()->routeIs('teams.*')" wire:navigate>{{ __('Teams') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch rounded-3xl border border-zinc-200 bg-white/80 p-4 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70 max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-6 w-full max-w-2xl">
            {{ $slot }}
        </div>
    </div>
</div>

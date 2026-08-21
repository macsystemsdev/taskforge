<div>
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-zinc-950 dark:text-white">Currently viewing</p>
                    <p class="mt-0.5 text-sm text-zinc-500">
                        {{ count($users) }} {{ \Illuminate\Support\Str::plural(__('member'), count($users)) }}
                        {{ __('online') }}
                    </p>
                </div>
                <span class="size-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
            </div>
        </div>

        <div class="space-y-1 p-4">
            @forelse ($users as $user)
                <div wire:key="presence-user-{{ $user['id'] }}"
                    class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user['name'], 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $user['name'] }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Viewing this project') }}</p>
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-zinc-500">{{ __('No active viewers.') }}</p>
            @endforelse
        </div>
    </x-ui.card>
</div>

<div>
    <x-ui.card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-semibold text-zinc-950 dark:text-white">
                    {{ __('Currently viewing') }}
                </p>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ count($users) }}
                    {{ \Illuminate\Support\Str::plural(
                        __('member'),
                        count($users)
                    ) }}
                    {{ __('online') }}
                </p>
            </div>

            <span
                class="size-2 rounded-full bg-emerald-500"
                aria-hidden="true"
            ></span>
        </div>

        <div class="space-y-3">
            @forelse ($users as $user)
                <div
                    wire:key="presence-user-{{ $user['id'] }}"
                    class="flex items-center gap-3"
                >
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        {{ \Illuminate\Support\Str::upper(
                            \Illuminate\Support\Str::substr(
                                $user['name'],
                                0,
                                1
                            )
                        ) }}
                    </div>

                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-zinc-950 dark:text-white"
                        >
                            {{ $user['name'] }}
                        </p>

                        <p
                            class="text-xs text-zinc-500 dark:text-zinc-400"
                        >
                            {{ __('Viewing this project') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No active viewers.') }}
                </p>
            @endforelse
        </div>
    </x-ui.card>
</div>
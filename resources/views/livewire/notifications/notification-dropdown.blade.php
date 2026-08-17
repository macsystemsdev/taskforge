<div>
    {{-- Header --}}
    <div class="border-b border-zinc-200 px-4 py-3 dark:border-white/10">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-semibold text-zinc-950 dark:text-white">
                    {{ __('Notifications') }}
                </p>

                @if ($this->unreadCount)
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $this->unreadCount }}
                        {{ \Illuminate\Support\Str::plural(__('unread'), $this->unreadCount) }}
                    </p>
                @endif
            </div>

            <a
                href="{{ route('notifications.index') }}"
                wire:navigate
                class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"
            >
                {{ __('View all') }}
            </a>
        </div>
    </div>

    {{-- List --}}
    <div class="max-h-96 overflow-y-auto">
        @forelse ($this->notifications as $notification)
            <flux:menu.item
                as="a"
                href="{{ route('notifications.redirect', $notification->id) }}"
                wire:navigate
                class="block border-b border-zinc-200 px-4 py-3 !p-0 last:border-0 dark:border-white/10"
            >
                <div class="flex gap-3 px-4 py-3">
                    <div class="mt-2 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-rose-600' }}"></div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">
                                {{ $notification->data['title'] ?? class_basename($notification->type) }}
                            </p>

                            <span class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="mt-1 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $notification->data['message'] ?? ($notification->data['body'] ?? __('Notification received.')) }}
                        </p>
                    </div>
                </div>
            </flux:menu.item>
        @empty
            <div class="px-4 py-8 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No notifications yet.') }}
                </p>
            </div>
        @endforelse
    </div>

    {{-- Footer --}}
    <div class="border-t border-zinc-200 p-2 dark:border-white/10">
        <a
            href="{{ route('notifications.index') }}"
            wire:navigate
            class="block rounded-lg px-3 py-2 text-center text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5"
        >
            {{ __('View all notifications') }}
        </a>
    </div>
</div>
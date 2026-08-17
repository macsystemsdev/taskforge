<div>
    <x-ui.page size="5xl">
        <x-ui.page-header
            :title="__('Notifications')"
            :description="__('Review recent assignments, membership changes, and workflow events that need attention.')"
        >
            <x-slot:actions>
                @if ($this->unreadCount)
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-rose-600 px-3 py-1 text-sm font-semibold text-white"
                        >
                            {{ $this->unreadCount }}
                            {{ \Illuminate\Support\Str::plural(__('new'), $this->unreadCount) }}
                        </span>

                        <button
                            type="button"
                            wire:click="markAllAsRead"
                            wire:loading.attr="disabled"
                            wire:target="markAllAsRead"
                            class="rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900"
                        >
                            <span wire:loading.remove wire:target="markAllAsRead">
                                {{ __('Mark all as read') }}
                            </span>

                            <span wire:loading wire:target="markAllAsRead">
                                {{ __('Marking...') }}
                            </span>
                        </button>
                    </div>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card>
            <div class="space-y-3">
                @forelse ($this->notifications as $notification)
                    <div
                        wire:key="notification-{{ $notification->id }}"
                        class="flex gap-3 rounded-lg border border-zinc-200 p-4 transition-colors hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/5"
                    >
                        <div
                            class="mt-1 size-2 rounded-full {{ $notification->read_at
                                ? 'bg-zinc-300 dark:bg-zinc-700'
                                : 'bg-zinc-950 dark:bg-white' }}"
                        ></div>

                        <a
                            href="{{ route('notifications.redirect', $notification->id) }}"
                            wire:navigate
                            class="min-w-0 flex-1"
                        >
                            <div
                                class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p class="font-medium text-zinc-950 dark:text-white">
                                    {{ $notification->data['title']
                                        ?? class_basename($notification->type) }}

                                    @if (! $notification->read_at)
                                        <span
                                            class="ml-2 inline-flex items-center rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white"
                                        >
                                            {{ __('New') }}
                                        </span>
                                    @endif
                                </p>

                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <p
                                class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400"
                            >
                                {{ $notification->data['message']
                                    ?? ($notification->data['body']
                                    ?? __('Notification received.')) }}
                            </p>
                        </a>

                        @if (! $notification->read_at)
                            <button
                                type="button"
                                wire:click="markAsRead('{{ $notification->id }}')"
                                wire:loading.attr="disabled"
                                wire:target="markAsRead('{{ $notification->id }}')"
                                class="shrink-0 text-xs font-medium text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"
                                title="{{ __('Mark as read') }}"
                            >
                                {{ __('Mark read') }}
                            </button>
                        @endif
                    </div>
                @empty
                    <x-ui.empty-state
                        title="No notifications"
                        description="Recent updates and workflow alerts will appear here once there is something to review."
                    />
                @endforelse
            </div>
        </x-ui.card>
    </x-ui.page>
</div>
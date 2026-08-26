<div>
    {{-- Header --}}
    <div class="relative border-b border-zinc-200/80 bg-gradient-to-b from-zinc-50/50 to-transparent px-4 py-4 dark:border-white/10 dark:from-white/[0.03]">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-white/5">
                        <flux:icon.bell class="size-4 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    @if ($this->unreadCount)
                        <span class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white shadow-sm">
                            {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
                        </span>
                    @endif
                </div>
                
                <div>
                    <p class="font-semibold text-zinc-950 dark:text-white">
                        {{ __('Notifications') }}
                    </p>

                    @if ($this->unreadCount)
                        <p class="text-xs font-medium text-rose-600 dark:text-rose-400">
                            {{ $this->unreadCount }}
                            {{ \Illuminate\Support\Str::plural(__('unread'), $this->unreadCount) }}
                        </p>
                    @else
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('You\'re all caught up!') }}
                        </p>
                    @endif
                </div>
            </div>

            <a
                href="{{ route('notifications.index') }}"
                wire:navigate
                class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-white"
            >
                {{ __('View all') }}
                <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    {{-- List --}}
    <div class="max-h-96 overflow-y-auto overscroll-contain">
        @forelse ($this->notifications as $notification)
            <flux:menu.item
                as="a"
                href="{{ route('notifications.redirect', $notification->id) }}"
                wire:navigate
                class="group relative block border-b border-zinc-100 !p-0 transition-colors duration-200 last:border-0 hover:bg-zinc-50/80 dark:border-white/5 dark:hover:bg-white/[0.02]"
            >
                <div class="flex gap-3 px-4 py-3.5">
                    {{-- Status indicator --}}
                    <div class="mt-1.5 flex flex-col items-center gap-1">
                        <div class="size-2.5 shrink-0 rounded-full transition-all duration-200 {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-rose-500 shadow-sm shadow-rose-500/50 ring-2 ring-rose-500/20' }}"></div>
                        @if (!$notification->read_at)
                            <div class="h-full w-px bg-gradient-to-b from-rose-500/30 to-transparent"></div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <p class="truncate text-sm font-medium text-zinc-950 transition-colors group-hover:text-zinc-900 dark:text-white dark:group-hover:text-zinc-100">
                                {{ $notification->data['title'] ?? class_basename($notification->type) }}
                            </p>

                            <span class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            {{ $notification->data['message'] ?? ($notification->data['body'] ?? __('Notification received.')) }}
                        </p>

                        {{-- Action hint on hover --}}
                        <div class="mt-2 hidden items-center gap-1 text-xs font-medium text-zinc-500 group-hover:flex dark:text-zinc-400">
                            <span>{{ __('Click to view') }}</span>
                            <svg class="size-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </flux:menu.item>
        @empty
            <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
                <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-white/5">
                    <flux:icon.bell-slash class="size-7 text-zinc-400 dark:text-zinc-500" />
                </div>
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                    {{ __('No notifications yet') }}
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('We\'ll notify you when something important happens.') }}
                </p>
            </div>
        @endforelse
    </div>

    {{-- Footer --}}
    @if ($this->notifications->count() > 0)
        <div class="border-t border-zinc-200/80 bg-zinc-50/50 p-2 dark:border-white/10 dark:bg-white/[0.02]">
            <a
                href="{{ route('notifications.index') }}"
                wire:navigate
                class="group flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition-all duration-200 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5"
            >
                {{ __('View all notifications') }}
                <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 7l5 5-5 5" />
                </svg>
            </a>
        </div>
    @endif
</div>
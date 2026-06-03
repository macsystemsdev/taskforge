<x-layouts::app :title="__('Notifications')">
    @php
        $notifications = auth()->user()->notifications()->latest()->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();
    @endphp

    <x-ui.page size="5xl">
        <x-ui.page-header
            :title="__('Notifications')"
            :description="__('Review recent assignments, membership changes, and workflow events that need attention.')"
        >
            <x-slot:actions>
                @if ($unreadCount)
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-rose-600 px-3 py-1 text-sm font-semibold text-white">
                            {{ $unreadCount }} {{ \Illuminate\Support\Str::plural(__('new'), $unreadCount) }}
                        </span>
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-900">
                                {{ __('Mark all as read') }}
                            </button>
                        </form>
                    </div>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card>
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    <div class="flex gap-3 rounded-lg border border-zinc-200 p-4 dark:border-white/10">
                        <div class="mt-1 size-2 rounded-full {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-zinc-950 dark:bg-white' }}"></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-medium text-zinc-950 dark:text-white">
                                    {{ $notification->data['title'] ?? class_basename($notification->type) }}
                                    @if (! $notification->read_at)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                                            {{ __('New') }}
                                        </span>
                                    @endif
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                {{ $notification->data['message'] ?? $notification->data['body'] ?? 'Notification received.' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state title="No notifications" description="Recent updates and workflow alerts will appear here once there is something to review." />
                @endforelse
            </div>
        </x-ui.card>
    </x-ui.page>
</x-layouts::app>

<x-layouts::app :title="__('Notifications')">
    @php
        $notifications = auth()->user()->notifications()->latest()->get();
    @endphp

    <x-ui.page size="5xl">
        <x-ui.page-header
            :title="__('Notifications')"
            :description="__('Review recent assignments, invitations, and workflow events that need attention.')"
        />

        <x-ui.card>
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    <div class="flex gap-3 rounded-lg border border-zinc-200 p-4 dark:border-white/10">
                        <div class="mt-1 size-2 rounded-full {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-zinc-950 dark:bg-white' }}"></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-medium text-zinc-950 dark:text-white">
                                    {{ $notification->data['title'] ?? class_basename($notification->type) }}
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
                    <x-ui.empty-state title="No notifications" description="Workflow and invitation updates will appear here when there is something to review." />
                @endforelse
            </div>
        </x-ui.card>
    </x-ui.page>
</x-layouts::app>

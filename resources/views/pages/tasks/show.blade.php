<x-layouts::app :title="$task->title">
    @php
        $task->loadMissing(['project.team.workspace', 'assignee', 'creator', 'activityLogs.user']);
        $activityLogs = $task->activityLogs->sortByDesc('created_at');
    @endphp

    <x-ui.page>
        <x-ui.page-header :title="$task->title" :description="$task->description ?: __('No task description has been added yet.')" :eyebrow="$task->project->workspace->name . ' / ' . $task->project->team->name . ' / ' . $task->project->name">
            <x-slot:actions>
                <x-ui.status-badge :status="$task->status" />

            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <x-ui.card>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <p class="tf-muted">Assignee</p>
                            <div class="mt-2 flex items-center gap-2">
                                <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                <p class="font-medium text-zinc-950 dark:text-white">
                                    {{ $task->assignee?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="tf-muted">Due date</p>
                            <p class="mt-2 font-medium text-zinc-950 dark:text-white">
                                {{ $task->due_date?->format('M d, Y') ?? 'No date' }}
                            </p>

                            @if ($task->isOverdue())
                                <p class="mt-1 text-sm font-medium text-red-600">
                                    Overdue
                                </p>
                            @endif
                        </div>

                        <div>
                            <p class="tf-muted">Created by</p>
                            <p class="mt-2 font-medium text-zinc-950 dark:text-white">
                                {{ $task->creator?->name ?? 'Unknown' }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>

                @livewire('comments.comment-section', [
                    'commentable' => $task,
                ])
            </div>

            <aside class="space-y-6">
                <x-ui.card>
                    <h2 class="tf-panel-title">Task Metadata</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">Project</dt>
                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                <a href="{{ route('projects.show', $task->project) }}" class="hover:underline"
                                    wire:navigate>
                                    {{ $task->project->name }}
                                </a>
                            </dd>
                        </div>
                        
                         <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">
                                Team
                            </dt>

                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->project->team->name }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">Workspace</dt>
                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->project->workspace->name }}</dd>
                        </div>
                       
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </x-ui.card>


                <x-ui.card>
                    <h2 class="tf-panel-title">Activity</h2>
                    <div class="mt-5 space-y-4">
                        @forelse ($activityLogs as $log)
                            <div class="relative border-l border-zinc-200 pl-4 dark:border-white/10">
                                <span
                                    class="absolute -left-1.5 top-1.5 size-3 rounded-full border-2 border-white bg-zinc-400 dark:border-zinc-900 dark:bg-zinc-500"></span>
                                <p class="text-sm font-medium text-zinc-950 dark:text-white">
                                    {{ str($log->event)->headline() }}
                                </p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $log->user?->name ?? 'System' }} - {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @empty
                            <x-ui.empty-state title="No activity yet"
                                description="Task events will appear here as work changes." class="py-8" />
                        @endforelse
                    </div>
                </x-ui.card>


            </aside>
        </div>
    </x-ui.page>
</x-layouts::app>

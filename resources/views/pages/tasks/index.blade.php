<x-layouts::app :title="__('Tasks')">
    @php
        $tasks = \App\Models\Task::query()
            ->with(['project.team', 'assignee'])
            ->where(function ($query) {
                $query
                    ->where('assigned_id', auth()->id())
                    ->orWhere('creator_id', auth()->id())
                    ->orWhereHas(
                        'project.team.members',
                        fn($memberQuery) => $memberQuery->where('users.id', auth()->id()),
                    );
            })
            ->latest()
            ->get();
    @endphp

    <x-ui.page>
        <div class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
            <x-ui.page-header :title="__('Tasks')" :description="__('Monitor task ownership, status, deadlines, and execution progress across projects.')" />
        </div>

        <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
            @if ($tasks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Assignee</th>
                                <th>Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr class="tf-row-link">
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}"
                                            class="font-medium text-zinc-950 hover:underline dark:text-white"
                                            wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                        @if ($task->description)
                                            <p class="mt-1 max-w-xl truncate text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $task->description }}</p>
                                        @endif
                                    </td>
                                    <td>{{ $task->project->name }}</td>
                                    <td><x-ui.status-badge :status="$task->status->value" /></td>

                                    <td>
                                        <div class="flex items-center gap-2">
                                            <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                            <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>

                                        <div class="flex flex-col">

                                            <span>
                                                {{ $task->due_date?->format('M d, Y') ?? 'No date' }}
                                            </span>

                                            @if ($task->isOverdue())
                                                <span class="text-xs font-medium text-red-600">
                                                    Overdue
                                                </span>
                                            @endif

                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5">
                    <x-ui.empty-state title="No tasks yet"
                        description="Tasks created inside projects will appear here for quick operational review." />
                </div>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-layouts::app>

<x-layouts::app :title="__('Tasks')">
    @php
        $tasks = \App\Models\Task::query()
            ->with(['project.workspace.organization', 'assignee'])
            ->where(function ($query) {
                $query
                    ->where('assigned_to', auth()->id())
                    ->orWhere('created_by', auth()->id())
                    ->orWhereHas('project.workspace.organization.members', fn ($memberQuery) => $memberQuery->where('users.id', auth()->id()));
            })
            ->latest()
            ->get();
    @endphp

    <x-ui.page>
        <x-ui.page-header
            :title="__('Tasks')"
            :description="__('A consolidated task queue with status, priority, ownership, and due-date signals.')"
        />

        <x-ui.card padding="p-0" class="overflow-hidden">
            @if ($tasks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr class="tf-row-link">
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-zinc-950 hover:underline dark:text-white" wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                        @if ($task->description)
                                            <p class="mt-1 max-w-xl truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $task->description }}</p>
                                        @endif
                                    </td>
                                    <td>{{ $task->project->name }}</td>
                                    <td><x-ui.status-badge :status="$task->status" /></td>
                                    <td><x-ui.priority-badge :priority="$task->priority" /></td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                            <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $task->due_date?->format('M d, Y') ?? 'No date' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5">
                    <x-ui.empty-state title="No tasks yet" description="Tasks created inside projects will appear here for quick operational review." />
                </div>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-layouts::app>

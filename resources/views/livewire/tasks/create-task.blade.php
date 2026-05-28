<?php

use App\Actions\Tasks\CreateTaskAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public ?string $statusFilter = null;

    public string $title = '';

    public string $description = '';

    public ?int $assigned_to = null;

    public string $priority = 'medium';

    public ?string $due_date = null;

    public function createTask(CreateTaskAction $action)
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'assigned_to' => ['nullable', 'exists:users,id'],

            'priority' => ['required', 'in:low,medium,high,urgent'],

            'due_date' => ['nullable', 'date'],
        ]);

        $data = new CreateTaskData(title: $validated['title'], description: $validated['description'] ?? null, assigned_to: $validated['assigned_to'] ?? null, priority: $validated['priority'], due_date: $validated['due_date'] ?? null);

        $action->handle(project: $this->project, data: $data);

        $this->reset(['title', 'description', 'assigned_to', 'priority', 'due_date']);

        Flux::toast(variant: 'success', text: __('Task created successfully.'));

        $this->dispatch('task-created');
    }

    public function render()
    {
        $this->project->load(['tasks.assignee']);

        return view('livewire.tasks.create-task', [
            'members' => $this->project->workspace->organization->members,
            'tasks' => $this->project->tasks()
                ->with('assignee')
                ->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))
                ->latest()
                ->get(),
        ]);
    }
};
?>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_1fr]">

    {{-- TASK CREATION FORM --}}
    <div>

        <x-ui.card class="space-y-5">

            <div>
                <h2 class="tf-panel-title">Create Task</h2>
                <p class="tf-panel-subtitle">Add work directly to this project.</p>
            </div>

            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit="createTask" class="space-y-5">

                <div class="space-y-2">
                    <label for="task-title">
                        Title
                    </label>

                    <input id="task-title" type="text" wire:model="title" class="w-full px-3 py-2.5">

                    @error('title')
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="task-description">
                        Description
                    </label>

                    <textarea id="task-description" wire:model="description" rows="4" class="w-full px-3 py-2.5"></textarea>
                </div>

                <div class="space-y-2">
                    <label for="task-assignee">
                        Assign To
                    </label>

                    <select id="task-assignee" wire:model="assigned_to" class="w-full px-3 py-2.5">
                        <option value="">
                            Unassigned
                        </option>

                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="space-y-2">
                    <label for="task-priority">
                        Priority
                    </label>

                    <select id="task-priority" wire:model="priority" class="w-full px-3 py-2.5">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="task-due-date">
                        Due Date
                    </label>

                    <input id="task-due-date" type="date" wire:model="due_date" class="w-full px-3 py-2.5">
                </div>

                <button type="submit" class="tf-button-primary w-full">
                    Create Task
                </button>

            </form>

        </x-ui.card>

    </div>

    {{-- TASK LIST --}}
    <div>

        <x-ui.card padding="p-0" class="overflow-hidden">

            <div class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="tf-panel-title">Project Tasks</h2>
                    <p class="tf-panel-subtitle">{{ $project->tasks->count() }} tasks in this project.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="$set('statusFilter', null)" class="{{ $statusFilter === null ? 'tf-button-primary' : 'tf-button-secondary' }} px-3 py-2">
                        All
                    </button>
                    @foreach (\App\Enums\TaskStatus::cases() as $status)
                        <button type="button" wire:click="$set('statusFilter', '{{ $status->value }}')" class="{{ $statusFilter === $status->value ? 'tf-button-primary' : 'tf-button-secondary' }} px-3 py-2">
                            {{ str($status->value)->headline() }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($tasks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assignee</th>
                                <th>Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr class="tf-row-link cursor-pointer" onclick="window.location.href='{{ route('tasks.show', $task) }}'">
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-zinc-950 hover:underline dark:text-white" wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                        @if ($task->description)
                                            <p class="mt-1 max-w-xl truncate text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $task->description }}
                                            </p>
                                        @endif
                                    </td>
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
                    <x-ui.empty-state title="No matching tasks" description="Create a task or change the status filter to widen the list." />
                </div>
            @endif

        </x-ui.card>

    </div>

</div>

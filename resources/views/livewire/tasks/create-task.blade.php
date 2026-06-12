<?php

use App\Actions\Tasks\CreateTaskAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public Project $project;

    public string $title = '';

    public ?string $statusFilter = null;

    public ?string $description = null;

    public ?int $assigneeId = null;

    public ?string $dueDate = null;

    #[Computed]
    public function teamMembers()
    {
        return $this->project->team->members->sortBy('name');
    }

    #[Computed]
    public function tasks()
    {
        return $this->project->tasks()->with('assignee')->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))->latest()->get();
    }

    public function mount(Project $project): void
    {
        $this->project = $project;

        $this->project->loadCount('tasks');
    }

    public function createTask(CreateTaskAction $action): void
    {
        Gate::authorize('createTask', $this->project);

        $validated = $this->validate();

        $task = $action->handle($this->project, CreateTaskData::from($validated));

        $this->reset(['title', 'description', 'assigneeId', 'dueDate']);

        $this->dispatch('task-created', taskId: $task->id);
    }

    public function render()
    {
        $this->project->load(['tasks.assignee']);

        return view('livewire.tasks.create-task', [
            'members' => $this->project->workspace->organization->members,
            'tasks' => $this->project->tasks()->with('assignee')->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))->latest()->get(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'assigneeId' => ['nullable', 'integer'],

            'dueDate' => ['nullable', 'date', 'after_or_equal:today'],
        ];
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
                <div
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit="createTask" class="space-y-5">

                <div class="space-y-2">
                    <label>Task</label>

                    <input type="text" wire:model="title" class="w-full px-3 py-2.5">
                    @error('title')
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label>Description</label>

                    <textarea rows="4" wire:model="description" class="w-full px-3 py-2.5"></textarea>
                </div>


                <div class="space-y-2">
                    <label>Assignee</label>

                    <select wire:model="assigneeId" class="w-full px-3 py-2.5">
                        <option value="">
                            Unassigned
                        </option>

                        @foreach ($this->teamMembers as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="space-y-2">
                    <label>Due Date</label>

                    <input type="date" wire:model="dueDate" class="w-full px-3 py-2.5">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="tf-button-primary">
                        Create Task
                    </button>
                </div>

            </form>

        </x-ui.card>

    </div>

    {{-- TASK LIST --}}
    <div>

        <x-ui.card padding="p-0" class="overflow-hidden">

            <div
                class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="tf-panel-title">Project Tasks</h2>
                    <p class="tf-panel-subtitle">{{ $project->tasks_count }} tasks in this project.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="$set('statusFilter', null)"
                        class="{{ $statusFilter === null ? 'tf-button-primary' : 'tf-button-secondary' }} px-3 py-2">
                        All
                    </button>
                    @foreach (\App\Domain\Tasks\TaskStatus::cases() as $status)
                        <button type="button" wire:click="$set('statusFilter', '{{ $status->value }}')"
                            class="{{ $statusFilter === $status->value ? 'tf-button-primary' : 'tf-button-secondary' }} px-3 py-2">
                            {{ str($status->value)->headline() }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($this->tasks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Assignee</th>
                                <th>Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->tasks as $task)
                                <tr>
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}"
                                            class="font-medium text-zinc-950 hover:underline dark:text-white"
                                            wire:navigate>
                                            {{ $task->title }}
                                        </a>

                                    </td>
                                    <td><x-ui.status-badge :status="$task->status" /></td>

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
                    <x-ui.empty-state title="No matching tasks"
                        description="Create a task or change the status filter to widen the list." />
                </div>
            @endif

        </x-ui.card>

    </div>

</div>

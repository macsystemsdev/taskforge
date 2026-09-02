<?php

use App\Actions\Tasks\CreateTaskAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Domain\Task\TaskPriority;
use App\Services\Tasks\TaskResourceService;
use Illuminate\Validation\Rule;

new class extends Component {
    public Project $project;

    public string $title = '';

    public string $priority = TaskPriority::MEDIUM->value;

    public array $resourceIds = [];

    public string $statusFilter = 'all';

    public ?string $description = null;

    public ?int $assigneeId = null;

    public string $resourceSearch = '';

    public ?string $dueDate = null;

    #[Computed]
    public function teamMembers()
    {
        return $this->project->team->members->sortBy('name');
    }

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
    ];

    #[Computed]
    public function tasks()
    {
        return $this->project
            ->tasks()
            ->with('assignee')
            ->when($this->statusFilter !== 'all', fn($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->get();
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

        // The action handles everything:
        // - Creating the task
        // - Assigning the assignee
        // - Attaching resources via $data->resourceIds
        // - Creating activity logs
        // - Sending notifications
        $task = $action->handle($this->project, CreateTaskData::from($validated));

        // Reset all form fields
        $this->reset(['title', 'description', 'assigneeId', 'dueDate', 'priority', 'resourceIds', 'resourceSearch']);

        // Set priority back to default
        $this->priority = TaskPriority::MEDIUM->value;

        // Dispatch event for Livewire 3 to refresh the task list
        $this->dispatch('task-created', taskId: $task->id);

        // Flash success message
        session()->flash('success', 'Task created successfully!');
    }

    #[On('task-created')]
    public function refreshTasks()
    {
        // This will automatically refresh the tasks computed property
    }

    #[Computed]
    public function availableResources()
    {
        // Get project file references from the service
        $resources = app(TaskResourceService::class)->projectResources($this->project);

        // If it's a collection, load the storedFile relationship
        if ($resources->isNotEmpty()) {
            // Load storedFile for each resource
            $resources->load('storedFile');
        }

        return $resources;
    }

    public function removeResource($resourceId)
    {
        $this->resourceIds = array_filter($this->resourceIds, function ($id) use ($resourceId) {
            return $id != $resourceId;
        });
    }

    public function render()
    {
        $this->project->load(['tasks.assignee']);

        return view('livewire.tasks.create-task', [
            'members' => $this->project->workspace->organization->members,
            'tasks' => $this->tasks,
            'organization' => $this->project->workspace->organization,
            'taskLimit' => $this->project->workspace->organization->currentPlan()?->max_tasks,
        ]);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'assigneeId' => ['nullable', 'integer'],

            'dueDate' => ['nullable', 'date', 'after_or_equal:today'],

            'priority' => ['required', Rule::enum(TaskPriority::class)],

            'resourceIds' => ['array'],

            'resourceIds.*' => ['integer'],
        ];
    }
};
?>


@php
    $organization = $project->workspace->organization;
    $taskLimit = $organization->currentPlan()?->max_tasks;
@endphp

<div class="space-y-6">
    {{-- Create Task --}}
    <div>
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Create Task</h2>
                <p class="mt-0.5 text-sm text-zinc-500">Add work directly to this project.</p>
            </div>

            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-white/10 dark:bg-zinc-950/40 dark:text-zinc-400">
                    Tasks in use: {{ $organization->taskUsage() }} / {{ $taskLimit === null ? 'Unlimited' : $taskLimit }}
                </div>

                <form wire:submit="createTask" class="space-y-5">
                    {{-- Title --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Task</label>
                        <input type="text" wire:model="title" placeholder="Enter task title"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white" />
                        @error('title')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                        <textarea rows="4" wire:model="description" placeholder="Add more details..."
                            class="w-full resize-none rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white"></textarea>
                    </div>

                    {{-- Grid: Assignee, Due Date, Priority --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Assignee</label>
                            <select wire:model="assigneeId"
                                class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white">
                                <option value="">Unassigned</option>
                                @foreach ($this->teamMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Due Date</label>
                            <input type="date" wire:model="dueDate"
                min="{{ now()->format('Y-m-d') }}"
                @if ($project->due_date) max="{{ $project->due_date->format('Y-m-d') }}" @endif
                                class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white" />
                            @error('dueDate')
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Priority</label>
                            <select wire:model="priority"
                                class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white">
                                @foreach (\App\Domain\Task\TaskPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}">{{ ucfirst($priority->value) }}</option>
                                @endforeach
                            </select>
                            @error('priority')
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Resources --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Task Resources</label>
                        <p class="text-xs text-zinc-500">Select existing project resources that are relevant to this task.</p>

                        <div class="relative">
                            <input type="text" wire:model.live="resourceSearch" placeholder="Search resources..."
                                class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white" />

                            @php
                                $allResources = $this->availableResources;
                                $resourceCount = $allResources->count();
                            @endphp

                            @if ($resourceCount > 0 && empty($resourceSearch))
                                <div class="mt-1 text-xs text-zinc-400">{{ $resourceCount }} resources available</div>
                            @endif

                            @if (!empty($resourceSearch) && $resourceCount > 0)
                                <div class="absolute z-20 mt-1 w-full rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-950/95 max-h-48 overflow-y-auto">
                                    @php
                                        $searchTerm = strtolower(trim($resourceSearch));
                                        $filteredResources = collect();
                                        foreach ($allResources as $resource) {
                                            $resourceName = $resource->storedFile?->original_filename ?? ($resource->name ?? '');
                                            if (strpos(strtolower($resourceName), $searchTerm) !== false) {
                                                $filteredResources->push($resource);
                                            }
                                        }
                                    @endphp

                                    @if ($filteredResources->isNotEmpty())
                                        @foreach ($filteredResources as $resource)
                                            <label class="flex items-center gap-3 px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-900/50 cursor-pointer">
                                                <input type="checkbox" wire:model.live="resourceIds" value="{{ $resource->id }}"
                                                    class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                                                <span class="flex-1 truncate text-sm text-zinc-700 dark:text-zinc-300">
                                                    {{ $resource->storedFile?->original_filename ?? ($resource->name ?? 'Unnamed') }}
                                                </span>
                                                <span class="text-xs text-zinc-400">
                                                    {{ number_format(($resource->storedFile?->size ?? 0) / 1024, 1) }} KB
                                                </span>
                                            </label>
                                        @endforeach
                                    @else
                                        <div class="px-3 py-2 text-sm text-zinc-500">No matching resources found</div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if (!empty($resourceIds))
                            <div class="flex flex-wrap gap-2">
                                @foreach ($allResources->whereIn('id', $resourceIds) as $resource)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                        {{ $resource->storedFile?->original_filename ?? ($resource->name ?? 'Unnamed') }}
                                        <button type="button" wire:click="removeResource({{ $resource->id }})"
                                            class="hover:text-blue-900 dark:hover:text-blue-100">×</button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @error('resourceIds')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end border-t border-zinc-200 pt-4 dark:border-white/10">
                        @if ($organization->canCreateTask())
                            <button type="submit" wire:loading.attr="disabled" wire:target="createTask"
                                class="tf-button-primary inline-flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="createTask">Create Task</span>
                                <span wire:loading.flex wire:target="createTask" class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z"></path>
                                    </svg>
                                    <span>Creating...</span>
                                </span>
                            </button>
                        @else
                            <a href="{{ route('organizations.billing', $organization) }}" class="tf-button-secondary" wire:navigate>
                                Upgrade plan
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>

    {{-- Task List --}}
    <div>
        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-zinc-200 px-6 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Project Tasks</h2>
                    <p class="mt-0.5 text-sm text-zinc-500">
                        {{ $this->tasks->count() }} tasks
                        @if ($statusFilter !== 'all')
                            <span class="text-xs text-zinc-400">({{ str($statusFilter)->headline() }})</span>
                        @endif
                    </p>
                </div>

                <div class="w-full sm:w-64">
                    <label for="task-status-filter" class="sr-only">Filter tasks by status</label>
                    <select id="task-status-filter" wire:model.live="statusFilter"
                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white">
                        <option value="all">All tasks</option>
                        @foreach (\App\Domain\Task\TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ str($status->value)->headline() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($this->tasks->isNotEmpty())
                {{-- Desktop --}}
                <div class="hidden md:block max-h-[360px] overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:bg-zinc-950/40 dark:text-zinc-300">
                            <tr>
                                <th class="px-6 py-3">Task</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Assignee</th>
                                <th class="px-6 py-3">Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                            @foreach ($this->tasks as $task)
                                <tr class="bg-white transition hover:bg-zinc-50 dark:bg-zinc-950 dark:hover:bg-zinc-900/50">
                                    <td class="px-6 py-3">
                                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-zinc-950 hover:underline dark:text-white" wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3"><x-ui.status-badge :status="$task->status" /></td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" :user="$task->assignee" clickable />
                                            <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-col">
                                            <span>{{ $task->due_date?->format('M d, Y') ?? 'No date' }}</span>
                                            @if ($task->isOverdue())
                                                <span class="text-xs font-medium text-red-600">Overdue</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile --}}
                <div class="md:hidden space-y-3 p-4 max-h-[360px] overflow-y-auto">
                    @foreach ($this->tasks as $task)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                            <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-zinc-950 hover:underline dark:text-white" wire:navigate>
                                {{ $task->title }}
                            </a>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <x-ui.status-badge :status="$task->status" />
                                <span class="text-xs text-zinc-500">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-zinc-500">{{ $task->due_date?->format('M d, Y') ?? 'No date' }}</span>
                                @if ($task->isOverdue())
                                    <span class="text-xs font-medium text-red-600">Overdue</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6">
                    <x-ui.empty-state title="No matching tasks" description="Create a task or change the status filter to widen the list." />
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

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

    {{-- TASK CREATION FORM --}}
    <div>

        <x-ui.card padding="p-0" class="space-y-0">

            <div class="px-5 py-4">
                <div>
                    <h2 class="tf-panel-title">Create Task</h2>
                    <p class="tf-panel-subtitle">Add work directly to this project.</p>
                </div>

                @if (session('success'))
                    <div
                        class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                <div
                    class="mt-4 mb-4 rounded-2xl border border-zinc-200 bg-zinc-50/80 px-4 py-3 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-400">
                    Tasks in use: {{ $organization->taskUsage() }} /
                    {{ $taskLimit === null ? 'Unlimited' : $taskLimit }}
                </div>

                <form wire:submit="createTask" class="space-y-5">

                    {{-- TASK TITLE --}}
                    <div class="space-y-2">
                        <label>Task</label>
                        <input type="text" wire:model="title" class="w-full px-3 py-2.5">
                        @error('title')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="space-y-2">
                        <label>Description</label>
                        <textarea rows="4" wire:model="description" class="w-full px-3 py-2.5"></textarea>
                    </div>

                    {{-- ASSIGNEE --}}
                    <div class="space-y-2">
                        <label>Assignee</label>
                        <select wire:model="assigneeId" class="w-full px-3 py-2.5">
                            <option value="">Unassigned</option>
                            @foreach ($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DUE DATE --}}
                    <div class="space-y-2">
                        <label>Due Date</label>
                        <input type="date" wire:model="dueDate" class="w-full px-3 py-2.5">
                        @error('dueDate')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- PRIORITY --}}
                    <div class="space-y-2">
                        <label>Priority</label>
                        <select wire:model="priority" class="w-full px-3 py-2.5">
                            @foreach (\App\Domain\Task\TaskPriority::cases() as $priority)
                                <option value="{{ $priority->value }}">
                                    {{ ucfirst($priority->value) }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- TASK RESOURCES --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Task Resources</label>
                        <p class="text-xs text-zinc-500">Select existing project resources that are relevant to this
                            task.</p>

                        {{-- Search input --}}
                        <div class="relative">
                            <input type="text" wire:model.live="resourceSearch" placeholder="Search resources..."
                                class="w-full px-3 py-2.5">

                            @php
                                $allResources = $this->availableResources;
                                $resourceCount = $allResources->count();
                            @endphp

                            @if ($resourceCount > 0 && empty($resourceSearch))
                                <div class="text-xs text-zinc-400 mt-1">{{ $resourceCount }} resources available</div>
                            @endif

                            @if (!empty($resourceSearch))
                                <div class="text-xs text-zinc-500 mt-1">Searching for: "{{ $resourceSearch }}"</div>
                            @endif

                            {{-- Dropdown results --}}
                            @if (!empty($resourceSearch) && $resourceCount > 0)
                                <div
                                    class="absolute z-20 mt-1 w-full rounded-2xl border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-950/95 max-h-48 overflow-y-auto">
                                    @php
                                        $searchTerm = strtolower(trim($resourceSearch));
                                        $filteredResources = collect();

                                        foreach ($allResources as $resource) {
                                            $resourceName =
                                                $resource->storedFile?->original_filename ?? ($resource->name ?? '');
                                            if (strpos(strtolower($resourceName), $searchTerm) !== false) {
                                                $filteredResources->push($resource);
                                            }
                                        }
                                    @endphp

                                    @if ($filteredResources->isNotEmpty())
                                        @foreach ($filteredResources as $resource)
                                            <label
                                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-900/50 cursor-pointer transition-colors">
                                                <input type="checkbox" wire:model.live="resourceIds"
                                                    value="{{ $resource->id }}"
                                                    class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                                    {{ $resource->storedFile?->original_filename ?? ($resource->name ?? 'Unnamed') }}
                                                </span>
                                                <span class="text-xs text-zinc-400 ml-auto">
                                                    {{ number_format(($resource->storedFile?->size ?? 0) / 1024, 1) }}
                                                    KB
                                                </span>
                                            </label>
                                        @endforeach
                                    @else
                                        <div class="px-4 py-3 text-sm text-zinc-500">
                                            No matching resources found for "{{ $resourceSearch }}"
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Selected resources chips --}}
                        @if (!empty($resourceIds))
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($allResources->whereIn('id', $resourceIds) as $resource)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                        {{ $resource->storedFile?->original_filename ?? ($resource->name ?? 'Unnamed') }}
                                        <button type="button" wire:click="removeResource({{ $resource->id }})"
                                            class="hover:text-blue-900 dark:hover:text-blue-100">
                                            ×
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @error('resourceIds')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <div class="flex justify-end">
                        @if ($organization->canCreateTask())
                            <button type="submit" wire:loading.attr="disabled" wire:target="createTask"
                                class="tf-button-primary inline-flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="createTask">Create Task</span>
                                <span wire:loading.flex wire:target="createTask"
                                    class="inline-flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                        aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                        </path>
                                    </svg>
                                    <span>Creating...</span>
                                </span>
                            </button>
                        @else
                            <a href="{{ route('organizations.billing', $organization) }}" class="tf-button-secondary"
                                wire:navigate>
                                Upgrade plan
                            </a>
                        @endif
                    </div>

                </form>
            </div>

        </x-ui.card>

    </div>

    {{-- TASK LIST --}}
    <div>

        <x-ui.card padding="p-0" class="overflow-hidden">

            <div
                class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="tf-panel-title">Project Tasks</h2>
                    <p class="tf-panel-subtitle">
                        {{ $this->tasks->count() }} tasks in this project
                        @if ($statusFilter !== 'all')
                            <span class="text-xs text-zinc-400">(filtered by
                                {{ str($statusFilter)->headline() }})</span>
                        @endif
                    </p>
                </div>

                <div class="grid gap-3 sm:w-72">
                    <label for="task-status-filter" class="sr-only">Filter tasks by status</label>
                    <select id="task-status-filter" wire:model.live="statusFilter"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-white">
                        <option value="all">All tasks</option>
                        @foreach (\App\Domain\Task\TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ str($status->value)->headline() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($this->tasks->isNotEmpty())
                <div class="overflow-y-auto max-h-[320px] sm:max-h-[360px] md:max-h-[420px]">
                    <table class="min-w-full border-separate border-spacing-0 text-sm">
                        <thead
                            class="bg-zinc-50 text-zinc-600 dark:bg-zinc-950/40 dark:text-zinc-300 sticky top-0 z-10">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold uppercase tracking-wide">Task</th>
                                <th class="px-5 py-3 text-left font-semibold uppercase tracking-wide">Status</th>
                                <th class="px-5 py-3 text-left font-semibold uppercase tracking-wide">Assignee</th>
                                <th class="px-5 py-3 text-left font-semibold uppercase tracking-wide">Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                            @foreach ($this->tasks as $task)
                                <tr
                                    class="bg-white dark:bg-zinc-950 hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <a href="{{ route('tasks.show', $task) }}"
                                            class="font-medium text-zinc-950 hover:underline dark:text-white"
                                            wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <x-ui.status-badge :status="$task->status" />
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                            <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
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
            @else
                <div class="p-5">
                    <x-ui.empty-state title="No matching tasks"
                        description="Create a task or change the status filter to widen the list." />
                </div>
            @endif

        </x-ui.card>

    </div>

</div>

<style>
    .relative {
        z-index: 1;
    }

    .relative .absolute {
        z-index: 20;
    }
</style>

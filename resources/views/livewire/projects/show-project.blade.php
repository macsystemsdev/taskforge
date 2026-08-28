<?php

use App\Models\Project;
use App\Models\Task;
use Livewire\Component;
use Flux\Flux;
use App\Actions\Projects\CancelProjectAction;
use App\Actions\Projects\CompleteProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;

new class extends Component {
    use AuthorizesRequests;

    public Project $project;

    public bool $showCreateTaskModal = false;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $this->project = $project->load([
            'workspace.organization',
            'team',
            'creator',
            'tasks' => fn($query) => $query->with(['assignee'])->latest(),
        ])->loadCount([
            'tasks as open_tasks_count' => fn($query) => $query->where('status', '!=', 'completed'),
            'tasks as completed_tasks_count' => fn($query) => $query->where('status', 'completed'),
            'tasks as overdue_tasks_count' => fn($query) => $query->where('due_date', '<', now())->where('status', '!=', 'completed'),
            'tasks as due_soon_tasks_count' => fn($query) => $query->whereBetween('due_date', [now(), now()->addDays(7)])->where('status', '!=', 'completed'),
        ]);
    }

    #[Computed]
    public function openTasks(): int
    {
        return $this->project->open_tasks_count ?? 0;
    }

    #[Computed]
    public function completedTasks(): int
    {
        return $this->project->completed_tasks_count ?? 0;
    }

    #[Computed]
    public function dueDate(): string
    {
        return $this->project->due_date
            ? $this->project->due_date->format('M d, Y')
            : __('No due date');
    }

    public function openCreateTaskModal(): void
    {
        $this->authorize('createTask', $this->project);
        $this->showCreateTaskModal = true;
    }

    public function completeProject(CompleteProjectAction $action): void
    {
        $this->authorize('complete', $this->project);
        $action->handle($this->project);
        Flux::toast(variant: 'success', text: __('Project completed.'));
        $this->redirect(route('projects.show', $this->project), navigate: true);
    }

    public function cancelProject(CancelProjectAction $action): void
    {
        $this->authorize('cancel', $this->project);
        $action->handle($this->project);
        Flux::toast(variant: 'success', text: __('Project cancelled.'));
        $this->redirect(route('projects.show', $this->project), navigate: true);
    }

    public function deleteProject(DeleteProjectAction $action): void
    {
        $this->authorize('delete', $this->project);
        $workspace = $this->project->workspace;
        $action->handle($this->project);
        Flux::toast(variant: 'success', text: __('Project deleted.'));
        $this->redirect(route('workspaces.show', $workspace), navigate: true);
    }
};
?>

<div id="taskforge-project" data-project-id="{{ $project->id }}" data-user-id="{{ auth()->id() }}"
    data-user-name="{{ auth()->user()->name }}">

    <div class="space-y-6">
        {{-- Header --}}
        <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('workspaces.show', $project->workspace) }}"
                        class="text-xs font-medium uppercase tracking-[0.15em] text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                        wire:navigate>
                        {{ $project->workspace->organization->name }} / {{ $project->workspace->name }}
                    </a>

                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                        {{ $project->name }}
                    </h1>

                    @if ($project->description)
                        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $project->description }}
                        </p>
                    @endif

                    @if ($project->isOverdue())
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                            {{ __('This project is overdue.') }}
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-ui.status-badge :status="$project->status" />

                    @if ($project->status->isActive())
                        @if (auth()->user()->can('update', $project))
                            <flux:button size="sm" href="{{ route('projects.edit', $project) }}">Edit</flux:button>
                        @endif

                        @if (auth()->user()->can('createTask', $project))
                            <flux:button size="sm" variant="primary" wire:click="openCreateTaskModal">
                                <flux:icon name="plus" class="size-4" />
                                Add Task
                            </flux:button>
                        @endif

                        @if (auth()->user()->can('complete', $project))
                            <flux:button size="sm" wire:click="completeProject">Complete</flux:button>
                        @endif

                        @if (auth()->user()->can('cancel', $project))
                            <flux:button size="sm" wire:click="cancelProject">Cancel</flux:button>
                        @endif
                    @endif

                    @if (auth()->user()->can('delete', $project))
                        <flux:button size="sm" variant="danger" wire:click="deleteProject">Delete</flux:button>
                    @endif
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                    <p class="text-xs text-zinc-500">Team</p>
                    <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $project->team->name }}</p>
                </div>

                <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                    <p class="text-xs text-zinc-500">Due Date</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">{{ $this->dueDate }}</p>
                </div>

                <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                    <p class="text-xs text-zinc-500">Open Tasks</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->openTasks }}</p>
                </div>

                <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                    <p class="text-xs text-zinc-500">Completed</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->completedTasks }}</p>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            {{-- Left column: Tasks + Comments --}}
            <div class="space-y-6 min-w-0">
                {{-- Tasks List --}}
                <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-white/10">
                        <div>
                            <p class="text-sm font-semibold text-zinc-950 dark:text-white">Tasks</p>
                            <p class="text-xs text-zinc-500">{{ $this->openTasks }} open • {{ $this->completedTasks }} done</p>
                        </div>

                        @if (auth()->user()->can('createTask', $project) && $project->status->isActive())
                            <flux:button size="sm" variant="primary" wire:click="openCreateTaskModal">
                                <flux:icon name="plus" class="size-4" />
                                Add
                            </flux:button>
                        @endif
                    </div>

                    @if ($project->tasks->isNotEmpty())
                        <div class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($project->tasks as $task)
                                <a href="{{ route('tasks.show', $task) }}"
                                    wire:key="task-{{ $task->id }}"
                                    class="group flex items-center justify-between gap-3 px-4 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                                    wire:navigate>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                            @if ($task->isOverdue())
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Overdue</span>
                                            @endif
                                        </div>

                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500">
                                            <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                            @if ($task->due_date)
                                                <span>•</span>
                                                <span>{{ $task->due_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <x-ui.status-badge :status="$task->status->value" size="sm" />
                                    <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600">→</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <p class="text-sm font-medium text-zinc-950 dark:text-white">No tasks yet</p>
                            <p class="mt-1 text-sm text-zinc-500">Add the first task to start tracking work.</p>
                        </div>
                    @endif
                </x-ui.card>

                {{-- Comments --}}
                @livewire('comments.comment-section', ['commentable' => $project])
            </div>

            {{-- Right column --}}
            <aside class="space-y-6 lg:sticky lg:top-20 h-fit">
                {{-- Project Health --}}
                <div class="rounded-2xl border border-zinc-200 bg-white/90 shadow-sm overflow-hidden dark:border-white/10 dark:bg-zinc-900/70">
                    <div class="border-b border-zinc-200 px-4 py-3 dark:border-white/10">
                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">Project Health</p>
                    </div>
                    <div class="grid grid-cols-3 divide-x divide-zinc-100 dark:divide-white/5">
                        <div class="p-3 text-center">
                            <p class="text-xs text-zinc-500">Overdue</p>
                            <p class="mt-1 text-lg font-semibold text-red-600 dark:text-red-400">{{ $project->overdue_tasks_count ?? 0 }}</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-xs text-zinc-500">Due Soon</p>
                            <p class="mt-1 text-lg font-semibold text-amber-600 dark:text-amber-400">{{ $project->due_soon_tasks_count ?? 0 }}</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-xs text-zinc-500">Done</p>
                            <p class="mt-1 text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $this->completedTasks }}</p>
                        </div>
                    </div>
                </div>

                @livewire('projects.project-presence', ['project' => $project])
                @livewire('projects.project-files', ['project' => $project])
            </aside>
        </div>
    </div>

    {{-- Create Task Modal --}}
    <flux:modal wire:model="showCreateTaskModal" class="max-w-lg">
        @livewire('tasks.create-task', ['project' => $project])
    </flux:modal>
</div>

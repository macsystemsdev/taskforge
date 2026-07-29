<?php

use App\Models\Project;
use Livewire\Component;
use Flux\Flux;
use App\Actions\Projects\CancelProjectAction;
use App\Actions\Projects\CompleteProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

new class extends Component {
    use AuthorizesRequests;

    public Project $project;

    public function completeProject(CompleteProjectAction $action): void
    {
        $this->authorize('complete', $this->project);

        $action->handle($this->project);

        $this->redirect(route('projects.show', $this->project), navigate: true);
    }

    public function cancelProject(CancelProjectAction $action): void
    {
        $this->authorize('cancel', $this->project);

        $action->handle($this->project);

        $this->redirect(route('projects.show', $this->project), navigate: true);
    }

    public function deleteProject(DeleteProjectAction $action): void
    {
        $this->authorize('delete', $this->project);

        $workspace = $this->project->workspace;

        $action->handle($this->project);

        $this->redirect(route('workspaces.show', $workspace), navigate: true);
    }


};
?>

<x-ui.page>
    @php
        $tasks = $project->tasks;
        $openTasks = $project->inProgressTaskCount();
        $completedTasks = $project->completedTaskCount();
        $dueDate = $project->due_date ? $project->due_date->format('M d, Y') : __('No due date');
    @endphp

    <div
        class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <x-ui.page-header :title="$project->name" :description="$project->description ?: __('No project description has been added yet.')" :eyebrow="$project->workspace->organization->name . ' / ' . $project->workspace->name">
            <x-slot:actions>

                <x-ui.status-badge :status="$project->status" />

                @if ($project->status->isActive())
                    @if (auth()->user()->can('update', $project))
                        <a href="{{ route('projects.edit', $project) }}" wire:navigate class="tf-button-secondary">
                            Edit
                        </a>
                    @endif

                    @if (auth()->user()->can('complete', $project))
                        <button wire:click="completeProject" class="tf-button-primary">
                            Complete
                        </button>
                    @endif

                    @if (auth()->user()->can('cancel', $project))
                        <button wire:click="cancelProject" class="tf-button-secondary">
                            Cancel
                        </button>
                    @endif
                @endif

                @if (auth()->user()->can('delete', $project))
                    <button wire:click="deleteProject" wire:confirm="Delete this project?" class="tf-button-danger">
                        Delete
                    </button>
                @endif

            </x-slot:actions>
        </x-ui.page-header>

        @if ($project->isOverdue())
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-700">
                This project is overdue.
            </div>
        @endif

        @if ($project->hasUpcomingDeadlines())
            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-700">
                Some tasks are approaching their deadlines.
            </div>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="space-y-2">
            <p class="tf-muted">Team</p>
            <div class="flex items-center gap-2">
                <p class="font-semibold text-zinc-950 dark:text-white">{{ $project->team->name }}</p>
            </div>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Due date</p>
            <p class="font-semibold text-zinc-950 dark:text-white">{{ $dueDate }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Open tasks</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $openTasks }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Completed</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $completedTasks }}</p>
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-[minmax(0,1fr)_320px]">

        <div class="space-y-6">

            @if (auth()->user()->can('createTask', $project))
                @livewire('tasks.create-task', ['project' => $project])
            @endif

            @livewire('comments.comment-section', [
                'commentable' => $project,
            ])

        </div>

        <aside class="space-y-6 md:sticky md:top-20">
            @livewire('projects.project-details', ['project' => $project])
        </aside>

    </div>
</x-ui.page>

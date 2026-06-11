<?php

use App\Models\Project;
use Livewire\Component;
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
    public function render()
    {
        $this->project->load(['workspace.organization', 'team', 'creator', 'tasks.assignee']);

        return view('livewire.projects.show-project');
    }
};
?>

<x-ui.page>
    @php
        $tasks = $project->tasks;
        $openTasks = $tasks->whereNotIn('status', [\App\Domain\Task\TaskStatus::DONE])->count();
        $completedTasks = $tasks->where('status', \App\Domain\Task\TaskStatus::DONE)->count();
        $dueDate = $project->due_date ? $project->due_date->format('M d, Y') : __('No due date');
    @endphp

    <x-ui.page-header :title="$project->name" :description="$project->description ?: __('No project description has been added yet.')" :eyebrow="$project->workspace->organization->name . ' / ' . $project->workspace->name">
        <x-slot:actions>

            <x-ui.status-badge :status="$project->status" />

            @if ($project->status->isActive())
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" wire:navigate class="tf-button-secondary">
                        Edit
                    </a>
                @endcan

                @can('complete', $project)
                    <button wire:click="completeProject" class="tf-button-primary">
                        Complete
                    </button>
                @endcan

                @can('cancel', $project)
                    <button wire:click="cancelProject" class="tf-button-secondary">
                        Cancel
                    </button>
                @endcan
            @endif

            @can('delete', $project)
                <button wire:click="deleteProject" wire:confirm="Delete this project?" class="tf-button-danger">
                    Delete
                </button>
            @endcan

        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="space-y-2">
            <p class="tf-muted">Team</p>
            <div class="flex items-center gap-2">
                <x-ui.icon :name="$project->team->name" size="sm" />
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
            
            @can('createTask', $workspace)
                @livewire('tasks.create-task', ['project' => $project])
            @endcan
            

            @livewire('comments.comment-section', [
                'commentable' => $project,
            ])
        </div>

        <aside class="space-y-6 md:sticky md:top-20">
            @livewire('projects.project-details', ['project' => $project])
        </aside>
    </div>
</x-ui.page>

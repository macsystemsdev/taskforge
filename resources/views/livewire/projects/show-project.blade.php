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

<div id="taskforge-project" data-project-id="{{ $project->id }}" data-user-id="{{ auth()->id() }}"
    data-user-name="{{ auth()->user()->name }}">
    <x-ui.page>
        @php
            $tasks = $project->tasks;
            $openTasks = $project->inProgressTaskCount();
            $completedTasks = $project->completedTaskCount();
            $dueDate = $project->due_date ? $project->due_date->format('M d, Y') : __('No due date');
        @endphp

        {{-- Project Header --}}
        <div
            class="mb-6 rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-900/70 sm:p-8">
            <x-ui.page-header :title="$project->name" :description="$project->description ?: __('No project description has been added yet.')" :eyebrow="$project->workspace->organization->name . ' / ' . $project->workspace->name">
                <x-slot:actions>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.status-badge :status="$project->status" />

                        @if ($project->status->isActive())
                            @if (auth()->user()->can('update', $project))
                                <a href="{{ route('projects.edit', $project) }}" wire:navigate
                                    class="tf-button-secondary">Edit</a>
                            @endif

                            @if (auth()->user()->can('complete', $project))
                                <button wire:click="completeProject" class="tf-button-primary">Complete</button>
                            @endif

                            @if (auth()->user()->can('cancel', $project))
                                <button wire:click="cancelProject" class="tf-button-secondary">Cancel</button>
                            @endif
                        @endif

                        @if (auth()->user()->can('delete', $project))
                            <button wire:click="deleteProject" wire:confirm="Delete this project?"
                                class="tf-button-danger">Delete</button>
                        @endif
                    </div>
                </x-slot:actions>
            </x-ui.page-header>

            @if ($project->isOverdue())
                <div
                    class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                    This project is overdue.
                </div>
            @endif

            @if ($project->hasUpcomingDeadlines())
                <div
                    class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300">
                    Some tasks are approaching their deadlines.
                </div>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card class="p-5">
                <p class="tf-muted text-sm">Team</p>
                <p class="mt-2 font-semibold text-zinc-950 dark:text-white truncate">{{ $project->team->name }}</p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="tf-muted text-sm">Due date</p>
                <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $dueDate }}</p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="tf-muted text-sm">Open tasks</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $openTasks }}
                </p>
            </x-ui.card>

            <x-ui.card class="p-5">
                <p class="tf-muted text-sm">Completed</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $completedTasks }}</p>
            </x-ui.card>
        </div>

        {{-- Main Content --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] xl:grid-cols-[1fr_420px]">
            {{-- Left column --}}
            <div class="space-y-6 min-w-0">
                @if (auth()->user()->can('createTask', $project))
                    @livewire('tasks.create-task', ['project' => $project])
                @endif

                @livewire('comments.comment-section', ['commentable' => $project])
            </div>

            {{-- Right column --}}
            <aside class="space-y-6 lg:sticky lg:top-20 h-fit">
                @livewire('projects.project-presence', ['project' => $project])
                @livewire('projects.project-details', ['project' => $project])
                @livewire('projects.project-files', ['project' => $project])
            </aside>
        </div>
    </x-ui.page>
</div>

<?php

use App\Models\Project;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function render()
    {
        $this->project->load([
                    'workspace.organization',
                    'team',
                    'creator',
                    'tasks.assignee',
        ]);

        return view('livewire.projects.show-project');
    }
};
?>

<x-ui.page>
    @php
        $tasks = $project->tasks;
        $openTasks = $tasks->whereNotIn('status', [\App\Domain\Task\TaskStatus::DONE])->count();
        $completedTasks = $tasks->where('status', \App\Domain\Task\TaskStatus::DONE)->count();
        $dueDate = $project->due_date
                ? $project->due_date->format('M d, Y')
                : __('No due date');
    @endphp

    <x-ui.page-header :title="$project->name" :description="$project->description ?: __('No project description has been added yet.')" :eyebrow="$project->workspace->organization->name . ' / ' . $project->workspace->name">
        <x-slot:actions>
            <x-ui.status-badge :status="$project->status" />
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
            @livewire('tasks.create-task', ['project' => $project])

            @livewire('comments.comment-section', [
                'commentable' => $project,
            ])
        </div>

        <aside class="space-y-6 md:sticky md:top-20">
                @livewire(
                    'projects.project-details',
                    ['project' => $project]
                )
        </aside>
    </div>
</x-ui.page>

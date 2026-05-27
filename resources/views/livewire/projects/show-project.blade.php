<?php

use App\Models\Project;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function render()
    {
        $this->project->load(['workspace', 'owner']);

        return view('livewire.projects.show-project');
    }
};
?>

<div class="max-w-5xl mx-auto py-10">

    <div class="flex items-center justify-between mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                {{ $project->name }}
            </h1>

            <p class="text-zinc-500 mt-2">
                {{ $project->description }}
            </p>
        </div>

        <div class="text-sm text-zinc-500">
            Workspace:
            {{ $project->workspace->name }}
        </div>

    </div>

    <div class="grid grid-cols-3 gap-6">

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Status
            </p>

            <p class="mt-2 font-semibold">
                {{ ucfirst($project->status) }}
            </p>
        </div>

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Owner
            </p>

            <p class="mt-2 font-semibold">
                {{ $project->owner->name }}
            </p>
        </div>

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Due Date
            </p>

            <p class="mt-2 font-semibold">
                {{ $project->due_date ?? 'No due date' }}
            </p>
        </div>

    </div>

    <div class="space-y-8 mt-4">

        <div class="rounded-2xl border bg-dark p-6">

            <h1 class="text-3xl font-bold">
                {{ $project->name }}
            </h1>

            <p class="mt-3 text-zinc-600">
                {{ $project->description }}
            </p>

        </div>

        @livewire('tasks.create-task', ['project' => $project])

    </div>

</div>

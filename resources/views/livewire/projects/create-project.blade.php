<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

new class extends Component {
    public Workspace $workspace;

    public string $name = '';

    public ?string $description = null;

    public ?string $dueDate = null;

    public ?int $teamId = null;

    #[Computed]
    public function teams()
    {
        return $this->workspace->teams->filter(fn($team) => !$this->workspace->organization->teamLocked($team));
    }

    public function createProject(CreateProjectAction $action)
    {
        Gate::authorize('createProject', $this->workspace);

        $validated = $this->validate();

        // handle function call in CreateprojectAction to create project with DTO data
        $project = $action->handle($this->workspace, new CreateProjectData(teamId: $this->teamId, name: $this->name, description: $this->description, dueDate: $this->dueDate));

        Flux::toast(variant: 'success', text: __('Project created successfully.'));

        return redirect()->route('projects.show', [
            'workspace' => $this->workspace,
            'project' => $project,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('projects', 'slug')->where(function ($query) {
                    return $query->where('workspace_id', $this->workspace->id);
                }),
            ],
            'teamId' => ['required', 'exists:teams,id'],
            'description' => ['nullable'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    // render this page which will pick up layout from the pages/projects
    public function render()
    {
        return view('livewire.projects.create-project');
    }
};

?>
<x-ui.page size="3xl">
    @php
        $organization = $workspace->organization;
        $projectLimit = $organization->currentPlan()?->max_projects;
    @endphp

    <x-ui.page-header :title="__('Create Project')" :description="__('Create a project inside :workspace and define its first operational boundary.', [
        'workspace' => $workspace->name,
    ])" />

    <x-ui.card>
        <div
            class="mb-4 rounded-2xl border border-zinc-200 bg-zinc-50/80 px-4 py-3 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-400">
            Projects in use: {{ $organization->projectUsage() }} /
            {{ $projectLimit === null ? 'Unlimited' : $projectLimit }}
        </div>

        <form wire:submit="createProject" class="space-y-6">

            <div class="space-y-2">
                <label>Project Name</label>

                <input type="text" wire:model="name" class="w-full px-3 py-2.5">

                @error('name')
                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-2">
                <label>Team</label>

                <select wire:model="teamId" class="w-full px-3 py-2.5">
                    <option value="">
                        Select team
                    </option>

                    @foreach ($this->teams as $team)
                        <option value="{{ $team->id }}">
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>

                @error('teamId')
                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-2">
                <label>Description</label>

                <textarea wire:model="description" rows="5" class="w-full px-3 py-2.5"></textarea>
            </div>

            <div class="space-y-2">
                <label>Due Date</label>

                <input type="date" wire:model="dueDate" class="w-full px-3 py-2.5">
            </div>

            <div class="flex justify-end">
                @if ($organization->canCreateProject())
                    <button type="submit" wire:loading.attr="disabled" wire:target="createProject"
                        class="tf-button-primary inline-flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="createProject">Create Project</span>
                        <span wire:loading.flex wire:target="createProject"
                            class="inline-flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
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

    </x-ui.card>
</x-ui.page>

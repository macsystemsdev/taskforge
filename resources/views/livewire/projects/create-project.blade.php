<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Workspace $workspace;

    public string $name = '';

    public ?string $description = null;

    public ?string $dueDate = null;

    public ?int $teamId = null;

     public function getTeamsProperty()
    {
        return $this->workspace
            ->teams()
            ->orderBy('name')
            ->get();
    }

    public function createProject(CreateProjectAction $action)
    {
        Gate::authorize('createProject', $this->workspace);

        $validated = $this->validate();

        // handle function call in CreateprojectAction to create project with DTO data
         $project = $action->handle(
            $this->workspace,
            new CreateProjectData(
                teamId: $this->teamId,
                name: $this->name,
                description: $this->description,
                dueDate: $this->dueDate,
            )
        );

        Flux::toast(variant: 'success', text: __('Project created successfully.'));

          return redirect()->route(
            'projects.show',
            [
                'workspace' => $this->workspace,
                'project' => $project,
            ]
        );
    }

        public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
            ],

            'teamId' => [
                'required',
                'exists:teams,id',
            ],

            'description' => [
                'nullable',
            ],

            'dueDate' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
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
    <x-ui.page-header :title="__('Create Project')" :description="__('Create a project inside :workspace and define its first operational boundary.', [
        'workspace' => $workspace->name,
    ])" />

    <x-ui.card>

        <form
            wire:submit="createProject"
            class="space-y-6"
        >

            <div class="space-y-2">
                <label>Project Name</label>

                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-3 py-2.5"
                >

                @error('name')
                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-2">
                <label>Team</label>

                <select
                    wire:model="teamId"
                    class="w-full px-3 py-2.5"
                >
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

                <textarea
                    wire:model="description"
                    rows="5"
                    class="w-full px-3 py-2.5"
                ></textarea>
            </div>

            <div class="space-y-2">
                <label>Due Date</label>

                <input
                    type="date"
                    wire:model="dueDate"
                    class="w-full px-3 py-2.5"
                >
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="tf-button-primary"
                >
                    Create Project
                </button>
            </div>

        </form>

    </x-ui.card>
</x-ui.page>

<?php

use Livewire\Component;
use App\Models\Project;
use App\Actions\Projects\UpdateProjectAction;
use App\Data\Projects\UpdateProjectData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Facade\Gate;


new class extends Component
{
    public Project $project;

    public string $name;

    public ?string $description;

    public ?string $dueDate;

    public function mount(): void
    {
        $this->name = $this->project->name;

        $this->description = $this->project->description;

        $this->dueDate = $this->project->due_date?->format('Y-m-d');
    }

        public function updateProject(UpdateProjectAction $action)
    {
        Gate::authorize('createProject', $organization)
        $validated = $this->validate();

        // handle function call in CreateprojectAction to create project with DTO data
         $project = $action->handle(
            $this->workspace,
            new UpdateProjectData(
                name: $this->name,
                description: $this->description,
                dueDate: $this->dueDate,
            )
        );

        Flux::toast(variant: 'success', text: __('Project updated successfully.'));

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
};
?>

<x-ui.page size="3xl">
    <x-ui.page-header
        :title="__('Edit Project')"
        :description="__('Update project information. Team ownership cannot be changed after creation.')"
    />

    <x-ui.card>

        <form
            wire:submit="updateProject"
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

                <input
                    type="text"
                    value="{{ $project->team->name }}"
                    disabled
                    class="w-full px-3 py-2.5 bg-zinc-100 dark:bg-zinc-800"
                >

                <p class="text-xs text-zinc-500">
                    Team ownership cannot be changed after project creation.
                </p>
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
                    Save Changes
                </button>
            </div>

        </form>

    </x-ui.card>
    
</x-ui.page>
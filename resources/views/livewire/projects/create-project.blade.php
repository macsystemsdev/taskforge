<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facade\Gate;

new class extends Component
{
    public Workspace $workspace;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    public function createProject(CreateProjectAction $action)
    {
        Gate::authorize('createProject', $organization)
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Project::where('slug', Str::slug((string) $value))->exists()) {
                        $fail(__('A project with that name already exists.'));
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        // pass data into Project DTO for binding into action
        $data = new CreateProjectData(owner_id: auth()->id(), name: $validated['name'], description: $validated['description'], due_date: $validated['due_date']);

        // handle function call in CreateprojectAction to create project with DTO data
        $project = $action->handle(workspace: $this->workspace, data: $data);

        Flux::toast(variant: 'success', text: __('Project created successfully.'));

        return redirect()->route('projects.show', $project);
    }

    // render this page which will pick up layout from the pages/projects
    public function render()
    {
        return view('livewire.projects.create-project');
    }
};

?>
<x-ui.page size="3xl">
    <x-ui.page-header
        :title="__('Create Project')"
        :description="__('Create a project inside :workspace and define its first operational boundary.', ['workspace' => $workspace->name])"
    />

    <x-ui.card>
        <form wire:submit="createProject" class="space-y-6">
            <div class="space-y-2">
                <label for="project-name">Project Name</label>
                <input id="project-name" type="text" wire:model="name" class="w-full px-3 py-2.5">

                @error('name')
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="project-description">Description</label>
                <textarea id="project-description" wire:model="description" rows="5" class="w-full px-3 py-2.5"></textarea>
            </div>

            <div class="space-y-2">
                <label for="project-due-date">Due Date</label>
                <input id="project-due-date" type="date" wire:model="due_date" class="w-full px-3 py-2.5">
            </div>

            <div class="flex justify-end border-t border-zinc-200 pt-5 dark:border-white/10">
                <button type="submit" class="tf-button-primary">
                    Create Project
                </button>
            </div>
        </form>
    </x-ui.card>
</x-ui.page>

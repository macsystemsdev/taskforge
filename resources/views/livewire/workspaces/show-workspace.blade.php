<?php

namespace App\Livewire\Workspaces;

use App\Actions\Workspaces\DeleteWorkspaceAction;
use App\Actions\Workspaces\UpdateWorkspaceAction;
use App\Data\WorkspaceData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Organization;

new class extends Component {
    public Workspace $workspace;

    public Organization $organization;

    public bool $showEditModal = false;

    public string $name = '';

    public ?string $description = null;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace->load(['organization', 'teams.members', 'projects'])->loadCount(['teams', 'projects']);

        $this->name = $workspace->name;
        $this->description = $workspace->description;
    }

    public function updateWorkspace(): void
    {
        Gate::authorize('update', $this->workspace);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        app(UpdateWorkspaceAction::class)->handle(
            $this->workspace,
            WorkspaceData::from([
                'name' => $this->name,
                'description' => $this->description,
            ]),
        );

        $this->workspace->refresh();

        Flux::toast(text: 'Workspace updated.', variant: 'success');

        $this->showEditModal = false;
    }

    public function deleteWorkspace(): void
    {
        Gate::authorize('delete', $this->workspace);

        $organization = $this->workspace->organization;

        app(DeleteWorkspaceAction::class)->handle($this->workspace);

        Flux::toast(text: 'Workspace deleted.', variant: 'success');

        $this->redirectRoute('organizations.show', $organization);
    }
};

?>


<div class="space-y-8">

    {{-- Workspace Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">

        <div>
            <h1 class="text-3xl font-bold text-zinc-950 dark:text-white">
                {{ $workspace->name }}
            </h1>

            @if ($workspace->description)
                <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">
                    {{ $workspace->description }}
                </p>
            @endif

            <p class="mt-3 text-sm text-zinc-500">
                Organization:
                {{ $workspace->organization->name }}
            </p>
        </div>

        <div class="flex gap-2">

            @can('update', $workspace)
                <flux:button wire:click="$set('showEditModal', true)">
                    Edit Workspace
                </flux:button>
            @endcan

            @can('delete', $workspace)
                <flux:button variant="danger" wire:click="deleteWorkspace" wire:confirm="Delete this workspace?">
                    Delete
                </flux:button>
            @endcan

        </div>

    </div>

    {{-- Workspace Stats --}}
    <div class="grid gap-4 md:grid-cols-2">

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Teams</p>

            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $workspace->teams->count() }}
            </p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Projects</p>

            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $workspace->projects->count() }}
            </p>
        </x-ui.card>

    </div>

    {{-- Teams --}}
    <x-ui.card class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="tf-panel-title">
                    Teams
                </h2>

                <p class="tf-panel-subtitle">
                    Organize members into teams within this workspace.
                </p>
            </div>

            @can('createTeam', $workspace)
                <a href="{{ route('teams.create', $workspace) }}" class="tf-button-primary px-3 py-2" wire:navigate>
                    Create Team
                </a>
            @endcan

        </div>

        <div class="grid gap-4 lg:grid-cols-2">

            @forelse ($workspace->teams as $team)
                <a href="{{ route('teams.show', $team) }}"
                    class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]"
                    wire:navigate>

                    <div class="space-y-2">

                        <h3 class="font-semibold text-zinc-950 dark:text-white">
                            {{ $team->name }}
                        </h3>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $team->description ?: 'No team description.' }}
                        </p>

                    </div>

                    <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-white/5">

                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $team->members->count() }} members
                        </span>

                    </div>

                </a>

            @empty

                <x-ui.empty-state title="No teams yet" description="Create your first team in this workspace." />
            @endforelse

        </div>

    </x-ui.card>

    {{-- Projects --}}
    <x-ui.card class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="tf-panel-title">
                    Projects
                </h2>

                <p class="tf-panel-subtitle">
                    Manage work being delivered inside this workspace.
                </p>
            </div>

            @can('createProject', $workspace)
                <a href="{{ route('projects.create', $workspace) }}" class="tf-button-primary px-3 py-2" wire:navigate>
                    Create Project
                </a>
            @endcan

        </div>

        <div class="grid gap-4 lg:grid-cols-2">

            @forelse ($workspace->projects as $project)
                <a href="{{ route('projects.show', $project) }}"
                    class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]"
                    wire:navigate>

                    <div class="space-y-2">

                        <h3 class="font-semibold text-zinc-950 dark:text-white">
                            {{ $project->name }}
                        </h3>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $project->description ?: 'No project description.' }}
                        </p>

                    </div>

                </a>

            @empty

                <x-ui.empty-state title="No projects yet" description="Create your first project in this workspace." />
            @endforelse

        </div>

    </x-ui.card>

</div>

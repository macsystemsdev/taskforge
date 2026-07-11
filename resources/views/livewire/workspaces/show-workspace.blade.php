<?php

namespace App\Livewire\Workspaces;

use App\Actions\Workspaces\DeleteWorkspaceAction;
use App\Actions\Workspaces\UpdateWorkspaceAction;
use App\Data\Workspaces\CreateWorkspaceData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Organization;

new class extends Component {
    public Workspace $workspace;

    public Organization $organization;

    public bool $showEditWorkspaceModal = false;

    public bool $showDeleteWorkspaceModal = false;

    public string $name = '';

    public ?string $description = null;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace->load(['organization', 'teams.members', 'projects'])->loadCount(['teams', 'projects']);

        $this->name = $workspace->name;
        $this->description = $workspace->description;
    }

    public function openEditWorkspaceModal(): void
    {
        Gate::authorize('update', $this->workspace);

        $this->workspaceName = $this->workspace->name;

        $this->showEditWorkspaceModal = true;
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
            CreateWorkspaceData::from([
                'name' => $this->name,
                'description' => $this->description,
            ]),
        );

        $this->workspace->refresh();

        $this->showEditWorkspaceModal = false;

        Flux::toast(text: 'Workspace updated.', variant: 'success');
    }

    // Delete Workspace modal
    public function openDeleteWorkspaceModal(): void
    {
        Gate::authorize('delete', $this->workspace);

        $this->workspaceName = $this->workspace->name;

        $this->showDeleteWorkspaceModal = true;
    }

    // Delete organization
    public function deleteWorkspace(): void
    {
        Gate::authorize('delete', $this->workspace);

        $organization = $this->workspace->organization;

        try {
            app(DeleteWorkspaceAction::class)->handle($this->workspace);
            $this->showDeleteWorkspaceModal = false;

            Flux::toast(text: 'Workspace deleted.', variant: 'success');

            $this->redirectRoute('organizations.show', $organization);
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
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

            @if (auth()->user()->can('update', $workspace))
                <flux:button wire:click="openEditWorkspaceModal" wire:loading.attr="disabled" wire:target="openEditWorkspaceModal">
                    Edit workspace
                </flux:button>
            @endif

            @if (auth()->user()->can('delete', $workspace))
                <flux:button variant="danger" wire:click="openDeleteWorkspaceModal" wire:loading.attr="disabled" wire:target="openDeleteWorkspaceModal">
                    Delete workspace
                </flux:button>
            @endif

        </div>

    </div>

    {{-- Workspace Stats --}}
    @php
        $organization = $workspace->organization;
        $teamLimit = $organization->currentPlan()?->max_teams;
        $projectLimit = $organization->currentPlan()?->max_projects;
    @endphp

    <div class="grid gap-4 md:grid-cols-2">

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Teams</p>

            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $workspace->teams->count() }} / {{ $teamLimit === null ? 'Unlimited' : $teamLimit }}
            </p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Projects</p>

            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ $workspace->projects->count() }} / {{ $projectLimit === null ? 'Unlimited' : $projectLimit }}
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

            @if (auth()->user()->can('createTeam', $workspace))
                @if ($organization->canCreateTeam())
                    <a href="{{ route('teams.create', $workspace) }}" class="tf-button-primary px-3 py-2" wire:navigate>
                        Create Team
                    </a>
                @else
                    <a href="{{ route('organizations.billing', $organization) }}" class="tf-button-secondary px-3 py-2" wire:navigate>
                        Upgrade plan
                    </a>
                @endif
            @endif

        </div>

        <div class="grid gap-4 lg:grid-cols-2">

            @forelse ($workspace->teams as $team)
                <a href="{{ route('teams.show', ['workspace' => $workspace, 'team' => $team]) }}"
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

            @if (auth()->user()->can('createProject', $workspace))
                @if ($organization->canCreateProject())
                    <a href="{{ route('projects.create', $workspace) }}" class="tf-button-primary px-3 py-2" wire:navigate>
                        Create Project
                    </a>
                @else
                    <a href="{{ route('organizations.billing', $organization) }}" class="tf-button-secondary px-3 py-2" wire:navigate>
                        Upgrade plan
                    </a>
                @endif
            @endif

        </div>

        <div class="grid gap-4 lg:grid-cols-2">

            @forelse ($workspace->projects as $project)
                <a href="{{ route('projects.show', $project) }}"
                    class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]"
                    wire:navigate>

                    <div class="space-y-2">

                        @php
                            $isProjectLocked = $organization->lockedProjects()->contains(fn($lockedProject) => $lockedProject->id === $project->id);
                        @endphp

                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-zinc-950 dark:text-white">
                                {{ $project->name }}
                            </h3>

                            @if ($isProjectLocked)
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-medium uppercase tracking-[0.2em] text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                                    Locked
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $project->description ?: 'No project description.' }}
                        </p>

                    </div>

                </a>

            @empty

                <x-ui.empty-state title="No projects yet" description="Create your first project in this workspace." />
            @endforelse

        </div>

        {{-- Modal to edit workspace --}}
        <flux:modal wire:model="showEditWorkspaceModal">

            <div class="space-y-4">

                <flux:heading>
                    Edit Workspace
                </flux:heading>

                <flux:input wire:model="name" label="Workspace Name" />
                
                <flux:textarea wire:model="description" label="Description" />

                <div class="flex justify-end gap-2">

                    <flux:button variant="ghost" wire:click="$set('showEditWorkspaceModal', false)">
                        Cancel
                    </flux:button>

                    <flux:button wire:click="updateWorkspace" wire:loading.attr="disabled" wire:target="updateWorkspace" class="inline-flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="updateWorkspace">Save Changes</span>
                        <span wire:loading.flex wire:target="updateWorkspace" class="items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z"></path>
                            </svg>
                            <span>Saving...</span>
                        </span>
                    </flux:button>

                </div>

            </div>

        </flux:modal>

        {{-- Modal to delete workspace --}}
        <flux:modal wire:model="showDeleteWorkspaceModal">

            <div class="space-y-4">

                <flux:heading>
                    Delete Workspace
                </flux:heading>

                <p>
                    Delete all teams and projects before deleting this Workspace.
                </p>

                <div class="flex justify-end gap-2">

                    <flux:button variant="ghost" wire:click="$set('showDeleteWorkspaceModal', false)">
                        Cancel
                    </flux:button>

                    <flux:button variant="danger" wire:click="deleteWorkspace" wire:loading.attr="disabled" wire:target="deleteWorkspace" class="inline-flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="deleteWorkspace">Delete</span>
                        <span wire:loading.flex wire:target="deleteWorkspace" class="items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z"></path>
                            </svg>
                            <span>Deleting...</span>
                        </span>
                    </flux:button>

                </div>

            </div>

        </flux:modal>

    </x-ui.card>

</div>

<?php

namespace App\Livewire\Workspaces;

use App\Actions\Workspaces\DeleteWorkspaceAction;
use App\Actions\Workspaces\UpdateWorkspaceAction;
use App\Data\Workspaces\CreateWorkspaceData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public Workspace $workspace;

    public bool $showEditWorkspaceModal = false;
    public bool $showDeleteWorkspaceModal = false;

    public string $name = '';
    public ?string $description = null;

    public function mount(Workspace $workspace): void
    {
        $this->authorize('view', $workspace);

        $this->workspace = $workspace->load([
            'organization.subscription.plan',
            'teams' => fn($query) => $query->withCount('members'),
            'projects' => fn($query) => $query->withCount('tasks'),
        ]);

        $this->name = $workspace->name;
        $this->description = $workspace->description;
    }

    #[Computed]
    public function teamsUsage(): int
    {
        return $this->workspace->teams->count();
    }

    #[Computed]
    public function projectsUsage(): int
    {
        return $this->workspace->projects->count();
    }

    #[Computed]
    public function lockedTeams()
    {
        return $this->workspace->organization->lockedTeams();
    }

    #[Computed]
    public function lockedProjects()
    {
        return $this->workspace->organization->lockedProjects();
    }

    public function openEditWorkspaceModal(): void
    {
        Gate::authorize('update', $this->workspace);
        $this->name = $this->workspace->name;
        $this->description = $this->workspace->description;
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

    public function openDeleteWorkspaceModal(): void
    {
        Gate::authorize('delete', $this->workspace);
        $this->showDeleteWorkspaceModal = true;
    }

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

<div class="space-y-6">
    @php
        $organization = $workspace->organization;
        $currentPlan = $organization->currentPlan();
        $teamLimit = $currentPlan?->max_teams;
        $projectLimit = $currentPlan?->max_projects;
    @endphp

    {{-- Header --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $workspace->name }}
                </h1>

                @if ($workspace->description)
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $workspace->description }}
                    </p>
                @endif

                <a href="{{ route('organizations.show', $organization) }}"
                    class="mt-3 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                    wire:navigate>
                    <span>{{ $organization->name }}</span>
                    <span>→</span>
                </a>
            </div>

            <div class="flex gap-2">
                @if (auth()->user()->can('update', $workspace))
                    <flux:button size="sm" wire:click="openEditWorkspaceModal">Edit</flux:button>
                @endif

                @if (auth()->user()->can('delete', $workspace))
                    <flux:button size="sm" variant="danger" wire:click="openDeleteWorkspaceModal">Delete</flux:button>
                @endif
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Teams</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ $this->teamsUsage }} / {{ $teamLimit === null ? 'Unlimited' : $teamLimit }}
                </p>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Projects</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">
                    {{ $this->projectsUsage }} / {{ $projectLimit === null ? 'Unlimited' : $projectLimit }}
                </p>
            </div>
        </div>
    </div>

    {{-- Teams Section --}}
    <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-200/70 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-white/10">
            <div>
                <h2 class="tf-panel-title">Teams</h2>
                <p class="tf-panel-subtitle">Organize members into teams within this workspace.</p>
            </div>

            @if (auth()->user()->can('createTeam', $workspace) && $organization->canCreateTeam())
                <a href="{{ route('teams.create', $workspace) }}" class="tf-button-primary" wire:navigate>
                    <flux:icon name="plus" class="size-4" />
                    New Team
                </a>
            @endif
        </div>

        @if ($workspace->teams->isNotEmpty())
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($workspace->teams as $team)
                    @php
                        $isTeamLocked = $this->lockedTeams->contains(fn($locked) => $locked->id === $team->id);
                    @endphp

                    @if ($isTeamLocked)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4 opacity-70 dark:border-amber-500/20 dark:bg-amber-500/5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $team->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $team->members_count }} members</p>
                                </div>
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                                    Locked
                                </span>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('teams.show', ['workspace' => $workspace, 'team' => $team]) }}"
                            wire:key="team-{{ $team->id }}"
                            class="group flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 transition hover:border-zinc-300 hover:bg-white dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20 dark:hover:bg-white/[0.04]"
                            wire:navigate>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $team->name }}</p>
                                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $team->members_count }} members
                                </p>
                            </div>
                            <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300">→</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="mt-4 rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-white/10">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">No teams yet</p>
                <p class="mt-1 text-sm text-zinc-500">Create your first team in this workspace.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Projects Section --}}
    <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-200/70 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-white/10">
            <div>
                <h2 class="tf-panel-title">Projects</h2>
                <p class="tf-panel-subtitle">Manage work being delivered inside this workspace.</p>
            </div>

            @if (auth()->user()->can('createProject', $workspace) && $organization->canCreateProject())
                <a href="{{ route('projects.create', $workspace) }}" class="tf-button-primary" wire:navigate>
                    <flux:icon name="plus" class="size-4" />
                    New Project
                </a>
            @endif
        </div>

        @if ($workspace->projects->isNotEmpty())
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($workspace->projects as $project)
                    @php
                        $isProjectLocked = $this->lockedProjects->contains(fn($locked) => $locked->id === $project->id);
                    @endphp

                    @if ($isProjectLocked)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4 opacity-70 dark:border-amber-500/20 dark:bg-amber-500/5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $project->tasks_count }} tasks</p>
                                </div>
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                                    Locked
                                </span>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('projects.show', $project) }}"
                            wire:key="project-{{ $project->id }}"
                            class="group flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 transition hover:border-zinc-300 hover:bg-white dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20 dark:hover:bg-white/[0.04]"
                            wire:navigate>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $project->tasks_count }} tasks
                                </p>
                            </div>
                            <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300">→</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="mt-4 rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-white/10">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">No projects yet</p>
                <p class="mt-1 text-sm text-zinc-500">Create your first project in this workspace.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Modals --}}
    <flux:modal wire:model="showEditWorkspaceModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>Edit Workspace</flux:heading>
            <flux:input wire:model="name" label="Workspace Name" required />
            <flux:textarea wire:model="description" label="Description" />
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showEditWorkspaceModal', false)">Cancel</flux:button>
                <flux:button wire:click="updateWorkspace">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteWorkspaceModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>Delete Workspace</flux:heading>
            <p class="text-sm text-zinc-600">Delete all teams and projects before deleting this workspace.</p>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showDeleteWorkspaceModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deleteWorkspace">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

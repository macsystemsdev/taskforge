<?php

namespace App\Livewire\Workspaces;

use App\Actions\Workspaces\DeleteWorkspaceAction;
use App\Actions\Workspaces\UpdateWorkspaceAction;
use App\Data\WorkspaceData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component {
    
    public Workspace $workspace;

    public bool $showEditModal = false;

    public string $name = '';

    public ?string $description = null;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace->loadCount(['teams', 'projects']);

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

    public function render()
    {
        return view('livewire.workspaces.show');
    }
};

?>

<div class="space-y-8">

    <div class="flex items-start justify-between">

        <div>
            <h1 class="text-3xl font-bold">
                {{ $workspace->name }}
            </h1>

            @if ($workspace->description)
                <p class="mt-2 text-zinc-500">
                    {{ $workspace->description }}
                </p>
            @endif
        </div>

        <div class="flex gap-2">

            @can('update', $workspace)
                <flux:button wire:click="$set('showEditModal', true)">
                    Edit
                </flux:button>
            @endcan

            @can('delete', $workspace)
                <flux:button variant="danger" wire:click="deleteWorkspace" wire:confirm="Delete this workspace?">
                    Delete
                </flux:button>
            @endcan

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


            {{-- Teams --}}
            @can('createTeam', $workspace)
                <x-ui.card class="mb-6 space-y-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="tf-panel-title">Teams</h2>
                            <p class="tf-panel-subtitle">Organize members into specialized teams within this organization.
                            </p>
                        </div>
                        <a href="{{ route('teams.create', $organization) }}" class="tf-button-primary px-3 py-2"
                            wire:navigate>
                            Create Team
                        </a>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse ($workspace->teams as $team)
                            <a href="{{ route('teams.show', ['organization' => $workspace, 'team' => $team]) }}"
                                class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]"
                                wire:navigate>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="truncate font-semibold text-zinc-950 dark:text-white">
                                            {{ $team->name }}
                                        </h3>

                                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                            {{ $team->description ?: 'No team description.' }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-white/5">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $team->members->count() }} members
                                    </span>
                                </div>
                            </a>

                        @empty
                            <div class="lg:col-span-2">
                                <x-ui.empty-state title="No teams yet"
                                    description="Create a team to organize members and projects within this organization." />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            @endcan


            <flux:card>
                @can('createProject', $organization)
                    <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-white/5">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $workspace->projects->count() }} projects
                        </span>

                        <a href="{{ route('projects.create', $workspace) }}" class="tf-button-secondary px-3 py-2"
                            wire:navigate>
                            Create Project
                        </a>
                    </div>
                @endcan
            </flux:card>

        </div>

    </div>

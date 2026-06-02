<?php

use App\Actions\Teams\AttachTeamsToProjectAction;
use App\Data\Teams\AttachTeamsToProjectData;
use App\Models\Project;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public array $selectedTeams = [];

    public Collection $teams;

    public function mount(): void
    {
        $this->project->load(['teams', 'workspace.organization.teams']);

        $this->selectedTeams = $this->project->teams->pluck('id')->toArray();
        $this->teams = $this->project->workspace->organization->teams ?? collect();
    }

    public function save(AttachTeamsToProjectAction $action): void
    {
        $action->handle(
            project: $this->project,
            data: new AttachTeamsToProjectData(
                team_ids: $this->selectedTeams,
            )
        );

        $this->project->load('teams');

        Flux::toast(variant: 'success', text: __('Project teams updated.'));
    }
};
?>

<flux:card>
    <flux:heading>
        {{ __('Project teams') }}
    </flux:heading>

    <div class="space-y-3 mt-4">
        @forelse ($teams as $team)
            <flux:checkbox wire:model="selectedTeams" value="{{ $team->id }}" label="{{ $team->name }}" />
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:text>{{ __('No teams are available for this organization yet.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        <flux:button wire:click="save" variant="primary">
            {{ __('Save teams') }}
        </flux:button>
    </div>
</flux:card>

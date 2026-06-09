<?php

use Livewire\Component;
use App\Models\Workspace;
use App\Data\Teams\CreateTeamData;
use App\Actions\Teams\CreateTeam;
use Illuminate\Support\Facades\Gate;


new class extends Component {

    public Workspace $workspace;

    public string $name = '';

    public string $description = '';

    public function createTeam(CreateTeam $action)
    {
        Gate::authorize('createTeam', $this->workspace);
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $team = $action->handle(
            workspace: $this->workspace,
            data: new CreateTeamData(
                name: $validated['name'],
                description: $validated['description']
            ),
            owner: auth()->user(),
        );

        return redirect()->route('teams.show', ['workspace' => $this->workspace, 'team' => $team]);
    }
};
?>

<div class="max-w-2xl mx-auto py-8">
    <form wire:submit="createTeam" class="space-y-6">
        <flux:heading size="lg">{{ __('Create New Team') }}</flux:heading>
        <flux:subheading>{{ __('Organize team members and projects within') }} {{ $workspace->name }}</flux:subheading>

        <flux:input
            wire:model="name"
            label="{{ __('Team Name') }}"
            placeholder="{{ __('e.g., Backend Team') }}"
            required
        />

        <flux:textarea
            wire:model="description"
            label="{{ __('Description') }}"
            placeholder="{{ __('What is this team responsible for?') }}"
        />

        <div class="flex gap-3 justify-end">
            <flux:button
                href="{{ route('workspaces.show', $workspace) }}"
                variant="ghost"
                wire:navigate
            >
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Create Team') }}
            </flux:button>
        </div>
    </form>
</div>

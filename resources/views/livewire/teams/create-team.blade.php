<?php

use Livewire\Component;
use App\Models\Organization;
use App\Data\Teams\CreateTeamData;
use App\Actions\Teams\CreateTeam;

new class extends Component {
    public Organization $organization;

    public string $name = '';

    public string $description = '';

    public function createTeam(CreateTeam $action)
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $team = $action->handle(
            organization: $this->organization,
            data: new CreateTeamData(
                name: $validated['name'],
                description: $validated['description']
            )
        );

        return redirect()->route('teams.show', ['organization' => $this->organization, 'team' => $team]);
    }
};
?>

<div class="max-w-2xl mx-auto py-8">
    <form wire:submit="createTeam" class="space-y-6">
        <flux:heading size="lg">{{ __('Create New Team') }}</flux:heading>
        <flux:subheading>{{ __('Organize team members and projects within') }} {{ $organization->name }}</flux:subheading>

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
                href="{{ route('organizations.show', $organization) }}"
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

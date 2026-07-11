<?php

use App\Actions\Teams\CreateTeam;
use App\Rules\TeamName;
use Flux\Flux;
use Livewire\Component;
use App\Models\Workspace;
use App\Data\Teams\CreateTeamData;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public string $name = '';
    public string $description = '';

    public Workspace $workspace;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function createTeam(): void
    {
        Gate::authorize('createTeam', $this->workspace);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $team = app(CreateTeam::class)->handle(
            workspace: $this->workspace,
            data: new CreateTeamData(
                name: $validated['name'],
                description: $validated['description'] ?? null,
                leaderId: auth()->id(),
                memberIds: [],
            ),
        );

        $this->dispatch('close-modal', name: 'create-team-switcher');

        $this->reset(['name', 'description']);

        Flux::toast(variant: 'success', text: __('Team created.'));

        $this->redirectRoute('teams.edit', ['team' => $team->slug], navigate: true);
    }
}; ?>

<flux:modal name="create-team-switcher" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="createTeam" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Create a new team') }}</flux:heading>
            <flux:subheading>{{ __('Give your team a name to get started.') }}</flux:subheading>
        </div>

        <flux:input wire:model="name" :label="__('Team name')" type="text" required autofocus
            data-test="switcher-create-team-name" />

        <flux:textarea wire:model="description" :label="__('Team Description')" />

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="primary" type="submit" data-test="switcher-create-team-submit">
                {{ __('Create team') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

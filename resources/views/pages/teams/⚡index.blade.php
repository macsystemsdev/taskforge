<?php

use App\Actions\Teams\CreateTeam;
use App\Data\Teams\CreateTeamData;
use App\Rules\TeamName;
use App\Support\UserTeam;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Organization;

new #[Title('Teams')] class extends Component {
    public string $name = '';

    public string $description = '';

    public function createTeam(CreateTeam $createTeam): void
    {
        $workspace = Auth::user()->currentTeam?->workspace ?? Auth::user()->personalTeam()?->workspace;

        abort_if(!$workspace, 422, __('No workspace is available for this team.'));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', new TeamName()],
            'description' => ['nullable', 'string'],
        ]);

        $team = $createTeam->handle($workspace, new CreateTeamData(name: $validated['name'], description: $validated['description'] ?? null, leaderId: Auth::id(), memberIds: []));

        $this->dispatch('close-modal', name: 'create-team');

        $this->reset(['name', 'description']);

        Flux::toast(variant: 'success', text: __('Team created.'));

        $this->redirectRoute('teams.edit', ['team' => $team->slug], navigate: true);
    }

    /**
     * @return Collection<int, UserTeam>
     */
    #[Computed]
    public function teams(): Collection
    {
        return Auth::user()->toUserTeams(includeCurrent: true);
    }

    #[Computed]
    public function organization(): ?Organization
    {
        $workspace = Auth::user()->currentTeam?->workspace ?? Auth::user()->personalTeam()?->workspace;

        return $workspace?->organization;
    }
}; ?>

<section class="w-full">
    

    <flux:heading class="sr-only">{{ __('Teams') }}</flux:heading>

    <div class="space-y-6">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <h1 class="text-2xl font-semibold tracking-tight text-white">{{ __('Teams') }}</h1>
        <p class="mt-2 max-w-xl text-sm text-blue-50 sm:text-base">{{ __('Manage your teams and team memberships.') }}</p>
    </div>

        <div class="mt-6 space-y-3">
            @forelse ($this->teams as $team)
                @if ($this->organization?->teamLocked($team))
                    <flux:button variant="ghost" size="sm" icon="lock-closed" disabled />
                @endif
                <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:border-white/10 dark:bg-zinc-900"
                    data-test="team-row">
                    <div class="flex items-center gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $team->name }}</span>
                                @if ($team->isPersonal)
                                    <flux:badge color="zinc">{{ __('Personal') }}</flux:badge>
                                @endif
                            </div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $team->roleLabel }}
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <flux:tooltip :content="$team->role === 'member' ? __('View team') : __('Edit team')">
                            <flux:button variant="ghost" size="sm"
                                :icon="$team->role === 'member' ? 'eye' : 'pencil'"
                                :href="route('teams.edit', $team->slug)" wire:navigate
                                :data-test="$team->role === 'member' ? 'team-view-button' : 'team-edit-button'" />
                        </flux:tooltip>
                    </div>
                </div>
            @empty
                <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('You don\'t belong to any teams yet.') }}
                </flux:text>
            @endforelse
        </div>
    </div>

    <flux:modal name="create-team" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="createTeam" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create a new team') }}</flux:heading>
                <flux:subheading>{{ __('Give your team a name to get started.') }}</flux:subheading>
            </div>

            <flux:input wire:model="name" :label="__('Team name')" type="text" required autofocus
                data-test="create-team-name" />

            <flux:textarea wire:model="description" :label="__('Description')"
                placeholder="{{ __('What is this team responsible for?') }}" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit" data-test="create-team-submit">
                    {{ __('Create team') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>

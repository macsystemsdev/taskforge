<?php

use App\Models\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Domain\Usage\Actions\DecreaseTeamsAction;

new class extends Component {
    public Team $team;

    public DecreaseTeamsAction $decreaseTeam;

    public string $deleteName = '';

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    #[Computed]
    public function deleteConfirmLabel(): string
    {
        return __('Type ":name" to confirm', ['name' => $this->team->name]);
    }

    public function deleteTeam(): void
    {
        Gate::authorize('delete', $this->team);

        $validated = $this->validate([
            'deleteName' => ['required', 'string'],
        ]);

        if ($validated['deleteName'] !== $this->team->name) {
            $this->addError('deleteName', __('The team name does not match.'));
            return;
        }

        $user = Auth::user();

        // Find fallback team BEFORE deletion
        $fallbackTeam = null;
        if ($user->isCurrentTeam($this->team)) {
            $fallbackTeam = $user->fallbackTeam($this->team);
        }

        DB::transaction(function () use ($user) {
            // Switch other affected users to their fallback team
            User::where('current_team_id', $this->team->id)
                ->where('id', '!=', $user->id)
                ->each(function (User $affectedUser) {
                    $fallback = $affectedUser->fallbackTeam($this->team);
                    if ($fallback) {
                        $affectedUser->switchTeam($fallback);
                    } else {
                        // No fallback team, set to null
                        $affectedUser->update(['current_team_id' => null]);
                    }
                });

            // Decrease team count
            app(DecreaseTeamsAction::class)->handle($this->team->workspace->organization);

            // Delete memberships and team
            $this->team->teamMemberships()->delete();
            $this->team->delete();
        });

        // Switch the current user AFTER deletion
        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Flux::toast(variant: 'success', text: __('Team deleted.'));

        $this->redirectRoute('teams.index', navigate: true);
    }

    /**
     * @return Collection<int, UserTeam>
     */
    #[Computed]
    public function otherTeams(): Collection
    {
        return Auth::user()->toUserTeams();
    }
}; ?>

<flux:modal name="delete-team" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="deleteTeam" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Are you sure?') }}</flux:heading>
            <flux:subheading>
                {{ __('This action cannot be undone. This will permanently delete the team ":name".', ['name' => $team->name]) }}
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="deleteName" :label="$this->deleteConfirmLabel" required
                data-test="delete-team-name" />
        </div>

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" type="submit" data-test="delete-team-confirm">
                {{ __('Delete team') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

<?php

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\Organization;
use App\Models\Membership;
use App\Models\User;
use App\Notifications\Teams\TeamMemberAdded;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Team $team;

    public Organization $org;

    public array $candidates = [];

    // Per-candidate role selections keyed by user id.
    public array $roles = [];

    public string $search = '';

    public function mount(Team $team): void
    {
        $this->team = $team;

        $this->loadCandidates();
    }

    private function loadCandidates(): void
    {
        $org = $this->team->workspace->organization;

        if (! $org) {
            $this->candidates = [];
            return;
        }

        $teamMemberIds = $this->team->members()->pluck('users.id')->toArray();

        $search = trim($this->search);

        $membersQuery = $org->members()
            ->whereNotIn('users.id', $teamMemberIds);

        if ($search !== '') {
            $membersQuery->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $members = $membersQuery->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
            ])->toArray();

        $this->candidates = $members;
    }

    public function addMember(int $userId): void
    {
        Gate::authorize('addMember', $this->team);

        $roleValue = $this->roles[$userId] ?? TeamRole::MEMBER->value;

        $validated = Validator::make(['role' => $roleValue], [
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ])->validate();

        // Prevent duplicates
        $exists = $this->team->memberships()->where('user_id', $userId)->exists();
        if ($exists) {
            Flux::toast(variant: 'warning', text: __('User is already a member of the team.'));
            $this->loadCandidates();
            return;
        }

        $user = User::findOrFail($userId);
        $role = TeamRole::from($validated['role']);

        $this->team->memberships()->create([
            'user_id' => $user->id,
            'role' => $role,
        ]);

        $user->notify(new TeamMemberAdded(
            $this->team,
            $role,
            Auth::user(),
        ));

        Flux::toast(variant: 'success', text: __('Member added to the team.'));

        $this->dispatch('close-modal', name: 'add-member');

        $this->redirectRoute('teams.edit', ['team' => $this->team->slug], navigate: true);
    }

    #[Computed]
    public function availableRoles(): array
    {
        return TeamRole::assignable();
    }
}; ?>

<flux:modal name="add-member" :show="$errors->isNotEmpty()" focusable class="max-w-2xl">
    <div>
        <flux:heading size="lg">{{ __('Add a team member') }}</flux:heading>
        <flux:subheading>{{ __('Select an existing organization member to add to this team.') }}</flux:subheading>
    </div>

    <div class="space-y-4 mt-4">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <input type="search" wire:model.debounce.300ms="search" placeholder="{{ __('Search members...') }}" class="w-full rounded-md border border-zinc-200 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900" />
            </div>
            @foreach ($this->candidates as $candidate)
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-4">
                        <flux:avatar :name="$candidate['name']" :initials="strtoupper(substr($candidate['name'], 0, 1))" />
                        <div>
                            <div class="font-medium">{{ $candidate['name'] }}</div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $candidate['email'] }}</flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:select wire:model="roles.{{ $candidate['id'] }}" :label="__('Role')">
                            @foreach ($this->availableRoles as $role)
                                <flux:select.option value="{{ $role['value'] }}">{{ $role['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:button variant="primary" wire:click.prevent="addMember({{ $candidate['id'] }})">{{ __('Add') }}</flux:button>
                    </div>
                </div>
            @endforeach

            @if (count($this->candidates) === 0)
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text>{{ __('There are no organization members available to add.') }}</flux:text>
                </div>
            @endif
        </div>
    </div>
</flux:modal>

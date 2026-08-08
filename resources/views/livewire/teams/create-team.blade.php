<?php

use Livewire\Component;
use App\Models\Workspace;
use App\Models\Organization;
use App\Data\Teams\CreateTeamData;
use App\Actions\Teams\CreateTeam;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Rules\BelongsToOrganization;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Workspace $workspace;

    public string $name = '';

    public int $leaderId;

    public array $memberIds = [];

    public string $description = '';

    public Collection $organizationMembers;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;

        $this->organizationMembers = $workspace->organization->members()->orderBy('name')->get();

        // If only 1 member exists, pre-select them automatically
        if ($this->organizationMembers->count() === 1) {
            // Use user_id explicitly if it's on a pivot, or explicitly target the user model ID
            $member = $this->organizationMembers->first();
            $this->leaderId = $member->user_id ?? $member->id;
        }
    }

    public function getOrganizationMembersProperty()
    {
        return $this->workspace->organization->members()->orderBy('name')->get();
    }

    public function createTeam(CreateTeam $action)
    {
        Gate::authorize('createTeam', $this->workspace);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'leaderId' => ['required', 'integer', new BelongsToOrganization($this->workspace->organization)],
            'memberIds' => ['array'],
            'memberIds.*' => ['integer', new BelongsToOrganization($this->workspace->organization)],
        ]);

        $team = $action->handle(workspace: $this->workspace, data: new CreateTeamData(name: $validated['name'], description: $validated['description'], leaderId: $validated['leaderId'], memberIds: $validated['memberIds'] ?? []));

        return redirect()->route('teams.show', ['workspace' => $this->workspace, 'team' => $team]);
    }
};
?>

@php
    $organization = $workspace->organization;
    $teamLimit = $organization->currentPlan()?->max_teams;
@endphp

<div class="max-w-2xl mx-auto py-8">
    <div
        class="mb-4 rounded-2xl border border-zinc-200 bg-zinc-50/80 px-4 py-3 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-400">
        Teams in use: {{ $organization->teamUsage() }} / {{ $teamLimit === null ? 'Unlimited' : $teamLimit }}
    </div>

    <form wire:submit="createTeam" class="space-y-6">
        <flux:heading size="lg">{{ __('Create New Team') }}</flux:heading>
        <flux:subheading>{{ __('Organize team members and projects within') }} {{ $workspace->name }}</flux:subheading>

        <flux:input wire:model="name" label="{{ __('Team Name') }}" placeholder="{{ __('e.g., Backend Team') }}"
            required />

        <flux:textarea wire:model="description" label="{{ __('Description') }}"
            placeholder="{{ __('What is this team responsible for?') }}" />

        <flux:select wire:model="leaderId" label="Team Leader">
            <option value="" disabled selected>Select a leader...</option>
            @foreach ($organizationMembers as $member)
                <option value="{{ $member->id }}">
                    {{ $member->name }}
                </option>
            @endforeach
        </flux:select>

        <flux:checkbox.group wire:model="memberIds" label="Team Members">
            @foreach ($organizationMembers as $member)
                <flux:checkbox value="{{ $member->id }}" label="{{ $member->name }}" />
            @endforeach
        </flux:checkbox.group>

        <div class="flex gap-3 justify-end">
            <flux:button href="{{ route('workspaces.show', $workspace) }}" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            @if ($organization->canCreateTeam())
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createTeam"
                    class="inline-flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="createTeam">{{ __('Create Team') }}</span>
                    <span wire:loading.flex wire:target="createTeam"
                        class="inline-flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                            </path>
                        </svg>
                        <span>{{ __('Creating...') }}</span>
                    </span>
                </flux:button>
            @else
                <a href="{{ route('organizations.billing', $organization) }}"
                    class="inline-flex items-center justify-center rounded-full bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    wire:navigate>
                    {{ __('Upgrade plan') }}
                </a>
            @endif
        </div>
    </form>
</div>

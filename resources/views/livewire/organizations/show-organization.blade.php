<?php

use App\Models\Organization;
use App\Models\Invitation;
use Livewire\Component;
use App\Actions\Organizations\InviteMemberAction;
use App\Data\Invitations\InviteMemberData;
use Flux\Flux;

new class extends Component {
    public Organization $organization;

    public function getInvitationsProperty()
    {
        return $this->organization->invitations()->latest()->get();
    }

    // load organization workspaces, teams and members
    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load(['workspaces.projects.tasks', 'members', 'teams']);
    }

    // Handle organization memeber invitation
    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public function inviteMember(InviteMemberAction $inviteMemberAction): void
    {
        $validated = $this->validate([
            'inviteEmail' => ['required', 'email'],

            'inviteRole' => ['required', 'string'],
        ]);

        // Call to handle function in Organization InviteMemberAction to send inviation email
        $inviteData = new InviteMemberData(organization_id: $this->organization->id, organization: $this->organization, email: $validated['inviteEmail'], role: $validated['inviteRole'], invited_by: auth()->id());

        $inviteMemberAction->handle($inviteData);

        // success message
        Flux::toast(variant: 'success', text: 'Invitation sent.');

        $this->reset(['inviteEmail', 'inviteRole']);
    }

    // Cancel invitation
    public function cancelInvitation(string $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);

        abort_if($invitation->status !== 'pending', 403, 'Only pending invitations can be cancelled.');

        abort_if($invitation->expires_at->isPast(), 403, 'Expired invitations cannot be cancelled.');

        $invitation->update([
            'status' => 'cancelled',
        ]);

        Flux::toast(variant: 'success', text: 'Invitation cancelled.');
    }
};

?>

<x-ui.page>
    <x-ui.page-header
        :title="$organization->name"
        :description="__('Manage workspaces, members, invitations, and project flow for this organization.')"
        :eyebrow="__('Organization')"
    >
        <x-slot:actions>
            <x-ui.status-badge :status="$organization->subscription_status ?? 'active'" />
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <x-ui.card class="space-y-2">
            <p class="tf-muted">Workspaces</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $organization->workspaces->count() }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Teams</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $organization->teams->count() }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Members</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $organization->members->count() }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2">
            <p class="tf-muted">Open invitations</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->invitations->where('status', 'pending')->count() }}</p>
        </x-ui.card>
    </div>

    {{-- Workspaces --}}
    <x-ui.card class="mb-6 space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="tf-panel-title">Workspaces</h2>
                <p class="tf-panel-subtitle">Operational spaces inside this organization.</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($organization->workspaces as $workspace)
                    <div class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="truncate font-semibold text-zinc-950 dark:text-white">
                                    {{ $workspace->name }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                    {{ $workspace->description ?: 'No workspace description.' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-white/5">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $workspace->projects->count() }} projects
                            </span>

                            <a href="{{ route('projects.create', $workspace) }}" class="tf-button-secondary px-3 py-2" wire:navigate>
                                Create Project
                            </a>
                        </div>
                    </div>

                @empty
                    <div class="lg:col-span-2">
                        <x-ui.empty-state title="No workspaces" description="Workspaces will appear here when they are added to this organization." />
                    </div>
                @endforelse
        </div>
    </x-ui.card>

    {{-- Teams --}}
    <x-ui.card class="mb-6 space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="tf-panel-title">Teams</h2>
                <p class="tf-panel-subtitle">Organize members into specialized teams within this organization.</p>
            </div>
            <a href="{{ route('teams.create', $organization) }}" class="tf-button-primary px-3 py-2" wire:navigate>
                Create Team
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($organization->teams as $team)
                    <a href="{{ route('teams.show', ['organization' => $organization, 'team' => $team]) }}" class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]" wire:navigate>
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

                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-white/5">
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $team->members->count() }} members
                            </span>
                        </div>
                    </a>

                @empty
                    <div class="lg:col-span-2">
                        <x-ui.empty-state title="No teams yet" description="Create a team to organize members and projects within this organization." />
                    </div>
                @endforelse
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Invitation Form --}}
        <x-ui.card class="space-y-6">
            <div>
                <h2 class="tf-panel-title">Invite Member</h2>
                <p class="tf-panel-subtitle">Invite collaborators into this organization.</p>
            </div>

                <form wire:submit="inviteMember" class="space-y-4">

                    <flux:input wire:model="inviteEmail" label="Email" type="email" required />

                    @error('email')
                        <p class="text-sm text-red-500 mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('inviteEmail')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror

                    <flux:select wire:model="inviteRole" label="Role">

                        <option value="member">
                            Member
                        </option>

                        <option value="admin">
                            Admin
                        </option>


                    </flux:select>

                    @error('inviteRole')
                        <p class="text-sm text-red-500 mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                    <flux:button variant="primary" type="submit" class="w-full sm:w-auto">
                        Send Invitation
                    </flux:button>

                </form>
        </x-ui.card>

        {{-- Members --}}
        <x-ui.card class="space-y-6">
            <div>
                <h2 class="tf-panel-title">Members</h2>
                <p class="tf-panel-subtitle">Organization participants.</p>
            </div>

                <div class="space-y-3">

                    @foreach ($organization->members as $member)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-white/10">
                            <div class="flex min-w-0 items-center gap-3">
                                <x-ui.avatar :name="$member->name" />
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-zinc-950 dark:text-white">
                                    {{ $member->name }}
                                    </p>

                                    <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $member->email }}
                                    </p>
                                </div>
                            </div>

                  
                            <flux:badge>
                                {{ $member->role }}
                            </flux:badge>

                        </div>
                    @endforeach

                </div>
        </x-ui.card>

    </div>

    {{-- Invitations Table --}}
    <x-ui.card padding="p-0" class="mt-6 overflow-hidden">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
            <h2 class="tf-panel-title">Invitations</h2>
            <p class="tf-panel-subtitle">Track invitation states and pending access.</p>
        </div>

            <div class="overflow-x-auto">
                <table>

                    <thead>
                        <tr>
                            <th>
                                Email
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Invited
                            </th>

                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($this->invitations as $invitation)
                            <tr class="tf-row-link">

                                <td>
                                    {{ $invitation->email }}
                                </td>

                                <td>
                                    {{ ucfirst($invitation->role) }}
                                </td>

                                <td>

                                    <x-ui.status-badge :status="$invitation->computed_status" />

                                    @if ($invitation->rejection_reason)
                                        <p class="text-xs text-zinc-500 mt-1">

                                            Reason:
                                            {{ $invitation->rejection_reason }}

                                        </p>
                                    @endif

                                </td>

                                <td>
                                    {{ $invitation->created_at->diffForHumans() }}
                                </td>

                                <td>

                                    @if ($invitation->isPending())
                                        <flux:button size="sm" variant="danger"
                                            wire:click="
                                        cancelInvitation(
                                            {{ $invitation->id }}
                                        )
                                    ">
                                            Cancel
                                        </flux:button>
                                    @endif

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No invitations" description="Pending and historical invitations will appear here." />
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
    </x-ui.card>
</x-ui.page>

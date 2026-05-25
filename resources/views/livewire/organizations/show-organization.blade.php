<?php

use App\Models\Organization;
use App\Models\Invitation;
use Livewire\Component;
use App\Actions\Organizations\InviteMemberAction;
use App\Data\Invitations\InviteMemberData;

new class extends Component {
    public Organization $organization;

    public function getInvitationsProperty()
    {
        return $this->organization->invitations()->latest()->get();
    }

    // load organization workspaces and memebers
    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load(['workspaces', 'members']);
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

<div class="max-w-7xl mx-auto py-10 space-y-8">

    <div class="flex items-start justify-between">

        <div>

            <flux:heading size="xl">
                {{ $organization->name }}
            </flux:heading>

            <flux:subheading class="mt-2">
                Manage workspaces, teams, and members.
            </flux:subheading>

        </div>

        <flux:badge color="green">
            {{ ucfirst($organization->subscription_plan) }}
        </flux:badge>

    </div>

    {{-- Workspaces --}}
    <div class="lg:col-span-2">

        <flux:card class="space-y-6">

            <div class="flex items-center justify-between">

                <div>

                    <flux:heading size="lg">
                        Workspaces
                    </flux:heading>

                    <flux:subheading>
                        Operational spaces inside the organization.
                    </flux:subheading>

                </div>

                <flux:button variant="primary">
                    New Workspace
                </flux:button>

            </div>

            <div class="space-y-4">

                @forelse ($organization->workspaces as $workspace)
                    <div class="border rounded-xl p-4">

                        <div class="flex items-center justify-between">

                            <div>

                                <h3 class="font-semibold text-lg">
                                    {{ $workspace->name }}
                                </h3>

                                <p class="text-sm text-zinc-500 mt-1">
                                    {{ $workspace->description }}
                                </p>

                            </div>

                        </div>
                        <a href="{{ route('projects.create', $workspace) }}"
                            class="px-4 py-2 rounded-xl bg-black text-white text-sm">
                            Create Project

                    </div>

                    </a>

                @empty

                    <div class="text-sm text-zinc-500">
                        No workspaces created yet.
                    </div>
                @endforelse

            </div>



        </flux:card>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Invitation Form --}}
        <div>
            <flux:card class="space-y-6">

                <div>

                    <flux:heading size="lg">
                        Invite Member
                    </flux:heading>

                    <flux:subheading>
                        Invite collaborators into this organization.
                    </flux:subheading>

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

                        <option value="manager">
                            Manager
                        </option>

                        <option value="manager">
                            Viewer
                        </option>

                    </flux:select>

                    @error('inviteRole')
                        <p class="text-sm text-red-500 mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                    <flux:button variant="primary" type="submit">
                        Send Invitation
                    </flux:button>

                </form>

            </flux:card>
        </div>

        {{-- Members --}}
        <div>

            <flux:card class="space-y-6">

                <div>

                    <flux:heading size="lg">
                        Members
                    </flux:heading>

                    <flux:subheading>
                        Organization participants.
                    </flux:subheading>

                </div>

                <div class="space-y-3">

                    @foreach ($organization->members as $member)
                        <div class="flex items-center justify-between">

                            <div>

                                <p class="font-medium">
                                    {{ $member->name }}
                                </p>

                                <p class="text-sm text-zinc-500">
                                    {{ $member->email }}
                                </p>

                            </div>

                            <flux:badge>
                                {{ $member->pivot->role }}
                            </flux:badge>

                        </div>
                    @endforeach

                </div>

            </flux:card>

        </div>


    </div>

    {{-- Invitations Table --}}
    <div>
        <flux:card class="mt-8">

            <div class="mb-6">

                <flux:heading size="lg">
                    Invitations
                </flux:heading>

                <flux:subheading>
                    Track invitation states.
                </flux:subheading>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3">
                                Email
                            </th>

                            <th class="text-left py-3">
                                Role
                            </th>

                            <th class="text-left py-3">
                                Status
                            </th>

                            <th class="text-left py-3">
                                Invited
                            </th>

                            <th class="text-left py-3">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($this->invitations as $invitation)
                            <tr class="border-b">

                                <td class="py-4">
                                    {{ $invitation->email }}
                                </td>

                                <td class="py-4">
                                    {{ ucfirst($invitation->role) }}
                                </td>

                                <td class="py-4">

                                    <flux:badge color="{{ $invitation->status_color }}">
                                        {{ ucfirst($invitation->computed_status) }}
                                    </flux:badge>

                                    @if ($invitation->rejection_reason)
                                        <p class="text-xs text-zinc-500 mt-1">

                                            Reason:
                                            {{ $invitation->rejection_reason }}

                                        </p>
                                    @endif

                                </td>

                                <td class="py-4">
                                    {{ $invitation->created_at->diffForHumans() }}
                                </td>

                                <td class="py-4">

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
                        @endforeach

                    </tbody>

                </table>

            </div>

        </flux:card>
    </div>




</div>

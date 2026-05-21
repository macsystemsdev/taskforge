<?php

use App\Models\Organization;
use Livewire\Component;
use App\Actions\Organizations\InviteMemberAction;

new class extends Component {
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load(['workspaces', 'members']);
    }

    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public function inviteMember(InviteMemberAction $inviteMemberAction): void
    {
        $validated = $this->validate([
            'inviteEmail' => ['required', 'email'],

            'inviteRole' => ['required', 'string'],
        ]);

        $inviteMemberAction->handle(
            organization: $this->organization,
            inviter: auth()->user(),
            data: [
                'email' => $validated['inviteEmail'],
                'role' => $validated['inviteRole'],
            ],
        );

        Flux::toast(variant: 'success', text: 'Invitation sent.');

        $this->reset(['inviteEmail', 'inviteRole']);
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

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

                        </div>

                    @empty

                        <div class="text-sm text-zinc-500">
                            No workspaces created yet.
                        </div>
                    @endforelse

                </div>

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

                    <flux:button variant="primary" type="submit">
                        Send Invitation
                    </flux:button>

                </form>

            </flux:card>
        </div>

    </div>




</div>

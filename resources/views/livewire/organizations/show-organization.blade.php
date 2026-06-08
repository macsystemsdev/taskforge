<?php

use App\Models\Organization;
use App\Models\Invitation;
use Livewire\Component;
use App\Actions\Organizations\InviteMemberAction;
use App\Data\Invitations\InviteMemberData;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Models\User;
use App\Actions\Organizations\UpdateOrganizationMemberRoleAction;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use App\Actions\Workspaces\CreateWorkspaceAction;
use App\Data\Workspaces\CreateWorkspaceData;
use App\Actions\Organizations\DeleteOrganizationAction;
use App\Actions\Organizations\UpdateOrganizationAction;
use App\Data\Organizations\UpdateOrganizationData;

new class extends Component {
    public Organization $organization;

    // Organization edit and delete modal control
    public bool $showEditOrganizationModal = false;

    public bool $showDeleteOrganizationModal = false;

    public string $organizationName = '';

    // Edit organization modal
    public function openEditOrganizationModal(): void
    {
        Gate::authorize('update', $this->organization);

        $this->organizationName = $this->organization->name;

        $this->showEditOrganizationModal = true;
    }

    // Update organization
    public function updateOrganization(): void
    {
        Gate::authorize('update', $this->organization);

        $this->validate([
            'organizationName' => ['required', 'string', 'max:255'],
        ]);

        app(UpdateOrganizationAction::class)->handle($this->organization, 
        UpdateOrganizationData::from([
            'name' => $this->organizationName
        ]));

        $this->organization->refresh();

        $this->showEditOrganizationModal = false;

        Flux::toast(text: 'Organization updated successfully.', variant: 'success');
    }

    // Delete Organization modal
    public function openDeleteOrganizationModal(): void
    {
        Gate::authorize('delete', $this->organization);

        $this->organizationName = $this->organization->name;

        $this->showDeleteOrganizationModal = true;
    }

    // Delete organization
    public function deleteOrganization(): void
    {
        Gate::authorize('delete', $this->organization);

        app(DeleteOrganizationAction::class)->handle($this->organization);

        Flux::toast(text: 'Organization deleted successfully.', variant: 'success');

        $this->redirectRoute('organizations.index');
    }

    // workspace modal control
    public bool $showCreateWorkspaceModal = false;

    public string $workspaceName = '';

    public ?string $workspaceDescription = null;

    // Create workspace

    public function openCreateWorkspaceModal(): void
    {
        Gate::authorize('createWorkspace', $this->organization);

        $this->showCreateWorkspaceModal = true;

        $this->reset(['workspaceName', 'workspaceDescription']);
    }

    public function createWorkspace()
    {
        Gate::authorize('createWorkspace', $this->organization);

        $this->validate([
            'workspaceName' => ['required', 'string', 'max:255'],
            'workspaceDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        app(CreateWorkspaceAction::class)->handle(
            $this->organization,
            CreateWorkspaceData::from([
                'name' => $this->workspaceName,
                'description' => $this->workspaceDescription,
            ]),
        );

        $this->showCreateWorkspaceModal = false;

        Flux::toast(text: 'Workspace created successfully.', variant: 'success');

        $this->organization = $this->organization->fresh([
            'workspaces' => fn($query) => $query->withCount(['teams', 'projects']),
            'members',
        ]);
    }

    public function getInvitationsProperty()
    {
        return $this->organization->invitations()->latest()->get();
    }

    // load organization workspaces, teams and members
    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load([
            'workspaces' => fn($query) => $query->withCount(['teams', 'projects']),
            'members',
        ]);
    }

    // Handle organization memeber invitation
    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public function inviteMember(InviteMemberAction $inviteMemberAction): void
    {
        Gate::authorize('inviteMembers', $organization);

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

    // Update Roles
    public function updateRole(UpdateOrganizationMemberRoleAction $updateRoleAction, int $memberId, string $role): void
    {
        Gate::authorize('changeMemberRole', $this->organization);
        try {
            $member = User::findOrFail($memberId);

            $updateRoleAction->handle(organization: $this->organization, member: $member, role: OrganizationRole::from($role));

            $this->organization->refresh();

            Flux::toast(text: 'Role updated successfully.', variant: 'success');
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }
};

?>

<x-ui.page>
    <x-ui.page-header :title="$organization->name" :description="__('Manage workspaces, members, invitations, and project flow for this organization.')" :eyebrow="__('Organization')">
        <x-slot:actions>
            <x-ui.status-badge :status="$organization->subscription_status ?? 'active'" />
        </x-slot:actions>
    </x-ui.page-header>

    @can('viewActivityLog', $organization)
        <div class="mb-6 grid gap-4 sm:grid-cols-4">
            <x-ui.card class="space-y-2">
                <p class="tf-muted">Workspaces</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $organization->workspaces->count() }}</p>
            </x-ui.card>



            <x-ui.card class="space-y-2">
                <p class="tf-muted">Members</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $organization->members->count() }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Open invitations</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $this->invitations->where('status', 'pending')->count() }}</p>
            </x-ui.card>
        </div>
    @endcan

    <div class="flex gap-2">

        @can('update', $organization)
            <flux:button wire:click="openEditOrganizationModal">
                Edit Organization
            </flux:button>
        @endcan

        @can('delete', $organization)
            <flux:button variant="danger" wire:click="openDeleteOrganizationModal">
                Delete Organization
            </flux:button>
        @endcan

    </div>


    {{-- Workspaces --}}

    {{-- modal to create workspace --}}

    <flux:modal wire:model="showCreateWorkspaceModal">

        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    Create Workspace
                </flux:heading>

                <flux:text class="mt-2">
                    Create a new workspace within this organization.
                </flux:text>
            </div>

            <flux:input wire:model="workspaceName" label="Name" />

            <flux:textarea wire:model="workspaceDescription" label="Description" />

            <div class="flex justify-end gap-2">

                <flux:button variant="ghost" wire:click="$set('showCreateWorkspaceModal', false)">
                    Cancel
                </flux:button>

                <flux:button variant="primary" wire:click="createWorkspace">
                    Create Workspace
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-ui.card class="mb-6 space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="tf-panel-title">Workspaces</h2>
                <p class="tf-panel-subtitle">Operational spaces inside this organization.</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($organization->workspaces as $workspace)
                <div
                    class="rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="truncate font-semibold text-zinc-950 dark:text-white">
                                {{ $workspace->name }}
                            </h3>


                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                {{ $workspace->description ?: 'No workspace description.' }}
                            </p>

                            <x-ui.card class="space-y-2">
                                <p class="tf-muted">Teams</p>
                                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                    {{ $workspace->teams_count }}</p>
                            </x-ui.card>

                            <x-ui.card class="space-y-2">
                                <p class="tf-muted">Projects</p>
                                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                    {{ $workspace->projects_count }}</p>
                            </x-ui.card>



                        </div>
                    </div>

                    <a href="{{ route('workspaces.show', ['workspace' => $workspace]) }}">
                        <flux:button>
                            View Workspace
                        </flux:button>
                    </a>

                </div>
            @endforeach

            @can('createWorkspace', $organization)
                <flux:card wire:click="openCreateWorkspaceModal" wire:key="create-workspace-card" class="cursor-pointer">
                    <div class="flex h-full flex-col items-center justify-center gap-2">

                        <flux:icon.plus />

                        <span>Create Workspace</span>

                    </div>
                </flux:card>
            @endcan
        </div>



        {{-- Modal to edit organization --}}
        <flux:modal wire:model="showEditOrganizationModal">

            <div class="space-y-4">

                <flux:heading>
                    Edit Organization
                </flux:heading>

                <flux:input wire:model="organizationName" label="Organization Name" />

                <div class="flex justify-end gap-2">

                    <flux:button variant="ghost" wire:click="$set('showEditOrganizationModal', false)">
                        Cancel
                    </flux:button>

                    <flux:button wire:click="updateOrganization">
                        Save Changes
                    </flux:button>

                </div>

            </div>

        </flux:modal>

        {{-- Modal to delete organization --}}
        <flux:modal wire:model="showDeleteOrganizationModal">

            <div class="space-y-4">

                <flux:heading>
                    Delete Organization
                </flux:heading>

                <p>
                    Delete all workspaces before deleting this organization.
                </p>

                <div class="flex justify-end gap-2">

                    <flux:button variant="ghost" wire:click="$set('showDeleteOrganizationModal', false)">
                        Cancel
                    </flux:button>

                    <flux:button variant="danger" wire:click="deleteOrganization">
                        Delete
                    </flux:button>

                </div>

            </div>

        </flux:modal>

    </x-ui.card>




    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Invitation Form --}}
        @can('inviteMembers', $organization)
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
        @endcan


        {{-- Members --}}
        <x-ui.card class="space-y-6">
            <div>
                <h2 class="tf-panel-title">Members</h2>
                <p class="tf-panel-subtitle">Organization participants.</p>
            </div>

            <div class="space-y-3">

                @foreach ($organization->members as $member)
                    <div
                        class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-white/10">
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


                        @can('changeMemberRole', $organization)
                            <flux:select
                                wire:change="updateRole(
        {{ $member->id }},
        $event.target.value
    )">
                                @foreach (App\Domain\Organizations\Enums\OrganizationRole::cases() as $role)
                                    <option value="{{ $role }}" @selected($member->pivot->role === $role)>
                                        {{ ucfirst($role->value) }}
                                    </option>
                                @endforeach
                            </flux:select>
                        @endcan

                        @if (!auth()->user()->can('changeMemberRole', $organization))
                            <input type="text" disabled value="{{ $member->pivot->role }}">
                        @endif

                    </div>
                @endforeach

            </div>
        </x-ui.card>

    </div>

    {{-- Invitations Table --}}
    @can('inviteMembers', $organization)
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
                                    <x-ui.empty-state title="No invitations"
                                        description="Pending and historical invitations will appear here." />
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
        </x-ui.card>
    @endcan

</x-ui.page>

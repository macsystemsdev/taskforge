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
use Illuminate\Database\Eloquent\Relations\HasMany;

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

        app(UpdateOrganizationAction::class)->handle(
            $this->organization,
            UpdateOrganizationData::from([
                'name' => $this->organizationName,
            ]),
        );

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

        try {
            app(DeleteOrganizationAction::class)->handle($this->organization);
            Flux::toast(text: 'Organization deleted successfully.', variant: 'success');

            $this->redirectRoute('organizations.index');
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
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
        Gate::authorize('inviteMembers', $this->organization);

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
    @php
        $currentPlan = $organization->currentPlan();
        $workspaceUsage = $organization->workspaces()->count();
        $projectUsage = $organization->projects()->count();
        $teamUsage = $organization->teams()->count();
        $taskUsage = $organization->tasks()->count();
        $memberUsage = $organization->members()->count();
        $workspaceLimit = $currentPlan?->max_workspaces;
        $projectLimit = $currentPlan?->max_projects;
        $teamLimit = $currentPlan?->max_teams;
        $taskLimit = $currentPlan?->max_tasks;
        $memberLimit = $currentPlan?->max_members;
        $storageLimit = $currentPlan?->max_storage_mb;
        $lockedWorkspaces = $organization->lockedWorkspaces();
    @endphp

    <div
        class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <x-ui.page-header :title="$organization->name" :description="__('Manage workspaces, members, invitations, and project flow for this organization.')" :eyebrow="__('Organization')">
            <x-slot:actions>
                <x-ui.status-badge :status="$organization->subscription_status ?? 'active'" />
            </x-slot:actions>
        </x-ui.page-header>

        <div class="mt-5 flex flex-wrap gap-2">
            @if (auth()->user()->can('update', $organization))
                <flux:button wire:click="openEditOrganizationModal" wire:loading.attr="disabled"
                    wire:target="openEditOrganizationModal">
                    Edit Organization
                </flux:button>
            @endif

            @if (auth()->user()->can('delete', $organization))
                <flux:button variant="danger" wire:click="openDeleteOrganizationModal" wire:loading.attr="disabled"
                    wire:target="openDeleteOrganizationModal">
                    Delete Organization
                </flux:button>
            @endif
        </div>
    </div>

    @if (auth()->user()->can('viewActivityLog', $organization))
        <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-ui.card class="space-y-2">
                <p class="tf-muted">Workspaces</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $workspaceUsage }} / {{ $workspaceLimit === null ? 'Unlimited' : $workspaceLimit }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Projects</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $projectUsage }} / {{ $projectLimit === null ? 'Unlimited' : $projectLimit }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Teams</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $teamUsage }} / {{ $teamLimit === null ? 'Unlimited' : $teamLimit }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Tasks</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $taskUsage }} / {{ $taskLimit === null ? 'Unlimited' : $taskLimit }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Members</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $memberUsage }} / {{ $memberLimit === null ? 'Unlimited' : $memberLimit }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="tf-muted">Storage Usage</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $organization->storageUsageMb() }} MB /
                    {{ $storageLimit === null ? 'Unlimited' : $storageLimit . ' MB' }}</p>
            </x-ui.card>
        </div>
    @endif

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

                <flux:button variant="primary" wire:click="createWorkspace" wire:loading.attr="disabled"
                    wire:target="createWorkspace" class="inline-flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="createWorkspace">Create Workspace</span>
                    <span wire:loading.flex wire:target="createWorkspace" class="items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                            </path>
                        </svg>
                        <span>Creating...</span>
                    </span>
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-ui.card class="mb-6 border-zinc-200/80 bg-white/90 shadow-sm">
        <div
            class="flex flex-col gap-4 border-b border-zinc-200/70 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-white/10">
            <div>
                <h2 class="tf-panel-title">Workspaces</h2>
                <p class="tf-panel-subtitle">Organize teams, projects, and delivery areas around each workspace.</p>
            </div>

            @if (auth()->user()->can('createWorkspace', $organization))
                @if ($organization->canCreateWorkspace())
                    <flux:button wire:click="openCreateWorkspaceModal" wire:loading.attr="disabled"
                        wire:target="openCreateWorkspaceModal">
                        Create Workspace
                    </flux:button>
                @else
                    <a href="{{ route('organizations.billing', $organization) }}"
                        class="inline-flex items-center justify-center rounded-full bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                        wire:navigate>
                        Upgrade plan
                    </a>
                @endif
            @endif
        </div>

        @if ($organization->workspaces->isNotEmpty())
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                @foreach ($organization->workspaces as $workspace)
                    <div
                        class="group rounded-2xl border border-zinc-200 bg-zinc-50/80 p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:bg-white dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-white/15 dark:hover:bg-white/[0.05]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-zinc-950 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">
                                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold text-zinc-950 dark:text-white">
                                        {{ $workspace->name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $workspace->projects_count }} projects • {{ $workspace->teams_count }}
                                        teams
                                    </p>
                                </div>
                            </div>

                            @php
                                $isWorkspaceLocked = $lockedWorkspaces->contains(
                                    fn($lockedWorkspace) => $lockedWorkspace->id === $workspace->id,
                                );
                            @endphp

                            @if ($isWorkspaceLocked)
                                <span
                                    class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                                    Locked
                                </span>
                            @else
                                <span
                                    class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    Active
                                </span>
                            @endif
                        </div>

                        <p class="mt-4 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            {{ $workspace->description ?: 'This workspace is ready for teams, projects, and delivery planning.' }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <div
                                class="rounded-xl border border-zinc-200 bg-white/80 px-3 py-2 text-sm text-zinc-700 shadow-sm dark:border-white/10 dark:bg-zinc-900/80 dark:text-zinc-200">
                                <span
                                    class="font-semibold text-zinc-950 dark:text-white">{{ $workspace->teams_count }}</span>
                                teams
                            </div>
                            <div
                                class="rounded-xl border border-zinc-200 bg-white/80 px-3 py-2 text-sm text-zinc-700 shadow-sm dark:border-white/10 dark:bg-zinc-900/80 dark:text-zinc-200">
                                <span
                                    class="font-semibold text-zinc-950 dark:text-white">{{ $workspace->projects_count }}</span>
                                projects
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Operational view ready</p>
                            <a href="{{ route('workspaces.show', ['workspace' => $workspace]) }}"
                                class="inline-flex items-center text-sm font-semibold text-zinc-950 transition hover:underline dark:text-white"
                                wire:navigate>
                                Open workspace
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="mt-6 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/70 p-8 text-center dark:border-white/10 dark:bg-white/[0.03]">
                <p class="text-base font-semibold text-zinc-950 dark:text-white">No workspaces yet</p>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Create the first workspace to organize projects
                    and teams around a clear operational home.</p>

                @if (auth()->user()->can('createWorkspace', $organization))
                    @if ($organization->canCreateWorkspace())
                        <button wire:click="openCreateWorkspaceModal" wire:loading.attr="disabled"
                            wire:target="openCreateWorkspaceModal"
                            class="tf-button-primary mt-5 inline-flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="openCreateWorkspaceModal">Create your first
                                workspace</span>
                            <span wire:loading.flex wire:target="openCreateWorkspaceModal"
                                class="items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                    </path>
                                </svg>
                                <span>Opening...</span>
                            </span>
                        </button>
                    @else
                        <a href="{{ route('organizations.billing', $organization) }}" class="tf-button-primary mt-5"
                            wire:navigate>
                            Upgrade plan
                        </a>
                    @endif
                @endif
            </div>
        @endif



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

                    <flux:button wire:click="updateOrganization" wire:loading.attr="disabled"
                        wire:target="updateOrganization" class="inline-flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="updateOrganization">Save Changes</span>
                        <span wire:loading.flex wire:target="updateOrganization"
                            class="items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                </path>
                            </svg>
                            <span>Saving...</span>
                        </span>
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

                    <flux:button variant="danger" wire:click="deleteOrganization" wire:loading.attr="disabled"
                        wire:target="deleteOrganization" class="inline-flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="deleteOrganization">Delete</span>
                        <span wire:loading.flex wire:target="deleteOrganization"
                            class="items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                </path>
                            </svg>
                            <span>Deleting...</span>
                        </span>
                    </flux:button>

                </div>

            </div>

        </flux:modal>

    </x-ui.card>




    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Invitation Form --}}
        @if (auth()->user()->can('inviteMembers', $organization))
            <x-ui.card class="space-y-6 border-zinc-200/80 bg-white/90 shadow-sm">
                <div>
                    <h2 class="tf-panel-title">Invite Member</h2>
                    <p class="tf-panel-subtitle">Bring collaborators into the organization with a clear role from the
                        start.
                    </p>
                </div>

                <form wire:submit="inviteMember" class="space-y-4">
                    <flux:input wire:model="inviteEmail" label="Email" type="email" required />

                    @error('email')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('inviteEmail')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <flux:select wire:model="inviteRole" label="Role">
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </flux:select>

                    @error('inviteRole')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    @if ($organization->canAddMember())
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled"
                            wire:target="inviteMember"
                            class="inline-flex w-full items-center justify-center gap-2 sm:w-auto">
                            <span wire:loading.remove wire:target="inviteMember">Send Invitation</span>
                            <span wire:loading.flex wire:target="inviteMember"
                                class="items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                    </path>
                                </svg>
                                <span>Sending...</span>
                            </span>
                        </flux:button>
                    @else
                        <flux:button variant="primary" type="button" class="w-full sm:w-auto"
                            @disabled(true)>
                            Upgrade to invite more members.
                        </flux:button>
                    @endif


                </form>
            </x-ui.card>
        @endif

        {{-- Members --}}
        <x-ui.card class="space-y-6 border-zinc-200/80 bg-white/90 shadow-sm">
            <div>
                <h2 class="tf-panel-title">Members</h2>
                <p class="tf-panel-subtitle">The people currently contributing to this organization.</p>
            </div>

            <div class="space-y-3">
                @foreach ($organization->members as $member)
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
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

                        @if (auth()->user()->can('changeMemberRole', $organization))
                            <flux:select wire:change="updateRole({{ $member->id }}, $event.target.value)">
                                @foreach (App\Domain\Organizations\Enums\OrganizationRole::cases() as $role)
                                    <option value="{{ $role }}" @selected($member->pivot->role === $role)>
                                        {{ ucfirst($role->value) }}
                                    </option>
                                @endforeach
                            </flux:select>
                        @endif

                        @if (!auth()->user()->can('changeMemberRole', $organization))
                            <div
                                class="rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-sm text-zinc-600 dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-300">
                                {{ ucfirst($member->pivot->role) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.card>

    </div>

    {{-- Invitations Table --}}
    @if (auth()->user()->can('inviteMembers', $organization))
        <x-ui.card padding="p-0" class="mt-6 overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                <h2 class="tf-panel-title">Invitations</h2>
                <p class="tf-panel-subtitle">Track the status of invites and pending access requests.</p>
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
                                            wire:click="cancelInvitation({{ $invitation->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="cancelInvitation({{ $invitation->id }})"
                                            class="inline-flex items-center justify-center gap-2">
                                            <span wire:loading.remove
                                                wire:target="cancelInvitation({{ $invitation->id }})">Cancel</span>
                                            <span wire:loading.flex
                                                wire:target="cancelInvitation({{ $invitation->id }})"
                                                class="items-center justify-center gap-2">
                                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                                    aria-hidden="true">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z">
                                                    </path>
                                                </svg>
                                                <span>Canceling...</span>
                                            </span>
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

</x-ui.page>

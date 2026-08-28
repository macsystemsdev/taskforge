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
use Livewire\Attributes\Computed;

new class extends Component {
    public Organization $organization;

    // Modals
    public bool $showEditOrganizationModal = false;
    public bool $showDeleteOrganizationModal = false;
    public bool $showCreateWorkspaceModal = false;
    public bool $showInviteModal = false;

    // Forms
    public string $organizationName = '';
    public string $workspaceName = '';
    public ?string $workspaceDescription = null;
    public string $inviteEmail = '';
    public string $inviteRole = 'member';

    // Tab state
    public string $activeTab = 'members';

    public function mount(Organization $organization): void
    {
        $this->organization = $organization->load([
            'workspaces' => fn($query) => $query->withCount(['teams', 'projects']),
            'members',
            'subscription.plan',
            'subscription.pendingPlan',
            'usage',
            'invitations' => fn($query) => $query->latest(),
        ]);
    }

    #[Computed]
    public function workspaceUsage(): int
    {
        return $this->organization->workspaces->count();
    }

    #[Computed]
    public function projectUsage(): int
    {
        return $this->organization->workspaces->sum('projects_count');
    }

    #[Computed]
    public function teamUsage(): int
    {
        return $this->organization->workspaces->sum('teams_count');
    }

    #[Computed]
    public function taskUsage(): int
    {
        return $this->organization->projects()->count();
    }

    #[Computed]
    public function memberUsage(): int
    {
        return $this->organization->members->count();
    }

    #[Computed]
    public function storageUsageMb(): float
    {
        return round(($this->organization->usage?->storage_used_bytes ?? 0) / 1024 / 1024, 2);
    }

    #[Computed]
    public function lockedWorkspaces()
    {
        $limit = $this->organization->currentPlan()?->max_workspaces;

        if ($limit === null) {
            return collect();
        }

        return $this->organization->workspaces
            ->sortBy('created_at')
            ->slice($limit);
    }

    public function openEditOrganizationModal(): void
    {
        Gate::authorize('update', $this->organization);
        $this->organizationName = $this->organization->name;
        $this->showEditOrganizationModal = true;
    }

    public function updateOrganization(): void
    {
        Gate::authorize('update', $this->organization);

        $this->validate([
            'organizationName' => ['required', 'string', 'max:255'],
        ]);

        app(UpdateOrganizationAction::class)->handle(
            $this->organization,
            UpdateOrganizationData::from(['name' => $this->organizationName]),
        );

        $this->organization->refresh();
        $this->showEditOrganizationModal = false;

        Flux::toast(text: 'Organization updated successfully.', variant: 'success');
    }

    public function openDeleteOrganizationModal(): void
    {
        Gate::authorize('delete', $this->organization);
        $this->organizationName = $this->organization->name;
        $this->showDeleteOrganizationModal = true;
    }

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

    public function openCreateWorkspaceModal(): void
    {
        Gate::authorize('createWorkspace', $this->organization);
        $this->reset(['workspaceName', 'workspaceDescription']);
        $this->showCreateWorkspaceModal = true;
    }

    public function createWorkspace(): void
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
            'subscription.plan',
            'subscription.pendingPlan',
            'usage',
            'invitations' => fn($query) => $query->latest(),
        ]);
    }

    public function openInviteModal(): void
    {
        Gate::authorize('inviteMembers', $this->organization);
        $this->reset(['inviteEmail', 'inviteRole']);
        $this->showInviteModal = true;
    }

    public function inviteMember(InviteMemberAction $inviteMemberAction): void
    {
        Gate::authorize('inviteMembers', $this->organization);

        $validated = $this->validate([
            'inviteEmail' => ['required', 'email'],
            'inviteRole' => ['required', 'string'],
        ]);

        $inviteData = new InviteMemberData(
            organization_id: $this->organization->id,
            organization: $this->organization,
            email: $validated['inviteEmail'],
            role: $validated['inviteRole'],
            invited_by: auth()->id(),
        );

        $inviteMemberAction->handle($inviteData);

        $this->showInviteModal = false;

        Flux::toast(variant: 'success', text: 'Invitation sent.');

        $this->organization = $this->organization->fresh([
            'workspaces' => fn($query) => $query->withCount(['teams', 'projects']),
            'members',
            'subscription.plan',
            'subscription.pendingPlan',
            'usage',
            'invitations' => fn($query) => $query->latest(),
        ]);
    }

    public function cancelInvitation(string $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);

        abort_if($invitation->status !== 'pending', 403, 'Only pending invitations can be cancelled.');
        abort_if($invitation->expires_at->isPast(), 403, 'Expired invitations cannot be cancelled.');

        $invitation->update(['status' => 'cancelled']);

        $this->organization = $this->organization->fresh([
            'workspaces' => fn($query) => $query->withCount(['teams', 'projects']),
            'members',
            'subscription.plan',
            'subscription.pendingPlan',
            'usage',
            'invitations' => fn($query) => $query->latest(),
        ]);

        Flux::toast(variant: 'success', text: 'Invitation cancelled.');
    }

    public function updateRole(UpdateOrganizationMemberRoleAction $updateRoleAction, int $memberId, string $role): void
    {
        Gate::authorize('changeMemberRole', $this->organization);

        try {
            $member = User::findOrFail($memberId);
            $updateRoleAction->handle(
                organization: $this->organization,
                member: $member,
                role: OrganizationRole::from($role),
            );

            $this->organization->refresh();

            Flux::toast(text: 'Role updated successfully.', variant: 'success');
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');
        }
    }
};
?>

<div class="space-y-6">
    @php
        $currentPlan = $organization->currentPlan();
        $workspaceLimit = $currentPlan?->max_workspaces;
        $projectLimit = $currentPlan?->max_projects;
        $teamLimit = $currentPlan?->max_teams;
        $taskLimit = $currentPlan?->max_tasks;
        $memberLimit = $currentPlan?->max_members;
        $storageLimit = $currentPlan?->max_storage_mb;
        $canInvite = auth()->user()->can('inviteMembers', $organization);
        $canChangeRole = auth()->user()->can('changeMemberRole', $organization);
    @endphp

    {{-- Header --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $organization->name }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Manage workspaces, members, invitations, and project flow.') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->can('update', $organization))
                    <flux:button size="sm" wire:click="openEditOrganizationModal">Edit</flux:button>
                @endif

                @if (auth()->user()->can('delete', $organization))
                    <flux:button size="sm" variant="danger" wire:click="openDeleteOrganizationModal">Delete</flux:button>
                @endif

                @if ($canInvite)
                    <flux:button size="sm" variant="primary" wire:click="openInviteModal">
                        <flux:icon name="plus" class="size-4" />
                        Invite
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Workspaces</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->workspaceUsage }}</p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Projects</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->projectUsage }}</p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Teams</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->teamUsage }}</p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Tasks</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->taskUsage }}</p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Members</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->memberUsage }}</p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Storage</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->storageUsageMb }} MB / {{ $storageLimit === null ? 'Unlimited' : $storageLimit . ' MB' }}</p>
            </div>
        </div>
    </div>

    {{-- Workspaces --}}
    <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-200/70 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-white/10">
            <div>
                <h2 class="tf-panel-title">Workspaces</h2>
                <p class="tf-panel-subtitle">Organize teams, projects, and delivery areas.</p>
            </div>

            @if (auth()->user()->can('createWorkspace', $organization) && $organization->canCreateWorkspace())
                <flux:button size="sm" variant="primary" wire:click="openCreateWorkspaceModal">
                    <flux:icon name="plus" class="size-4" />
                    New Workspace
                </flux:button>
            @endif
        </div>

        @if ($organization->workspaces->isNotEmpty())
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($organization->workspaces as $workspace)
                    <a href="{{ route('workspaces.show', ['workspace' => $workspace]) }}"
                        wire:key="workspace-{{ $workspace->id }}"
                        class="group flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 transition hover:border-zinc-300 hover:bg-white dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20 dark:hover:bg-white/[0.04]"
                        wire:navigate>
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-950 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">
                                {{ strtoupper(substr($workspace->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $workspace->name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $workspace->projects_count }} projects • {{ $workspace->teams_count }} teams
                                </p>
                            </div>
                        </div>
                        <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300">→</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="mt-4 rounded-xl border border-dashed border-zinc-300 p-6 text-center dark:border-white/10">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">No workspaces yet</p>
                <p class="mt-1 text-sm text-zinc-500">Create your first workspace to get started.</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Members & Invitations Tabs --}}
    <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
        {{-- Tab Headers --}}
        <div class="flex border-b border-zinc-200 dark:border-white/10">
            <button wire:click="$set('activeTab', 'members')"
                class="flex-1 px-4 py-3 text-sm font-medium transition
                    {{ $activeTab === 'members' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
                Members ({{ $this->memberUsage }})
            </button>

            @if ($canInvite)
                <button wire:click="$set('activeTab', 'invitations')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition
                        {{ $activeTab === 'invitations' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
                    Invitations ({{ $organization->invitations->count() }})
                </button>
            @endif
        </div>

        {{-- Members Tab --}}
        @if ($activeTab === 'members')
            <div class="divide-y divide-zinc-100 dark:divide-white/5">
                @foreach ($organization->members as $member)
                    <div wire:key="member-{{ $member->id }}"
                        class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-ui.avatar :name="$member->name" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                            </div>
                        </div>

                        @if ($canChangeRole)
                            <flux:select size="sm" wire:change="updateRole({{ $member->id }}, $event.target.value)">
                                @foreach (App\Domain\Organizations\Enums\OrganizationRole::cases() as $role)
                                    <option value="{{ $role }}" @selected($member->pivot->role === $role)>
                                        {{ ucfirst($role->value) }}
                                    </option>
                                @endforeach
                            </flux:select>
                        @else
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                {{ ucfirst($member->pivot->role->value) }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Invitations Tab --}}
        @if ($activeTab === 'invitations' && $canInvite)
            <div class="divide-y divide-zinc-100 dark:divide-white/5">
                @forelse ($organization->invitations as $invitation)
                    <div wire:key="invitation-{{ $invitation->id }}"
                        class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $invitation->email }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ ucfirst($invitation->role) }} • {{ $invitation->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.status-badge :status="$invitation->computed_status" />
                            @if ($invitation->isPending())
                                <flux:button size="sm" variant="ghost" wire:click="cancelInvitation({{ $invitation->id }})">
                                    Cancel
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <p class="text-sm text-zinc-500">No invitations sent yet.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </x-ui.card>

    {{-- Modals --}}
    <flux:modal wire:model="showCreateWorkspaceModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Create Workspace</flux:heading>
            <flux:input wire:model="workspaceName" label="Name" required />
            <flux:textarea wire:model="workspaceDescription" label="Description" />
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showCreateWorkspaceModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="createWorkspace">Create</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showInviteModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">Invite Member</flux:heading>
            <flux:input wire:model="inviteEmail" label="Email" type="email" required />
            <flux:select wire:model="inviteRole" label="Role">
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </flux:select>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showInviteModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="inviteMember">Send Invitation</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showEditOrganizationModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>Edit Organization</flux:heading>
            <flux:input wire:model="organizationName" label="Organization Name" />
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showEditOrganizationModal', false)">Cancel</flux:button>
                <flux:button wire:click="updateOrganization">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteOrganizationModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>Delete Organization</flux:heading>
            <p class="text-sm text-zinc-600">Delete all workspaces before deleting this organization.</p>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showDeleteOrganizationModal', false)">Cancel</flux:button>
                <flux:button variant="danger" wire:click="deleteOrganization">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

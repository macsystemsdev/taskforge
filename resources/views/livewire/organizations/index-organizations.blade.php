<?php

use App\Data\Organizations\CreateOrganizationData;
use App\Models\Organization;
use App\Services\OrganizationService;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $name = '';
    public string $workspace_name = '';
    public bool $showCreateModal = false;

    #[Computed]
    public function organizations()
    {
        $ownedOrganizations = Organization::query()
            ->where('owner_id', auth()->id())
            ->withCount(['workspaces', 'invitations'])
            ->latest()
            ->get();

        $memberOrganizations = auth()
            ->user()
            ->organizations()
            ->withCount(['workspaces', 'invitations'])
            ->latest('organizations.created_at')
            ->get();

        return $ownedOrganizations
            ->merge($memberOrganizations)
            ->unique('id')
            ->values();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'workspace_name']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['name', 'workspace_name']);
    }

    public function createOrganization(OrganizationService $organizationService): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Organization::where('slug', Str::slug((string) $value))->exists()) {
                        $fail(__('An organization with that name already exists.'));
                    }
                },
            ],
            'workspace_name' => ['required', 'string', 'max:255'],
        ]);

        $organizationData = new CreateOrganizationData(
            name: $this->name,
            owner_id: auth()->id(),
            workspace_name: $this->workspace_name,
        );

        $organization = $organizationService->create($organizationData);

        $this->closeCreateModal();

        Flux::toast(
            variant: 'success',
            text: __('Organization created successfully.'),
        );

        $this->redirectRoute('organizations.show', ['organization' => $organization], navigate: true);
    }
};
?>

<div>
    <div class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ __('Organizations') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Manage the operating boundaries for teams, workspaces, invitations, and project ownership.') }}
                </p>
            </div>

            <flux:button
                variant="primary"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2"
            >
                <flux:icon name="plus" class="size-4" />
                {{ __('New Organization') }}
            </flux:button>
        </div>
    </div>

    @if ($this->organizations->isNotEmpty())
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($this->organizations as $organization)
                <a href="{{ route('organizations.show', $organization) }}"
                    class="rounded-2xl border border-zinc-200 bg-white/90 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md dark:border-white/10 dark:bg-zinc-900/70 dark:hover:border-white/20"
                    wire:navigate>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="truncate text-base font-semibold text-zinc-950 dark:text-white">
                                {{ $organization->name }}
                            </h2>
                            @can('viewActivityLog', $organization)
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ ucfirst($organization->subscription_plan ?? 'standard') }} plan
                                </p>
                            @endcan
                        </div>

                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                            {{ $organization->subscription_status ?? 'active' }}
                        </span>
                    </div>

                    @can('inviteMembers', $organization)
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                                <p class="text-zinc-500 dark:text-zinc-400">Workspaces</p>
                                <p class="mt-1 font-semibold text-zinc-950 dark:text-white">
                                    {{ $organization->workspaces_count }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                                <p class="text-zinc-500 dark:text-zinc-400">Invitations</p>
                                <p class="mt-1 font-semibold text-zinc-950 dark:text-white">
                                    {{ $organization->invitations_count }}
                                </p>
                            </div>
                        </div>
                    @endcan
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/50 p-12 text-center dark:border-white/10 dark:bg-white/[0.03]">
            <p class="text-base font-semibold text-zinc-950 dark:text-white">
                {{ __('No organizations yet') }}
            </p>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Create an organization to group workspaces, projects, members, and invitations.') }}
            </p>
            <flux:button
                variant="primary"
                wire:click="openCreateModal"
                class="mt-6"
            >
                {{ __('Create Organization') }}
            </flux:button>
        </div>
    @endif

    {{-- Create Organization Modal --}}
    <flux:modal wire:model="showCreateModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">
                    {{ __('Create Organization') }}
                </h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('Set up a durable operating space for workspaces, projects, members, and invitations.') }}
                </p>
            </div>

            <form wire:submit="createOrganization" class="space-y-4">
                <flux:input
                    wire:model="name"
                    :label="__('Organization Name')"
                    type="text"
                    placeholder="Acme Inc."
                    required
                    autofocus
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <flux:input
                    wire:model="workspace_name"
                    :label="__('Default Workspace Name')"
                    type="text"
                    placeholder="Default Workspace"
                    required
                />
                @error('workspace_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" type="button" wire:click="closeCreateModal">
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button
                        variant="primary"
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="createOrganization"
                        class="inline-flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="createOrganization">
                            {{ __('Create Organization') }}
                        </span>
                        <span wire:loading.flex wire:target="createOrganization" class="items-center gap-2">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ __('Creating...') }}
                        </span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

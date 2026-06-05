<x-layouts::app :title="__('Organizations')">
    @php
        $ownedOrganizations = \App\Models\Organization::query()
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
        $organizations = $ownedOrganizations->merge($memberOrganizations)->unique('id')->values();
    @endphp

    <x-ui.page>
        <x-ui.page-header :title="__('Organizations')" :description="__('Manage the operating boundaries for teams, workspaces, invitations, and project ownership.')">
            <x-slot:actions>
                <a href="{{ route('organizations.create') }}" class="tf-button-primary" wire:navigate>
                    <flux:icon name="plus" class="mr-2 size-4" />
                    New Organization
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($organizations->isNotEmpty())
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($organizations as $organization)
                    <a href="{{ route('organizations.show', $organization) }}"
                        class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md dark:border-white/10 dark:bg-zinc-900/70 dark:hover:border-white/20"
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

                            <x-ui.status-badge :status="$organization->subscription_status ?? 'active'" />

                        </div>

                        @can('inviteMembers', $organization)
                            <div>
                                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                                        <p class="text-zinc-500 dark:text-zinc-400">Workspaces</p>
                                        <p class="mt-1 font-semibold text-zinc-950 dark:text-white">
                                            {{ $organization->workspaces_count }}</p>
                                    </div>

                                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                                        <p class="text-zinc-500 dark:text-zinc-400">Invitations</p>
                                        <p class="mt-1 font-semibold text-zinc-950 dark:text-white">
                                            {{ $organization->invitations_count }}</p>
                                    </div>
                                </div>
                            </div>
                        @endcan



                    </a>
                @endforeach
            </div>
        @else
            <x-ui.empty-state title="No organizations yet"
                description="Create an organization to group workspaces, projects, members, and invitations.">
                <x-slot:actions>
                    <a href="{{ route('organizations.create') }}" class="tf-button-primary" wire:navigate>
                        Create Organization
                    </a>
                </x-slot:actions>
            </x-ui.empty-state>
        @endif
    </x-ui.page>
</x-layouts::app>

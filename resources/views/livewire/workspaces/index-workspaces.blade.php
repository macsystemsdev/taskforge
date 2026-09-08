<?php

use App\Models\Organization;
use App\Models\Workspace;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public $lockedWorkspaceIds = [];

    #[Computed]
    public function workspaces()
    {
        $user = auth()->user();

        $accessibleOrgs = Organization::where('owner_id', $user->id)->orWhereHas('members', fn($q) => $q->where('users.id', $user->id)->where('organization_user.status', 'active'))->get();

        $orgIds = $accessibleOrgs->pluck('id');

        $lockedWorkspaceIds = collect();
        foreach ($accessibleOrgs as $org) {
            $lockedWorkspaceIds = $lockedWorkspaceIds->merge($org->lockedWorkspaces()->pluck('id'));
        }
        $lockedWorkspaceIds = $lockedWorkspaceIds->unique();
        $this->lockedWorkspaceIds = $lockedWorkspaceIds;

        return Workspace::query()
            ->with(['organization'])
            ->withCount(['teams', 'projects'])
            ->whereHas('organization', fn($q) => $q->whereIn('organizations.id', $orgIds))
            ->latest()
            ->get();
    }
};
?>

<div class="space-y-6">
    <div
        class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ __('Workspaces') }}
        </h1>
        <p class="mt-2 max-w-xl text-sm text-blue-50 sm:text-base">
            {{ __('All workspaces across your organizations.') }}
        </p>
    </div>

    @if ($this->workspaces->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->workspaces as $workspace)
                @php($isLocked = $this->lockedWorkspaceIds->contains($workspace->id))
                <a href="{{ $isLocked ? '#' : route('workspaces.show', $workspace) }}"
                    wire:key="workspace-{{ $workspace->id }}"
                    class="group rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md dark:border-white/10 dark:bg-zinc-900/70 dark:hover:border-blue-500/30 {{ $isLocked ? 'opacity-60 pointer-events-none' : '' }}"
                    {{ $isLocked ? 'aria-disabled="true"' : 'wire:navigate' }}>
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">
                                {{ $workspace->name }}</p>
                            <p class="mt-1 truncate text-xs text-zinc-500">{{ $workspace->organization->name }}</p>
                        </div>
                        @if ($isLocked)
                            <span
                                class="rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                                🔒 Locked
                            </span>
                        @endif
                    </div>
                    <!-- rest of card content remains unchanged -->
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-white/10">
            <p class="text-base font-semibold text-zinc-950 dark:text-white">No workspaces yet</p>
            <p class="mt-2 text-sm text-zinc-500">Create an organization and workspace to get started.</p>
            <flux:button variant="primary" href="{{ route('organizations.index') }}" class="mt-6">
                Go to Organizations
            </flux:button>
        </div>
    @endif
</div>

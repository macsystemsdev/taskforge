<?php

use App\Models\Workspace;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function workspaces()
    {
        $orgIds = auth()->user()->organizations()->pluck('organizations.id');

        return Workspace::query()
            ->with(['organization'])
            ->withCount(['teams', 'projects'])
            ->whereHas('organization', function ($query) use ($orgIds) {
                $query->whereIn('organizations.id', $orgIds)
                    ->orWhere('organizations.owner_id', auth()->id());
            })
            ->latest()
            ->get();
    }
};
?>

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
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
                <a href="{{ route('workspaces.show', $workspace) }}"
                    wire:key="workspace-{{ $workspace->id }}"
                    class="group rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md dark:border-white/10 dark:bg-zinc-900/70 dark:hover:border-blue-500/30"
                    wire:navigate>
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $workspace->name }}</p>
                            <p class="mt-1 truncate text-xs text-zinc-500">{{ $workspace->organization->name }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                            <p class="text-zinc-500">Teams</p>
                            <p class="mt-1 font-semibold">{{ $workspace->teams_count }}</p>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/[0.03]">
                            <p class="text-zinc-500">Projects</p>
                            <p class="mt-1 font-semibold">{{ $workspace->projects_count }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <p class="truncate text-sm text-zinc-500">{{ $workspace->description ?: 'No description' }}</p>
                        <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-blue-500">→</span>
                    </div>
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

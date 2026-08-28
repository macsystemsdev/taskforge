<?php

use App\Models\Team;
use App\Models\Workspace;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public Workspace $workspace;
    public Team $team;

    public function mount(Workspace $workspace, Team $team): void
    {
        $this->workspace = $workspace;
        $this->team = $team->load([
            'members',
            'projects' => fn($query) => $query->withCount('tasks')->latest(),
        ]);
    }

    #[Computed]
    public function projectsCount(): int
    {
        return $this->team->projects->count();
    }

    #[Computed]
    public function membersCount(): int
    {
        return $this->team->members->count();
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <a href="{{ route('workspaces.show', $workspace) }}"
                    class="text-xs font-medium uppercase tracking-[0.15em] text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                    wire:navigate>
                    {{ $workspace->organization->name }} / {{ $workspace->name }}
                </a>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $team->name }}
                </h1>

                @if ($team->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $team->description }}
                    </p>
                @endif
            </div>

            <flux:button size="sm" href="{{ route('teams.edit', $team->slug) }}" wire:navigate>
                <flux:icon name="pencil" class="size-4" />
                Edit Team
            </flux:button>
        </div>

        {{-- Quick Stats --}}
        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Members</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->membersCount }}</p>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Projects</p>
                <p class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $this->projectsCount }}</p>
            </div>
        </div>
    </div>

    {{-- Members --}}
    <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-white/10">
            <div>
                <p class="text-sm font-semibold text-zinc-950 dark:text-white">Members</p>
                <p class="text-xs text-zinc-500">{{ $this->membersCount }} total</p>
            </div>
        </div>

        <div class="divide-y divide-zinc-100 dark:divide-white/5">
            @foreach ($team->members as $member)
                <div wire:key="member-{{ $member->id }}" class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-ui.avatar :name="$member->name" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                        </div>
                    </div>

                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                        {{ ucfirst($member->pivot->role->value) }}
                    </span>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Projects --}}
    <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-white/10">
            <div>
                <p class="text-sm font-semibold text-zinc-950 dark:text-white">Projects</p>
                <p class="text-xs text-zinc-500">{{ $this->projectsCount }} total</p>
            </div>
        </div>

        @if ($team->projects->isNotEmpty())
            <div class="divide-y divide-zinc-100 dark:divide-white/5">
                @foreach ($team->projects as $project)
                    <a href="{{ route('projects.show', $project) }}"
                        wire:key="project-{{ $project->id }}"
                        class="group flex items-center justify-between gap-3 px-4 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                        wire:navigate>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                            <p class="mt-1 text-xs text-zinc-500">{{ $project->tasks_count }} tasks</p>
                        </div>
                        <x-ui.status-badge :status="$project->status" size="sm" />
                        <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600">→</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">No projects assigned to this team</p>
                <p class="mt-1 text-sm text-zinc-500">Projects will appear here when created with this team.</p>
            </div>
        @endif
    </x-ui.card>
</div>

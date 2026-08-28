<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\Workspace;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $query = '';
    public bool $showResults = false;

    public function updatedQuery(): void
    {
        $this->showResults = $this->query !== '';
    }

    public function clearSearch(): void
    {
        $this->query = '';
        $this->showResults = false;
    }

    #[Computed]
    public function results()
    {
        if (strlen($this->query) < 2) {
            return [
                'projects' => collect(),
                'tasks' => collect(),
                'organizations' => collect(),
                'teams' => collect(),
                'workspaces' => collect(),
            ];
        }

        $query = $this->query;
        $userId = auth()->id();

        $projects = Project::query()
            ->with(['workspace'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->whereHas('team.members', fn($q) => $q->where('users.id', $userId))
            ->latest()
            ->limit(5)
            ->get();

        $tasks = Task::query()
            ->with(['project.workspace'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->where(function ($q) use ($userId) {
                $q->where('assignee_id', $userId)
                    ->orWhere('creator_id', $userId)
                    ->orWhereHas('project.team.members', fn($mq) => $mq->where('users.id', $userId));
            })
            ->latest()
            ->limit(5)
            ->get();

        $organizations = Organization::query()
            ->where(function ($q) use ($userId) {
                $q->where('owner_id', $userId)
                    ->orWhereHas('members', fn($mq) => $mq->where('users.id', $userId));
            })
            ->where('name', 'like', "%{$query}%")
            ->latest()
            ->limit(3)
            ->get();

        $teams = Team::query()
            ->with(['workspace'])
            ->where('name', 'like', "%{$query}%")
            ->whereHas('members', fn($q) => $q->where('users.id', $userId))
            ->latest()
            ->limit(3)
            ->get();

        $workspaces = Workspace::query()
            ->with(['organization'])
            ->where('name', 'like', "%{$query}%")
            ->whereHas('organization', function ($q) use ($userId) {
                $q->where('owner_id', $userId)
                    ->orWhereHas('members', fn($mq) => $mq->where('users.id', $userId));
            })
            ->latest()
            ->limit(3)
            ->get();

        return [
            'projects' => $projects,
            'tasks' => $tasks,
            'organizations' => $organizations,
            'teams' => $teams,
            'workspaces' => $workspaces,
        ];
    }
};
?>

<div class="relative" x-data="{ open: @entangle('showResults') }" @click.outside="open = false">
    <div class="relative hidden sm:block">
        <flux:input
            wire:model.live.debounce.300ms="query"
            placeholder="Search..."
            icon="magnifying-glass"
            class="w-56 lg:w-64"
            @focus="open = true"
        />

        @if ($query)
            <button
                type="button"
                wire:click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
        @endif
    </div>

    {{-- Results Dropdown --}}
    @if ($showResults && strlen($query) >= 2)
        <div class="absolute left-0 right-0 top-full z-50 mt-2 max-h-96 w-80 overflow-y-auto rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-white/10 dark:bg-zinc-900">
            {{-- Projects --}}
            @if ($this->results['projects']->isNotEmpty())
                <div class="border-b border-zinc-100 dark:border-white/5">
                    <p class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">Projects</p>
                    @foreach ($this->results['projects'] as $project)
                        <a href="{{ route('projects.show', $project) }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/[0.03]">
                            <flux:icon.folder class="size-4 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $project->workspace->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Tasks --}}
            @if ($this->results['tasks']->isNotEmpty())
                <div class="border-b border-zinc-100 dark:border-white/5">
                    <p class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">Tasks</p>
                    @foreach ($this->results['tasks'] as $task)
                        <a href="{{ route('tasks.show', $task) }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/[0.03]">
                            <flux:icon.check-circle class="size-4 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $task->project->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Teams --}}
            @if ($this->results['teams']->isNotEmpty())
                <div class="border-b border-zinc-100 dark:border-white/5">
                    <p class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">Teams</p>
                    @foreach ($this->results['teams'] as $team)
                        <a href="{{ route('teams.show', ['workspace' => $team->workspace, 'team' => $team]) }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/[0.03]">
                            <flux:icon.users class="size-4 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $team->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $team->workspace->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Workspaces --}}
            @if ($this->results['workspaces']->isNotEmpty())
                <div class="border-b border-zinc-100 dark:border-white/5">
                    <p class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">Workspaces</p>
                    @foreach ($this->results['workspaces'] as $workspace)
                        <a href="{{ route('workspaces.show', $workspace) }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/[0.03]">
                            <flux:icon.squares-2x2 class="size-4 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $workspace->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $workspace->organization->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Organizations --}}
            @if ($this->results['organizations']->isNotEmpty())
                <div>
                    <p class="px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">Organizations</p>
                    @foreach ($this->results['organizations'] as $organization)
                        <a href="{{ route('organizations.show', $organization) }}" wire:navigate
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-zinc-50 dark:hover:bg-white/[0.03]">
                            <flux:icon.building-office-2 class="size-4 text-zinc-400" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $organization->name }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- No results --}}
            @if ($this->results['projects']->isEmpty() && $this->results['tasks']->isEmpty() && $this->results['organizations']->isEmpty() && $this->results['teams']->isEmpty() && $this->results['workspaces']->isEmpty())
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-zinc-500">No results found for "{{ $query }}"</p>
                </div>
            @endif
        </div>
    @endif
</div>

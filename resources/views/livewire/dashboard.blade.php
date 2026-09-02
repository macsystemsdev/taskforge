<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function organizations()
    {
        $ownedOrganizations = Organization::query()
            ->where('owner_id', auth()->id())
            ->withCount(['workspaces', 'projects', 'members'])
            ->latest()
            ->get();

        $memberOrganizations = auth()
            ->user()
            ->organizations()
            ->withCount(['workspaces', 'projects', 'members'])
            ->latest('organizations.created_at')
            ->get();

        return $ownedOrganizations
            ->merge($memberOrganizations)
            ->unique('id')
            ->values();
    }

    #[Computed]
    public function totalWorkspaces(): int
    {
        return $this->organizations->sum('workspaces_count');
    }

    #[Computed]
    public function totalProjects(): int
    {
        return $this->organizations->sum('projects_count');
    }

    #[Computed]
    public function totalMembers(): int
    {
        return $this->organizations->sum('members_count');
    }

    #[Computed]
    public function assignedTasks()
    {
        return auth()
            ->user()
            ->assignedTasks()
            ->with(['project.workspace', 'project.team'])
            ->where('status', '!=', 'completed')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function dueSoonTasks()
    {
        $orgIds = $this->organizations->pluck('id');

        return Task::query()
            ->with(['project.workspace'])
            ->whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->where('status', '!=', 'completed')
            ->latest('due_date')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentProjects()
    {
        $orgIds = $this->organizations->pluck('id');

        return Project::query()
            ->with(['workspace', 'team'])
            ->withCount('tasks')
            ->whereHas('workspace', fn($q) => $q->whereIn('organization_id', $orgIds))
            ->latest()
            ->limit(5)
            ->get();
    }
};
?>

<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-6 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-8 backdrop-blur">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    {{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-blue-50 sm:text-base">
                    {{ __('Here\'s what\'s happening across your organizations.') }}
                </p>
            </div>

            <div class="flex gap-2">
                <flux:button href="{{ route('organizations.create') }}" class="!bg-white !text-blue-700 hover:!bg-blue-50">
                    <flux:icon name="plus" class="size-4" />
                    {{ __('New Organization') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.card class="space-y-2 border-t-2 border-t-blue-500">
            <p class="text-sm text-zinc-500">Organizations</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->organizations->count() }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2 border-t-2 border-t-indigo-500">
            <p class="text-sm text-zinc-500">Workspaces</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->totalWorkspaces }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2 border-t-2 border-t-emerald-500">
            <p class="text-sm text-zinc-500">Projects</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->totalProjects }}</p>
        </x-ui.card>

        <x-ui.card class="space-y-2 border-t-2 border-t-amber-500">
            <p class="text-sm text-zinc-500">Team Members</p>
            <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->totalMembers }}</p>
        </x-ui.card>
    </div>

    {{-- Main Grid --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        {{-- Left column --}}
        <div class="space-y-6">
            {{-- Recent Projects --}}
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Recent Projects</h2>
                        <p class="text-xs text-zinc-500">Latest activity across your organizations</p>
                    </div>
                    <a href="{{ route('projects.index') }}" wire:navigate class="text-xs font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        View all
                    </a>
                </div>

                @if ($this->recentProjects->isNotEmpty())
                    <div class="divide-y divide-zinc-100 dark:divide-white/5">
                        @foreach ($this->recentProjects as $project)
                            <a href="{{ route('projects.show', $project) }}"
                                wire:key="recent-project-{{ $project->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                                wire:navigate>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $project->workspace->name }} • {{ $project->tasks_count }} tasks</p>
                                </div>
                                <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600">→</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-zinc-500">No projects yet.</p>
                    </div>
                @endif
            </x-ui.card>

            {{-- Your Tasks --}}
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Your Tasks</h2>
                        <p class="text-xs text-zinc-500">Open tasks assigned to you</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        View all
                    </a>
                </div>

                @if ($this->assignedTasks->isNotEmpty())
                    <div class="divide-y divide-zinc-100 dark:divide-white/5">
                        @foreach ($this->assignedTasks as $task)
                            <a href="{{ route('tasks.show', $task) }}"
                                wire:key="task-{{ $task->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                                wire:navigate>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $task->project->name }}</p>
                                </div>
                                <x-ui.status-badge :status="$task->status->value" size="sm" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-zinc-500">No open tasks. You're all caught up!</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- Right column --}}
        <aside class="space-y-6 lg:sticky lg:top-20 h-fit">
            {{-- Due Soon --}}
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Due Soon</h2>
                    <p class="text-xs text-zinc-500">Next 7 days</p>
                </div>

                @if ($this->dueSoonTasks->isNotEmpty())
                    <div class="divide-y divide-zinc-100 dark:divide-white/5">
                        @foreach ($this->dueSoonTasks as $task)
                            <a href="{{ route('tasks.show', $task) }}"
                                wire:key="due-task-{{ $task->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                                wire:navigate>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $task->project->workspace->name }}</p>
                                </div>
                                <span class="text-xs font-medium text-amber-600">{{ $task->due_date->format('M d') }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-zinc-500">Nothing due soon.</p>
                    </div>
                @endif
            </x-ui.card>

            {{-- Organizations --}}
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Your Organizations</h2>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-white/5">
                    @foreach ($this->organizations as $org)
                        <a href="{{ route('organizations.show', $org) }}"
                            wire:key="org-{{ $org->id }}"
                            class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                            wire:navigate>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $org->name }}</p>
                                <p class="mt-0.5 text-xs text-zinc-500">{{ $org->workspaces_count }} workspaces • {{ $org->projects_count }} projects</p>
                            </div>
                            <span class="text-zinc-400 transition group-hover:translate-x-1 group-hover:text-zinc-600">→</span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </aside>
    </div>
</div>

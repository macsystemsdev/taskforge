<?php

use App\Models\Task;
use App\Models\Project;
use App\Domain\Task\TaskStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';
    #[Url]
    public string $statusFilter = 'all';
    #[Url]
    public ?int $projectId = null;
    public $lockedProjectIds = [];

    #[Computed]
    public function tasks()
    {
        $user = auth()->user();

        $activeOrgs = $user->activeOrganizations()->get();
        $activeOrgIds = $activeOrgs->pluck('id');

        // Gather locked project IDs from all active organizations
        $lockedProjectIds = collect();
        foreach ($activeOrgs as $org) {
            $lockedWorkspaceIds = $org->lockedWorkspaces()->pluck('id');
            $lockedTeamIds = $org->lockedTeams()->pluck('id');
            $lockedProjectIdsFromCount = $org->lockedProjects()->pluck('id');

            $lockedFromWorkspace = Project::whereIn('workspace_id', $lockedWorkspaceIds)->pluck('id');
            $lockedFromTeam = Project::whereIn('team_id', $lockedTeamIds)->pluck('id');

            $lockedProjectIds = $lockedProjectIds->merge($lockedProjectIdsFromCount)->merge($lockedFromWorkspace)->merge($lockedFromTeam);
        }
        $lockedProjectIds = $lockedProjectIds->unique();
        $this->lockedProjectIds = $lockedProjectIds;

        return Task::query()
            ->with(['project.team', 'assignee'])
            ->when($this->projectId, fn($query) => $query->where('project_id', $this->projectId))
            ->when($activeOrgIds->isEmpty(), fn($query) => $query->whereRaw('1 = 0'))
            ->when($activeOrgIds->isNotEmpty(), fn($query) => $query->whereHas('project.workspace.organization', fn($q) => $q->whereIn('id', $activeOrgIds)))
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('project.workspace.organization', fn($q) => $q->where('owner_id', $user->id))
                    ->orWhereHas(
                        'project.workspace.organization.members',
                        fn($q) => $q
                            ->where('users.id', $user->id)
                            ->whereIn('organization_user.role', ['owner', 'admin'])
                            ->where('organization_user.status', 'active'),
                    )
                    ->orWhereHas('project.team.members', fn($q) => $q->where('users.id', $user->id));
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'due_soon', function ($query) {
                $query->whereNotIn('status', [TaskStatus::DONE->value, TaskStatus::CANCELLED->value])->whereBetween('due_date', [now(), now()->addDays(7)]);
            })
            ->when($this->statusFilter !== 'all' && $this->statusFilter !== 'due_soon', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div
        class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">
                    {{ __('Tasks') }}
                </h1>
                <p class="mt-2 text-sm text-blue-50">
                    {{ __('Monitor task ownership, status, deadlines, and execution progress.') }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search tasks..."
                    icon="magnifying-glass" />
            </div>

            <div class="flex gap-2 overflow-x-auto">
                @foreach ([
        'all' => 'All',
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'blocked' => 'Blocked',
        'due_soon' => 'Due Soon',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
    ] as $value => $label)
                    <button wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-full px-3 py-1.5 text-xs font-medium transition whitespace-nowrap
                            {{ $statusFilter === $value
                                ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950'
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-white/10 dark:text-zinc-400 dark:hover:bg-white/15' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tasks Table --}}
    <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
        @if ($this->tasks->isNotEmpty())
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Assignee</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->tasks as $task)
                            @php($isTaskLocked = $this->lockedProjectIds->contains($task->project_id))
                            <tr class="tf-row-link cursor-pointer {{ $isTaskLocked ? 'opacity-60 pointer-events-none' : '' }}"
                                wire:key="task-{{ $task->id }}"
                                @if (!$isTaskLocked)
                                    onclick="window.location='{{ route('tasks.show', $task) }}'"
                                @endif>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                        @if ($isTaskLocked)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V8H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 7V5.5a3 3 0 10-6 0V8h6z" clip-rule="evenodd"/></svg>
                                                Locked
                                            </span>
                                        @endif
                                    </div>
                                    @if ($task->description)
                                        <p class="mt-1 max-w-xl truncate text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $task->description }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="text-sm text-zinc-600 dark:text-zinc-300">{{ $task->project->name }}</span>
                                </td>
                                <td><x-ui.status-badge :status="$task->status->value" /></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                        <span
                                            class="text-sm text-zinc-600 dark:text-zinc-300">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $task->due_date?->format('M d, Y') ?? '—' }}
                                    </span>
                                    @if ($task->isOverdue())
                                        <span
                                            class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Overdue</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-white/10">
                {{ $this->tasks->links() }}
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">
                    {{ $search ? 'No tasks found matching your search.' : 'No tasks yet.' }}
                </p>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ $search ? 'Try a different search term.' : 'Tasks created inside projects will appear here.' }}
                </p>
            </div>
        @endif
    </x-ui.card>
</div>

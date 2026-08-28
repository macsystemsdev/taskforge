<?php

use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    #[Computed]
    public function tasks()
    {
        return Task::query()
            ->with(['project.team', 'assignee'])
            ->where(function ($query) {
                $query
                    ->where('assignee_id', auth()->id())
                    ->orWhere('creator_id', auth()->id())
                    ->orWhereHas('project.team.members', fn($memberQuery) => $memberQuery->where('users.id', auth()->id()));
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
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
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ __('Tasks') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Monitor task ownership, status, deadlines, and execution progress.') }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tasks..."
                    icon="magnifying-glass"
                />
            </div>

            <div class="flex gap-2 overflow-x-auto">
                @foreach (['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $value => $label)
                    <button
                        wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-full px-3 py-1.5 text-xs font-medium transition whitespace-nowrap
                            {{ $statusFilter === $value
                                ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950'
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-white/10 dark:text-zinc-400 dark:hover:bg-white/15' }}"
                    >
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
                            <tr class="tf-row-link cursor-pointer" wire:key="task-{{ $task->id }}"
                                onclick="window.location='{{ route('tasks.show', $task) }}'">
                                <td>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $task->title }}</p>
                                    @if ($task->description)
                                        <p class="mt-1 max-w-xl truncate text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $task->description }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $task->project->name }}</span>
                                </td>
                                <td><x-ui.status-badge :status="$task->status->value" /></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $task->due_date?->format('M d, Y') ?? '—' }}
                                    </span>
                                    @if ($task->isOverdue())
                                        <span class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">Overdue</span>
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

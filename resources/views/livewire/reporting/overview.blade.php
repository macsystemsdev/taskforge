<?php

use App\Domain\Reporting\ReportingPeriod;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $period = 'last_30_days';

    #[Computed]
    public function organizations()
    {
        // Only organizations where user is OWNER or ADMIN
        return auth()
            ->user()
            ->organizations()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function orgIds()
    {
        return $this->organizations->pluck('id');
    }

    #[Computed]
    public function dateRange()
    {
        return match (ReportingPeriod::from($this->period)) {
            ReportingPeriod::TODAY => [now()->startOfDay(), now()->endOfDay()],
            ReportingPeriod::LAST_7_DAYS => [now()->subDays(7), now()],
            ReportingPeriod::LAST_30_DAYS => [now()->subDays(30), now()],
            ReportingPeriod::THIS_MONTH => [now()->startOfMonth(), now()->endOfMonth()],
            ReportingPeriod::LAST_MONTH => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            ReportingPeriod::THIS_QUARTER => [now()->startOfQuarter(), now()->endOfQuarter()],
            ReportingPeriod::THIS_YEAR => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->subDays(30), now()],
        };
    }

    #[Computed]
    public function stats()
    {
        [$start, $end] = $this->dateRange;
        $orgIds = $this->orgIds;

        if ($orgIds->isEmpty()) {
            return [
                'total_projects' => 0,
                'active_projects' => 0,
                'completed_projects' => 0,
                'total_tasks' => 0,
                'tasks_created' => 0,
                'tasks_completed' => 0,
                'overdue_tasks' => 0,
                'due_soon_tasks' => 0,
            ];
        }

        return [
            'total_projects' => Project::whereHas('workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->count(),
            'active_projects' => Project::whereHas('workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->where('status', 'active')->count(),
            'completed_projects' => Project::whereHas('workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->where('status', 'completed')->count(),
            'total_tasks' => Task::whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->count(),
            'tasks_created' => Task::whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->whereBetween('created_at', [$start, $end])->count(),
            'tasks_completed' => Task::whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->whereBetween('completed_at', [$start, $end])->count(),
            'overdue_tasks' => Task::whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->where('due_date', '<', now())->where('status', '!=', 'completed')->count(),
            'due_soon_tasks' => Task::whereHas('project.workspace', fn($q) => $q->whereIn('organization_id', $orgIds))->whereBetween('due_date', [now(), now()->addDays(7)])->where('status', '!=', 'completed')->count(),
        ];
    }

    #[Computed]
    public function tasksByProject()
    {
        $orgIds = $this->orgIds;

        if ($orgIds->isEmpty()) {
            return collect();
        }

        return Project::query()
            ->with(['workspace'])
            ->withCount('tasks')
            ->whereHas('workspace', fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderByDesc('tasks_count')
            ->limit(10)
            ->get();
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ __('Reports') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Track progress, identify bottlenecks, and make data-driven decisions.') }}
                </p>
            </div>

            <flux:select wire:model.live="period" size="sm" class="w-40">
                @foreach (App\Domain\Reporting\ReportingPeriod::cases() as $period)
                    @unless ($period->isCustom())
                        <flux:select.option value="{{ $period->value }}">{{ $period->label() }}</flux:select.option>
                    @endunless
                @endforeach
            </flux:select>
        </div>
    </div>

    @if ($this->organizations->isNotEmpty())
        {{-- Key Metrics --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Active Projects</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['active_projects'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Completed Projects</p>
                <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['completed_projects'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Tasks Created</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['tasks_created'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Tasks Completed</p>
                <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['tasks_completed'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Overdue Tasks</p>
                <p class="text-3xl font-semibold tracking-tight text-red-600 dark:text-red-400">{{ $this->stats['overdue_tasks'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Due Soon</p>
                <p class="text-3xl font-semibold tracking-tight text-amber-600 dark:text-amber-400">{{ $this->stats['due_soon_tasks'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Total Tasks</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['total_tasks'] }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2">
                <p class="text-sm text-zinc-500">Total Projects</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['total_projects'] }}</p>
            </x-ui.card>
        </div>

        {{-- Top Projects --}}
        @if ($this->tasksByProject->isNotEmpty())
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Projects by Task Volume</h2>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-white/5">
                    @foreach ($this->tasksByProject as $project)
                        <a href="{{ route('projects.show', $project) }}"
                            wire:key="report-project-{{ $project->id }}"
                            class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                            wire:navigate>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $project->workspace->name }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                @php
                                    $maxTasks = $this->tasksByProject->first()->tasks_count;
                                    $barWidth = $maxTasks > 0 ? round(($project->tasks_count / $maxTasks) * 100) : 0;
                                @endphp
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $project->tasks_count }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    @else
        <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/50 p-12 text-center dark:border-white/10 dark:bg-white/[0.03]">
            <p class="text-base font-semibold text-zinc-950 dark:text-white">No reports available</p>
            <p class="mt-2 text-sm text-zinc-500">You need to be an organization owner or admin to view reports.</p>
        </div>
    @endif
</div>

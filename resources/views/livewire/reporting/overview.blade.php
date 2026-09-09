<?php

use App\Domain\Reporting\ReportingPeriod;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Task\TaskStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public \App\Domain\Reporting\ReportingPeriod $period = \App\Domain\Reporting\ReportingPeriod::LAST_30_DAYS;

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
        return match ($this->period) {
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
                'projects_created' => 0,
                'projects_completed' => 0,
                'projects_overdue' => 0,
                'projects_due_soon' => 0,
                'total_tasks' => 0,
                'tasks_created' => 0,
                'tasks_completed' => 0,
                'tasks_completed_overall' => 0,
                'overdue_tasks' => 0,
                'due_soon_tasks' => 0,
            ];
        }

        $projectStats = Project::whereHas('workspace', fn ($q) => $q->whereIn('organization_id', $orgIds))
            ->selectRaw('
                COUNT(*) as total_projects,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as active_projects,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as completed_projects,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as projects_created,
                COALESCE(SUM(CASE WHEN status = ? AND updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as projects_completed,
                COALESCE(SUM(CASE WHEN status = ? AND due_date < ? THEN 1 ELSE 0 END), 0) as projects_overdue,
                COALESCE(SUM(CASE WHEN status = ? AND due_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as projects_due_soon
            ', [
                ProjectStatus::Active->value,
                ProjectStatus::Completed->value,
                $start,
                $end,
                ProjectStatus::Completed->value,
                $start,
                $end,
                ProjectStatus::Active->value,
                now(),
                ProjectStatus::Active->value,
                now(),
                now()->addDays(7),
            ])
            ->first();

        $taskStats = Task::whereHas('project.workspace', fn ($q) => $q->whereIn('organization_id', $orgIds))
            ->selectRaw('
                COUNT(*) as total_tasks,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as tasks_created,
                COALESCE(SUM(CASE WHEN status = ? AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as tasks_completed,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as tasks_completed_overall,
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) AND due_date < ? THEN 1 ELSE 0 END), 0) as overdue_tasks,
                COALESCE(SUM(CASE WHEN status NOT IN (?, ?) AND due_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as due_soon_tasks
            ', [
                $start,
                $end,
                TaskStatus::DONE->value,
                $start,
                $end,
                TaskStatus::DONE->value,
                TaskStatus::DONE->value,
                TaskStatus::CANCELLED->value,
                now(),
                TaskStatus::DONE->value,
                TaskStatus::CANCELLED->value,
                now(),
                now()->addDays(7),
            ])
            ->first();

        return [
            'total_projects' => (int) $projectStats->total_projects,
            'active_projects' => (int) $projectStats->active_projects,
            'completed_projects' => (int) $projectStats->completed_projects,
            'projects_created' => (int) $projectStats->projects_created,
            'projects_completed' => (int) $projectStats->projects_completed,
            'projects_overdue' => (int) $projectStats->projects_overdue,
            'projects_due_soon' => (int) $projectStats->projects_due_soon,
            'total_tasks' => (int) $taskStats->total_tasks,
            'tasks_created' => (int) $taskStats->tasks_created,
            'tasks_completed' => (int) $taskStats->tasks_completed,
            'tasks_completed_overall' => (int) $taskStats->tasks_completed_overall,
            'overdue_tasks' => (int) $taskStats->overdue_tasks,
            'due_soon_tasks' => (int) $taskStats->due_soon_tasks,
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

    #[Computed]
    public function projectCompletionRate(): float
    {
        return $this->percentage(
            $this->stats['completed_projects'],
            $this->stats['total_projects'],
        );
    }

    #[Computed]
    public function taskCompletionRate(): float
    {
        return $this->percentage(
            $this->stats['tasks_completed_overall'],
            $this->stats['total_tasks'],
        );
    }

    protected function percentage(int $part, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($part / $total) * 100, 1);
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">
                    {{ __('Reports') }}
                </h1>
                <p class="mt-2 text-sm text-blue-50">
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
        <div class="space-y-10">
            <!-- Projects Section -->
            <section>
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Projects</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Total Projects</p>
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['total_projects'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Projects Created ({{ $period->label() }})</p>
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['projects_created'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Projects Completed ({{ $period->label() }})</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['projects_completed'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Active Projects</p>
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['active_projects'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Completed Projects</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['completed_projects'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Overdue Projects</p>
                        <p class="text-3xl font-semibold tracking-tight text-red-600 dark:text-red-400">{{ $this->stats['projects_overdue'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Due Soon Projects</p>
                        <p class="text-3xl font-semibold tracking-tight text-amber-600 dark:text-amber-400">{{ $this->stats['projects_due_soon'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Completion Rate</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->projectCompletionRate }}%</p>
                    </x-ui.card>
                </div>
            </section>

            <!-- Tasks Section -->
            <section>
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Tasks</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Total Tasks</p>
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['total_tasks'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Tasks Created ({{ $period->label() }})</p>
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $this->stats['tasks_created'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Tasks Completed ({{ $period->label() }})</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['tasks_completed'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Completed Tasks</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->stats['tasks_completed_overall'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Task Completion Rate</p>
                        <p class="text-3xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $this->taskCompletionRate }}%</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Overdue Tasks</p>
                        <p class="text-3xl font-semibold tracking-tight text-red-600 dark:text-red-400">{{ $this->stats['overdue_tasks'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="space-y-2">
                        <p class="text-sm text-zinc-500">Tasks Due Soon</p>
                        <p class="text-3xl font-semibold tracking-tight text-amber-600 dark:text-amber-400">{{ $this->stats['due_soon_tasks'] }}</p>
                    </x-ui.card>
                </div>
            </section>
        </div>

        {{-- Top Projects --}}
        @if ($this->tasksByProject->isNotEmpty())
            <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Top 10 Projects by Task Volume</h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">The 10 projects with the most tasks across your organizations.</p>
                        </div>
                        <a href="{{ route('projects.index') }}"
                           class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 whitespace-nowrap">
                            View All Projects →
                        </a>
                    </div>
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

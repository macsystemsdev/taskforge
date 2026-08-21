<?php

use App\Models\Project;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function render()
    {
        return view('livewire.projects.project-details');
    }
};

?>

<flux:card class="!p-0 overflow-hidden">
    <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Project Details</h2>
    </div>

    <div class="space-y-4 p-6">
        {{-- Health --}}
        <div
            class="rounded-xl border p-4 
            @if ($project->hasOverdueTasks()) border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/40
            @elseif ($project->hasUpcomingDeadlines())
                border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/40
            @else
                border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/40 @endif">
            <p class="tf-muted text-xs">Health</p>
            <p
                class="mt-1 font-semibold 
                @if ($project->hasOverdueTasks()) text-red-600 dark:text-red-400
                @elseif ($project->hasUpcomingDeadlines())
                    text-amber-600 dark:text-amber-400
                @else
                    text-emerald-600 dark:text-emerald-400 @endif">
                @if ($project->hasOverdueTasks())
                    At Risk
                @elseif ($project->hasUpcomingDeadlines())
                    Attention Needed
                @else
                    Healthy
                @endif
            </p>
        </div>

        {{-- Metrics --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Overdue</dt>
                <dd class="mt-1 text-lg font-semibold text-red-600 dark:text-red-400">{{ $project->overdueTaskCount() }}
                </dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Due Soon</dt>
                <dd class="mt-1 text-lg font-semibold text-amber-600 dark:text-amber-400">
                    {{ $project->dueSoonTaskCount() }}</dd>
            </div>
        </div>

        {{-- Details --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Team</dt>
                <dd class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $project->team->name }}
                </dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created By</dt>
                <dd class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">
                    {{ $project->creator->name }}</dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Status</dt>
                <dd class="mt-1"><x-ui.status-badge :status="$project->status" size="sm" /></dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created</dt>
                <dd class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">
                    {{ $project->created_at->format('M d, Y') }}</dd>
            </div>
            <div
                class="col-span-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Due Date</dt>
                <dd class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">
                    {{ $project->due_date?->format('M d, Y') ?? __('No due date') }}</dd>
            </div>
        </div>
    </div>
</flux:card>

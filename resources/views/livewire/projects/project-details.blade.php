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

<flux:card>
    <flux:heading>
        {{ __('Project Details') }}
    </flux:heading>

    <div class="mt-4 space-y-4">

        {{-- Health as a prominent banner --}}
        <div class="rounded-3xl border border-zinc-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-zinc-950/60">
            <p class="tf-muted text-sm">Health</p>
            @if ($project->hasOverdueTasks())
                <p class="mt-1 font-semibold text-red-600">At Risk</p>
            @elseif ($project->hasUpcomingDeadlines())
                <p class="mt-1 font-semibold text-amber-600">Attention Needed</p>
            @else
                <p class="mt-1 font-semibold text-green-600">Healthy</p>
            @endif
        </div>

        {{-- Metrics in a 2-column grid --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Overdue</dt>
                <dd class="mt-1 font-semibold text-red-600 text-lg">{{ $project->overdueTaskCount() }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Due Soon</dt>
                <dd class="mt-1 font-semibold text-amber-600 text-lg">{{ $project->dueSoonTaskCount() }}</dd>
            </div>
        </div>

        {{-- Details in a 2-column grid --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Team</dt>
                <dd class="mt-1 font-semibold text-zinc-950 dark:text-white text-sm truncate">{{ $project->team->name }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created By</dt>
                <dd class="mt-1 font-semibold text-zinc-950 dark:text-white text-sm truncate">{{ $project->creator->name }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Status</dt>
                <dd class="mt-1"><x-ui.status-badge :status="$project->status" size="sm" /></dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created</dt>
                <dd class="mt-1 font-semibold text-zinc-950 dark:text-white text-sm">{{ $project->created_at->format('M d, Y') }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-zinc-950/80 col-span-2">
                <dt class="tf-muted text-xs">Due Date</dt>
                <dd class="mt-1 font-semibold text-zinc-950 dark:text-white text-sm">{{ $project->due_date?->format('M d, Y') ?? __('No due date') }}</dd>
            </div>
        </div>

    </div>
</flux:card>

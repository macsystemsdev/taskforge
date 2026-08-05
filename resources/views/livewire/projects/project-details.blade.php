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

    <div class="mt-4 grid gap-4">

        <div class="rounded-3xl border border-zinc-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-zinc-950/60">
            <p class="tf-muted">Health</p>
            @if ($project->hasOverdueTasks())
                <p class="mt-2 font-semibold text-red-600">At Risk</p>
            @elseif ($project->hasUpcomingDeadlines())
                <p class="mt-2 font-semibold text-amber-600">Attention Needed</p>
            @else
                <p class="mt-2 font-semibold text-green-600">Healthy</p>
            @endif
        </div>

        <dl class="grid gap-4">
            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Overdue Tasks</dt>
                <dd class="mt-2 font-semibold text-red-600">{{ $project->overdueTaskCount() }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Due Soon</dt>
                <dd class="mt-2 font-semibold text-amber-600">{{ $project->dueSoonTaskCount() }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Project Team</dt>
                <dd class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $project->team->name }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Created By</dt>
                <dd class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $project->creator->name }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Status</dt>
                <dd class="mt-2"><x-ui.status-badge :status="$project->status" /></dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Created</dt>
                <dd class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $project->created_at->format('M d, Y') }}</dd>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted">Due Date</dt>
                <dd class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $project->due_date?->format('M d, Y') ?? __('No due date') }}</dd>
            </div>
        </dl>

    </div>

</flux:card>

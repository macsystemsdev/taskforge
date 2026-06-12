<?php

use App\Models\Task;
use Livewire\Component;
use App\Actions\Tasks\StartTaskAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Actions\Tasks\CancelTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;

new class extends Component {
    use AuthorizesRequests;

    public Task $task;

    public ?int $assigneeId = null;

    public function mount(Task $task): void
    {
        $this->task = $task;

        $this->assigneeId = $task->assignee_id;
    }

    #[Computed]
    public function teamMembers()
    {
        return $this->task->project->team->members->sortBy('name');
    }

    public function startTask(StartTaskAction $action): void
    {
        $this->authorize('start', $this->task);

        $action->handle($this->task);

        $this->task->refresh();
    }

    public function completeTask(CompleteTaskAction $action): void
    {
        $this->authorize('complete', $this->task);

        $action->handle($this->task);

        $this->task->refresh();
    }

    public function cancelTask(CancelTaskAction $action): void
    {
        $this->authorize('cancel', $this->task);

        $action->handle($this->task);

        $this->task->refresh();
    }

    public function reassignTask(ReassignTaskAction $action): void
    {
        $this->authorize('reassign', $this->task);

        $user = User::findOrFail($this->assigneeId);

        $action->handle($this->task, $user);

        $this->task->refresh();
    }

    public function deleteTask(DeleteTaskAction $action): void
    {
        $this->authorize('delete', $this->task);

        $project = $this->task->project;

        $action->handle($this->task);

        $this->redirect(route('projects.show', $project), navigate: true);
    }

    public function getActivityLogsProperty()
    {
        return $this->task->activityLogs->sortByDesc('created_at');
    }

    public function render()
    {
        $this->task->load(['project.team.workspace', 'assignee', 'creator', 'activityLogs.user']);

        return view('livewire.tasks.show-task');
    }
};

?>


<x-ui.page>


    <x-ui.page-header :title="$task->title" :description="$task->description ?: __('No task description has been added yet.')" :eyebrow="$task->project->workspace->name . ' / ' . $task->project->team->name . ' / ' . $task->project->name">
        <x-slot:actions>
            <x-ui.status-badge :status="$task->status" />

        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <x-ui.card>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <p class="tf-muted">Assignee</p>
                        <div class="mt-2 flex items-center gap-2">
                            <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" />
                            <p class="font-medium text-zinc-950 dark:text-white">
                                {{ $task->assignee?->name ?? 'Unassigned' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="tf-muted">Due date</p>
                        <p class="mt-2 font-medium text-zinc-950 dark:text-white">
                            {{ $task->due_date?->format('M d, Y') ?? 'No date' }}
                        </p>

                        @if ($task->due_date && !$task->isOverdue())
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ now()->diffInDays($task->due_date, false) }}
                                days remaining
                            </p>
                        @endif

                        @if ($task->isOverdue())
                            <p class="mt-1 text-sm font-medium text-red-600">
                                Overdue
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="tf-muted">Created by</p>
                        <p class="mt-2 font-medium text-zinc-950 dark:text-white">
                            {{ $task->creator?->name ?? 'Unknown' }}
                        </p>
                    </div>
                    <div>
                        <p class="tf-muted">
                            Status
                        </p>

                        <div class="mt-2">
                            <x-ui.status-badge :status="$task->status" />
                        </div>
                    </div>
                </div>
            </x-ui.card>

            @livewire('comments.comment-section', [
                'commentable' => $task,
            ])
        </div>

        <aside class="space-y-6 md:sticky md:top-20">
            <x-ui.card>
                <h2 class="tf-panel-title">Task Metadata</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Project</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            <a href="{{ route('projects.show', $task->project) }}" class="hover:underline"
                                wire:navigate>
                                {{ $task->project->name }}
                            </a>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">
                            Team
                        </dt>

                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->project->team->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Workspace</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->project->workspace->name }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->updated_at->diffForHumans() }}</dd>
                    </div>

                    @if ($task->completed_at)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">
                                Completed
                            </dt>

                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->completed_at->format('M d, Y') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.card>

                <h2 class="tf-panel-title">
                    Task Actions
                </h2>

                <div class="mt-4 flex flex-col gap-2">

                    @can('start', $task)
                        <button wire:click="startTask" class="tf-button-primary">
                            Start Task
                        </button>
                    @endcan

                    @can('complete', $task)
                        <button wire:click="completeTask" class="tf-button-primary">
                            Complete Task
                        </button>
                    @endcan

                    @can('cancel', $task)
                        <button wire:click="cancelTask" class="tf-button-secondary">
                            Cancel Task
                        </button>
                    @endcan

                    @can('delete', $task)
                        @if ($task->status->isTodo() || $task->status->isCancelled())
                            <button wire:click="deleteTask" wire:confirm="Delete this task?" class="tf-button-danger">
                                Delete Task
                            </button>
                        @endif
                    @endcan

                </div>

                @if ($task->status->isDone())
                    <p class="mt-4 text-sm text-zinc-500">
                        This task has been completed.
                    </p>
                @endif

                @if ($task->status->isCancelled())
                    <p class="mt-4 text-sm text-zinc-500">
                        This task has been cancelled.
                    </p>
                @endif

            </x-ui.card>
            @can('reassign', $task)

                @if (!$task->status->isDone() && !$task->status->isCancelled())
                    <x-ui.card>

                        <h2 class="tf-panel-title">
                            Reassign Task
                        </h2>

                        <div class="mt-4 space-y-3">

                            <select wire:model="assigneeId" class="w-full px-3 py-2.5">

                                @foreach ($this->teamMembers as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->name }}
                                    </option>
                                @endforeach

                            </select>

                            <button wire:click="reassignTask" class="tf-button-primary w-full">
                                Reassign
                            </button>

                        </div>

                    </x-ui.card>
                @endif

            @endcan

            <x-ui.card>
                <h2 class="tf-panel-title">Activity</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($this->activityLogs as $log)
                        <div class="relative border-l border-zinc-200 pl-4 dark:border-white/10">
                            <span
                                class="absolute -left-1.5 top-1.5 size-3 rounded-full border-2 border-white bg-zinc-400 dark:border-zinc-900 dark:bg-zinc-500"></span>
                            <p class="text-sm font-medium text-zinc-950 dark:text-white">
                                {{ str($log->event)->headline() }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $log->user?->name ?? 'System' }} - {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <x-ui.empty-state title="No activity yet"
                            description="Task events will appear here as work changes." class="py-8" />
                    @endforelse
                </div>
            </x-ui.card>


        </aside>
    </div>
</x-ui.page>

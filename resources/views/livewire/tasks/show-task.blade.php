<?php

use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use App\Actions\Tasks\StartTaskAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Actions\Tasks\CancelTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\ReassignTaskAction;
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

    #[Computed]
    public function fileReferences()
    {
        return $this->task
            ->fileReferences()
            ->with(['fileAttachment.storedFile', 'fileAttachment'])
            ->get();
    }

    public function render()
    {
        $this->task->load(['project.team.workspace', 'assignee', 'creator', 'activityLogs.user', 'fileReferences.fileAttachment.storedFile']);

        return view('livewire.tasks.show-task');
    }
};
?>

<x-ui.page>
    <div
        class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <x-ui.page-header :title="$task->title" :description="$task->description ?: __('No task description has been added yet.')" :eyebrow="$task->project->workspace->name . ' / ' . $task->project->team->name . ' / ' . $task->project->name">
            <x-slot:actions>
                <x-ui.status-badge :status="$task->status" />
            </x-slot:actions>
        </x-ui.page-header>

        <div
            class="mt-5 rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-400">
            Keep this task moving with clear ownership, deadlines, and next actions.
        </div>
    </div>

    {{-- MAIN CONTENT - 2 COLUMN LAYOUT --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- LEFT COLUMN --}}
        <div class="space-y-6">
            {{-- Assignee / Due / Created --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
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
                                {{ ceil(now()->diffInDays($task->due_date, false)) }}
                                days remaining
                            </p>
                        @endif

                        @if ($task->isOverdue())
                            <p class="mt-1 text-sm font-medium text-red-600">
                                Overdue by {{ ceil(now()->diffInDays($task->due_date, true)) }} days
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="tf-muted">Created by</p>
                        <p class="mt-2 font-medium text-zinc-950 dark:text-white">
                            {{ $task->creator?->name ?? 'Unknown' }}
                        </p>
                    </div>
                </div>
            </x-ui.card>

            {{-- Task Metadata --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="tf-panel-title">Task Metadata</h2>
                <dl class="mt-5 space-y-3 text-sm">
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
                        <dt class="text-zinc-500 dark:text-zinc-400">Team</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->project->team->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Workspace</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->project->workspace->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Created</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->created_at->format('M d, Y') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">Updated</dt>
                        <dd class="text-right font-medium text-zinc-950 dark:text-white">
                            {{ $task->updated_at->diffForHumans() }}
                        </dd>
                    </div>

                    @if ($task->completed_at)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">Completed</dt>
                            <dd class="text-right font-medium text-zinc-950 dark:text-white">
                                {{ $task->completed_at->format('M d, Y') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            {{-- Activity --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
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
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">
            {{-- Task Actions --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="tf-panel-title">Task Actions</h2>

                @if ($task->status->isDone())
                    <p class="mt-4 text-sm text-zinc-500">This task has been completed.</p>
                @endif

                @if ($task->status->isCancelled())
                    <p class="mt-4 text-sm text-zinc-500">This task has been cancelled.</p>
                @endif

                <div class="mt-4 flex flex-col gap-2">
                    @if (auth()->user()->can('start', $task))
                        <button wire:click="startTask" class="tf-button-primary">Start Task</button>
                    @endif

                    @if (auth()->user()->can('block', $task))
                        <button wire:click="startTask" class="tf-button-primary">Block Task</button>
                    @endif

                    @if (auth()->user()->can('complete', $task))
                        <button wire:click="completeTask" class="tf-button-primary">Complete Task</button>
                    @endif

                    @if (auth()->user()->can('cancel', $task))
                        <button wire:click="cancelTask" class="tf-button-secondary">Cancel Task</button>
                    @endif

                    @if (auth()->user()->can('delete', $task))
                        @if ($task->status->isTodo() || $task->status->isCancelled())
                            <button wire:click="deleteTask" wire:confirm="Delete this task?"
                                class="tf-button-danger">Delete Task</button>
                        @endif
                    @endif
                </div>
            </x-ui.card>

            {{-- Reassign Task --}}
            @if (auth()->user()->can('reassign', $task) && !$task->status->isDone() && !$task->status->isCancelled())
                <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                    <h2 class="tf-panel-title">Reassign Task</h2>
                    <p class="mt-2 text-sm text-zinc-500">
                        Current assignee: {{ $task->assignee?->name ?? 'Unassigned' }}
                    </p>
                    <div class="mt-4 space-y-3">
                        <select wire:model="assigneeId" class="w-full px-3 py-2.5">
                            @foreach ($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="reassignTask" class="tf-button-primary w-full">Reassign</button>
                    </div>
                </x-ui.card>
            @endif

            {{-- Task Resources --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <div>
                    <h2 class="tf-panel-title">Task Resources</h2>
                    <p class="tf-panel-subtitle">Project resources referenced for this task.</p>
                </div>

                <div class="mt-5 space-y-3">
                    @php $references = $this->fileReferences; @endphp

                    @if ($references->isEmpty())
                        <x-ui.empty-state title="No resources"
                            description="No project resources have been referenced for this task." class="py-8" />
                    @else
                        @foreach ($references as $reference)
                            @php
                                // The relationship is fileAttachment, not attachment
                                $attachment = $reference->fileAttachment ?? $reference->attachment;
                                $file = $attachment?->storedFile;
                            @endphp
                            @if ($file)
                                <div
                                    class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-2xl dark:bg-zinc-800">
                                            @php
                                                $icon = match (true) {
                                                    str_starts_with($file->mime_type, 'image/') => '🖼️',
                                                    str_contains($file->mime_type, 'pdf') => '📄',
                                                    str_contains($file->mime_type, 'word') ||
                                                        str_contains($file->mime_type, 'document')
                                                        => '📝',
                                                    str_contains($file->mime_type, 'excel') ||
                                                        str_contains($file->mime_type, 'sheet')
                                                        => '📊',
                                                    str_contains($file->mime_type, 'zip') ||
                                                        str_contains($file->mime_type, 'rar')
                                                        => '📦',
                                                    default => '📎',
                                                };
                                            @endphp
                                            {{ $icon }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate text-sm font-semibold text-zinc-950 dark:text-white">
                                                        {{ $file->original_filename }}
                                                    </p>
                                                    <p class="text-xs text-zinc-500">
                                                        Added to project:
                                                        {{ $attachment?->created_at?->diffForHumans() ?? 'Unknown' }}
                                                    </p>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if ($attachment)
                                                        <a href="{{ route('projects.attachments.download', [$task->project, $attachment]) }}"
                                                            class="tf-button-secondary inline-flex items-center justify-center gap-2 text-sm">
                                                            Download
                                                        </a>

                                                        @if (str_starts_with($file->mime_type, 'image/') || $file->mime_type === 'application/pdf')
                                                            <a href="{{ route('projects.attachments.view', [$task->project, $attachment]) }}"
                                                                target="_blank"
                                                                class="tf-button-secondary inline-flex items-center justify-center gap-2 text-sm">
                                                                Preview
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <p class="mt-2 text-xs text-zinc-500">
                                                {{ number_format($file->size / 1024, 1) }} KB
                                            </p>

                                            @if (str_starts_with($file->mime_type, 'image/'))
                                                <div
                                                    class="mt-3 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-white/10">
                                                    <img src="{{ asset('storage/' . $file->path) }}"
                                                        alt="{{ $file->original_filename }}"
                                                        class="h-32 w-full object-cover" />
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-ui.page>

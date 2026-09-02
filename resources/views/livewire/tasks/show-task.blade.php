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
use Flux\Flux;

new class extends Component {
    use AuthorizesRequests;

    public Task $task;

    public ?int $assigneeId = null;

    public function mount(Task $task): void
    {
        $this->authorize('view', $task);

        $this->task = $task->load([
            'project.workspace.organization',
            'project.team.members',
            'assignee',
            'creator',
            'activityLogs' => fn($query) => $query->with('user')->latest(),
            'fileReferences.fileAttachment.storedFile',
        ]);

        $this->assigneeId = $task->assignee_id;
    }

    #[Computed]
    public function teamMembers()
    {
        return $this->task->project->team->members->sortBy('name');
    }

    #[Computed]
    public function activityLogs()
    {
        return $this->task->activityLogs;
    }

    #[Computed]
    public function fileReferences()
    {
        return $this->task->fileReferences;
    }

    #[\Livewire\Attributes\On('task-updated')]
    public function refreshActivity(): void
    {
        $this->task->refresh();
        $this->task->load([
            'activityLogs' => fn($query) => $query->with('user')->latest(),
        ]);
    }

    public function startTask(StartTaskAction $action): void
    {
        $this->authorize('start', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task started.'));
        $this->dispatch('task-updated');
    }

    public function completeTask(CompleteTaskAction $action): void
    {
        $this->authorize('complete', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task completed.'));
        $this->dispatch('task-updated');
    }

    public function cancelTask(CancelTaskAction $action): void
    {
        $this->authorize('cancel', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task cancelled.'));
        $this->dispatch('task-updated');
    }

    public function reassignTask(ReassignTaskAction $action): void
    {
        $this->authorize('reassign', $this->task);

        $user = User::findOrFail($this->assigneeId);
        $action->handle($this->task, $user);
        $this->task->refresh();

        Flux::toast(variant: 'success', text: __('Task reassigned.'));
        $this->dispatch('task-updated');
    }

    public function deleteTask(DeleteTaskAction $action): void
    {
        $this->authorize('delete', $this->task);

        $project = $this->task->project;
        $action->handle($this->task);

        Flux::toast(variant: 'success', text: __('Task deleted.'));

        $this->redirect(route('projects.show', $project), navigate: true);
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <a href="{{ route('projects.show', $task->project) }}"
                    class="text-xs font-medium uppercase tracking-[0.15em] text-blue-100 hover:text-white transition"
                    wire:navigate>
                    {{ $task->project->workspace->name }} / {{ $task->project->name }}
                </a>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    {{ $task->title }}
                </h1>

                @if ($task->description)
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-blue-50">
                        {{ $task->description }}
                    </p>
                @endif

                @if ($task->isOverdue())
                    <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-red-500/20 px-4 py-1.5 text-sm font-medium text-red-100">
                        <span class="h-2 w-2 rounded-full bg-red-400"></span>
                        {{ __('This task is overdue.') }}
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.status-badge :status="$task->status" />

                @if (auth()->user()->can('start', $task) && $task->status->isTodo())
                    <flux:button size="sm" class="!bg-white !text-blue-700 hover:!bg-blue-50" wire:click="startTask">Start</flux:button>
                @endif

                @if (auth()->user()->can('complete', $task) && $task->status->isInProgress())
                    <flux:button size="sm" class="!bg-white !text-blue-700 hover:!bg-blue-50" wire:click="completeTask">Complete</flux:button>
                @endif

                @if (auth()->user()->can('cancel', $task) && !$task->status->isDone() && !$task->status->isCancelled())
                    <flux:button size="sm" class="!bg-white/20 !text-white hover:!bg-white/30" wire:click="cancelTask">Cancel</flux:button>
                @endif

                @if (auth()->user()->can('delete', $task))
                    <flux:button size="sm" variant="danger" wire:click="deleteTask">Delete</flux:button>
                @endif
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-xs text-blue-100">Assignee</p>
                <div class="mt-1 flex items-center justify-center gap-2">
                    <x-ui.avatar :name="$task->assignee?->name ?? 'Unassigned'" size="sm" :user="$task->assignee" clickable />
                    <p class="truncate text-sm font-semibold text-white">{{ $task->assignee?->name ?? 'Unassigned' }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-xs text-blue-100">Due Date</p>
                <p class="mt-1 text-sm font-semibold text-white">{{ $task->due_date?->format('M d, Y') ?? '—' }}</p>
            </div>

            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-xs text-blue-100">Created By</p>
                <div class="mt-1 flex items-center justify-center gap-2">
                    <x-ui.avatar :name="$task->creator?->name ?? '—'" size="sm" :user="$task->creator" clickable />
                    <p class="truncate text-sm font-semibold text-white">{{ $task->creator?->name ?? '—' }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-xs text-blue-100">Team</p>
                <p class="mt-1 truncate text-sm font-semibold text-white">{{ $task->project->team->name }}</p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        {{-- Left column --}}
        <div class="space-y-6 min-w-0">
            {{-- Reassign --}}
            @if (auth()->user()->can('reassign', $task) && !$task->status->isDone() && !$task->status->isCancelled())
                <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                    <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Reassign Task</h2>
                    <p class="mt-1 text-sm text-zinc-500">Current: {{ $task->assignee?->name ?? 'Unassigned' }}</p>

                    <div class="mt-4 flex gap-2">
                        <select wire:model="assigneeId" class="flex-1 px-3 py-2.5 rounded-lg border border-zinc-200 bg-white text-sm dark:border-white/10 dark:bg-zinc-900 dark:text-white">
                            @foreach ($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <flux:button variant="primary" wire:click="reassignTask">Reassign</flux:button>
                    </div>
                </x-ui.card>
            @endif

            {{-- Activity Timeline --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Activity</h2>

                <div class="mt-5 space-y-5">
                    @forelse ($this->activityLogs as $log)
                        <div class="relative flex gap-3 border-l border-zinc-200 pl-4 dark:border-white/10">
                            <span class="absolute -left-[5px] top-1.5 size-2.5 rounded-full border-2 border-white bg-blue-500 dark:border-zinc-900"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-zinc-950 dark:text-white">
                                    {{ str($log->event)->headline() }}
                                </p>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $log->user?->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-zinc-500">No activity yet.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Right column --}}
        <aside class="space-y-6 lg:sticky lg:top-20 h-fit">
            {{-- Task Resources --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Resources</h2>
                <p class="text-xs text-zinc-500">Files attached to this task</p>

                <div class="mt-4 space-y-3">
                    @forelse ($this->fileReferences as $reference)
                        @php
                            $attachment = $reference->fileAttachment;
                            $file = $attachment?->storedFile;
                        @endphp

                        @if ($file)
                            <a href="{{ route('projects.attachments.download', [$task->project, $attachment]) }}"
                                wire:key="ref-{{ $reference->id }}"
                                class="flex items-center gap-3 rounded-xl border border-zinc-200 p-3 transition hover:border-blue-300 hover:bg-blue-50/50 dark:border-white/10 dark:hover:border-blue-500/30 dark:hover:bg-blue-950/20">
                                <span class="text-xl">
                                    @if (str_starts_with($file->mime_type, 'image/')) 🖼️
                                    @elseif ($file->mime_type === 'application/pdf') 📄
                                    @elseif (str_contains($file->mime_type, 'spreadsheet')) 📊
                                    @else 📎
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-zinc-950 dark:text-white">{{ $file->original_filename }}</span>
                                    <span class="block text-xs text-zinc-500">{{ number_format($file->size / 1024, 1) }} KB</span>
                                </span>
                            </a>
                        @endif
                    @empty
                        <p class="py-6 text-center text-sm text-zinc-500">No resources attached.</p>
                    @endforelse
                </div>
            </x-ui.card>

            {{-- Back to Project --}}
            <a href="{{ route('projects.show', $task->project) }}"
                class="block rounded-xl border border-zinc-200 bg-white p-4 text-center text-sm font-medium text-zinc-700 shadow-sm transition hover:border-blue-300 hover:text-blue-600 dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-blue-500/30 dark:hover:text-blue-400"
                wire:navigate>
                ← Back to Project
            </a>
        </aside>
    </div>
</div>

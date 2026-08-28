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

    public function startTask(StartTaskAction $action): void
    {
        $this->authorize('start', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task started.'));
    }

    public function completeTask(CompleteTaskAction $action): void
    {
        $this->authorize('complete', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task completed.'));
    }

    public function cancelTask(CancelTaskAction $action): void
    {
        $this->authorize('cancel', $this->task);
        $action->handle($this->task);
        $this->task->refresh();
        Flux::toast(variant: 'success', text: __('Task cancelled.'));
    }

    public function reassignTask(ReassignTaskAction $action): void
    {
        $this->authorize('reassign', $this->task);

        $user = User::findOrFail($this->assigneeId);
        $action->handle($this->task, $user);
        $this->task->refresh();

        Flux::toast(variant: 'success', text: __('Task reassigned.'));
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
    <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 dark:border-white/10 dark:bg-zinc-900/70">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <a href="{{ route('projects.show', $task->project) }}"
                    class="text-xs font-medium uppercase tracking-[0.15em] text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300"
                    wire:navigate>
                    {{ $task->project->workspace->name }} / {{ $task->project->name }}
                </a>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    {{ $task->title }}
                </h1>

                @if ($task->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $task->description }}
                    </p>
                @endif

                @if ($task->isOverdue())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                        {{ __('This task is overdue.') }}
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.status-badge :status="$task->status" />

                @if (auth()->user()->can('start', $task) && $task->status->isTodo())
                    <flux:button size="sm" variant="primary" wire:click="startTask">Start</flux:button>
                @endif

                @if (auth()->user()->can('complete', $task) && $task->status->isInProgress())
                    <flux:button size="sm" variant="primary" wire:click="completeTask">Complete</flux:button>
                @endif

                @if (auth()->user()->can('cancel', $task) && !$task->status->isDone() && !$task->status->isCancelled())
                    <flux:button size="sm" wire:click="cancelTask">Cancel</flux:button>
                @endif

                @if (auth()->user()->can('delete', $task))
                    <flux:button size="sm" variant="danger" wire:click="deleteTask">Delete</flux:button>
                @endif
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Assignee</p>
                <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $task->assignee?->name ?? 'Unassigned' }}</p>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Due Date</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">{{ $task->due_date?->format('M d, Y') ?? '—' }}</p>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Created By</p>
                <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $task->creator?->name ?? '—' }}</p>
            </div>

            <div class="rounded-xl bg-zinc-50 p-3 text-center dark:bg-white/[0.03]">
                <p class="text-xs text-zinc-500">Team</p>
                <p class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $task->project->team->name }}</p>
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
                    <h2 class="tf-panel-title">Reassign Task</h2>
                    <p class="mt-1 text-sm text-zinc-500">Current: {{ $task->assignee?->name ?? 'Unassigned' }}</p>

                    <div class="mt-4 flex gap-2">
                        <select wire:model="assigneeId" class="flex-1 px-3 py-2.5">
                            @foreach ($this->teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <flux:button variant="primary" wire:click="reassignTask">Reassign</flux:button>
                    </div>
                </x-ui.card>
            @endif

            {{-- Activity --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="tf-panel-title">Activity</h2>

                <div class="mt-4 space-y-4">
                    @forelse ($this->activityLogs as $log)
                        <div class="relative border-l border-zinc-200 pl-4 dark:border-white/10">
                            <span class="absolute -left-1.5 top-1.5 size-3 rounded-full border-2 border-white bg-zinc-400 dark:border-zinc-900 dark:bg-zinc-500"></span>
                            <p class="text-sm font-medium text-zinc-950 dark:text-white">
                                {{ str($log->event)->headline() }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $log->user?->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-zinc-500">No activity yet.</p>
                    @endforelse
                </div>
            </x-ui.card>

            {{-- Comments --}}
        </div>

        {{-- Right column --}}
        <aside class="space-y-6 lg:sticky lg:top-20 h-fit">
            {{-- Task Resources --}}
            <x-ui.card class="border-zinc-200/80 bg-white/90 shadow-sm">
                <h2 class="tf-panel-title">Resources</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($this->fileReferences as $reference)
                        @php
                            $attachment = $reference->fileAttachment;
                            $file = $attachment?->storedFile;
                        @endphp

                        @if ($file)
                            <a href="{{ route('projects.attachments.download', [$task->project, $attachment]) }}"
                                wire:key="ref-{{ $reference->id }}"
                                class="flex items-center gap-3 rounded-xl border border-zinc-200 p-3 transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-white/10 dark:hover:border-white/20 dark:hover:bg-white/[0.03]">
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
        </aside>
    </div>
</div>

<x-layouts::app>

    <div class="max-w-5xl mx-auto py-8 space-y-6">

        {{-- TASK HEADER --}}
        <div class="rounded-2xl border bg-dark p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>

                    <h1 class="text-3xl font-bold">
                        {{ $task->title }}
                    </h1>

                    <p class="text-zinc-500 mt-3">
                        {{ $task->description }}
                    </p>

                </div>

                <div class="flex flex-col gap-2 items-end">

                    <span class="rounded-full border px-3 py-1 text-sm">
                        {{ str($task->status->value)->headline() }}
                    </span>

                    <span class="rounded-full border px-3 py-1 text-sm">
                        {{ str($task->priority->value)->headline() }}
                    </span>

                </div>

            </div>

            <div class="mt-6 grid grid-cols-2 gap-6 text-sm">

                <div>

                    <p class="text-zinc-500">
                        Assigned To
                    </p>

                    <p class="font-medium mt-1">
                        {{ $task->assignee?->name ?? 'Unassigned' }}
                    </p>

                </div>

                <div>

                    <p class="text-zinc-500">
                        Due Date
                    </p>

                    <p class="font-medium mt-1">
                        {{ $task->due_date?->format('M d, Y') ?? 'N/A' }}
                    </p>

                </div>

            </div>

        </div>

        {{-- COMMENTS --}}
        @livewire('comments.comment-section', [
            'commentable' => $task,
        ])

    </div>

</x-layouts::app>

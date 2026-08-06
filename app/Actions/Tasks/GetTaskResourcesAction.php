<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class GetTaskResourcesAction
{
    /**
     * Retrieve all resources referenced by a task.
     *
     * Future:
     * - Filtering.
     * - Sorting.
     * - Search.
     * - Visibility enforcement.
     * 
     * TODO:
     * Queue notification for assignee.
     *TODO:
     *Queue offline synchronization.
     */
    public function handle(
        Task $task,
    ): Collection {

        return $task

            ->fileReferences()

            ->with([

                'fileAttachment',

                'fileAttachment.storedFile',

                'creator',

            ])

            ->latest()

            ->get();
    }
}

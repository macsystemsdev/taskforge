<?php

namespace App\Actions\Tasks;


use App\Models\FileAttachment;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class DetachTaskResourceAction
{
    /**
     * Remove a resource reference from a task.
     *
     * The underlying project resource is never deleted.
     *
     * Future:
     * - Activity logging.
     * - Offline sync event.
     */
    public function handle(
        Task $task,
        FileAttachment $attachment,
    ): void {

        DB::transaction(function () use (
            $task,
            $attachment,
        ) {

            $task

                ->fileReferences()

                ->where(
                    'file_attachment_id',
                    $attachment->id,
                )

                ->delete();

        });
    }
}

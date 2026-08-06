<?php

namespace App\Actions\Tasks;

use App\Models\FileAttachment;
use App\Models\Task;
use App\Models\TaskFileReference;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttachTaskResourceAction
{
    /**
     * Attach an existing project resource to a task.
     *
     * Resources are never duplicated.
     *
     * Future:
     * - Notify assignee.
     * - Sync offline devices.
     * - Activity feed event.
     */
    public function handle(
        Task $task,
        int|string $attachmentId,
        int|string $userId,
    ): TaskFileReference {

    $attachment = FileAttachment::query()
        ->findOrFail($attachmentId);

        $user = User::query()
            ->findOrFail($userId);

        return DB::transaction(function () use (
            $task,
            $attachment,
            $user,
        ) {

            return TaskFileReference::firstOrCreate(

                [

                    'task_id' => $task->id,

                    'file_attachment_id' => $attachment->id,

                ],

                [

                    'created_by' => $user->id,

                ],

            );
        });
    }
}

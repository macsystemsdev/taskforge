<?php

namespace App\Actions\Tasks;

use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskFileReference;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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

        // CRITICAL: Verify the attachment belongs to the same project as the task
        if ($attachment->attachable_type !== Project::class
            || $attachment->attachable_id !== $task->project_id) {
            abort(404, 'Attachment not found for this project.');
        }

        // CRITICAL: Verify the user is authorized to view the project
        Gate::authorize('view', $task->project);

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

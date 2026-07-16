<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Task;
use DomainException;

class DeleteTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task
    ): void {

        if (
            ! $task->status->isTodo()
            || ! $task->status->isCancelled()
        ) {
            throw new DomainException(
                'Only todo or cancelled tasks can be deleted.'
            );
        }

        $task->notifyAssignee(
            new \App\Notifications\Tasks\TaskDeletedNotification(
                $task
            )
        );
        $task->delete();


        $this->activity->handle(
            subject: $task,
            event: 'task_deleted',
            properties: [
                'task_id' => $task->id,
                'task_title' => $task->title,
            ]
        );
    }
}

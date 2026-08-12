<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
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
            $task->status === TaskStatus::TODO->value
            ||  $task->status === TaskStatus::CANCELLED->value
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
        
        $this->activity->handle(
            subject: $task,
            event: 'task_deleted',
            properties: [
                'task_id' => $task->id,
                'task_title' => $task->title,
            ]
        );

        $task->delete();
    }
}

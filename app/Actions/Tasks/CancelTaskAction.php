<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use DomainException;

class CancelTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task
    ): Task {

        if (
            ! $task->status->canTransitionTo(
                TaskStatus::CANCELLED
            )
        ) {
            throw new DomainException(
                'Task cannot be cancelled.'
            );
        }

        $task->update([
            'status' => TaskStatus::CANCELLED,
            'completed_at' => null,
        ]);

        $this->activity->handle(
            event: 'task_cancelled',
            properties: [
                'task_title' => $task->title,
            ],
            subject: $task,
        );

        return $task;
    }
}

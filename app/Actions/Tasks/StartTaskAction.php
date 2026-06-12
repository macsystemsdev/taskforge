<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use DomainException;

class StartTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task
    ): Task {

        if (
            ! $task->status->canTransitionTo(
                TaskStatus::IN_PROGRESS
            )
        ) {
            throw new DomainException(
                'Task cannot be started.'
            );
        }

        $task->update([
            'status' => TaskStatus::IN_PROGRESS,
        ]);

        $this->activity->handle(
            event: 'task_started',
            properties: [
                'task_title' => $task->title,
            ],
            subject: $task,
        );

        return $task;
    }
}

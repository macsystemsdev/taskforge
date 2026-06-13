<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use App\Notifications\TaskCompletedNotification;
use DomainException;

class CompleteTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task
    ): Task {

        if (
            ! $task->status->canTransitionTo(
                TaskStatus::DONE
            )
        ) {
            throw new DomainException(
                'Task cannot be completed.'
            );
        }

        $task->update([
            'status' => TaskStatus::DONE,
            'completed_at' => now(),
        ]);

        $task->creator?->notify(
            new TaskCompletedNotification($task)
        );

        $this->activity->handle(
            event: 'task_completed',
            properties: [
                'task_title' => $task->title,
            ],
            subject: $task,
        );

        return $task;
    }
}

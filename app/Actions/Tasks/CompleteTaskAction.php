<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use App\Notifications\Tasks\TaskCompletedNotification;
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

        $task->notifyLeadership(
            new TaskCompletedNotification(
                $task
            ),
            auth()->user()
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

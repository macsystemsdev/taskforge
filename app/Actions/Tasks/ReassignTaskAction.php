<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use DomainException;
use App\Actions\ActivityLogs\CreateActivityLogAction;

class ReassignTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task,
        User $assignee
    ): Task {

        if (
            $task->status->isDone()
            || $task->status->isCancelled()
        ) {
            throw new DomainException(
                'This task can no longer be reassigned.'
            );
        }

        $belongsToTeam = $task
            ->project
            ->team
            ->members()
            ->whereKey($assignee)
            ->exists();

        if (! $belongsToTeam) {
            throw new DomainException(
                'User does not belong to the project team.'
            );
        }

        $previousAssignee = $task->assignee;

        $task->update([
            'assignee_id' => $assignee->id,
        ]);

        $assignee->notify(
            new \App\Notifications\TaskReassignedNotification($task)
        );
        
        $this->activity->handle(
            event: 'task_reassigned',
            properties: [
                'from' => $previousAssignee?->name,
                'to' => $assignee->name,
            ],
            subject: $task,
        );

        return $task->fresh();
    }
}

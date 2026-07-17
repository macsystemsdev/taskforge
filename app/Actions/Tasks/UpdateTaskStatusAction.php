<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;


use App\Models\Task;
use App\Notifications\Tasks\TaskUpdatedNotification;

class UpdateTaskStatusAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Task $task,
        TaskStatus $status,
    ): Task {

        $currentStatus = $task->status;

        $task->update([
            'status' => $status,
        ]);


        $this->activity->handle(
            subject: $task,
            event: 'task_status_updated',
            properties: [
                'from' => $currentStatus->value,
                'to' => $status->value,
            ],
        );

        return $task;
    }
}

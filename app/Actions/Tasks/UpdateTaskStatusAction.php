<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;

class UpdateTaskStatusAction
{
    public function handle(
        Task $task,
        TaskStatus $status,
    ): Task {

        $task->update([
            'status' => $status,
        ]);

        return $task;
    }
}
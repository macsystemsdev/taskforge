<?php

namespace App\Actions\Tasks;

use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;

class CreateTaskAction
{
    /**
     * Create a new class instance.
     */
    public function handle(
        CreateTaskData $data,
        Project $project,
    ) {

        $task = $project->tasks()->create([
            'created_by' => Auth::id(),

            'assigned_to' => $data->assigned_to,

            'title' => $data->title,

            'description' => $data->description,

            'priority' => $data->priority,

            'status' => TaskStatus::TODO,

            'due_date' => $data->due_date,
        ]);

        if ($task->assignee) {

            $task->assignee->notify(
                new TaskAssignedNotification($task)
            );
        }

        return $task;
    }
}

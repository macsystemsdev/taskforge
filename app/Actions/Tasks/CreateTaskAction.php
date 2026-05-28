<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;
use Illuminate\Support\Str;

class CreateTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
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

            'slug' => Str::slug($data->title) . '-' . Str::random(5),

            'status' => TaskStatus::TODO,

            'due_date' => $data->due_date,
        ]);

        $this->activity->handle(
            event: 'task_created',

            subject: $task,

            properties: [
                'title' => $task->title,
                'assigned_to' => $task->assigned_to,
            ]
        );

        if ($task->assignee) {

            $task->assignee->notify(
                new TaskAssignedNotification($task)
            );
        }

        return $task;
    }
}

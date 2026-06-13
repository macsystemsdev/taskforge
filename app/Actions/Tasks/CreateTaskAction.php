<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Tasks\CreateTaskData;
use App\Domain\Task\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class CreateTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Project $project,
        CreateTaskData $data
    ): Task {

        if (
            $data->assigneeId &&
            ! $project->team
                ->members()
                ->where(
                    'users.id',
                    $data->assigneeId
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'assigneeId' => __(
                    'Selected user does not belong to the project team.'
                ),
            ]);
        }

        $slug = Str::slug($data->title);

        $task = $project->tasks()->create([
            'creator_id' => auth()->id(),
            'assignee_id' => $data->assigneeId,
            'title' => $data->title,
            'slug' => $slug,
            'description' => $data->description,
            'status' => TaskStatus::TODO,
            'due_date' => $data->dueDate,
        ]);

        if ($task->assignee) {
            $task->assignee->notify(
                new TaskAssignedNotification($task)
            );
        }

        $this->activity->handle(
            event: 'task_created',
            properties: [
                'task_title' => $task->title,
            ],
            subject: $task,
        );

        return $task;
    }
}

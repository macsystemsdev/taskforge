<?php

namespace App\Actions\Tasks;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Tasks\CreateTaskData;
use App\Domain\Task\TaskStatus;
use App\Domain\Usage\Actions\IncreaseTasksAction;
use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\Tasks\TaskAssignedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class CreateTaskAction
{
    public function __construct(
        protected CreateActivityLogAction $activity,
        protected AttachTaskResourceAction $attachResource,
        protected IncreaseTasksAction $increaseTasks
    ) {}

  public function handle(
    Project $project,
    CreateTaskData $data
): Task {
    // 1. Validation before touching the DB
    if (
        $data->assigneeId &&
        ! $project->team
            ->members()
            ->where('users.id', $data->assigneeId)
            ->exists()
    ) {
        throw ValidationException::withMessages([
            'assigneeId' => __('Selected user does not belong to the project team.'),
        ]);
    }

    $slug = Str::slug($data->title);

    // 2. Pure Database Writes inside the Transaction
    $task = DB::transaction(function () use ($project, $data, $slug) {
        $task = $project->tasks()->create([
            'creator_id'  => auth()->id(),
            'assignee_id' => $data->assigneeId,
            'title'       => $data->title,
            'slug'        => $slug,
            'description' => $data->description,
            'priority'    => $data->priority,
            'status'      => TaskStatus::TODO,
            'due_date'    => $data->dueDate,
        ]);

        foreach ($data->resourceIds as $attachmentId) {
            $this->attachResource->handle(
                $task,
                $attachmentId,
                auth()->id()
            );
        }

        $this->increaseTasks->handle($project->workspace->organization);

        return $task;
    });

    // 3. Post-Commit Actions (Notifications & Logs after DB commit)
    $task->notifyAssignee(
        new TaskAssignedNotification($task)
    );

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

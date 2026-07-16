<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Models\Project;
use App\Notifications\Projects\ProjectDeletedNotification;
use Illuminate\Validation\ValidationException;

class DeleteProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Project $project
    ): void {

        if ($project->hasIncompleteTasks()) {
            throw ValidationException::withMessages([
                'project' => __('A project with incomplete tasks cannot be deleted.'),
            ]);
        }

        $project->team?->notifyMembers(
            new ProjectDeletedNotification(
                $project
            ),
            auth()->user()
        );

        $project
            ->workspace
            ->organization
            ->notifyAdministrators(
                new ProjectDeletedNotification(
                    $project
                ),
                auth()->user()
            );
        $project->delete();

        $this->activity->handle(
            event: 'project_deleted',
            properties: [
                'project_name' => $project->name,
            ],
            subject: $project,
        );
    }
}

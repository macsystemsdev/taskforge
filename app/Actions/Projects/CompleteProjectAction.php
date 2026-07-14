<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Models\Project;
use App\Notifications\Projects\ProjectCompletedNotification;
use Illuminate\Validation\ValidationException;

class CompleteProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Project $project
    ): Project {

        if ($project->hasIncompleteTasks()) {
            throw ValidationException::withMessages([
                'project' => __(
                    'All tasks must be completed first.'
                ),
            ]);
        }

        $project->update([
            'status' => ProjectStatus::Completed,
        ]);

        $project
            ->workspace
            ->organization
            ->notifyAdministrators(
                new ProjectCompletedNotification(
                    $project
                ),
                auth()->user()
            );

        $this->activity->handle(
            event: 'project_completed',
            properties: [
                'project_name' => $project->name,
            ],
            subject: $project,
        );

        return $project->refresh();
    }
}

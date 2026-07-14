<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Models\Project;
use App\Notifications\Projects\ProjectCancelledNotification;
use Illuminate\Validation\ValidationException;

class CancelProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Project $project
    ): Project {

        if ($project->status->isCompleted()) {
            throw ValidationException::withMessages([
                'project' => __(
                    'Completed projects cannot be cancelled.'
                ),
            ]);
        }

        $project->update([
            'status' => ProjectStatus::Cancelled,
        ]);

        $project
            ->workspace
            ->organization
            ->notifyAdministrators(
                new ProjectCancelledNotification(
                    $project
                ),
                auth()->user()
            );

        $this->activity->handle(
            event: 'project_cancelled',
            properties: [
                'project_name' => $project->name,
            ],
            subject: $project,
        );

        return $project->refresh();
    }
}

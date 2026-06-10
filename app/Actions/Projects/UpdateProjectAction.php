<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Projects\UpdateProjectData;
use App\Models\Project;
use DomainException;

class UpdateProjectAction
{
     public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Project $project,
        UpdateProjectData $data
    ): Project {

        if (! $project->status->isActive()) {
            throw new DomainException(
                'Only active projects may be updated.'
            );
        }

        $project->update([
            'name' => $data->name,
            'description' => $data->description,
            'due_date' => $data->dueDate,
        ]);

        $this->activity->handle(
            event: 'project_updated',
            properties: [
                'project_name' => $project->name,
            ],
            subject: $project,
        );

        return $project->refresh();
    }
}

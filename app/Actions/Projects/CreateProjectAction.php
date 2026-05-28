<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Data\Projects\CreateProjectData;

class CreateProjectAction
{

    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
    public function handle(
        Workspace $workspace,
        CreateProjectData $data,

    ): Project {
        $project = $workspace->projects()->create([
            'owner_id' => $data->owner_id,
            'name' => $data->name,
            'slug' => Str::slug($data->name),
            'description' => $data->description,
            'status' => 'active',
            'due_date' => $data->due_date,
        ]);

        $this->activity->handle(
            event: 'project_created',
            properties: [
                'project_name' => $project->name,
            ],
            subject: $project,
        );

        return $project;
    }
}

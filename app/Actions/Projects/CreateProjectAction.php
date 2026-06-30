<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\Team;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Exceptions\FeatureLimitExceededException;
use DomainException;

class CreateProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(
        Workspace $workspace,
        CreateProjectData $data,

    ): Project {

        $organization = $workspace->organization;
        
        if (! $organization->canCreateProject()) {
            throw new FeatureLimitExceededException(
                'Your subscription has reached the maximum number of projects.'
            );
        }

        $team = Team::query()->findOrFail($data->teamId);

        if ($team->workspace_id !== $workspace->id) {
            throw new DomainException(
                'Selected team does not belong to workspace.'
            );
        }
        $slug = Str::slug($data->name);

        if (
            $workspace->projects()
            ->where('slug', $slug)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => __('A project with that name already exists.'),
            ]);
        }

        $project = $workspace->projects()->create([
            'team_id' => $team->id,
            'created_by' => auth()->id(),
            'name' => $data->name,
            'slug' => $slug,
            'description' => $data->description,
            'status' => ProjectStatus::Active,
            'due_date' => $data->dueDate,
        ]);

        $this->activity->handle(
            event: 'project_created',
            properties: [
                'project_name' => $project->name,
                'team_name' => $team->name,
            ],
            subject: $project,
        );

        return $project;
    }
}

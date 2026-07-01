<?php

namespace App\Actions\Workspaces;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Workspaces\CreateWorkspaceData;
use App\Exceptions\FeatureLimitExceededException;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Support\Str;

class CreateWorkspaceAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
    public function handle(
        Organization $organization,
        CreateWorkspaceData
        $data,
    ): Workspace {

        
        if (! $organization->canCreateWorkspace()) {
            throw new FeatureLimitExceededException(
                'Your subscription has reached the maximum number of workspaces.'
            );
        }
        $slug = Str::slug($data->name);
        $workspace = $organization->workspaces()->create([
            'name' => $data->name,
            'slug' => $slug,
            'description' => $data->description,
        ]);


        //activity log
        $this->activity->handle(
            event: 'workspace_created',
            properties: [
                'workspace_name' => $workspace->name,
            ],
            subject: $workspace,
        );

        return $workspace;
    }
}

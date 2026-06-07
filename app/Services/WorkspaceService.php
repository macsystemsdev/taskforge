<?php

namespace App\Services;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
    public function createDefaultWorkspace(
        Organization $organization, string $workspace_name
    ): Workspace {

        $workspace = $organization->workspaces()->create([
            'organization_id' => $organization->id,
            'name' => $workspace_name,
            'slug' => Str::slug($workspace_name),
            'description' => 'Default workspace',
        ]);

        // activity log
        $this->activity->handle(
            event: 'workspace_created',
            subject: $workspace,
            properties: [
                'workspace_name' => $workspace->name,
            ]
        );

        return $workspace;
    }
}

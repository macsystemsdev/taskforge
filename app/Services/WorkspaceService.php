<?php

namespace App\Services;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Usage\Actions\IncreaseWorkspacesAction;
use App\Models\Organization;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function __construct(
        protected CreateActivityLogAction $activity,
        protected IncreaseWorkspacesAction $increaseWorkspaces
    ) {}
    public function createDefaultWorkspace(
        Organization $organization, string $workspace_name
    ): Workspace {

    return DB::transaction(function () use ($organization, $workspace_name) {
        $workspace = $organization->workspaces()->create([
            'organization_id' => $organization->id,
            'name' => $workspace_name,
            'slug' => Str::slug($workspace_name),
            'is_default' => true,
            'description' => 'Default workspace',
        ]);

        $this->increaseWorkspaces->handle($organization);
        // activity log
        $this->activity->handle(
            event: 'workspace_created',
            subject: $workspace,
            properties: [
                'workspace_name' => $workspace->name,
            ]
        );

        return $workspace;
    });

    }
}

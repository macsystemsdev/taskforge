<?php
namespace App\Services;

use App\Models\Organization;
use App\Models\Workspace;

class WorkspaceService
{
    public function createDefaultWorkspace(
        Organization $organization
    ): Workspace {

        return $organization->workspaces()->create([
            'organization_id' => $organization->id,
            'name' => 'General Workspace',
            'description' => 'Default workspace',
        ]);
    }
}
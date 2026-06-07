<?php

namespace App\Actions\Workspaces;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Workspaces\CreateWorkspaceData;
use App\Models\Workspace;
use Illuminate\Support\Str;

class UpdateWorkspaceAction
{
    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {
    }
    public function handle(
    Workspace $workspace,
    CreateWorkspaceData $data,
): Workspace {
    $slug = Str::slug($data->name);
    $workspace->update([
        'name' => $data->name,
        'slug' => $slug,
        'description' => $data->description,
    ]);

     //activity log
        $this->activity->handle(
            event: 'workspace_updated',
            properties: [
                'workspace_name' => $workspace->name,
            ],
            subject: $workspace,
        );

    return $workspace;
   }
}

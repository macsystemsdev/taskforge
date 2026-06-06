<?php

namespace App\Actions\Worksapces;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Workspace;
use DomainException;

class DeleteWorkspaceAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {
        //
    }
    public function execute(Workspace $workspace)
    {
        if ($workspace->is_default) {
            throw new DomainException('Default workspace cannot be deleted.');
        }
        $workspace->delete();

        //activity log
        $this->activity->handle(
            event: "{$workspace->name} has been deleted",
            subject: $workspace->id,
            properties: [
                'workspace_name' => $workspace->name,
            ]
        );
    }
}

<?php

namespace App\Actions\Workspaces;

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
    public function handle(Workspace $workspace)
    {
        if ($workspace->is_default) {
            throw new DomainException('Default workspace cannot be deleted.');
        }

        if ($workspace->teams()->exists()) {
            throw new DomainException(
                'Delete all teams before deleting this workspace.'
            );
        }

        if ($workspace->projects()->exists()) {
            throw new DomainException(
                'Delete all projects before deleting this workspace.'
            );
        }

        $workspace->delete();

        //activity log
        $this->activity->handle(
            event: "{$workspace->name} has been deleted",
            subject: $workspace,
            properties: [
                'workspace_name' => $workspace->name,
            ]
        );
    }
}

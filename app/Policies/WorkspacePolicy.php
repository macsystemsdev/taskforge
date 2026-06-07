<?php

namespace App\Policies;

use App\Domain\Workspaces\Support\WorkspacePermissions;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{

    public function update(User $user, Workspace $workspace): bool
    {
        return WorkspacePermissions::canUpdate(
            $workspace->roleFor($user)
        );
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return WorkspacePermissions::canDelete(
            $workspace->roleFor($user)
        );
    }
}

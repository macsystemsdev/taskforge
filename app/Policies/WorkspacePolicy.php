<?php

namespace App\Policies;

use App\Domain\Workspaces\Support\WorkspacePermissions;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{

    public function view(
        User $user,
        Workspace $workspace
    ): bool {

        if (
            $workspace
            ->organization
            ->workspaceLocked(
                $workspace
            )
        ) {
            return false;
        }

        return WorkspacePermissions::canView(
            $workspace
                ->organization
                ->roleFor($user)
        );
    }

    public function update(
        User $user,
        Workspace $workspace
    ): bool {
        if (
            $workspace
            ->organization
            ->workspaceLocked(
                $workspace
            )
        ) {
            return false;
        }
        return WorkspacePermissions::canUpdate(
            $workspace->organization->roleFor($user)
        );
    }

    public function delete(
        User $user,
        Workspace $workspace
    ): bool {

        if (
            $workspace
            ->organization
            ->workspaceLocked(
                $workspace
            )
        ) {
            return false;
        }
        return WorkspacePermissions::canDelete(
            $workspace->organization->roleFor($user)
        );
    }

    public function createTeam(
        User $user,
        Workspace $workspace
    ): bool {
        return

            WorkspacePermissions::canCreateTeam(
                $workspace
                    ->organization
                    ->roleFor($user)
            )

            &&

            $workspace
            ->organization
            ->canCreateTeam();
    }

    public function createProject(
        User $user,
        Workspace $workspace
    ): bool {
        return

            WorkspacePermissions::canCreateProject(
                $workspace
                    ->organization
                    ->roleFor($user)
            )

            &&

            $workspace
            ->organization
            ->canCreateProject();
    }

    public function viewActivityLog(
        User $user,
        Workspace $workspace
    ): bool {
        return WorkspacePermissions::canViewActivityLog(
            $workspace->organization->roleFor($user)
        );
    }
}

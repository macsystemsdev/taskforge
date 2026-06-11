<?php

namespace App\Policies;

use App\Domain\Projects\ProjectPermissions;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function update(
        User $user,
        Project $project
    ): bool {
        return ProjectPermissions::canUpdate(
            $project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }

    public function delete(
        User $user,
        Project $project
    ): bool {
        return ProjectPermissions::canDelete(
            $project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }

    public function complete(
        User $user,
        Project $project
    ): bool {
        return ProjectPermissions::canComplete(
            $project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }

    public function cancel(
        User $user,
        Project $project
    ): bool {
        return ProjectPermissions::canCancel(
            $project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }

    public function createTask(
        User $user,
        Project $project
    ): bool {
        return ProjectPermissions::canCreateTask(
            $project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }
}

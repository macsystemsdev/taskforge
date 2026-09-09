<?php

namespace App\Policies;

use App\Domain\Projects\ProjectPermissions;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{

    public function view(
        User $user,
        Project $project
    ): bool {

        if (
            $project
            ->workspace
            ->organization
            ->projectLocked(
                $project
            )
        ) {
            return false;
        }

        $role = $project
            ->workspace
            ->organization
            ->roleFor($user);

        if (in_array($role, [
            \App\Domain\Organizations\Enums\OrganizationRole::OWNER,
            \App\Domain\Organizations\Enums\OrganizationRole::ADMIN,
        ])) {
            return true;
        }

        return $project->team
            ? $user->belongsToTeam($project->team)
            : false;
    }
    public function update(
        User $user,
        Project $project
    ): bool {

        if (
            $project
            ->workspace
            ->organization
            ->projectLocked(
                $project
            )
        ) {
            return false;
        }
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

        if (
            $project
            ->workspace
            ->organization
            ->projectLocked(
                $project
            )
        ) {
            return false;
        }

        // Only allow deletion when project is active and has no tasks yet
        if (
            $project->status !== \App\Domain\Projects\Enums\ProjectStatus::Active
            || $project->tasks()->count() > 0
        ) {
            return false;
        }

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

        if (
            $project
            ->workspace
            ->organization
            ->projectLocked(
                $project
            )
        ) {
            return false;
        }
        return ProjectPermissions::canComplete(
            $project->team?->roleFor($user)
        );
    }

    public function cancel(
        User $user,
        Project $project
    ): bool {

        if (
            $project
            ->workspace
            ->organization
            ->projectLocked(
                $project
            )
        ) {
            return false;
        }
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
            $project->team?->roleFor($user)
        )

            && $project
            ->workspace
            ->organization
            ->canCreateTask();
    }


    protected function teamRole(
        User $user,
        ?\App\Models\Team $team,
    ) {
        if (! $team) {
            return null;
        }

        return $team->roleFor($user);
    }

}

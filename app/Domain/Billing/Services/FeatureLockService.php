<?php

namespace App\Domain\Billing\Services;

use App\Exceptions\Billing\LockedFeatures\ProjectLockedException;
use App\Exceptions\Billing\LockedFeatures\TaskLockedException;
use App\Exceptions\Billing\LockedFeatures\TeamLockedException;
use App\Exceptions\Billing\LockedFeatures\WorkspaceLockedException;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;

class FeatureLockService
{
    public function ensureWorkspaceAccessible(
        Organization $organization,
        Workspace $workspace,
    ): void {

        if (
            $organization->workspaceLocked(
                $workspace
            )
        ) {
            throw new WorkspaceLockedException();
        }
    }

    public function ensureProjectAccessible(
        Organization $organization,
        Project $project,
    ): void {

        if (
            $organization->projectLocked(
                $project
            )
        ) {
            throw new ProjectLockedException();
        }
    }

    public function ensureTaskAccessible(
        Organization $organization,
        Task $task,
    ): void {

        if (
            $organization->taskLocked(
                $task
            )
        ) {
            throw new TaskLockedException();
        }
    }

    public function ensureTeamAccessible(
        Organization $organization,
        $team,
    ): void {

        if (
            $organization->teamLocked(
                $team
            )
        ) {
            throw new TeamLockedException();
        }
    }
}

<?php

namespace App\Domain\Billing\Services;

use App\Exceptions\Billing\MemberLimitReachedException;
use App\Exceptions\Billing\ProjectLimitReachedException;
use App\Exceptions\Billing\TaskLimitReachedException;
use App\Exceptions\Billing\TeamLimitReachedException;
use App\Exceptions\Billing\WorkspaceLimitReachedException;
use App\Models\Organization;

class FeatureLimitService
{
    public function ensureCanCreateWorkspace(
        Organization $organization,
    ): void {

        if (! $organization->canCreateWorkspace()) {
            throw new WorkspaceLimitReachedException();
        }
    }

    public function ensureCanCreateProject(
        Organization $organization,
    ): void {

        if (! $organization->canCreateProject()) {
            throw new ProjectLimitReachedException();
        }
    }

    public function ensureCanCreateTask(
        Organization $organization,
    ): void {

        if (! $organization->canCreateTask()) {
            throw new TaskLimitReachedException();
        }
    }

    public function ensureCanCreateTeam(
        Organization $organization,
    ): void {

        if (! $organization->canCreateTeam()) {
            throw new TeamLimitReachedException();
        }
    }

    public function ensureCanAddMember(
        Organization $organization,
    ): void {

        if (! $organization->canAddMember()) {
            throw new MemberLimitReachedException();
        }
    }
}

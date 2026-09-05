<?php

namespace Tests\Unit\Policies;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Policies\TeamPolicy;


class TestableUser extends User
{
    public function hasTeamPermission($team, $permission): bool
    {
        return true;
    }
}

it('blocks team updates when the organization has locked the team', function () {
    $policy = new TeamPolicy();
    $user = new TestableUser();
    $team = new Team();

    $organization = new class extends Organization
    {
        public function teamLocked($team): bool
        {
            return true;
        }
    };

    $workspace = new class($organization)
    {
        public function __construct(public readonly Organization $organization)
        {
        }
    };

    $team->setRelation('workspace', $workspace);

    expect($policy->update($user, $team))->toBeFalse();
});

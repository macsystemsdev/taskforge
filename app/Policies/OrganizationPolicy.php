<?php

namespace App\Policies;

use App\Domain\Organizations\Support\OrganizationPermissions;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{

    public function view(
        User $user,
        Organization $organization
    ): bool {
        return $organization->roleFor($user) !== null;
    }

    public function update(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canUpdate(
            $organization->roleFor($user)
        );
    }

    public function delete(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canDelete(
            $organization->roleFor($user)
        );
    }

    public function inviteMembers(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canInviteMembers(
            $organization->roleFor($user)
        );
    }

    public function removeMembers(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canRemoveMembers(
            $organization->roleFor($user)
        );
    }

    public function changeMemberRole(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canChangeMemberRole(
            $organization->roleFor($user)
        );
    }

    public function createTeam(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canCreateTeam(
            $organization->roleFor($user)
        );
    }

    public function createWorkspace(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canCreateWorkspace(
            $organization->roleFor($user)
        );
    }

    public function createProject(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canCreateProject(
            $organization->roleFor($user)
        );
    }

    public function viewActivityLog(
        User $user,
        Organization $organization
    ): bool {
        return OrganizationPermissions::canViewActivityLog(
            $organization->roleFor($user)
        );
    }
}

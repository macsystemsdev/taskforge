<?php

namespace App\Domain\Organizations\Support;

use App\Domain\Organizations\Enums\OrganizationRole;

readonly class OrganizationPermissions
{
    public static function canUpdate(
        ?OrganizationRole $role
    ): bool {
        return $role === OrganizationRole::OWNER;
    }

    public static function canDelete(
        ?OrganizationRole $role
    ): bool {
        return $role === OrganizationRole::OWNER;
    }

    public static function canInviteMembers(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }

    public static function canRemoveMembers(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }

    public static function canChangeMemberRole(
        ?OrganizationRole $role
    ): bool {
        return $role === OrganizationRole::OWNER;
    }

    public static function canCreateTeam(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }

    public static function canCreateWorkspace(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }

    public static function canCreateProject(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }

    public static function canViewActivityLog(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ],
            true
        );
    }
}

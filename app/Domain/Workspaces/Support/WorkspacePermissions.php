<?php

namespace App\Domain\Workspaces\Support;

use App\Domain\Organizations\Enums\OrganizationRole;

readonly class WorkspacePermissions
{
    public static function canView(
        ?OrganizationRole $role
    ): bool {
        return $role !== null;
    }

    public static function canUpdate(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ]
        );
    }

    public static function canDelete(
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
            ]
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
            ]
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
            ]
        );
    }

    
}

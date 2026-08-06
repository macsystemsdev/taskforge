<?php

namespace App\Domain\Projects;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Teams\Enums\TeamRole;

readonly class ProjectPermissions
{
    public static function canView(
        ?OrganizationRole $role
    ): bool {
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
                OrganizationRole::MEMBER,
            ]
        );
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
        return in_array(
            $role,
            [
                OrganizationRole::OWNER,
                OrganizationRole::ADMIN,
            ]
        );
    }

    public static function canComplete(
        ?teamRole $role
    ): bool {
        return $role == TeamRole::LEADER;
    }

    public static function canCancel(
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

    public static function canCreateTask(
        ?TeamRole $role
    ): bool {
        return $role === TeamRole::LEADER;
    }
}

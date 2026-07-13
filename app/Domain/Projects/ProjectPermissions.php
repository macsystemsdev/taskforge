<?php

namespace App\Domain\Projects;

use App\Domain\Organizations\Enums\OrganizationRole;

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

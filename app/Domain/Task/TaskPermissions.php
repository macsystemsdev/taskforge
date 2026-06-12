<?php

namespace App\Domain\Task;

use App\Domain\Organizations\Enums\OrganizationRole;

readonly class TaskPermissions
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
                OrganizationRole::MEMBER,
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

    public static function canReassign(
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

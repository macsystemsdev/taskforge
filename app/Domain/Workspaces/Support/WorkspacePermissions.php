<?php

namespace App\Domain\Workspaces\Support;

use App\Domain\Organizations\Enums\OrganizationRole;

readonly class WorkspacePermissions
{
    public static function canUpdate(
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

    public static function canDelete(
        ?OrganizationRole $role
    ): bool {
        return $role === OrganizationRole::OWNER;
    }
}

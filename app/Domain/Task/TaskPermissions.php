<?php

namespace App\Domain\Task;

use App\Domain\Teams\Enums\TeamRole;

readonly class TaskPermissions
{
    /**
     * Any team member may view a task.
     */
    public static function canView(
        ?TeamRole $role,
    ): bool {

        return $role !== null;
    }

    /**
     * Team members may edit their own task.
     *
     * Leadership overrides ownership through policy.
     */
    public static function canUpdate(
        ?TeamRole $role,
    ): bool {

        return $role !== null;
    }

    /**
     * Leaders manage execution.
     */
    public static function canDelete(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }

    public static function canCancel(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }

    public static function canReassign(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }

    public static function canAttachResource(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }

    public static function canDetachResource(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }

    /**
     * Reserved for future private task resources.
     */
    public static function canViewPrivateResource(
        ?TeamRole $role,
    ): bool {

        return $role === TeamRole::LEADER;
    }
}
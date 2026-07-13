<?php

namespace App\Policies;

use App\Domain\Task\TaskPermissions;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{

    public function view(
        User $user,
        Task $task
    ): bool {
        if (
            $task
            ->project
            ->workspace
            ->organization
            ->taskLocked(
                $task
            )
        ) {
            return false;
        }
        return $task->project
            ->workspace
            ->organization
            ->roleFor($user) !== null;
    }

    public function start(
        User $user,
        Task $task
    ): bool {

        if (
            $task
            ->project
            ->workspace
            ->organization
            ->taskLocked(
                $task
            )
        ) {
            return false;
        }
        return $task->assignee_id === $user->id
            && $task->status === TaskStatus::TODO;
    }

    public function complete(
        User $user,
        Task $task
    ): bool {
        if (
            $task
            ->project
            ->workspace
            ->organization
            ->taskLocked(
                $task
            )
        ) {
            return false;
        }
        return $task->assignee_id === $user->id
            && $task->status === TaskStatus::IN_PROGRESS;
    }

    public function cancel(
        User $user,
        Task $task
    ): bool {

        if (
            $task
            ->project
            ->workspace
            ->organization
            ->taskLocked(
                $task
            )
        ) {
            return false;
        }
        $role = $task
            ->project
            ->workspace
            ->organization
            ->roleFor($user);


        return TaskPermissions::canCancel(
            $role
        );
    }

    public function update(
        User $user,
        Task $task
    ): bool {

        if (
            $task->assignee_id === $user->id
        ) {
            return true;
        }

        return $task
            ->project
            ->team
            ->leader()
            ?->is($user);
    }

    public function delete(
        User $user,
        Task $task
    ): bool {

        return TaskPermissions::canDelete(
            $task->project
                ->workspace
                ->organization
                ->roleFor($user)
        );

    }

    public function reassign(
        User $user,
        Task $task
    ): bool {

        return TaskPermissions::canReassign(
            $task->project
                ->workspace
                ->organization
                ->roleFor($user)
        );
    }
}

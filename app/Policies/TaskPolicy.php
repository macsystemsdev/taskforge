<?php

namespace App\Policies;

use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Task\TaskPermissions;
use App\Domain\Task\TaskStatus;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{

    protected function locked(
        Task $task,
    ): bool {

        return $task

            ->project

            ->workspace

            ->organization

            ->taskLocked($task);
    }

  public function view(User $user, Task $task): bool {
    if ($this->locked($task)) {
        return false;
    }

    $organization = $task->project->workspace->organization;
    $role = $organization->roleFor($user);

    if (in_array($role, [
        OrganizationRole::OWNER,
        OrganizationRole::ADMIN,
    ])) {
        return true;
    }

    return $task->project->team->roleFor($user) !== null;
}

    public function start(
        User $user,
        Task $task
    ): bool {

        if ($this->locked($task)) {
            return false;
        }
        return $task->assignee_id === $user->id
            && $task->status === TaskStatus::TODO
            || $task->status === TaskStatus::BLOCKED;
    }

    public function block(
        User $user,
        Task $task
    ): bool {

        if ($this->locked($task)) {
            return false;
        }
        return $task->assignee_id === $user->id
            && $task->status === TaskStatus::IN_PROGRESS;
    }

    public function unblock(
        User $user,
        Task $task
    ): bool {

        if ($this->locked($task)) {
            return false;
        }

        return $task->assignee_id === $user->id
            && $task->status === TaskStatus::BLOCKED;
    }

    public function complete(
        User $user,
        Task $task
    ): bool {
        if ($this->locked($task)) {
            return false;
        }
        return $task->assignee_id === $user->id
            && $task->status == TaskStatus::IN_PROGRESS;
    }

    public function cancel(
        User $user,
        Task $task
    ): bool {

        if ($this->locked($task)) {
            return false;
        }
        $role = $task
            ->project
            ->team
            ->roleFor($user);

            if($task->status === TaskStatus::DONE){
                return false;
            }

            if($task->status === TaskStatus::CANCELLED){
                return false;
            }


        return TaskPermissions::canCancel(
            $role
        );
    }

    public function update(
        User $user,
        Task $task
    ): bool {

        if ($this->locked($task)) {
            return false;
        }

        if ($task->assignee->is($user)) {
            return true;
        }

        return $task
            ->project
            ->team
            ->leader()
            ?->is($user) ?? false;
    }

    public function delete(
        User $user,
        Task $task
    ): bool {

        // Only allow deletion when task is still untouched (todo, never started/blocked/completed)
        if (
            $task->status !== TaskStatus::TODO
            || $task->started_at !== null
            || $task->blocked_at !== null
            || $task->completed_at !== null
        ) {
            return false;
        }

        return TaskPermissions::canDelete(
            $task
                ->project
                ->team
                ->roleFor($user)
        );
    }

    public function reassign(
        User $user,
        Task $task
    ): bool {

        return TaskPermissions::canReassign(
            $task
                ->project
                ->team
                ->roleFor($user)
        );
    }

    public function attachResource(
        User $user,
        Task $task,
    ): bool {
        if ($this->locked($task)) {
            return false;
        }

        return TaskPermissions::canAttachResource(
            $task->project
                ->team
                ->roleFor($user),
        );
    }

    public function detachResource(
        User $user,
        Task $task,
    ): bool {
        if ($this->locked($task)) {
            return false;
        }

        return TaskPermissions::canDetachResource(
            $task->project
                ->team
                ->roleFor($user),
        );
    }

    public function viewPrivateResource(
        User $user,
        Task $task,
    ): bool {
        if ($this->locked($task)) {
            return false;
        }

        if ($task->assignee_id === $user->id) {
            return true;
        }

        return TaskPermissions::canViewPrivateResource(
            $task->project
                ->team
                ->roleFor($user),
        );
    }
}

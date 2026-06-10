<?php

namespace App\Actions\Teams;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use DomainException;

class UpdateMemberAction
{
    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {}

    public function handle(
        User $actor,
        Team $team,
        int $memberId,
        TeamRole $role,
    ): void {
        // User cannot modify their own role
        if ($actor->id === $memberId) {
            throw new DomainException(
                'You cannot change your own role.'
            );
        }

        if ($actor->role === TeamRole::LEADER) {

            $leaderCount = $team
                ->members()
                ->wherePivot(
                    'role',
                    TeamRole::LEADER
                )
                ->count();

            if ($leaderCount === 1) {
                throw new DomainException(
                    'Transfer leadership before removing the current leader.'
                );
            }
        }

        $membership = $team
            ->memberships()
            ->where('user_id', $memberId)
            ->firstOrFail();

        $membership->update([
            'role' => $role,
        ]);

            $this->activity->handle(
            subject: $team,
            event: 'Updated role of {$member->name} to {$role->value} in organization {$organization->name}',
            properties: [
                'member_id' => $memberId,
                'role' => $role->value,
            ]
        );
    }
}

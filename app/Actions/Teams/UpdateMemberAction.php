<?php

namespace App\Actions\Teams;

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use DomainException;

class UpdateMemberAction
{
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

        $membership = $team
            ->memberships()
            ->where('user_id', $memberId)
            ->firstOrFail();

        $membership->update([
            'role' => $role,
        ]);
    }
}

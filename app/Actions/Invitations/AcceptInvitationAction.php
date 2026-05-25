<?php

namespace App\Actions\Invitations;

use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;

class AcceptInvitationAction
{
    public function handle(
        string $token,
        User $user,
    ): Organization {

        $invitation = Invitation::query()
            ->where('token', $token)
            ->firstOrFail();

        abort_if(
            $user->email !== $invitation->email,
            403,
            'Unauthorized invitation.'
        );

        abort_if(
            $invitation->status !== 'pending',
            403,
            'Invitation is no longer valid.'
        );

        abort_if(
            $invitation->expires_at->isPast(),
            403,
            'Invitation expired.'
        );

        $invitation->organization
            ->members()
            ->syncWithoutDetaching([
                $user->id => [
                    'role' => $invitation->role,
                    'joined_at' => now(),
                    'status' => 'active',
                    'invited_by' => $invitation->invited_by,
                ]
            ]);

        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $invitation->  organization;
    }
}
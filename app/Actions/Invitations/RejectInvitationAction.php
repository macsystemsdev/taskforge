<?php

namespace App\Actions\Invitations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Invitation;
use App\Models\User;

class RejectInvitationAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
    public function handle(
        string $token,
        User $user,
        ?string $reason = null,
    ): void {

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
            'Invitation already processed.'
        );


        abort_if(
            $invitation->expires_at->isPast(),
            403,
            'Invitation expired.'
        );

    

        $invitation->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->activity->handle(
            subject: $invitation,
            event: 'invitation_rejected',
            properties: [
                'email' => $invitation->email,
                'reason' => $reason,
            ]
        );
    }
}
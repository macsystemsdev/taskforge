<?php

namespace App\Actions\Invitations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Usage\Actions\IncreaseMembersAction;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptInvitationAction
{

    public function __construct(
        protected CreateActivityLogAction $activity,
        protected IncreaseMembersAction $increaseMemberAction
    ) {}
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

       
        return DB::transaction(function () use ($invitation, $user) {
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

            $this->increaseMemberAction->handle($invitation->organization);

            // log activity

            $this->activity->handle(
                subject: $invitation,
                event: 'Invitation accepted',
                properties: [
                    'organization' => $invitation->organization->name,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                ]
            );
            return $invitation->organization;
        });
    }
}

<?php

namespace App\Actions\Organizations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Invitations\InviteMemberData;
use App\Exceptions\FeatureLimitExceededException;
use App\Mail\OrganizationInvitationMail;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteMemberAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}
    public function handle(
        InviteMemberData $data,

    ): Invitation {

        // Check if pending invitation already exists for the email and organization
        $existing = Invitation::query()
            ->where('organization_id', $data->organization_id)
            ->where('email', $data->email)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'Pending invitation already exists.'
            ]);
        }

        // Check if user is member of organization and avoid sending invitation if they are already a member
        $alreadyMember = $data->organization
            ->members()
            ->where('email', $data->email)
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'inviteEmail' => 'User already belongs to organization.'
            ]);
        }

        if (! $data->organization->canAddMember()) {
            throw new FeatureLimitExceededException(
                'Your subscription has reached the maximum number of members.'
            );
        }
        // Create invitation
        $invitation = Invitation::create([
            'organization_id' => $data->organization_id,
            'invited_by' => $data->invited_by,
            'email' => $data->email,
            'role' => $data->role,
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        // Log activity
        $this->activity->handle(
            subject: $invitation,
            event: 'member_invited',
            properties: [
                'invited_email' => $data->email,
                'role' => $data->role,
            ]
        );

        Mail::to($invitation->email)
            ->queue(
                new OrganizationInvitationMail($invitation)
            );

        return $invitation;
    }
}

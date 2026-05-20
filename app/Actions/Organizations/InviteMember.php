<?php

use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InviteMemberAction
{
    public function execute(Organization $organization, User $inviter, string $email, string $role = 'member'): OrganizationUser
    {
        $email = strtolower(trim($email));

        $existing = OrganizationUser::where('organization_id', $organization->id)
            ->where('email', $email)
            ->whereNull('joined_at')
            ->first();

        if ($existing) {
            return $existing; // or throw — depends on UX strategy
        }

        $invitation = OrganizationUser::create([
            'organization_id' => $organization->id,
            'email' => $email,
            'role' => $role,
            'invited_by' => $inviter->id,
            'token' => bin2hex(random_bytes(32)),
        ]);

        Mail::to($email)->send(new OrganizationInvitationMail($invitation));

        return $invitation;
    }
}
<?php

namespace App\Actions\Organizations;

use App\Mail\OrganizationInvitationMail;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteMemberAction
{
    public function handle(
        Organization $organization,
        User $inviter,
        array $data
    ): Invitation {

        // Check if pending invitation already exists for the email and organization
        $existing = Invitation::query()
            ->where('organization_id', $organization->id)
            ->where('email', $data['email'])
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'Pending invitation already exists.'
            ]);
        }

        // Check if user is member of organization and avoid sending invitation if they are already a member
        $alreadyMember = $organization
            ->members()
            ->where('email', $data['email'])
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'inviteEmail' => 'User already belongs to organization.'
            ]);
        }

        // Create invitation
        $invitation = Invitation::create([
            'organization_id' => $organization->id,
            'invited_by' => $inviter->id,
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)
            ->send(
                new OrganizationInvitationMail($invitation)
            );

        return $invitation;
    }
}

<?php

namespace App\Actions\Organizations;

use App\Mail\OrganizationInvitationMail;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteMemberAction
{
    public function handle(
        Organization $organization,
        User $inviter,
        array $data
    ): Invitation {

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
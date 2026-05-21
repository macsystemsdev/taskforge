<?php

namespace App\Http\Controllers;

use App\Models\Invitation;


class AcceptInvitationController extends Controller
{
    public function InvitationAccept(string $token)
    {
        dd($token);
        $invitation = Invitation::where(
            'token',
            $token
        )->firstOrFail();

        abort_if(
            $invitation->expires_at->isPast(),
            403,
            'Invitation expired.'
        );

        abort_if(
            $invitation->accepted_at,
            403,
            'Invitation already accepted.'
        );

        $invitation->organization->members()->attach(
            auth()->user()->email === $invitation->email
                ? auth()->user()->id
                : null,
            [
                'role' => $invitation->role,
                'joined_at' => now(),
                'status' => 'active',
                'invited_by' => $invitation->invited_by,
            ]
        );

        $invitation->update([
            'accepted_at' => now(),
        ]);

        return redirect()->route(
            'organizations.show',
            $invitation->organization
        );
    }
}

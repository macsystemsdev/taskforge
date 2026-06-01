<?php

namespace App\Http\Controllers;

use App\Actions\Invitations\AcceptInvitationAction;
use App\Actions\Invitations\RejectInvitationAction;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationInvitationController
{
    public function accept(
        string $token,
        AcceptInvitationAction $action
    ) {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        
        // Check if user is authenticated
        if (!Auth::check()) {
            session()->put('invitation_token', $token);
            session()->put('invitation_action', 'accept');
            return redirect()->route('login')->with('message', 
                'Please login to accept the invitation to ' . $invitation->email
            );
        }
        
        // Verify email matches
        if (Auth::user()->email !== $invitation->email) {
            Auth::logout();
            session()->put('invitation_token', $token);
            session()->put('invitation_action', 'accept');
            return redirect()->route('login')->withErrors([
                'email' => 'This invitation was sent to ' . $invitation->email . 
                          '. Please login with that email address.'
            ]);
        }
        
        $action->handle(
            token: $token,
            user: auth()->user(),
        );

        return redirect()
            ->route('organizations.show', $invitation->organization)
            ->with('success', 'Invitation accepted successfully!');
    }

    public function showRejectForm(
        string $token
    ) {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        
        // Check if user is authenticated
        if (!Auth::check()) {
            session()->put('invitation_token', $token);
            session()->put('invitation_action', 'reject');
            return redirect()->route('login')->with('message', 
                'Please login to reject the invitation to ' . $invitation->email
            );
        }
        
        // Verify email matches
        if (Auth::user()->email !== $invitation->email) {
            Auth::logout();
            session()->put('invitation_token', $token);
            session()->put('invitation_action', 'reject');
            return redirect()->route('login')->withErrors([
                'email' => 'This invitation was sent to ' . $invitation->email . 
                          '. Please login with that email address.'
            ]);
        }

        return view('pages.invitations.reject', compact('invitation'));
    }

    public function reject(
        Request $request,
        string $token,
        RejectInvitationAction $action
    ) {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        
        // Check if user is authenticated
        if (!Auth::check()) {
            session()->put('invitation_token', $token);
            session()->put('invitation_action', 'reject');
            session()->put('rejection_reason', $request->reason);
            return redirect()->route('login')->with('message', 
                'Please login to reject the invitation'
            );
        }
        
        // Verify email matches
        if (Auth::user()->email !== $invitation->email) {
            return redirect()->route('login')->withErrors([
                'email' => 'You are not authorized to reject this invitation.'
            ]);
        }
        
        $action->handle(
            token: $token,
            user: auth()->user(),
            reason: $request->reason,
        );

        return redirect()
            ->route('dashboard')
            ->with('message', 'Invitation rejected successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUser;
use Illuminate\Http\Request;

class AcceptInvitationController extends Controller
{
    public function __invoke(string $token)
    {
        $invitation = OrganizationUser::where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        if ($user->email !== $invitation->email) {
            abort(403);
        }

        $invitation->update(['joined_at' => now()]);

        $invitation->organization->users()->attach($user->id, [
            'role' => $invitation->role,
        ]);

        return redirect('/dashboard');
    }
}

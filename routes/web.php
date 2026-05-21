<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Organization;
use App\Models\Invitation;

Route::get(
    '/invitations/{token}/accept',
    function (string $token) {

        $invitation = Invitation::where(
            'token',
            $token
        )->firstOrFail();

        abort_if(
            auth()->user()->email !== $invitation->email,
            403,
            'Unauthorized invitation access.'
        );

        abort_if(
            $invitation->expires_at->isPast(),
            403,
            'Invitation expired.',
            $invitation->update([
            'status' => 'expired',
        ])

        );

        abort_if(
            $invitation->accepted_at,
            403,
            'Invitation already accepted.'
        );

        $invitation->organization->members()->syncWithoutDetaching([
            auth()->id() =>
            [
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

        return redirect()->route(
            'organizations.show',
            $invitation->organization
        );
    }
)->middleware('auth')->name('invitations.accept');

Route::post(
    '/invitations/{token}/reject',
    function (string $token) {

        $invitation = Invitation::where(
            'token',
            $token
        )->firstOrFail();

        abort_if(
            $invitation->status !== 'pending',
            403
        );

        $invitation->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('dashboard');
    }
)->middleware('auth')->name('invitations.reject');

Route::view('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');

    Route::get('/organizations/create', fn() => view('pages.organizations.create'))->name('organizations.create');

    Route::get(
        '/organizations/{organization}',
        function (Organization $organization) {

            return view(
                'pages.organizations.show',
                compact('organization')
            );
        }
    )->name('organizations.show');
});

require __DIR__ . '/settings.php';

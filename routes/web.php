<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Organization;

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

    Route::get(
    '/organizations/create',
    fn () => view('pages.organizations.create')
)->name('organizations.create');

   Route::get(
    '/organizations/{organization}',
    function (Organization $organization) {

        return view(
            'pages.organizations.show',
            compact('organization')
        );
    }
)->name('organizations.show');

Route::get('/invitations/{token}', AcceptInvitationController::class);
});

require __DIR__ . '/settings.php';
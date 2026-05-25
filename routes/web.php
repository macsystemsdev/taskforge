<?php

use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Organization;
use App\Models\Project;

Route::get('/invitations/{token}/accept', [OrganizationInvitationController::class, 'accept'])
    ->name('invitations.accept')->middleware('auth');

Route::get('/invitations/{token}/reject', [OrganizationInvitationController::class, 'reject'])
    ->name('invitations.reject')->middleware('auth');
    
Route::get('/invitations/{token}/reject', [OrganizationInvitationController::class, 'showRejectForm'])->name('invitations.reject.form')->middleware('auth');

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

    // Create and show organization
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

    Route::get(
        '/workspaces/{workspace}/projects/create',
        fn() => view('pages.projects.create')
    )->name('projects.create');

    Route::get(
        '/projects/{project}',
        function (Project $project) {
            return view('pages.projects.show', compact('project'));
        }
    )->name('projects.show');
});


require __DIR__ . '/settings.php';

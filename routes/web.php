<?php

use App\Http\Controllers\OrganizationInvitationController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Livewire\Volt\Volt;



Route::get('/invitations/{token}/accept', [OrganizationInvitationController::class, 'accept'])
    ->name('invitations.accept')->middleware('auth');

Route::post('/invitations/{token}/reject', [OrganizationInvitationController::class, 'reject'])
    ->name('invitations.reject')->middleware('auth');

Route::get('/invitations/{token}/reject', [OrganizationInvitationController::class, 'showRejectForm'])->name('invitations.reject.form')->middleware('auth');

Route::view('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('team-invitations.accept');

    Route::view('/organizations', 'pages.organizations.index')->name('organizations.index');

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

    // Teams routes (within organization)
    Route::get('/organizations/{organization}/teams/create', function (Organization $organization) {
        return view('livewire.teams.create-team', compact('organization'));
    })->name('teams.create');
    Route::get('/organizations/{organization}/teams/{team}', function (Organization $organization, \App\Models\Team $team) {
        return view('pages.teams.show', compact('organization', 'team'));
    })->name('teams.show');

    Route::get(
        '/workspaces/{workspace}/projects/create',
        function (Workspace $workspace) {
            return view(
                'pages.projects.create',
                compact('workspace')
            );
        }
    )->name('projects.create');

    Route::get(
        '/projects/{project}',
        function (Project $project) {
            return view(
                'pages.projects.show',
                compact('project')
            );
        }
    )->name('projects.show');

    Route::view('/projects', 'pages.projects.index')->name('projects.index');

    // create task
    Route::get(
        '/projects/{project}/tasks/create',
        function (Project $project) {
            return view(
                'pages.tasks.create',
                compact('project')
            );
        }
    )->name('tasks.create');

    Route::get(
        '/tasks/{task:slug}',
        function (Task $task) {

            return view(
                'pages.tasks.show',
                compact('task')
            );
        }
    )->name('tasks.show');

    Route::view('/tasks', 'pages.tasks.index')->name('tasks.index');

    Route::view('/notifications', 'pages.notifications.index')->name('notifications.index');
});


require __DIR__ . '/settings.php';

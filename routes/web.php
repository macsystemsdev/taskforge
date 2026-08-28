<?php

use App\Http\Controllers\NotificationRedirectController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Billing\ShowBillingCancel;
use App\Livewire\Billing\ShowBillingSuccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Broadcast;


Broadcast::routes(['middleware' => ['auth', 'verified']]);


Route::view('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');


Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/reports', function () {
        return view('pages.reports.index');
    })->name('reports.index');

    Route::view('/organizations', 'pages.organizations.index')->name('organizations.index');

    // Create and show organization
    Route::redirect('/organizations/create', '/organizations')->name('organizations.create');

    Route::get(
        '/organizations/{organization}',
        function (Organization $organization) {

            Gate::authorize('view', $organization);

            return view('pages.organizations.show', compact('organization'));
        }
    )->name('organizations.show');

    Route::get('/invitations/{token}/accept', [OrganizationInvitationController::class, 'accept'])
        ->name('invitations.accept');

    Route::post('/invitations/{token}/reject', [OrganizationInvitationController::class, 'reject'])
        ->name('invitations.reject');

    Route::get('/invitations/{token}/reject', [OrganizationInvitationController::class, 'showRejectForm'])->name('invitations.reject.form');

    // show workspace
    Route::get(
        '/workspaces/{workspace}',
        function (Workspace $workspace) {
            return view(
                'pages.workspaces.show',
                compact('workspace')
            );
        }
    )->name('workspaces.show');

    // Teams routes (within workspace)
    Route::get('/workspaces/{workspace}/teams/create', function (Workspace $workspace) {
        return view('pages.teams.create', compact('workspace'));
    })->name('teams.create');

    Route::get('/workspaces/{workspace}/teams/{team}', function (Workspace $workspace, \App\Models\Team $team) {
        return view('pages.teams.show', compact('workspace', 'team'));
    })->name('teams.show');

    Route::view(
        '/projects',
        'pages.projects.index'
    )->name('projects.index');

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
            Gate::authorize('view', $project);

            return view(
                'pages.projects.show',
                compact('project')
            );
        }
    )->name('projects.show');

    

    Route::get(
        '/projects/{project}/attachments/{attachment}/download',
        function (Project $project, \App\Models\FileAttachment $attachment, \App\Domain\Storage\Actions\DownloadProjectAttachmentAction $download) {
            abort_unless(
                $attachment->attachable_type === Project::class
                    && $attachment->attachable_id === $project->id,
                404,
            );

            Gate::authorize('view', $project);

            return $download->handle($attachment, Auth::user());
        }
    )->name('projects.attachments.download');

    Route::get(
        '/projects/{project}/attachments/{attachment}/view',
        function (Project $project, \App\Models\FileAttachment $attachment) {
            abort_unless(
                $attachment->attachable_type === Project::class
                    && $attachment->attachable_id === $project->id,
                404,
            );

            Gate::authorize('view', $project);

            return response()->file(
                \Illuminate\Support\Facades\Storage::disk('public')->path($attachment->storedFile->path),
            );
        }
    )->name('projects.attachments.view');

    Route::get('/tasks/{task}/attachments/{attachment}/download', function ($taskId, $attachmentId) {
        $task = App\Models\Task::findOrFail($taskId);
        $attachment = $task->attachments()->findOrFail($attachmentId);

        return response()->download(
            storage_path('app/' . $attachment->storedFile->path),
            $attachment->storedFile->original_filename
        );
    })->name('tasks.attachments.download');

    Route::get('/tasks/{task}/attachments/{attachment}/view', function ($taskId, $attachmentId) {
        $task = App\Models\Task::findOrFail($taskId);
        $attachment = $task->attachments()->findOrFail($attachmentId);

        return response()->file(
            storage_path('app/' . $attachment->storedFile->path)
        );
    })->name('tasks.attachments.view');

    Route::get(
        '/projects/{project}/edit',
        function (Project $project) {
            return view(
                'pages.projects.edit',
                compact('project')
            );
        }
    )->name('projects.edit');

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

    Route::post('/notifications/mark-all-read', function () {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return back();
    })->name('notifications.read-all');

    Route::get('/notifications/{id}', NotificationRedirectController::class)
        ->name('notifications.redirect');

    Route::get(
        '/billing',
        \App\Livewire\Billing\BillingDashboard::class
    )->name('billing.index');

    Route::get(
        '/organizations/{organization}/billing',
        \App\Livewire\Billing\BillingDashboard::class
    )->name('organizations.billing');

    Route::get(
        '/organizations/{organization}/billing/success',
        ShowBillingSuccess::class
    )->name('billing.success');

    Route::get(
        '/organizations/{organization}/billing/cancel',
        ShowBillingCancel::class
    )->name('billing.cancel');
});

// Stripe webhook route
Route::post(
    '/stripe/webhook',
    StripeWebhookController::class
)->name('stripe.webhook');

require __DIR__ . '/settings.php';

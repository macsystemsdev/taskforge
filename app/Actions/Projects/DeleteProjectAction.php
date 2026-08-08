<?php

namespace App\Actions\Projects;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Task\TaskStatus;
use App\Domain\Usage\Actions\DecreaseProjectsAction;
use App\Models\Project;
use App\Notifications\Projects\ProjectDeletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $activity,
        protected DecreaseProjectsAction $decreaseProjects
    ) {}

    public function handle(
        Project $project
    ): void {

        if ($project->hasIncompleteTasks()) {
            throw ValidationException::withMessages([
                'project' => __('A project with incomplete tasks cannot be deleted.'),
            ]);
        }

        $project->team?->notifyMembers(
            new ProjectDeletedNotification(
                $project
            ),
            auth()->user()
        );

        $project
            ->workspace
            ->organization
            ->notifyAdministrators(
                new ProjectDeletedNotification(
                    $project
                ),
                auth()->user()
            );
        DB::transaction(function () use ($project) {
            $project->delete();

            $this->decreaseProjects->handle($project->workspace->organization);

            $this->activity->handle(
                event: 'project_deleted',
                properties: [
                    'project_name' => $project->name,
                ],
                subject: $project,
            );
        });
    }
}

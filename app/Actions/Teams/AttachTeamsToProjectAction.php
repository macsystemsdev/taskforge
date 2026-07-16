<?php

namespace App\Actions\Teams;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Teams\AttachTeamsToProjectData;
use App\Models\Project;
use App\Models\ProjectTeam;
use App\Models\Team;
use App\Notifications\Projects\ProjectCreatedNotification;

class AttachTeamsToProjectAction
{
    public function __construct(
        protected CreateActivityLogAction $logAction,
    ) {}

    public function handle(
        Project $project,
        AttachTeamsToProjectData $data,
    ): void {

        $oldTeams = $project->teams()->pluck('teams.id')->toArray();

        $project->teams()->sync($data->team_ids);

        $addedTeams = array_diff(
            $data->team_ids,
            $oldTeams
        );

        $removedTeams = array_diff(
            $oldTeams,
            $data->team_ids
        );
        foreach ($addedTeams as $teamId) {

            $team = Team::find($teamId);

            $team?->notifyMembers(
                new ProjectCreatedNotification(
                    $project
                ),
                auth()->user()
            );

            $this->logAction->handle(
                subject: $project,
                event: 'team_attached_to_project',
                properties: [
                    'team_id' => $teamId,
                ],
            );
        }
        
        foreach ($removedTeams as $teamId) {

            $this->logAction->handle(
                subject: $project,
                event: 'team_removed_from_project',
                properties: [
                    'team_id' => $teamId,
                ],
            );
        }
    }
}

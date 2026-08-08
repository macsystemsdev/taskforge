<?php

namespace App\Actions\Teams;

use App\Data\Teams\CreateTeamData;
use App\Domain\Teams\Enums\TeamRole;
use App\Domain\Usage\Actions\IncreaseTeamsAction;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeam
{

    public function __construct(
        protected IncreaseTeamsAction $increaseTeams,
    ) {}
    public function handle(
        Workspace $workspace,
        CreateTeamData $data,
    ): Team {

        return DB::transaction(function () use ($workspace, $data) {
            $team = Team::create([
                'name' => $data->name,
                'description' => $data->description,
                'workspace_id' => $workspace->id,
            ]);

            $this->increaseTeams->handle($workspace->organization);

            // Ensure leader ID is explicitly cast or compared correctly
            $leaderId = (string) $data->leaderId;

            // Combine leader into member list and remove nulls/duplicates
            $memberIds = array_unique(
                array_filter([
                    ...($data->memberIds ?? []),
                    $data->leaderId,
                ])
            );

            foreach ($memberIds as $memberId) {
                $team->memberships()->create([
                    'user_id' => $memberId,
                    // Cast to string (or use ==) so type difference doesn't break equality
                    'role' => (string) $memberId === $leaderId
                        ? TeamRole::LEADER
                        : TeamRole::MEMBER,
                ]);
            }

            return $team;
        });
    }
}

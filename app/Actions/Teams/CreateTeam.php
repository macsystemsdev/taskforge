<?php

namespace App\Actions\Teams;

use App\Data\Teams\CreateTeamData;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\User;

class CreateTeam
{
    
     public function handle(
        Workspace $workspace,
        CreateTeamData $data,
    ): Team {

        $team = Team::create([
            'name' => $data->name,
            'description' => $data->description,
            'workspace_id' => $workspace->id,
        ]);

        $memberIds = array_unique([
            ...$data->memberIds,
            $data->leaderId,
        ]);

        foreach ($memberIds as $memberId) {

            $team->memberships()->create([
                'user_id' => $memberId,
                'role' => $memberId === $data->leaderId
                    ? TeamRole::LEADER
                    : TeamRole::MEMBER,
            ]);
        }

        return $team;
    }
}

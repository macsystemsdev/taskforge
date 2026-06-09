<?php

namespace App\Actions\Teams;

use App\Data\Teams\CreateTeamData;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\User;

class CreateTeam
{
    /**
     * Create a new team and add the user as leader(using owner as variable to store since decision to change owner to leader was made earlier on).
     */
    public function handle(
        User|Workspace $workspace,
        CreateTeamData|string $data,
        ?User $owner = null,
    ): Team {
        if ($workspace instanceof Workspace) {
            $work = $workspace;
            $owner = $owner ?? auth()->user();
        } else {
            $work = null;
            $owner = $workspace;
        }

        if (is_string($data)) {
            $data = new CreateTeamData(name: $data);
        }

        $team = Team::create([
            'name' => $data->name,
            'description' => $data->description,
            'workspace_id' => $work?->id,
        ]);

        $team->memberships()->create([
            'user_id' => $owner->id,
            'role' => TeamRole::LEADER,
        ]);

        return $team;
    }
}

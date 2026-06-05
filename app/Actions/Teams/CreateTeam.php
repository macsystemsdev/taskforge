<?php

namespace App\Actions\Teams;

use App\Data\Teams\CreateTeamData;
use App\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;

class CreateTeam
{
    /**
     * Create a new team and add the user as leader(using owner as variable to store since decision to change owner to leader was made earlier on).
     */
    public function handle(
        User|Organization $organization,
        CreateTeamData|string $data,
        ?User $owner = null,
    ): Team {
        if ($organization instanceof Organization) {
            $org = $organization;
            $owner = $owner ?? auth()->user();
        } else {
            $org = null;
            $owner = $organization;
        }

        if (is_string($data)) {
            $data = new CreateTeamData(name: $data);
        }

        $team = Team::create([
            'name' => $data->name,
            'description' => $data->description,
            'organization_id' => $org?->id,
        ]);

        $team->memberships()->create([
            'user_id' => $owner->id,
            'role' => TeamRole::LEADER,
        ]);

        return $team;
    }
}

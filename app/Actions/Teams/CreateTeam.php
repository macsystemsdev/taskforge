<?php

namespace App\Actions\Teams;

use App\Data\Teams\CreateTeamData;
use Illuminate\Support\Str;
use App\Models\Organization;


class CreateTeam
{
    /**
     * Create a new team and add the user as owner.
     */
    public function handle(
        Organization $organization,
        CreateTeamData $data,
    ) {
        return $organization->teams()->create([
            'name' => $data->name,
            'slug' => Str::slug($data->name),
            'description' => $data->description,
        ]);
    }
}

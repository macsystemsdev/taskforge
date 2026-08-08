<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class IncreaseTeamsAction
{
    public function handle(Organization $organization){
        $decrese = new OrganizationUsageService($organization);
        $decrese->increaseTeams(1);
    }
}

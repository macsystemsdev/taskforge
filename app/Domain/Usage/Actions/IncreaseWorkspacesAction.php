<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class IncreaseWorkspacesAction
{
    public function handle(Organization $organization){
        $decrese = new OrganizationUsageService($organization);
        $decrese->increaseWorkspaces(1);
    }
}

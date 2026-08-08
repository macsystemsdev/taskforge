<?php

namespace App\Domain\Usage\Actions;

use App\Models\Organization;
use App\Domain\Usage\Services\OrganizationUsageService;

class DecreaseProjectsAction
{
    public function handle(Organization $organization){
        $decrese = new OrganizationUsageService($organization);
        $decrese->decreaseProjects(1);
    }
}

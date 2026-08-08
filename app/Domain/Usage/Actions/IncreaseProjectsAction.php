<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class IncreaseProjectsAction
{
   public function handle(Organization $organization){
        $decrese = new OrganizationUsageService($organization);
        $decrese->increaseProjects(1);
    }
}

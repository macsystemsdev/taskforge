<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class DecreaseTasksAction
{
    public function handle(Organization $organization){
        $decrese = new OrganizationUsageService($organization);
        $decrese->decreaseTasks(1);
    }
}

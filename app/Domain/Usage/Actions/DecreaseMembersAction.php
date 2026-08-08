<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class DecreaseMembersAction
{
    public function handle(Organization $organization): void
    {
        $decrease = new OrganizationUsageService($organization);
        $decrease->decreaseMembers(1);
    }
}

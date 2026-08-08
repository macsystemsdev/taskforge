<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class IncreaseMembersAction
{
    public function handle(Organization $organization): void
    {
        $increase = new OrganizationUsageService($organization);
        $increase->increaseMembers(1);
    }
}

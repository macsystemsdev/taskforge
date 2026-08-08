<?php

namespace App\Domain\Usage\Actions;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;

class IncreaseStorageUsageAction
{
    public function handle(Organization $organization, int $bytes){
        $decrese = new OrganizationUsageService($organization);
        $decrese->increaseStorage($bytes);
    }
}

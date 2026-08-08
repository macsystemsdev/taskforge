<?php

namespace App\Console\Commands;

use App\Domain\Usage\Services\OrganizationUsageService;
use App\Models\Organization;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usage:recalculate {organization? : The ID of a specific organization}')]
#[Description('Recalculates the usage metrics for a specific organization.')]
class RecalculateOrganizationUsage extends Command
{
    public function handle(): void
    {
        $orgId = $this->argument('organization');

        $query = $orgId
            ? Organization::where('id', $orgId)
            : Organization::query();

        $query->chunk(100, function ($organizations) {
            foreach ($organizations as $org) {
                (new OrganizationUsageService($org))
                    ->recalculate();
            }
        });

        $this->info('Usage recalculation completed successfully.');
    }
}

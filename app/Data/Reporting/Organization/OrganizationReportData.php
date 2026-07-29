<?php

namespace App\Data\Reporting\Organization;

use Spatie\LaravelData\Data;

class OrganizationReportData extends Data
{
    public function __construct(

        public int $organizationId,

        public string $organizationName,

        public int $totalProjects,

        public int $activeProjects,

        public int $completedProjects,

        public int $totalTasks,

        public int $completedTasks,

        public int $overdueTasks,

        public int $healthyProjects,

        public int $atRiskProjects,

        public int $criticalProjects,

        public float $completionPercentage,

    ) {
    }
}

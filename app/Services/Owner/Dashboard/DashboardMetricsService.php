<?php

namespace App\Services\Owner\Dashboard;

use App\Services\Owner\Infrastructure\InfrastructureMetricsService;
use App\Services\Owner\Organization\OrganizationMetricsService;
use App\Services\Owner\Revenue\RevenueMetricsService;

class DashboardMetricsService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected OrganizationMetricsService $organizations,
        protected RevenueMetricsService $revenue,
        protected InfrastructureMetricsService $infrastructure,
    ) {}

    public function overview(): array
    {
        return array_merge(
            $this->organizations->metrics(),
            $this->revenue->metrics(),
            $this->infrastructure->metrics(),
        );
    }
}

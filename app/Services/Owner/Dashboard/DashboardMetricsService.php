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

    public function platformMetrics(): array
    {
        return $this->organizations->platformMetrics();
    }

    public function usageMetrics(): array
    {
        return $this->organizations->usageMetrics();
    }

    public function revenueMetrics(): array
    {
        return $this->revenue->revenueMetrics();
    }

    public function infrastructureMetrics(): array
    {
        return $this->infrastructure->healthMetrics();
    }
}

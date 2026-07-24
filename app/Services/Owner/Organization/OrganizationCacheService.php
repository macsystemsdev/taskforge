<?php

namespace App\Services\Owner\Organization;

use Illuminate\Support\Facades\Cache;

class OrganizationCacheService
{
    public function __construct(
        private OrganizationMetricsService $metrics,
    ) {}

    public function platformMetrics(): array
    {
        $serialized = Cache::remember(
            'owner.dashboard.organization.platform',
            now()->addMinutes(10),
            fn() => serialize(
                $this->metrics->platformMetrics()
            )
        );

        return unserialize($serialized);
    }

    public function usageMetrics(): array
    {
        $serialized = Cache::remember(
            'owner.dashboard.organization.usage',
            now()->addMinutes(10),
            fn() => serialize(
                $this->metrics->usageMetrics()
            )
        );

        return unserialize($serialized);
    }

    public function forget(): void
    {
        Cache::forget(
            'owner.dashboard.organization.platform'
        );

        Cache::forget(
            'owner.dashboard.organization.usage'
        );
    }

    public function refresh(): array
    {
        $this->forget();

        return $this->platformMetrics();
    }
}

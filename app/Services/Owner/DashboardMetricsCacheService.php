<?php

namespace App\Services\Owner;

use App\Services\Owner\Dashboard\DashboardMetricsService;
use App\Services\Owner\DTO\MetricData;
use Illuminate\Support\Facades\Cache;

class DashboardMetricsCacheService
{
    public function __construct(
        private DashboardMetricsService $metrics,
    ) {}

    public function overview(): array
    {
        $serialized = Cache::remember(
            'owner.dashboard.metrics',
            now()->addMinutes(5),
            fn() => serialize($this->metrics->overview())
        );

        return unserialize($serialized);
    }

    public function forget(): void
    {
        Cache::forget('owner.dashboard.metrics');
    }

    public function refresh(): array
    {
        $this->forget();

        return $this->overview();
    }
}

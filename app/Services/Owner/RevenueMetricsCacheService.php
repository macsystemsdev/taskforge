<?php

namespace App\Services\Owner;

use App\Services\Owner\Revenue\RevenueMetricsService;
use Illuminate\Support\Facades\Cache;

class RevenueMetricsCacheService
{
    public function __construct(
        private RevenueMetricsService $metrics,
    ) {}

    public function overview(): array
    {
        $serialized = Cache::remember(
            'owner.dashboard.revenue',
            now()->addMinutes(10),
            fn() => serialize($this->metrics->metrics())
        );

        return unserialize($serialized);
    }

    public function forget(): void
    {
        Cache::forget(
            'owner.dashbaord.revenue'
        );
    }

    public function refresh(): array
    {
        $this->forget();

        return $this->overview();
    }
}

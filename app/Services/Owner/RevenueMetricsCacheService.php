<?php

namespace App\Services\Owner;

use App\Services\Owner\Revenue\RevenueMetricsService;
use Illuminate\Support\Facades\Cache;

class RevenueMetricsCacheService
{
    public function __construct(
        private RevenueMetricsService $metrics,
    ) {}

    public function overviewMetrics(): array
    {
        $serialized = Cache::remember(
            'owner.dashboard.revenue.overview',
            now()->addMinutes(10),
            fn () => serialize(
                $this->metrics->revenueMetrics()
            ),
        );

        return unserialize($serialized);
    }

    public function monthlyRevenueTrend(): array
    {
        return Cache::remember(
            'owner.dashboard.revenue.trend',
            now()->addMinutes(10),
            fn () => $this->metrics->monthlyRevenueTrend()->toArray(),
        );
    }

    public function subscriptionBreakdown(): array
    {
        return Cache::remember(
            'owner.dashboard.revenue.breakdown',
            now()->addMinutes(10),
            fn () => $this->metrics->subscriptionBreakdown()->toArray(),
        );
    }

    public function forget(): void
    {
        Cache::forget('owner.dashboard.revenue.overview');
        Cache::forget('owner.dashboard.revenue.trend');
        Cache::forget('owner.dashboard.revenue.breakdown');
    }

    public function refresh(): array
    {
        $this->forget();

        return $this->overviewMetrics();
    }
}

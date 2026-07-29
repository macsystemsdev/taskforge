<?php

namespace App\Services\Reporting\Cache;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;
use App\Services\Reporting\BillingReportingService;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Future
|--------------------------------------------------------------------------
|
| Reporting caches will be invalidated through domain events once
| reporting metrics become mutable in real time.
|
*/

class ProjectReportingCacheService
{
    public function __construct(
        protected BillingReportingService $reporting,
    ) {
    }

    /**
     * @return array<ReportMetricData>
     */
    public function overview(
        ReportFilterData $filters,
    ): array {

        return Cache::remember(
            $this->cacheKey('overview', $filters),
            $this->ttl(),
            fn() => $this->reporting->overview($filters),
        );
    }

    public function revenueTrend(
        ReportFilterData $filters,
    ): ChartSeriesData {

        return Cache::remember(
            $this->cacheKey('revenue-trend', $filters),
            $this->ttl(),
            fn() => $this->reporting->revenueTrend($filters),
        );
    }

    public function planDistribution(
        ReportFilterData $filters,
    ): Collection {

        return Cache::remember(
            $this->cacheKey('plan-distribution', $filters),
            $this->ttl(),
            fn() => $this->reporting->planDistribution($filters),
        );
    }

    protected function ttl(): CarbonInterval
    {
        return CarbonInterval::minutes(5);
    }

protected function cacheKey(
    string $metric,
    ReportFilterData $filters,
): string {

    return sprintf(
        'reports.billing.%s.%s.org-%s.workspace-%s.team-%s.project-%s',
        $metric,
        $filters->period->value,
        $filters->organizationId ?? 'all',
        $filters->workspaceId ?? 'all',
        $filters->teamId ?? 'all',
        $filters->projectId ?? 'all',
    );
}
}

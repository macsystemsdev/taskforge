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

class ProjectReportingCacheService extends BaseReportingCacheService
{
    public function __construct(
        protected BillingReportingService $reporting,
    ) {}

    /**
     * @return array<ReportMetricData>
     */
    public function overview(
        ReportFilterData $filters,
    ): array {

        return $this->remember(

            $this->cacheKey(
                'overview',
                $filters,
            ),

            fn() => $this->reporting
                ->overview($filters),
        );
    }

    public function revenueTrend(
        ReportFilterData $filters,
    ): ChartSeriesData {

        return $this->remember(
            $this->cacheKey(
                'revenue-trend',
                $filters,
            ),
            fn() => $this->reporting->revenueTrend($filters),
        );
    }

    public function planDistribution(
        ReportFilterData $filters,
    ): Collection {

        return $this->remember(
            $this->cacheKey(
                'plan-distribution',
                $filters,
            ),
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

<?php

namespace App\Services\Reporting\Cache;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;
use App\Services\Reporting\OrganizationReportingService;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Organization Reporting Cache
|--------------------------------------------------------------------------
|
| Provides cached reporting data for organization analytics.
|
| Future:
| Cache invalidation will be driven through domain events once reporting
| becomes highly dynamic.
|
*/

class OrganizationReportingCacheService
{
    public function __construct(
        protected OrganizationReportingService $reporting,
    ) {
    }


    /**
     * @return array<int, ReportMetricData>
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


    public function growthTrend(
        ReportFilterData $filters,
    ): ChartSeriesData {

        return Cache::remember(
            $this->cacheKey('growth-trend', $filters),
            $this->ttl(),
            fn() => $this->reporting->growthTrend($filters),
        );
    }


    /**
     * @return Collection<int, mixed>
     */
    public function health(
        ReportFilterData $filters,
    ): Collection {

        return Cache::remember(
            $this->cacheKey('health', $filters),
            $this->ttl(),
            fn() => $this->reporting->health($filters),
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
            'reports.organization.%s.period-%s.org-%s.workspace-%s.team-%s',
            $metric,
            $filters->period->value,
            $filters->organizationId ?? 'all',
            $filters->workspaceId ?? 'all',
            $filters->teamId ?? 'all',
        );
    }
}
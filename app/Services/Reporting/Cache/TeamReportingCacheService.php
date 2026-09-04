<?php

namespace App\Services\Reporting\Cache;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\Team\TeamProductivityData;
use App\Data\Reporting\Team\TeamReportFilterData;
use App\Services\Reporting\TeamReportingService;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Future
|--------------------------------------------------------------------------
|
| Reporting caches will eventually be invalidated by domain events
| whenever team productivity metrics change.
|
*/

class TeamReportingCacheService extends BaseReportingCacheService
{
    public function __construct(
        protected TeamReportingService $reporting,
    ) {}

    /**
     * Executive overview.
     *
     * @return array<string,int>
     */
    public function overview(
        TeamReportFilterData $filters,
    ): array {

        return Cache::remember(
            $this->cacheKey(
                'overview',
                $filters,
            ),
            $this->ttl(),
            fn() => $this->reporting
                ->overview($filters),
        );
    }

    /**
     * Productivity report.
     *
     * @return Collection<int, TeamProductivityData>
     */
    // In TeamReportingCacheService.php
    public function productivity(
        TeamReportFilterData $filters,
    ): Collection {
         return $this->remember(
            $this->cacheKey(
                'productivity',
                $filters,
            ),
            fn() => $this->reporting->productivity($filters),
        );
    }

    /**
     * Completion trend.
     */
    public function completionTrend(
        TeamReportFilterData $filters,
    ): ChartSeriesData {

        return Cache::remember(
            $this->cacheKey(
                'completion-trend',
                $filters,
            ),
            $this->ttl(),
            fn() => $this->reporting
                ->completionTrend($filters),
        );
    }
    /**
     * Reporting cache lifetime.
     */
    protected function ttl(): CarbonInterval
    {
        return CarbonInterval::minutes(15);
    }

    /**
     * Generate a unique cache key.
     */
    protected function cacheKey(
        string $metric,
        TeamReportFilterData $filters,
    ): string {

        return sprintf(
            'reports.v3.teams.%s.org-%s.workspace-%s.team-%s',
            $metric,
            $filters->organizationId ?? 'all',
            $filters->workspaceId ?? 'all',
            $filters->teamId ?? 'all',
        );
    }
}

<?php

namespace App\Services\Reporting\Cache;

use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Services\Reporting\ProjectReportingService;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProjectReportingCacheService extends BaseReportingCacheService
{
    private const OVERVIEW = 'overview';
    private const HEALTH = 'health';
    private const COMPLETION_TREND = 'completion-trend';

    public function __construct(
        protected ProjectReportingService $reporting,
    ) {}

    /**
     * @return Collection<int, ProjectHealthData>
     */
    public function health(
        ProjectReportFilterData $filters,
    ): Collection {

        return $this->remember(

            $this->cacheKey(
                self::HEALTH,
                $filters,
            ),

            fn() => $this->reporting
                ->health($filters),
        );
    }

    public function overview(
        ProjectReportFilterData $filters,
    ): array {
          return $this->remember(

            $this->cacheKey(
                self::OVERVIEW,
                $filters,
            ),

            fn() => $this->reporting
                ->overview($filters),
        );
    }

    public function completionTrend(
        ProjectReportFilterData $filters,
    ): never {
        throw new \BadMethodCallException(
            'Completion trend reporting has not been implemented yet.'
        );
    }

    protected function ttl(): CarbonInterval
    {
        return CarbonInterval::minutes(2);
    }

    protected function cacheKey(
        string $metric,
        ProjectReportFilterData $filters,
    ): string {
        return sprintf(
            'reports.v2.%s.org-%s.workspace-%s.team-%s',
            $metric,
            $filters->organizationId ?? 'all',
            $filters->workspaceId ?? 'all',
            $filters->teamId ?? 'all',
        );
    }
}

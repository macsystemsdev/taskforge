<?php

namespace App\Services\Reporting\Cache;

use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Services\Reporting\ProjectReportingService;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProjectReportingCacheService
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
        $key = $this->cacheKey(self::HEALTH, $filters);
        
        // Check if cache exists and is valid
        if (Cache::has($key)) {
            $cached = Cache::get($key);
            
            // Check for corrupted cache
            if (is_object($cached) && get_class($cached) === '__PHP_Incomplete_Class') {
                Cache::forget($key);
                Log::warning('Corrupted cache removed for key: ' . $key);
            } elseif ($cached instanceof Collection) {
                return $cached;
            }
        }
        
        // Generate fresh data
        $data = $this->reporting->health($filters);
        
        // Store in cache
        Cache::put($key, $data, $this->ttl());
        
        return $data;
    }

    public function overview(
        ProjectReportFilterData $filters,
    ): array {
        return Cache::remember(
            $this->cacheKey(self::OVERVIEW, $filters),
            $this->ttl(),
            fn() => $this->reporting->overview($filters),
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
        return CarbonInterval::minutes(5);
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

    public function forget(ProjectReportFilterData $filters): void
    {
        Cache::forget($this->cacheKey(self::OVERVIEW, $filters));
        Cache::forget($this->cacheKey(self::HEALTH, $filters));
        Cache::forget($this->cacheKey(self::COMPLETION_TREND, $filters));
    }

    public function refresh(ProjectReportFilterData $filters): void
    {
        $this->forget($filters);
        $this->overview($filters);
        $this->health($filters);
    }
}
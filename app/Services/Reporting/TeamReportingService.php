<?php

namespace App\Services\Reporting;

use App\Data\Reporting\Team\TeamProductivityData;
use App\Data\Reporting\Team\TeamReportFilterData;
use App\Domain\Teams\Enums\TeamProductivityStatus;
use App\Models\Team;
use App\Services\Reporting\Concerns\LoadsTaskReportingCounts;
use App\Services\Team\TeamProductivityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Produces team analytics for dashboards, reports and exports.
 *
 * Responsibilities:
 * - Team KPIs
 * - Productivity reporting
 * - Completion trends
 * - Executive summaries
 *
 * Business rules remain inside TeamProductivityService.
 */
class TeamReportingService
{
    use LoadsTaskReportingCounts;

    public function __construct(
        protected TeamProductivityService $productivityService,
    ) {}

    /**
     * Build the reporting query.
     */
    protected function teamQuery(
        TeamReportFilterData $filters,
    ): Builder {

        return Team::query()

            ->when(
                $filters->organizationId,
                fn($query, $organizationId) =>
                $query->where(
                    'organization_id',
                    $organizationId
                )
            )

            ->when(
                $filters->workspaceId,
                fn($query, $workspaceId) =>
                $query->where(
                    'workspace_id',
                    $workspaceId
                )
            )

            ->when(
                $filters->teamId,
                fn($query, $teamId) =>
                $query->whereKey($teamId)
            );
    }

    /**
     * Team productivity report.
     *
     * @return Collection<int, TeamProductivityData>
     */
    public function productivity(TeamReportFilterData $filters): Collection
    {
        return $this->applyTaskReportingCounts(
            $this->teamQuery($filters),
            'team'
        )
            ->lazyById()
            ->map(fn(Team $team) => $this->productivityService->evaluate($team))
            ->collect();
    }


    /**
     * Executive summary.
     *
     * @return array<string,int>
     */
    public function overview(
        TeamReportFilterData $filters,
    ): array {

        $teams = $this->productivity($filters);

        return [

            'total_teams' => $teams->count(),

            'high_productivity' => $teams
                ->where(
                    'status',
                    TeamProductivityStatus::HIGH,
                )
                ->count(),

            'normal_productivity' => $teams
                ->where(
                    'status',
                    TeamProductivityStatus::NORMAL,
                )
                ->count(),

            'low_productivity' => $teams
                ->where(
                    'status',
                    TeamProductivityStatus::LOW,
                )
                ->count(),

            'idle_teams' => $teams
                ->where(
                    'status',
                    TeamProductivityStatus::IDLE,
                )
                ->count(),

        ];
    }

    /**
     * Completion trend.
     *
     * Phase 3.4
     */
    public function completionTrend(
        TeamReportFilterData $filters,
    ) {
        throw new \BadMethodCallException(
            'Not implemented yet.'
        );
    }
}

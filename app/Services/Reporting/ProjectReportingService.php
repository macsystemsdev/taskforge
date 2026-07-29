<?php

namespace App\Services\Reporting;


use App\Domain\Projects\Enums\ProjectHealthStatus;
use App\Models\Project;
use App\Services\Project\ProjectHealthService;
use Illuminate\Support\Collection;
use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Data\Reporting\Project\ProjectHealthData;
use Illuminate\Database\Eloquent\Builder;

/**
 * Produces project analytics for dashboards, reports and exports.
 *
 * Responsibilities:
 * - Project KPIs
 * - Completion trends
 * - Project health
 * - Executive summaries
 *
 * This service contains business calculations only.
 * UI components consume this service through DTOs.
 */
class ProjectReportingService
{

    public function __construct(
        protected ProjectHealthService $healthService,
    ) {}

    protected function projectQuery(
        ProjectReportFilterData $filters,
    ): Builder {
        return Project::query()

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
                $query->where(
                    'team_id',
                    $teamId
                )
            );
    }

    /**
     * Get project health data for reporting.
     *
     * @return Collection<int, ProjectHealthData>
     */
    public function health(
        ProjectReportFilterData $filters,
    ): Collection {

        return $this->projectQuery($filters)

            ->withCount([
                'tasks',

                'tasks as completed_tasks_count' => fn($query) =>
                $query->completed(),

                'tasks as in_progress_tasks_count' => fn($query) =>
                $query->inProgress(),

                'tasks as blocked_tasks_count' => fn($query) =>
                $query->blocked(),

                'tasks as cancelled_tasks_count' => fn($query) =>
                $query->cancelled(),

                'tasks as overdue_tasks_count' => fn($query) =>
                $query->overdue(),

                'tasks as due_soon_tasks_count' => fn($query) =>
                $query->dueSoon(),
            ])

            ->lazyById()

            ->map(
                fn(Project $project) =>
                $this->healthService->evaluate($project)
            )

            ->collect();
    }


    /**
     * Get project reporting summary.
     *
     * @return array<string,int>
     */
    public function overview(
        ProjectReportFilterData $filters,
    ): array {
        $projects = $this->health($filters);

        return [

            'total_projects' => $projects->count(),

            'healthy_projects' => $projects
                ->where(
                    'status',
                    ProjectHealthStatus::HEALTHY
                )
                ->count(),

            'at_risk_projects' => $projects
                ->where(
                    'status',
                    ProjectHealthStatus::AT_RISK
                )
                ->count(),

            'critical_projects' => $projects
                ->where(
                    'status',
                    ProjectHealthStatus::CRITICAL
                )
                ->count(),

        ];
    }
}

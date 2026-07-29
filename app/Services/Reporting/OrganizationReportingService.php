<?php

namespace App\Services\Reporting;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\Project\ProjectHealthData;
use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;
use App\Data\Reporting\ReportTableRowData;
use App\Domain\Projects\Enums\ProjectHealthStatus;
use Illuminate\Support\Collection;

/**
 * Produces organization-wide executive reporting.
 *
 * Responsibilities:
 * - Organization metrics
 * - Organization project health
 * - Executive summaries
 * - Reporting aggregation
 *
 * This service consumes domain reporting services
 * and transforms results into reporting DTOs.
 */
class OrganizationReportingService
{
    public function __construct(
        protected ProjectReportingService $projectReporting,
    ) {}


    /**
     * @return array<int, ReportMetricData>
     */
    public function overview(
        ReportFilterData $filters,
    ): array {

        $projects = $this->projectReporting
            ->health(
                $this->projectFilters($filters)
            );


        $healthSummary = $projects
            ->groupBy(
                fn(ProjectHealthData $project) =>
                $project->status->value
            )
            ->map
            ->count();


        return [

            new ReportMetricData(
                label: 'Total Projects',
                value: $projects->count(),
                description: 'Projects across organization',
                icon: 'heroicon-o-folder',
                color: 'primary',
            ),


            new ReportMetricData(
                label: 'Healthy Projects',
                value: $healthSummary[ProjectHealthStatus::HEALTHY->value] ?? 0,
                description: 'Projects operating normally',
                icon: 'heroicon-o-check-circle',
                color: 'success',
            ),


            new ReportMetricData(
                label: 'At Risk Projects',
                value: $healthSummary[ProjectHealthStatus::AT_RISK->value] ?? 0,
                description: 'Projects requiring attention',
                icon: 'heroicon-o-exclamation-triangle',
                color: 'warning',
            ),


            new ReportMetricData(
                label: 'Critical Projects',
                value: $healthSummary[ProjectHealthStatus::CRITICAL->value] ?? 0,
                description: 'Projects requiring immediate action',
                icon: 'heroicon-o-x-circle',
                color: 'danger',
            ),
        ];
    }


    /**
     * Generate organization growth trends.
     *
     * Requires historical reporting data.
     */
    public function growthTrend(
        ReportFilterData $filters,
    ): ChartSeriesData {

        throw new \BadMethodCallException(
            'Organization growth trend reporting is not implemented yet.'
        );
    }


    /**
     * @return Collection<int, ReportTableRowData>
     */
    public function health(
        ReportFilterData $filters,
    ): Collection {

        return $this->projectReporting
            ->health(
                $this->projectFilters($filters)
            )
            ->map(
                fn(ProjectHealthData $project) =>

                new ReportTableRowData(
                    title: $project->projectName,

                    columns: [
                        'health' => $project->status->value,
                        'completion' => $project->completionPercentage . '%',
                        'overdue_tasks' => $project->overdueTasks,
                    ]
                )
            );
    }


    /**
     * Convert organization reporting filters
     * into project reporting filters.
     */
    protected function projectFilters(
        ReportFilterData $filters,
    ): ProjectReportFilterData {

        return new ProjectReportFilterData(
            organizationId: $filters->organizationId,
            workspaceId: $filters->workspaceId,
            teamId: $filters->teamId,
        );
    }
}

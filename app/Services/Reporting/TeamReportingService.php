<?php

namespace App\Services\Reporting;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;

/**
 * Produces team productivity analytics.
 *
 * Responsibilities:
 * - Team KPIs
 * - Productivity trends
 * - Workload distribution
 */
class TeamReportingService
{
    /**
     * @return array<ReportMetricData>
     */
    public function overview(
        ReportFilterData $filters,
    ): array {
        return [];
    }

    public function productivityTrend(
        ReportFilterData $filters,
    ): ChartSeriesData {
        // TODO
        throw new \BadMethodCallException(
            'Not implemented yet.'
        );
    }

    public function workloadDistribution(
        ReportFilterData $filters,
    ): ChartSeriesData {
        // TODO
        throw new \BadMethodCallException(
            'Not implemented yet.'
        );
    }
}

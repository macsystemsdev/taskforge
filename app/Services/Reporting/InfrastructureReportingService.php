<?php

namespace App\Services\Reporting;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;

/**
 * Produces infrastructure capacity and platform reporting.
 */
class InfrastructureReportingService
{
    /**
     * @return array<ReportMetricData>
     */
    public function overview(
        ReportFilterData $filters,
    ): array
    {
        return [];
    }

    public function storageTrend(
        ReportFilterData $filters,
    ): ChartSeriesData
    {
        // TODO
        throw new \BadMethodCallException(
    'Not implemented yet.'
);
    }

    public function capacity(
        ReportFilterData $filters,
    ): ChartSeriesData
    {
        // TODO
        throw new \BadMethodCallException(
    'Not implemented yet.'
);
    }
}
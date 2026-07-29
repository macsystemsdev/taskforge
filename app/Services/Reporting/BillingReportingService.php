<?php

namespace App\Services\Reporting;

use App\Data\Reporting\ChartSeriesData;
use App\Data\Reporting\ReportFilterData;
use App\Data\Reporting\ReportMetricData;

/**
 * Produces commercial and subscription reporting.
 */
class BillingReportingService
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

    public function revenueTrend(
        ReportFilterData $filters,
    ): ChartSeriesData
    {
        // TODO
        throw new \BadMethodCallException(
    'Not implemented yet.'
);
    }

    public function planDistribution(
        ReportFilterData $filters,
    ): ChartSeriesData
    {
        // TODO
        throw new \BadMethodCallException(
    'Not implemented yet.'
);
    }
}
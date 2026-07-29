<?php

namespace App\Data\Reporting;

use App\Domain\Reporting\ReportingPeriod;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class ReportFilterData extends Data
{
    public function __construct(
        public ReportingPeriod $period,

        public ?Carbon $startDate = null,

        public ?Carbon $endDate = null,

        public ?int $organizationId = null,

        public ?int $workspaceId = null,

        public ?int $teamId = null,

        public ?int $projectId = null,
    ) {
    }
}

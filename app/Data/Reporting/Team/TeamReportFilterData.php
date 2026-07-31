<?php

namespace App\Data\Reporting\Team;

use Spatie\LaravelData\Data;

class TeamReportFilterData extends Data
{
    public function __construct(

        public ?int $organizationId = null,

        public ?int $workspaceId = null,

        public ?int $teamId = null,

    ) {
    }
}

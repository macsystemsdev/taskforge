<?php

namespace App\Data\Reporting\Project;

use Spatie\LaravelData\Data;

class ProjectReportFilterData extends Data
{
    public function __construct(

        public ?int $organizationId = null,

        public ?int $workspaceId = null,

        public ?int $teamId = null,

    ) {
    }
}

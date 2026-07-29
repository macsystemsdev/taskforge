<?php

namespace App\Data\Reporting\Project;

use App\Domain\Projects\Enums\ProjectHealthStatus;
use Spatie\LaravelData\Data;

class ProjectHealthData extends Data
{
    public function __construct(
        public int $projectId,

        public string $projectName,

        public string $projectSlug,

        public ProjectHealthStatus $status,

        public int $completionPercentage,

        public int $totalTasks,

        public int $completedTasks,

        public int $inProgressTasks,

        public int $blockedTasks,

        public int $overdueTasks,

        public int $dueSoonTasks,

        public ?string $reason = null,
    ) {
    }
}

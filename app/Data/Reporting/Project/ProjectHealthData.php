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
    ) {}

    /**
     * Specify which properties should be serialized when cached.
     */
    public function __sleep(): array
    {
        return [
            'projectId',
            'projectName',
            'projectSlug',
            'status',
            'completionPercentage',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'blockedTasks',
            'overdueTasks',
            'dueSoonTasks',
            'reason',
        ];
    }

    /**
     * Re-initialize properties after unserialization.
     */
    public function __wakeup(): void
    {
        // Any re-initialization needed
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'projectId' => $this->projectId,
            'projectName' => $this->projectName,
            'projectSlug' => $this->projectSlug,
            'status' => $this->status->value,
            'completionPercentage' => $this->completionPercentage,
            'totalTasks' => $this->totalTasks,
            'completedTasks' => $this->completedTasks,
            'inProgressTasks' => $this->inProgressTasks,
            'blockedTasks' => $this->blockedTasks,
            'overdueTasks' => $this->overdueTasks,
            'dueSoonTasks' => $this->dueSoonTasks,
            'reason' => $this->reason,
        ];
    }
}
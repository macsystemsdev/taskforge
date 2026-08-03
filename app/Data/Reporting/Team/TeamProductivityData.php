<?php

namespace App\Data\Reporting\Team;

use App\Domain\Teams\Enums\TeamProductivityStatus;
use Spatie\LaravelData\Data;

class TeamProductivityData extends Data
{
    public function __construct(
        public int $teamId,

        public string $teamName,

        public TeamProductivityStatus $status,

        /**
         * Productivity score (0-100).
         */
        public int $score,

        public int $memberCount,

        public int $projectCount,

        public int $totalTasks,

        public int $completedTasks,

        public ?int $inProgressTasks,

        public ?int $blockedTasks,

        public ?int $overdueTasks,

        /**
         * Percentage of completed tasks.
         */
        public int $completionPercentage,

        /**
         * Human-readable explanation for the current status.
         */
        public ?string $reason = null,
    ) {}

    /**
     * Explicit serialization for cache drivers.
     */
    public function __sleep(): array
    {
        return [
            'teamId',
            'teamName',
            'status',
            'score',
            'memberCount',
            'projectCount',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'blockedTasks',
            'overdueTasks',
            'completionPercentage',
            'reason',
        ];
    }

    /**
     * Reserved for future cache rehydration.
     */
    public function __wakeup(): void
    {
        //
    }

    public function toArray(): array
    {
        return [

            'teamId' => $this->teamId,

            'teamName' => $this->teamName,

            'status' => $this->status->value,

            'score' => $this->score,

            'memberCount' => $this->memberCount,

            'projectCount' => $this->projectCount,

            'totalTasks' => $this->totalTasks,

            'completedTasks' => $this->completedTasks,

            'inProgressTasks' => $this->inProgressTasks,

            'blockedTasks' => $this->blockedTasks,

            'overdueTasks' => $this->overdueTasks,

            'completionPercentage' => $this->completionPercentage,

            'reason' => $this->reason,
        ];
    }
}
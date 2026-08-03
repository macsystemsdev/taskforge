<?php

namespace App\Services\Team;

use App\Data\Reporting\Team\TeamProductivityData;
use App\Domain\Teams\Enums\TeamProductivityStatus;
use App\Models\Team;

class TeamProductivityService
{
    /**
     * Evaluate the overall productivity of a team.
     *
     * The reporting service is responsible for loading all required
     * relationship counts before calling this method.
     */
    public function evaluate(
        Team $team,
    ): TeamProductivityData {

        $completion = $this->completionPercentage($team);

        $score = $this->score(
            $team,
            $completion,
        );

        return new TeamProductivityData(

            teamId: $team->id,

            teamName: $team->name,

            status: $this->status($score),

            score: $score,

            memberCount: $team->members_count ?? 0,

            projectCount: $team->projects_count ?? 0,

            totalTasks: $team->tasks_count ?? 0,

            completedTasks: $team->completed_tasks_count,

            inProgressTasks: $team->in_progress_tasks_count,

            blockedTasks: $team->blocked_tasks_count,

            overdueTasks: $team->overdue_tasks_count,

            completionPercentage: $completion,

            reason: $this->reason(
                $team,
                $score,
                $completion,
            ),
        );
    }

    /**
     * Calculate task completion percentage.
     */
    protected function completionPercentage(
        Team $team,
    ): int {

        if ($team->tasks_count === 0) {
            return 0;
        }

        return (int) round(
            ($team->completed_tasks_count / $team->tasks_count) * 100
        );
    }

    /**
     * Calculate productivity score.
     *
     * Starts at 100 and deducts points for indicators
     * of reduced productivity.
     */
    protected function score(
        Team $team,
        int $completion,
    ): int {

        if ($team->tasks_count === 0) {
            return 0;
        }

        $blockedRate = $team->blocked_tasks_count
            / $team->tasks_count;

        $overdueRate = $team->overdue_tasks_count
            / $team->tasks_count;

        $inProgressRate = $team->in_progress_tasks_count
            / $team->tasks_count;

        $score = 100;

        /*
    |--------------------------------------------------------------------------
    | Completion
    |--------------------------------------------------------------------------
    */

        $score -= (100 - $completion) * 0.40;

        /*
    |--------------------------------------------------------------------------
    | Overdue tasks
    |--------------------------------------------------------------------------
    */

        $score -= $overdueRate * 35;

        /*
    |--------------------------------------------------------------------------
    | Blocked tasks
    |--------------------------------------------------------------------------
    */

        $score -= $blockedRate * 30;

        /*
    |--------------------------------------------------------------------------
    | Work in progress
    |--------------------------------------------------------------------------
    |
    | A little work in progress is healthy.
    | Too much usually means work is piling up.
    |
    */

        if ($inProgressRate > 0.60) {
            $score -= 10;
        }

        return (int) max(
            0,
            min(100, round($score))
        );
    }

    /**
     * Convert productivity score into a status.
     */
    protected function status(
        int $score,
    ): TeamProductivityStatus {

        return match (true) {

            $score >= 90
            => TeamProductivityStatus::HIGH,

            $score >= 70
            => TeamProductivityStatus::NORMAL,

            $score >= 40
            => TeamProductivityStatus::LOW,

            default
            => TeamProductivityStatus::IDLE,
        };
    }

    /**
     * Human-readable explanation.
     */
    protected function reason(
        Team $team,
        int $score,
        int $completion,
    ): ?string {

        if ($team->tasks_count === 0) {
            return 'No work assigned.';
        }

        if ($team->blocked_tasks_count > 0) {
            return 'Blocked tasks reducing productivity.';
        }

        if ($team->overdue_tasks_count > 0) {
            return 'Overdue tasks require attention.';
        }

        if ($completion < 60) {
            return 'Low task completion rate.';
        }

        if ($score >= 90) {
            return 'Consistently delivering work.';
        }

        return null;
    }
}

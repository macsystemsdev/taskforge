<?php

namespace App\Services\Project;

use App\Data\Reporting\Project\ProjectHealthData;
use App\Domain\Projects\Enums\ProjectHealthStatus;
use App\Models\Project;

/*
|--------------------------------------------------------------------------
| Project Health Evaluation
|--------------------------------------------------------------------------
|
| Evaluates the operational health of a project based on its current state.
|
| This service is the single source of truth for project health.
|
| Consumers:
| - Reporting
| - Owner Dashboard
| - Future Workspace Dashboard
| - Notifications
| - Automation Rules
|
| Future Health Rules
|--------------------------------------------------------------------------
|
| Future versions may incorporate:
|
| - Velocity trends
| - Task aging
| - Team workload
| - Milestone completion
| - Estimated vs Actual delivery
| - AI-assisted risk prediction
|
*/

class ProjectHealthService
{
    /**
     * Number of overdue tasks that immediately places a project
     * into a critical operational state.
     */
    private const CRITICAL_OVERDUE_TASKS = 3;

    /**
     * Minimum overdue tasks before a project requires attention.
     */
    private const AT_RISK_OVERDUE_TASKS = 1;

    public function evaluate(
        Project $project,
    ): ProjectHealthData {

        $status = $this->determineStatus($project);

        return new ProjectHealthData(
            projectId: $project->id,

            projectName: $project->name,

            projectSlug: $project->slug,

            status: $status,

            completionPercentage: $project->completionPercentage(),

            totalTasks: $project->totalTaskCount(),

            completedTasks: $project->completedTaskCount(),

            inProgressTasks: $project->inProgressTaskCount(),

            blockedTasks: $project->blockedTaskCount(),

            overdueTasks: $project->overdueTaskCount(),

            dueSoonTasks: $project->dueSoonTaskCount(),

            reason: $this->reason(
                $project,
                $status,
            ),
        );
    }

    protected function determineStatus(
        Project $project,
    ): ProjectHealthStatus {

        /*
        |--------------------------------------------------------------------------
        | Completed Projects
        |--------------------------------------------------------------------------
        */

        if ($project->status->isCompleted()) {
            return ProjectHealthStatus::HEALTHY;
        }

        /*
        |--------------------------------------------------------------------------
        | Project Deadline Missed
        |--------------------------------------------------------------------------
        */

        if ($project->isOverdue()) {
            return ProjectHealthStatus::CRITICAL;
        }

        /*
        |--------------------------------------------------------------------------
        | Excessive Overdue Tasks
        |--------------------------------------------------------------------------
        */

        if (
            $project->overdueTaskCount() >=
            self::CRITICAL_OVERDUE_TASKS
        ) {
            return ProjectHealthStatus::CRITICAL;
        }

        /*
        |--------------------------------------------------------------------------
        | Blocked Work
        |--------------------------------------------------------------------------
        */

        if ($project->hasBlockedTasks()) {
            return ProjectHealthStatus::AT_RISK;
        }

        /*
        |--------------------------------------------------------------------------
        | Minor Overdue Work
        |--------------------------------------------------------------------------
        */

        if (
            $project->overdueTaskCount() >=
            self::AT_RISK_OVERDUE_TASKS
        ) {
            return ProjectHealthStatus::AT_RISK;
        }

        return ProjectHealthStatus::HEALTHY;
    }

    protected function reason(
        Project $project,
        ProjectHealthStatus $status,
    ): ?string {

        return match ($status) {

            ProjectHealthStatus::HEALTHY =>
                'No operational risks detected.',

            ProjectHealthStatus::AT_RISK => $this->atRiskReason(
                $project,
            ),

            ProjectHealthStatus::CRITICAL => $this->criticalReason(
                $project,
            ),
        };
    }

    protected function atRiskReason(
        Project $project,
    ): string {

        if ($project->hasBlockedTasks()) {

            return sprintf(
                '%d blocked task(s) require attention.',
                $project->blockedTaskCount(),
            );
        }

        return sprintf(
            '%d overdue task(s) detected.',
            $project->overdueTaskCount(),
        );
    }

    protected function criticalReason(
        Project $project,
    ): string {

        if ($project->isOverdue()) {
            return 'Project deadline has passed.';
        }

        return sprintf(
            '%d overdue task(s) detected.',
            $project->overdueTaskCount(),
        );
    }
}

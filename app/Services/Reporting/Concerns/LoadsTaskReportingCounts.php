<?php

namespace App\Services\Reporting\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait LoadsTaskReportingCounts
{
    /**
     * Apply task counts for Project model (direct relationship)
     */
    protected function applyProjectTaskCounts(Builder $query): Builder
    {
        return $query->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn($q) => $q->completed(),
            'tasks as in_progress_tasks_count' => fn($q) => $q->inProgress(),
            'tasks as blocked_tasks_count' => fn($q) => $q->blocked(),
            'tasks as cancelled_tasks_count' => fn($q) => $q->cancelled(),
            'tasks as overdue_tasks_count' => fn($q) => $q->overdue(),
            'tasks as due_soon_tasks_count' => fn($q) => $q->dueSoon(),
        ]);
    }

    /**
     * Apply task counts for Team model (using subqueries with table prefixes)
     */
    protected function applyTeamTaskCounts(Builder $query): Builder
    {
        return $query
            ->withCount('projects')
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id
                ) as tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status = "completed"
                ) as completed_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status = "in_progress"
                ) as in_progress_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status = "blocked"
                ) as blocked_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status = "cancelled"
                ) as cancelled_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status IN ("todo", "in_progress", "blocked")
                    AND tasks.due_date IS NOT NULL 
                    AND tasks.due_date < datetime("now")
                ) as overdue_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    WHERE projects.team_id = teams.id 
                    AND tasks.status IN ("todo", "in_progress", "blocked")
                    AND tasks.due_date IS NOT NULL 
                    AND tasks.due_date BETWEEN datetime("now") AND datetime("now", "+3 days")
                ) as due_soon_tasks_count'
            );
    }

    /**
     * Apply task counts for Organization model (using subqueries with table prefixes)
     */
    protected function applyOrganizationTaskCounts(Builder $query): Builder
    {
        return $query
            ->withCount(['teams', 'teams.projects'])
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id
                ) as tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status = "completed"
                ) as completed_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status = "in_progress"
                ) as in_progress_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status = "blocked"
                ) as blocked_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status = "cancelled"
                ) as cancelled_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status IN ("todo", "in_progress", "blocked")
                    AND tasks.due_date IS NOT NULL 
                    AND tasks.due_date < datetime("now")
                ) as overdue_tasks_count'
            )
            ->selectRaw(
                '(
                    SELECT COUNT(*) 
                    FROM tasks 
                    JOIN projects ON projects.id = tasks.project_id 
                    JOIN teams ON teams.id = projects.team_id 
                    WHERE teams.organization_id = organizations.id 
                    AND tasks.status IN ("todo", "in_progress", "blocked")
                    AND tasks.due_date IS NOT NULL 
                    AND tasks.due_date BETWEEN datetime("now") AND datetime("now", "+3 days")
                ) as due_soon_tasks_count'
            );
    }

    /**
     * Main method to apply task counts based on model type
     */
    protected function applyTaskReportingCounts(Builder $query, string $type = 'project'): Builder
    {
        return match($type) {
            'project' => $this->applyProjectTaskCounts($query),
            'team' => $this->applyTeamTaskCounts($query),
            'organization' => $this->applyOrganizationTaskCounts($query),
            default => $this->applyProjectTaskCounts($query),
        };
    }
}
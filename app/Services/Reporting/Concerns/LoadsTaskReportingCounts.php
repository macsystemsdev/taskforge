<?php

namespace App\Services\Reporting\Concerns;

trait LoadsTaskReportingCounts
{
    /**
     * Task counts required by reporting services.
     *
     * Keeps Project, Team and Organization reporting
     * consistent and avoids duplicated withCount()
     * definitions.
     * 
     * @param string $relation The relationship path (e.g., 'tasks', 'projects.tasks')
     * @return array
     */
    protected function taskReportingCounts(string $relation = 'tasks'): array
    {
        return [
            $relation,
            
            $relation . ' as completed_tasks_count' => function ($query) {
                $query->completed();
            },
            
            $relation . ' as in_progress_tasks_count' => function ($query) {
                $query->inProgress();
            },
            
            $relation . ' as blocked_tasks_count' => function ($query) {
                $query->blocked();
            },
            
            $relation . ' as cancelled_tasks_count' => function ($query) {
                $query->cancelled();
            },
            
            $relation . ' as overdue_tasks_count' => function ($query) {
                $query->overdue();
            },
            
            $relation . ' as due_soon_tasks_count' => function ($query) {
                $query->dueSoon();
            },
        ];
    }
}

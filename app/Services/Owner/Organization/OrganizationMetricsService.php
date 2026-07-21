<?php

namespace App\Services\Owner\Organization;

use App\Domain\Task\TaskStatus;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Owner\DTO\MetricData;

class OrganizationMetricsService
{

    private function organizations(): int
    {
        return Organization::count();
    }

    private function users(): int
    {
        return User::count();
    }

    private function workspaces(): int
    {
        return Workspace::count();
    }

    private function teams(): int
    {
        return Team::count();
    }

    private function projects(): int
    {
        return Project::count();
    }

    private function tasks(): int
    {
        return Task::count();
    }

    private function averageProjectsPerOrganization(): float
    {
        $organizations = $this->organizations();

        if ($organizations === 0) {
            return 0;
        }

        return round(
            $this->projects() / $organizations,
            1
        );
    }

    private function averageTeamsPerOrganization(): float
    {
        $organizations = $this->organizations();

        if ($organizations === 0) {
            return 0;
        }

        return round(
            $this->teams() / $organizations,
            1
        );
    }

    private function averageTasksPerProject(): float
    {
        $projects = $this->projects();

        if ($projects === 0) {
            return 0;
        }

        return round(
            $this->tasks() / $projects,
            1
        );
    }

    private function averageMembersPerOrganization(): float
    {
        $organizations = $this->organizations();

        if ($organizations === 0) {
            return 0;
        }

        $members = OrganizationUser::count();

        return round(
            $members / $organizations,
            1
        );
    }

    private function projectCompletionRate(): float
    {
        // Only count projects that actually have tasks
        $projectsWithTasks = Project::has('tasks')->count();

        if ($projectsWithTasks === 0) {
            return 0;
        }

        $completedProjects = Project::has('tasks')
            ->whereDoesntHave(
                'tasks',
                fn($query) => $query->where('status', '!=', TaskStatus::DONE)
            )
            ->count();

        return round(($completedProjects / $projectsWithTasks) * 100, 1);
    }

    private function taskCompletionRate(): float
    {
        $tasks = Task::count();

        if ($tasks === 0) {
            return 0;
        }

        $completed = Task::where(
            'status',
            TaskStatus::DONE
        )->count();

        return round(
            ($completed / $tasks) * 100,
            1
        );
    }

    private function activeOrganizations(): int
    {
        return Organization::whereHas(
            'activityLogs',
            fn($query) =>
            $query->where(
                'created_at',
                '>=',
                now()->subDays(30)
            )
        )->count();
    }

    private function averageTaskCompletionPerOrganization(): float
    {
        $organizations = $this->organizations();

        if ($organizations === 0) {
            return 0;
        }

        $completedTasks =
            Task::where(
                'status',
                TaskStatus::DONE
            )->count();

        return round(
            $completedTasks /
                $organizations,
            1
        );
    }



    public function metrics(): array
    {
        return [

            'organizations' => new MetricData(label: 'Organizations', value: $this->organizations(), description: 'Registered Organizations', icon: 'heroicon-o-building-office-2', color: 'primary'),

            'users' => new MetricData(label: 'Users', value: $this->users(), description: 'Registered users on platform', color: 'info', icon: 'heroicon-o-users'),

            'workspaces' => new MetricData(label: 'Workspaces', value: $this->workspaces(), description: 'Number of workspaces on platform', color: 'primary', icon: 'heroicon-o-squares-2x2'),

            'teams' => new MetricData(label: 'Teams', value: $this->teams(), description: 'Number of teams on platform', color: 'info', icon: 'heroicon-o-user-group'),

            'projects' => new MetricData(label: 'Projects', value: $this->projects(), description: 'Number of created projects on platform', color: 'primary', icon: 'heroicon-o-folder'),

            'tasks' => new MetricData(label: 'Tasks', value: $this->tasks(), description: 'Number of created tasks on platform', color: 'gray', icon: 'heroicon-o-check-circle'),

            'averageProjectsPerOrganization' => new MetricData(
                label: 'Avg Projects / Organization',
                value: $this->averageProjectsPerOrganization(),
                description: 'Average projects created per organization',
                icon: 'heroicon-o-chart-bar',
                color: 'info',
            ),

            'averageTeamsPerOrganization' => new MetricData(
                label: 'Avg Teams / Organization',
                value: $this->averageTeamsPerOrganization(),
                description: 'Average teams per organization',
                icon: 'heroicon-o-user-group',
                color: 'info',
            ),

            'averageTasksPerProject' => new MetricData(
                label: 'Avg Tasks / Project',
                value: $this->averageTasksPerProject(),
                description: 'Average tasks created per project',
                icon: 'heroicon-o-check-circle',
                color: 'primary',
            ),

            'averageMembersPerOrganization' => new MetricData(
                label: 'Avg Members / Organization',
                value: $this->averageMembersPerOrganization(),
                description: 'Average members per organization',
                icon: 'heroicon-o-users',
                color: 'info',
            ),
            'projectCompletionRate' => new MetricData(
                label: 'Project Completion',
                value: $this->projectCompletionRate(),
                description: 'Projects fully completed',
                icon: 'heroicon-o-folder-open',
                color: 'success',
            ),

            'taskCompletionRate' => new MetricData(
                label: 'Task Completion',
                value: $this->taskCompletionRate(),
                description: 'Percentage of completed tasks',
                icon: 'heroicon-o-check-badge',
                color: 'success',
            ),

            'activeOrganizations' => new MetricData(
                label: 'Active Organizations',
                value: $this->activeOrganizations(),
                description: 'Organizations active in the last 30 days',
                icon: 'heroicon-o-fire',
                color: 'success',
            ),

            'averageTaskCompletionPerOrganization' => new MetricData(
                label: 'Avg Completed Tasks / Organization',
                value: $this->averageTaskCompletionPerOrganization(),
                description: 'Average completed tasks per organization',
                icon: 'heroicon-o-check-badge',
                color: 'success',
            ),



        ];
    }
}

<?php

namespace App\Domain\Usage\Services;

use App\Events\UsageUpdated;
use App\Models\Organization;
use App\Models\OrganizationUsage;

class OrganizationUsageService
{
    public function __construct(
        protected Organization $organization
    ) {}

    // --- Storage ---
    public function increaseStorage(int $bytes): void
    {
        $this->increment('storage_used_bytes', $bytes);
    }

    public function decreaseStorage(int $bytes): void
    {
        $this->decrement('storage_used_bytes', $bytes);
    }

    // --- Metrics ---
    public function increaseProjects(int $count = 1): void
    {
        $this->increment('projects_count', $count);
    }

    public function decreaseProjects(int $count = 1): void
    {
        $this->decrement('projects_count', $count);
    }

    public function increaseTasks(int $count = 1): void
    {
        $this->increment('tasks_count', $count);
    }

    public function decreaseTasks(int $count = 1): void
    {
        $this->decrement('tasks_count', $count);
    }

    public function increaseMembers(int $count = 1): void
    {
        $this->increment('members_count', $count);
    }

    public function decreaseMembers(int $count = 1): void
    {
        $this->decrement('members_count', $count);
    }

    public function increaseTeams(int $count = 1): void
    {
        $this->increment('teams_count', $count);
    }

    public function decreaseTeams(int $count = 1): void
    {
        $this->decrement('teams_count', $count);
    }

    public function increaseWorkspaces(int $count = 1): void
    {
        $this->increment('workspaces_count', $count);
    }

    public function decreaseWorkspaces(int $count = 1): void
    {
        $this->decrement('workspaces_count', $count);
    }

    // --- Core Helper Methods ---
    protected function increment(string $column, int $amount): void
    {
        $this->organization->usage()->firstOrCreate()->increment($column, $amount);
    }

    protected function decrement(string $column, int $amount): void
    {
        $this->organization->usage()->firstOrCreate()->decrement($column, $amount);
    }

    /**
     * Recalculate usage from actual database records.
     * Used by Artisan repair commands and scheduled background jobs.
     */
    public function recalculate(): void
    {
        // Traverses relationships and calculates hard counts from database
        $workspacesCount = $this->organization->workspaces()->count();

        // Custom deep counts across hierarchy
        $teamsCount = $this->organization->teams()->count(); // via HasManyThrough
        $projectsCount = $this->organization->projects()->count();
        $tasksCount = $this->organization
            ->projects()
            ->withCount('tasks')
            ->get()
            ->sum('tasks_count');
        $membersCount = $this->organization->members()->count();

        // Update the usage table in one single write query
        $this->organization->usage()->updateOrCreate([
            'workspaces_count' => $workspacesCount,
            'teams_count' => $teamsCount,
            'projects_count' => $projectsCount,
            'tasks_count' => $tasksCount,
            'members_count' => $membersCount,
        ]);

        // TODO: Emit UsageRecalculated event
    }
}

<?php

namespace App\Livewire;

use App\Domain\Task\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Livewire\Component;

class Dashboard extends Component
{
    public $organizations;
    public $totalWorkspaces;
    public $totalProjects;
    public $totalTasks;
    public $recentProjects;
    public $assignedTasks;
    public $dueSoonTasks;

    public function mount(): void
    {
        $user = auth()->user();

        $this->organizations = $user->organizations()
            ->withCount(["workspaces", "projects"])
            ->latest()
            ->get();

        $orgIds = $this->organizations->pluck("id");

        $this->totalWorkspaces = $this->organizations->sum("workspaces_count");
        $this->totalProjects = $this->organizations->sum("projects_count");
        $this->totalTasks = Task::whereHas("project.workspace.organization", fn ($q) => $q->whereKey($orgIds))->count();

        $this->recentProjects = Project::whereHas("workspace.organization", fn ($q) => $q->whereKey($orgIds))
            ->with("workspace")
            ->withCount("tasks")
            ->latest("updated_at")
            ->limit(5)
            ->get();

        $this->assignedTasks = Task::where("assignee_id", $user->id)
            ->whereNotIn("status", [TaskStatus::DONE->value, TaskStatus::CANCELLED->value])
            ->with("project")
            ->latest("updated_at")
            ->limit(5)
            ->get();

        $this->dueSoonTasks = Task::where("assignee_id", $user->id)
            ->whereNotIn("status", [TaskStatus::DONE->value, TaskStatus::CANCELLED->value])
            ->whereBetween("due_date", [now(), now()->addDays(7)])
            ->with("project.workspace")
            ->get();
    }

    public function render()
    {
        return view("livewire.dashboard");
    }
}

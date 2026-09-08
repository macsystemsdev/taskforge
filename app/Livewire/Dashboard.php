<?php

namespace App\Livewire;

use App\Domain\Task\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $organizations;
    public $totalOrganizations;
    public $totalWorkspaces;
    public $totalProjects;
    public $totalTasks;
    public $recentProjects;
    public $assignedTasks;

    public function mount(): void
    {
        $user = auth()->user();

        $activeOrganizations = $user->activeOrganizations()
            ->withCount(["workspaces", "projects"])
            ->latest()
            ->get();

        $orgIds = $activeOrganizations->pluck("id");

        $this->organizations = $activeOrganizations->take(5);
        $this->totalOrganizations = $activeOrganizations->count();

        $this->totalWorkspaces = $activeOrganizations->sum("workspaces_count");
        $this->totalProjects = $activeOrganizations->sum("projects_count");

        $this->totalTasks = Task::whereHas(
            "project.workspace.organization",
            fn ($q) => $q->whereKey($orgIds),
        )->count();

        $this->recentProjects = Project::whereHas(
            "workspace.organization",
            fn ($q) => $q->whereKey($orgIds),
        )
            ->with("workspace")
            ->withCount("tasks")
            ->latest("updated_at")
            ->limit(5)
            ->get();

        $this->assignedTasks = Task::where("assignee_id", $user->id)
            ->whereNotIn("status", [
                TaskStatus::DONE->value,
                TaskStatus::CANCELLED->value,
            ])
            ->whereHas("project.workspace.organization", fn ($q) => $q->whereKey($orgIds))
            ->with("project")
            ->latest("updated_at")
            ->limit(5)
            ->get();
    }

    public function getDueSoonTasksProperty()
    {
        $user = auth()->user();

        $orgIds = $user->activeOrganizations()->pluck("organizations.id");

        return Task::where("assignee_id", $user->id)
            ->whereNotIn("status", [
                TaskStatus::DONE->value,
                TaskStatus::CANCELLED->value,
            ])
            ->whereHas("project.workspace.organization", fn ($q) => $q->whereKey($orgIds))
            ->whereBetween("due_date", [now(), now()->addDays(7)])
            ->with("project.workspace")
            ->orderBy("due_date")
            ->paginate(8, pageName: "due-soon-page");
    }

    public function render()
    {
        return view("livewire.dashboard");
    }
}

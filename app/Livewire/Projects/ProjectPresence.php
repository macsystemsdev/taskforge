<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectPresence extends Component
{
    public Project $project;

    public array $users = [];

    #[On('project-presence-here')]
    public function setUsers(array $users): void
    {
        $this->users = collect($users)
            ->keyBy('id')
            ->values()
            ->all();
    }

    #[On('project-presence-joining')]
    public function addUser(array $user): void
    {
        $this->users = collect($this->users)
            ->keyBy('id')
            ->put($user['id'], $user)
            ->values()
            ->all();
    }

    #[On('project-presence-leaving')]
    public function removeUser(array $user): void
    {
        $this->users = collect($this->users)
            ->reject(
                fn (array $presenceUser) =>
                    $presenceUser['id'] === $user['id']
            )
            ->values()
            ->all();
    }

    public function render()
    {
        return view(
            'livewire.projects.project-presence'
        );
    }
}
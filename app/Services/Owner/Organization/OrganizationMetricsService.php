<?php

namespace App\Services\Owner\Organization;

use App\Models\Organization;
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

    private function freeOrganizations(): int
    {
        return Organization::whereHas(
            'currentPlan',
            fn($query) => $query->where('slug', 'free')
        )->count();
    }

    private function trialOrganizations(): int
    {
        return Organization::where('trial_ends_at', '>', now())
            ->count();
    }

    private function payingOrganizations(): int
    {
        return Organization::whereHas(
            'subscription',
            fn($query) => $query->active()
        )->count();
    }

    public function metrics(): array
    {
        return [

            'organizations' => new MetricData(label: 'Organizations', value: $this->organizations(), description: 'Registered Organizations', icon: 'heroicon-o-building-office', color: 'primary'),

            'trialOrganizations' => [
                'value' => $this->trialOrganizations(),
            ],

            'payingOrganizations' => [
                'value' => $this->payingOrganizations(),
            ],

            'freeOrganizations' => [
                'value' => $this->freeOrganizations(),
            ],

            'users' => [
                'value' => $this->users(),
            ],

            'workspaces' => [
                'value' => $this->workspaces(),
            ],

            'teams' => [
                'value' => $this->teams(),
            ],

            'projects' => [
                'value' => $this->projects(),
            ],

            'tasks' => [
                'value' => $this->tasks(),
            ],

        ];
    }
}

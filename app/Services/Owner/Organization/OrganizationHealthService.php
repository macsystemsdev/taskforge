<?php

namespace App\Services\Owner\Organization;

use App\Models\Organization;
use App\Services\Owner\DTO\OrganizationHealthData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;


/*
|--------------------------------------------------------------------------
| Phase 3 Enhancements
|--------------------------------------------------------------------------
|
| Add:
| - Failed payment detection
| - Grace period monitoring
| - Pending invitations
| - Queue failures
| - Average response time
Storage thresholds

Payment failures

Grace periods

Pending invitations

Owner inactivity
|
*/

class OrganizationHealthService
{
    public function __construct(
        protected OrganizationHealthCalculator $calculator,
    ) {}

    public function organizations(): Collection
    {
        return Organization::query()

            ->with([
                'owner',
                'subscription.plan',
                'workspaces',
                'teams',
                'projects.tasks',
                'activityLogs',
            ])

            ->get()

            ->map(
                fn($organization) =>
                $this->makeHealthData($organization)
            );
    }

    private function makeHealthData(
        Organization $organization,
    ): OrganizationHealthData {
        return new OrganizationHealthData(

            organizationId: $organization->id,

            subscriptionId: $organization->subscription?->id ?? 0,

            organizationName: $organization->name,

            owner: $organization->owner->name,

            plan: $organization
                ->subscription
                ?->plan
                ?->name
                ?? 'Free',

            members: $organization->members()->count(),

            workspaces: $organization->workspaces()->count(),

            teams: $organization->teams()->count(),

            projects: $organization->projects()->count(),

            tasks: $organization
                ->projects
                ->sum(
                    fn($project) =>
                    $project->tasks->count()
                ),

            storageUsed: $this->storageUsed($organization),

            storageLimit: $this->storageLimit($organization),

            storagePercentage: $this->storagePercentage($organization),

            lastActivity: $this->lastActivity($organization),

            trialEndsAt: $organization->subscription?->trial_ends_at, // trial_ends_at is on subscriptions table
                

            subscriptionEndsAt: $organization
                ->subscription
                ?->ends_at,

            health: $this->calculator
                ->calculate($organization),

        );
    }

    private function storageUsed(Organization $organization): float
    {
      $used = $organization
            ->usage()
            ->firstOrCreate()
            ->storage_used_bytes;
            $used = $used ? round($used / (1024 * 1024), 2) : 0; // Convert bytes to MB
        return (float) $used;
    }

    private function storageLimit(Organization $organization): float
    {
        $plan = $organization->subscription?->plan;

        if (! $plan) {
            return 0;
        }

        return (float) $plan->max_storage_mb;
    }

    private function storagePercentage(Organization $organization): float
    {
        $limit = (float) $this->storageLimit($organization);
        $used = (float) $this->storageUsed($organization);

        if ($limit <= 0 || $used === 0) {
            return 0;
        }      

        return round(
            ($used / $limit) * 100,
            1
        );
    }

    private function lastActivity(Organization $organization): ?CarbonInterface
    {
        return $organization
            ->activityLogs()
            ->latest('created_at')
            ->value('created_at');
    }
}

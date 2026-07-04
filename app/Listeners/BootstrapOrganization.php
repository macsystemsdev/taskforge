<?php

namespace App\Listeners;

use App\Domain\Billing\SubscriptionStatus;
use App\Events\OrganizationCreated;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BootstrapOrganization
{
    public function handle(OrganizationCreated $event): void
    {
        $organization = $event->organization;

        // Free subscription
        $freePlan = SubscriptionPlan::where('slug', 'free')->firstOrFail();

        $organization->subscription()->create([
            'subscription_plan_id' => $freePlan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
        ]);

        // Owner membership
        $organization->members()->attach($organization->owner_id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }
}

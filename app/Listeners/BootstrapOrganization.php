<?php

namespace App\Listeners;

use App\Domain\Billing\SubscriptionStatus;
use App\Domain\Usage\Actions\IncreaseMembersAction;
use App\Events\OrganizationCreated;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class BootstrapOrganization
{
    public function __construct(
        protected IncreaseMembersAction $increaseMemberAction
    ) {
        //
    }
    public function handle(OrganizationCreated $event): void
    {
        $organization = $event->organization;

        // Free subscription
        $freePlan = SubscriptionPlan::where('slug', 'free')->firstOrFail();

        DB::transaction(function () use ($organization, $freePlan) {
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

            $this->increaseMemberAction->handle($organization);
        });
    }
}

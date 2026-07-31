<?php

namespace App\Domain\Billing\Services;

use App\Models\Subscription;

class ActivatePendingSubscriptionsService
{
    public function handle(): void
    {
    
        Subscription::query()
            ->whereNotNull('pending_subscription_plan_id')
            ->lazyById()
            ->each(function (Subscription $subscription) {

                if (! $subscription->shouldActivatePendingPlan()) {
                    return;
                }

                $subscription->activatePendingPlan();
            });
    }
}

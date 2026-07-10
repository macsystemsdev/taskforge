<?php

namespace App\Domain\Billing\Services;

use App\Models\Subscription;

class ExpireSubscriptionsService
{
    public function __construct(
        protected DowngradeSubscriptionService $downgrade,
    ) {}

    public function handle(): void
    {
        $subscriptions = Subscription::query()
            ->active()
            ->expired()
            ->with(['plan', 'organization'])
            ->lazy();

        foreach ($subscriptions as $subscription) {

            if ($subscription->hasTrialExpired()) {

                $subscription->clearTrial();

                $subscription->activatePendingPlan();

                continue;
            }

            $this->downgrade->handle($subscription);
        }
    }
}

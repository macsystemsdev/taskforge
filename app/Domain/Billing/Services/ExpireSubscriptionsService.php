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

            if ($subscription->hasPendingPlan()) {

                $subscription->activatePendingPlan();

                continue;
            }

            if ($subscription->isInGracePeriod()) {

                if ($subscription->hasGraceExpired()) {

                    $subscription->clearGracePeriod();

                    $this->downgrade->handle(
                        $subscription
                    );
                }

                continue;
            }

            $subscription->startGracePeriod();
        }
    }
}

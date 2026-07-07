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

            $this->downgrade->handle($subscription);
        }
    }
}

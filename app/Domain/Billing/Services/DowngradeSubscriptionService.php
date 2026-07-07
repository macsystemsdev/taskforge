<?php

namespace App\Domain\Billing\Services;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class DowngradeSubscriptionService
{
    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {}

    public function handle(Subscription $subscription): void
    {

        $freePlan = SubscriptionPlan::query()
            ->where('billing_interval', BillingInterval::NONE)
            ->firstOrFail();

        if ($subscription->plan->is($freePlan)) {
            return;
        }

        $previousPlan = $subscription->plan;

        DB::transaction(function () use ($subscription, $freePlan, $previousPlan) {
            $subscription->update([
                'subscription_plan_id' => $freePlan->id,
                'status' => SubscriptionStatus::ACTIVE,
                'starts_at' => now(),
                'ends_at' => null,
            ]);

            $this->activity->handle(
                event: 'subscription_downgraded',
                subject: $subscription->organization,
                properties: [
                    'from_plan' => $previousPlan->name,
                    'to_plan' => $freePlan->name,
                ],
            );
        });
    }
}

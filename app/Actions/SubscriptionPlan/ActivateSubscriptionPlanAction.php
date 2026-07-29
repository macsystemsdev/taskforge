<?php

namespace App\Actions\SubscriptionPlan;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use DomainException;

class ActivateSubscriptionPlanAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    )
    {
    
    }
       public function handle(
        SubscriptionPlan $plan
    ): SubscriptionPlan {

        if (! $plan->canBeActivated()) {
            throw new DomainException(
                'Subscription plan cannot be activated.'
            );
        }

        return DB::transaction(function () use ($plan) {

            $plan->update([
                'status' => SubscriptionPlanStatus::ACTIVE,
                'activated_at' => now(),
            ]);

            // Activity log here

            $this->activity->handle(
                event: 'subscription_plan_activated',
                properties: [
                    'plan_name' => $plan->name,
                ],
                subject: $plan,
            );

            return $plan->refresh();
        });
    }
}
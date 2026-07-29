<?php

namespace App\Actions\SubscriptionPlan;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Models\SubscriptionPlan;
use DomainException;
use Illuminate\Support\Facades\DB;

class ArchiveSubscriptionPlanAction
{
  public function __construct(
        private CreateActivityLogAction $createActivityLogAction,
    ) {
    }

    public function handle(
        SubscriptionPlan $plan,
    ): SubscriptionPlan {

        if (! $plan->canBeArchived()) {
            throw new DomainException(
                'Subscription plan cannot be archived.'
            );
        }

        if (
            ! $plan->retirement_effective_at ||
            $plan->retirement_effective_at->isFuture()
        ) {
            throw new DomainException(
                'Subscription plan retirement period has not ended.'
            );
        }

        if ($plan->subscriptions()->active()->exists()) {
            throw new DomainException(
                'Subscription plan still has active subscriptions.'
            );
        }

        return DB::transaction(function () use ($plan) {

            $plan->update([
                'status' => SubscriptionPlanStatus::ARCHIVED,
                'archived_at' => now(),
            ]);

            $this->createActivityLogAction->handle(
                event: 'subscription_plan_archived',
                properties: [
                    'plan_name' => $plan->name,
                ],
                subject: $plan,
            );

            return $plan->refresh();
        });
    }
}

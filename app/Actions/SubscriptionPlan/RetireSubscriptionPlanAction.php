<?php

namespace App\Actions\SubscriptionPlan;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\SubscriptionPlan\RetireSubscriptionPlanData;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Models\SubscriptionPlan;
use DomainException;
use Illuminate\Support\Facades\DB;

class RetireSubscriptionPlanAction
{

    public function __construct(
        private CreateActivityLogAction $createActivityLogAction,
    ) {
    }
// TODO:
//
// Inactive plans:
//
// • cannot receive new subscriptions
// • cannot receive plan changes
// • continue serving existing subscribers
// • continue allowing renewals
//
// Enforce inside:
//
// - StartSubscriptionAction
// - ChangeSubscriptionPlanAction
// - NOT RenewSubscriptionAction

public function handle(
        SubscriptionPlan $plan,
        RetireSubscriptionPlanData $data,
    ): SubscriptionPlan {

        if (! $plan->canBeRetired()) {
            throw new DomainException(
                'Subscription plan cannot be retired.'
            );
        }

        if ($data->effectiveDate->isPast()) {
            throw new DomainException(
                'Retirement effective date must be in the future.'
            );
        }

        return DB::transaction(function () use ($plan, $data) {

            $plan->update([
                'status' => SubscriptionPlanStatus::RETIRED,
                'retirement_effective_at' => $data->effectiveDate,
                'retired_at' => now(),
            ]);

            $this->createActivityLogAction->handle(
                event: 'subscription_plan_retired',
                properties: [
                    'plan_name' => $plan->name,
                    'retirement_effective_at' => $data->effectiveDate,
                ],
                subject: $plan,
            );

            return $plan->refresh();
        });
    }
}
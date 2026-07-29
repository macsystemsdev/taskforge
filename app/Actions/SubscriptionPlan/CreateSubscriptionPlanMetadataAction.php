<?php

namespace App\Actions\SubscriptionPlan;
use App\Data\SubscriptionPlan\SubscriptionPlanMetadataData;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanMetadata;
use DomainException;

class CreateSubscriptionPlanMetadataAction
{
    public function handle(
        SubscriptionPlan $plan,
        SubscriptionPlanMetadataData $data,
    ): SubscriptionPlanMetadata {

        if ($plan->metadata()->exists()) {
            throw new DomainException(
                'Subscription plan metadata already exists.'
            );
        }

        return $plan->metadata()->create([
            'display_name' => $data->displayName,
        ]);
    }
}

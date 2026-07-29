<?php

namespace App\Actions\SubscriptionPlan;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\SubscriptionPlan\UpdateSubscriptionPlanMetadataData;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanMetadata;
use Illuminate\Support\Facades\DB;

class UpdateSubscriptionPlanMetadataAction
{
    public function __construct(
        private CreateActivityLogAction $createActivityLogAction,
    ) {}

    public function handle(
        SubscriptionPlan $plan,
        UpdateSubscriptionPlanMetadataData $data,
    ): SubscriptionPlanMetadata {

        return DB::transaction(function () use ($plan, $data) {
            $metadata = $plan->metadata;

            $metadata->update([
                'display_name' => $data->displayName,
                'subtitle' => $data->subtitle,
                'description' => $data->description,
                'badge' => $data->badge,
                'popular' => $data->popular,
                'recommended' => $data->recommended,
                'accent_color' => $data->accentColor,
                'card_order' => $data->cardOrder,
                'button_text' => $data->buttonText,
                'marketing_copy' => $data->marketingCopy,
            ]);

            $this->createActivityLogAction->handle(
                event: 'subscription_plan_metadata_updated',
                properties: [
                    'plan_name' => $plan->name,
                ],
                subject: $plan,
            );

            return $metadata->refresh();
        });
    }
}

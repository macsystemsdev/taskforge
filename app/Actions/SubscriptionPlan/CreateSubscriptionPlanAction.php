<?php

namespace App\Actions\SubscriptionPlan;

use App\Data\SubscriptionPlan\SubscriptionPlanData;
use App\Data\SubscriptionPlan\SubscriptionPlanMetadataData;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSubscriptionPlanAction
{

    public function __construct(
        private CreateSubscriptionPlanMetadataAction $createSubscriptionPlanMetadataAction,
    ) {}

    public function handle(
        SubscriptionPlanData $data
    ): SubscriptionPlan {

        return DB::transaction(function () use ($data) {

            $slug = $this->generateSlug($data->name);

            $plan = SubscriptionPlan::create([
                'name' => $data->name,
                'slug' => $slug,
                'price' => $data->price,
                'currency' => $data->currency,
                'billing_interval' => $data->billing_interval,

                'max_workspaces' => $data->max_workspaces,
                'max_projects' => $data->max_projects,
                'max_members' => $data->max_members,
                'max_teams' => $data->max_teams,
                'max_tasks' => $data->max_tasks,
                'max_storage_mb' => $data->max_storage_mb,

                'status' => SubscriptionPlanStatus::DRAFT,
            ]);

            $this->createSubscriptionPlanMetadataAction->handle(
                plan: $plan,
                data: new SubscriptionPlanMetadataData(
                     displayName: $plan->name,
                ),
            );

            return $plan->refresh();
        });
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);

        if (
            SubscriptionPlan::query()
            ->where('slug', $slug)
            ->exists()
        ) {
            throw new \DomainException(
                "A subscription plan with this name already exists."
            );
        }

        return $slug;
    }
}

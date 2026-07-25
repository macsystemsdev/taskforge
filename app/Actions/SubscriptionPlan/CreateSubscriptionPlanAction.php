<?php

namespace App\Actions\SubscriptionPlan;

use App\Data\SubscriptionPlan\SubscriptionPlanData;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class CreateSubscriptionPlanAction
{
    public function handle(
        SubscriptionPlanData $data
    ): SubscriptionPlan {
        $slug = $this->generateSlug($data->name);

        return SubscriptionPlan::create([
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

            'is_active' => true,
        ]);
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

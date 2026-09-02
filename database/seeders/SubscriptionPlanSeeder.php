<?php

namespace Database\Seeders;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::NONE,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 1,
                'max_projects' => 5,
                'max_members' => 5,
            ],
            [
                'name' => 'Pro Monthly',
                'slug' => 'pro-monthly',
                'price' => 19.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::MONTHLY,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 5,
                'max_projects' => 20,
                'max_members' => 10,
            ],
            [
                'name' => 'Team Yearly',
                'slug' => 'team-yearly',
                'price' => 199.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::YEARLY,
                'status' => SubscriptionPlanStatus::ACTIVE,
                'max_workspaces' => 10,
                'max_projects' => 50,
                'max_members' => 25,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

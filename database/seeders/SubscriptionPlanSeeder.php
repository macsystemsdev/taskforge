<?php

namespace Database\Seeders;

use App\Domain\Billing\BillingInterval;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {

        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::NONE,
                'max_workspaces' => 1,
                'max_projects' => 5,
                'max_members' => 5,
                'max_teams' => 2,
                'max_tasks' => 30,
                'is_active' => true,
            ],

            [
                'name' => 'Pro',
                'slug' => 'pro-monthly',
                'price' => 19.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::MONTHLY,
                'max_workspaces' => 10,
                'max_projects' => 100,
                'max_members' => 50,
                'max_teams' => 25,
                'max_tasks' => 5000,
                'is_active' => true,
            ],

            [
                'name' => 'Pro',
                'slug' => 'pro-yearly',
                'price' => 199.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::YEARLY,
                'max_workspaces' => 10,
                'max_projects' => 100,
                'max_teams' => 10,
                'max_tasks' => 100,
                'max_members' => 50,
                'is_active' => true,
            ],

            [
                'name' => 'Enterprise',
                'slug' => 'enterprise-monthly',
                'price' => 99.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::MONTHLY,
                'max_workspaces' => null,
                'max_projects' => null,
                'max_teams' => null,
                'max_tasks' => null,
                'max_members' => null,
                'is_active' => true,
            ],

            [
                'name' => 'Enterprise',
                'slug' => 'enterprise-yearly',
                'price' => 999.99,
                'currency' => 'USD',
                'billing_interval' => BillingInterval::YEARLY,
                'max_workspaces' => null,
                'max_projects' => null,
                'max_teams' => null,
                'max_tasks' => null,
                'max_members' => null,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                [
                    'slug' => $plan['slug'],
                ],
                $plan
            );
        }
    }
}

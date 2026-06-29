<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'billing_interval' => 'monthly',
            'max_members' => 5,
            'max_workspaces' => 1,
            'max_projects' => 5,
        ]);
        SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 19.99,
            'billing_interval' => 'monthly',
            'max_members' => 25,
            'max_workspaces' => 10,
            'max_projects' => null,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price' => 99.99,
            'billing_interval' => 'monthly',
            'max_members' => null,
            'max_workspaces' => null,
            'max_projects' => null,
        ]);
    }
}

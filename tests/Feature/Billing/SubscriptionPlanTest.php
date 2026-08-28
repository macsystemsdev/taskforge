<?php

use App\Models\SubscriptionPlan;

test('free plan is not purchasable', function () {
    $this->createBillingPlans();
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    expect($freePlan->isPurchasable())->toBeFalse();
});

test('active monthly plan is purchasable', function () {
    $this->createBillingPlans();
    $plan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    expect($plan->isPurchasable())->toBeTrue();
});

test('active yearly plan is purchasable', function () {
    $this->createBillingPlans();
    $plan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    expect($plan->isPurchasable())->toBeTrue();
});

test('retired plan is not purchasable', function () {
    $this->createBillingPlans();
    // Create a retired plan
    $retiredPlan = SubscriptionPlan::firstOrCreate(
        ['slug' => 'retired-plan'],
        [
            'name' => 'Retired Plan',
            'price' => 9.99,
            'currency' => 'USD',
            'billing_interval' => \App\Domain\Billing\BillingInterval::MONTHLY,
            'status' => \App\Domain\Billing\SubscriptionPlanStatus::RETIRED,
            'max_workspaces' => 2,
            'max_projects' => 10,
            'max_members' => 5,
        ]
    );
    
    expect($retiredPlan->isPurchasable())->toBeFalse();
});

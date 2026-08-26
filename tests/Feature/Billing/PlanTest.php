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

test('monthly plan calculates correct end date', function () {
    $this->createBillingPlans();
    $plan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $endDate = $plan->subscriptionEndsAt();
    
    expect($endDate->gt(now()->addDays(29)))->toBeTrue()
        ->and($endDate->lt(now()->addDays(32)))->toBeTrue();
});

test('yearly plan calculates correct end date', function () {
    $this->createBillingPlans();
    $plan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    
    $endDate = $plan->subscriptionEndsAt();
    
    expect($endDate->gt(now()->addMonths(11)))->toBeTrue()
        ->and($endDate->lt(now()->addMonths(13)))->toBeTrue();
});

test('trial plan returns pro-monthly', function () {
    $this->createBillingPlans();
    $trialPlan = SubscriptionPlan::trialPlan();
    expect($trialPlan->name)->toBe('Pro Monthly')
        ->and($trialPlan->slug)->toBe('pro-monthly');
});

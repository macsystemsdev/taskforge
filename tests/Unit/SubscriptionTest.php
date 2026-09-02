<?php

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('subscription trial eligibility - it does not allow a free trial when plan is paid', function () {
    $this->createBillingPlans();
    
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    $paidPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    expect($freePlan->isFree())->toBeTrue()
        ->and($paidPlan->isFree())->toBeFalse();
});

test('subscription trial eligibility - it falls back gracefully when no dedicated trial plan exists', function () {
    $this->createBillingPlans();
    
    expect(fn () => SubscriptionPlan::trialPlan())->not->toThrow(Exception::class);
    
    $plan = SubscriptionPlan::trialPlan();
    
    expect($plan->name)->toBe('Pro Monthly')
        ->and($plan->slug)->toBe('pro-monthly');
});

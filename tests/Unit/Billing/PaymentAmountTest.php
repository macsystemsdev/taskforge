<?php

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout uses correct minor unit amount', function () {
    $this->createBillingPlans();
    
    $plan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    expect((float) $plan->price)->toBe(19.99);
    
    $minorUnits = (int) round((float) $plan->price * 100);
    expect($minorUnits)->toBe(1999);
});

test('yearly plan uses correct minor unit amount', function () {
    $this->createBillingPlans();
    
    $plan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    
    expect((float) $plan->price)->toBe(199.99);
    
    $minorUnits = (int) round((float) $plan->price * 100);
    expect($minorUnits)->toBe(19999);
});

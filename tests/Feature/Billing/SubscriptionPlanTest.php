<?php

use App\Models\SubscriptionPlan;
use Tests\Feature\Billing\BillingTestCase;


test('free plan is not purchasable', function () {
    $this->createBillingPlans();
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    
    expect($freePlan->isPurchasable())->toBeFalse();
});

test('inactive plan is not purchasable', function () {
    $this->createBillingPlans();
    $inactivePlan = SubscriptionPlan::where('slug', 'inactive')->first();
    
    expect($inactivePlan->isPurchasable())->toBeFalse();
});

test('retired plan is not purchasable', function () {
    $this->createBillingPlans();
    $retiredPlan = SubscriptionPlan::where('slug', 'retired')->first();
    
    expect($retiredPlan->isPurchasable())->toBeFalse();
});

test('archived plan is not purchasable', function () {
    $this->createBillingPlans();
    $archivedPlan = SubscriptionPlan::where('slug', 'archived')->first();
    
    expect($archivedPlan->isPurchasable())->toBeFalse();
});

test('active paid plan is purchasable', function () {
    $this->createBillingPlans();
    $monthlyPlan = SubscriptionPlan::where('slug', 'team-monthly')->first();
    
    expect($monthlyPlan->isPurchasable())->toBeTrue();
});

<?php

use App\Domain\Billing\Services\StartTrialService;
use App\Exceptions\Billing\TrialUnavailableException;
use App\Models\SubscriptionPlan;

test('free subscription can start trial', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    expect($subscription->canStartTrial())->toBeTrue();
});

test('starting trial sets correct dates', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $service = app(StartTrialService::class);
    $service->handle($subscription);
    
    $subscription->refresh();
    
    $daysDiff = $subscription->trial_ends_at->diffInDays($subscription->trial_starts_at);
    $daysDiff = abs($daysDiff);
    
    expect($subscription->has_used_trial)->toBeTrue()
        ->and($subscription->trial_starts_at)->not->toBeNull()
        ->and($subscription->trial_ends_at)->not->toBeNull()
        ->and($daysDiff)->toBeGreaterThan(13)
        ->and($daysDiff)->toBeLessThan(16)
        ->and($subscription->isTrial())->toBeTrue();
});

test('trial cannot be started twice', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $service = app(StartTrialService::class);
    $service->handle($subscription);
    $subscription->refresh();
    
    expect($subscription->canStartTrial())->toBeFalse();
    
    $this->expectException(TrialUnavailableException::class);
    $service->handle($subscription);
});

test('paid subscription cannot start trial', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'ends_at' => now()->addMonth(),
    ]);
    
    expect($subscription->canStartTrial())->toBeFalse();
});

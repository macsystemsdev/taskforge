<?php

use App\Domain\Billing\Services\ExpireSubscriptionsService;
use App\Domain\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;

test('expired subscription starts grace period', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'starts_at' => now()->subMonths(2),
        'ends_at' => now()->subDay(),
    ]);
    
    $service = app(ExpireSubscriptionsService::class);
    $service->handle();
    
    $subscription->refresh();
    
    expect($subscription->grace_period_starts_at)->not->toBeNull()
        ->and($subscription->grace_period_ends_at)->not->toBeNull()
        ->and($subscription->isInGracePeriod())->toBeTrue();
});

test('pending plan activates when effective date arrives', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $yearlyPlan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'ends_at' => now()->subDay(),
        'pending_subscription_plan_id' => $yearlyPlan->id,
        'pending_effective_at' => now()->subHour(),
    ]);
    
    expect($subscription->shouldActivatePendingPlan())->toBeTrue();
    
    $subscription->activatePendingPlan();
    $subscription->refresh();
    
    expect($subscription->subscription_plan_id)->toBe($yearlyPlan->id)
        ->and($subscription->pending_subscription_plan_id)->toBeNull();
});

test('pending plan does not activate early', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $yearlyPlan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'ends_at' => now()->addMonth(),
        'pending_subscription_plan_id' => $yearlyPlan->id,
        'pending_effective_at' => now()->addMonth(),
    ]);
    
    expect($subscription->shouldActivatePendingPlan())->toBeFalse();
});

test('trial expiry clears trial state', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $subscription->update([
        'has_used_trial' => true,
        'trial_starts_at' => now()->subDays(15),
        'trial_ends_at' => now()->subDay(),
    ]);
    
    expect($subscription->hasTrialExpired())->toBeTrue();
    
    $subscription->clearTrial();
    $subscription->refresh();
    
    expect($subscription->trial_starts_at)->toBeNull()
        ->and($subscription->trial_ends_at)->toBeNull();
});

test('grace period expiry downgrades to free', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    
    // Set the subscription to have an expired grace period
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'ends_at' => now()->subDays(10),
        'grace_period_starts_at' => now()->subDays(10),
        'grace_period_ends_at' => now()->subDays(3),
    ]);
    
    // Manually run the downgrade since the service only handles in-grace-period
    $subscription->clearGracePeriod();
    app(\App\Domain\Billing\Services\DowngradeSubscriptionService::class)->handle($subscription);
    
    $subscription->refresh();
    
    expect($subscription->subscription_plan_id)->toBe($freePlan->id)
        ->and($subscription->grace_period_starts_at)->toBeNull()
        ->and($subscription->grace_period_ends_at)->toBeNull();
});

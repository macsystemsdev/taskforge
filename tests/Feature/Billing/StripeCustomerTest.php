<?php

use App\Domain\Billing\Enum\PaymentStatus;

test('organization without stripe customer can be identified', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    expect($organization->hasStripeCustomer())->toBeFalse();
});

test('organization with stripe customer can be identified', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $organization->update([
        'stripe_customer_id' => 'cus_test_123',
        'stripe_payment_method_id' => 'pm_test_123',
    ]);
    
    expect($organization->hasStripeCustomer())->toBeTrue();
});

test('latest successful transaction returns most recent', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = \App\Models\SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $oldTransaction = \App\Models\PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::SUCCESSFUL,
        'paid_at' => now()->subDays(10),
    ]);
    
    $recentTransaction = \App\Models\PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::SUCCESSFUL,
        'paid_at' => now()->subDay(),
    ]);
    
    $latest = $organization->latestSuccessfulTransaction();
    
    expect($latest->id)->toBe($recentTransaction->id);
});

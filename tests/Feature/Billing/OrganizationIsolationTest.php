<?php

use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CompletePaymentService;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('billing page for organization A shows A subscription', function () {
    $this->createBillingPlans();
    [$orgA, $owner] = $this->createOrganizationWithOwner();
    
    // Create Org B
    $orgB = \App\Models\Organization::create([
        'owner_id' => $owner->id,
        'name' => 'Org B',
        'slug' => 'org-b-' . uniqid(),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);
    $orgB->members()->attach($owner->id, ['role' => 'owner']);
    
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    // Give Org A pro plan
    $orgA->subscription->update(['subscription_plan_id' => $proPlan->id]);
    
    // Give Org B free plan
    \App\Models\Subscription::create([
        'organization_id' => $orgB->id,
        'subscription_plan_id' => $freePlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);
    
    $this->actingAs($owner);
    
    // Check Org A billing shows Pro
    $responseA = $this->get(route('organizations.billing', $orgA));
    $responseA->assertOk();
    $responseA->assertSee('Pro Monthly');
    
    // Check Org B billing shows Free
    $responseB = $this->get(route('organizations.billing', $orgB));
    $responseB->assertOk();
    $responseB->assertSee('Free');
});

test('checkout for organization A creates transaction for A not B', function () {
    $this->createBillingPlans();
    [$orgA, $owner] = $this->createOrganizationWithOwner();
    
    // Create Org B
    $orgB = \App\Models\Organization::create([
        'owner_id' => $owner->id,
        'name' => 'Org B',
        'slug' => 'org-b-' . uniqid(),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);
    $orgB->members()->attach($owner->id, ['role' => 'owner']);
    
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    \App\Models\Subscription::create([
        'organization_id' => $orgB->id,
        'subscription_plan_id' => $freePlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    // Create transaction for Org A
    $transaction = PaymentTransaction::create([
        'organization_id' => $orgA->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::PROCESSING,
    ]);
    
    expect($transaction->organization_id)->toBe($orgA->id)
        ->and($transaction->organization_id)->not->toBe($orgB->id);
    
    // Complete payment should only affect Org A
    app(CompletePaymentService::class)->handle($transaction->id);
    
    $orgA->subscription->refresh();
    $orgB->subscription->refresh();
    
    expect($orgA->subscription->subscription_plan_id)->toBe($proPlan->id)
        ->and($orgB->subscription->subscription_plan_id)->toBe($freePlan->id);
});

test('trial state is organization-specific', function () {
    $this->createBillingPlans();
    [$orgA, $owner] = $this->createOrganizationWithOwner();
    
    // Create Org B
    $orgB = \App\Models\Organization::create([
        'owner_id' => $owner->id,
        'name' => 'Org B',
        'slug' => 'org-b-' . uniqid(),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);
    $orgB->members()->attach($owner->id, ['role' => 'owner']);
    
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    $subscriptionB = \App\Models\Subscription::create([
        'organization_id' => $orgB->id,
        'subscription_plan_id' => $freePlan->id,
        'status' => 'active',
        'starts_at' => now(),
    ]);
    
    // Start trial for Org A
    $service = app(\App\Domain\Billing\Services\StartTrialService::class);
    $service->handle($orgA->subscription);
    
    // Org B should not have trial
    expect($orgA->subscription->fresh()->isTrial())->toBeTrue()
        ->and($subscriptionB->fresh()->isTrial())->toBeFalse();
});

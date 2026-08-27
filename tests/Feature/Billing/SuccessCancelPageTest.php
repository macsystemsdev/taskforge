<?php

use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;

test('success page does not mark payment successful', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::PROCESSING,
    ]);
    
    $this->actingAs($owner)
        ->get(route('billing.success', $organization))
        ->assertOk();
    
    $transaction->refresh();
    expect($transaction->status)->toBe(PaymentStatus::PROCESSING);
});

test('cancel page does not change subscription', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $originalPlanId = $organization->subscription->subscription_plan_id;
    
    $this->actingAs($owner)
        ->get(route('billing.cancel', $organization))
        ->assertOk();
    
    $organization->subscription->refresh();
    expect($organization->subscription->subscription_plan_id)->toBe($originalPlanId);
});

test('success page for organization A cannot be accessed by organization B owner', function () {
    $this->createBillingPlans();
    [$orgA, $ownerA] = $this->createOrganizationWithOwner();
    $ownerB = User::factory()->create();
    
    $orgB = Organization::create([
        'owner_id' => $ownerB->id,
        'name' => 'Org B',
        'slug' => 'org-b-' . uniqid(),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);
    $orgB->members()->attach($ownerB->id, ['role' => 'owner']);
    
    $this->actingAs($ownerB)
        ->get(route('billing.success', $orgA))
        ->assertForbidden();
});

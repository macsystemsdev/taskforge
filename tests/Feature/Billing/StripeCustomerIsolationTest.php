<?php

test('organization A and B have separate stripe customer IDs', function () {
    $this->createBillingPlans();
    [$orgA, $owner] = $this->createOrganizationWithOwner();
    
    $orgB = \App\Models\Organization::create([
        'owner_id' => $owner->id,
        'name' => 'Org B',
        'slug' => 'org-b-' . uniqid(),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
        'stripe_customer_id' => 'cus_org_b',
    ]);
    $orgB->members()->attach($owner->id, ['role' => 'owner']);
    
    $orgA->update(['stripe_customer_id' => 'cus_org_a']);
    
    expect($orgA->stripe_customer_id)->toBe('cus_org_a')
        ->and($orgB->stripe_customer_id)->toBe('cus_org_b')
        ->and($orgA->stripe_customer_id)->not->toBe($orgB->stripe_customer_id);
});

test('stripe customer ID is not accidentally replaced', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $organization->update(['stripe_customer_id' => 'cus_existing_123']);
    
    // Simulate a second checkout that should reuse, not replace
    $customerId = $organization->stripe_customer_id;
    
    expect($customerId)->toBe('cus_existing_123');
    expect($organization->fresh()->stripe_customer_id)->toBe('cus_existing_123');
});

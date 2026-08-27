<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use Livewire\Livewire;

test('non-owner cannot invoke confirmPlanChange', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $this->actingAs($admin);
    
    Livewire::test('billing.show-billing', ['organization' => $organization])
        ->set('selectedPlan', $proPlan)
        ->call('confirmPlanChange')
        ->assertForbidden();
});

test('non-owner cannot invoke startTrial', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($member);
    
    Livewire::test('billing.show-billing', ['organization' => $organization])
        ->call('startTrial')
        ->assertForbidden();
});

test('outsider cannot invoke confirmPlanChange', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $outsider = User::factory()->create();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $this->actingAs($outsider);
    
    Livewire::test('billing.show-billing', ['organization' => $organization])
        ->set('selectedPlan', $proPlan)
        ->call('confirmPlanChange')
        ->assertForbidden();
});

test('free plan cannot be confirmed through checkout', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    
    $this->actingAs($owner);
    
    // The component filters by purchasable() before findOrFail
    // So a free plan will throw ModelNotFoundException
    try {
        Livewire::test('billing.show-billing', ['organization' => $organization])
            ->set('selectedPlan', $freePlan)
            ->call('confirmPlanChange');
        
        $this->fail('Expected ModelNotFoundException');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // This is expected - free plan is not purchasable
        $this->assertTrue(true);
    }
});

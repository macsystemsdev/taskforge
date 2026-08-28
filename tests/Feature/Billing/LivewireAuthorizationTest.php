<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use Livewire\Livewire;

test('non-owner cannot access billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($admin)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('non-owner cannot access organization billing', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($member)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('outsider cannot access organization billing', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $outsider = User::factory()->create();
    
    $this->actingAs($outsider)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('owner can access billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $this->actingAs($owner)
        ->get(route('organizations.billing', $organization))
        ->assertOk();
});

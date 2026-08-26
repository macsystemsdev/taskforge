<?php

use App\Models\User;

test('owner can view billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $this->actingAs($owner)
        ->get(route('organizations.billing', $organization))
        ->assertOk();
});

test('admin cannot view billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($admin)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('member cannot view billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($member)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('outsider cannot view billing page', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $outsider = User::factory()->create();
    
    $this->actingAs($outsider)
        ->get(route('organizations.billing', $organization))
        ->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $this->get(route('organizations.billing', $organization))
        ->assertRedirect(route('login'));
});

test('owner can access success and cancel pages', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    $this->actingAs($owner)
        ->get(route('billing.success', $organization))
        ->assertOk();
    
    $this->actingAs($owner)
        ->get(route('billing.cancel', $organization))
        ->assertOk();
});

test('admin cannot access success or cancel pages', function () {
    $this->createBillingPlans();
    [$organization, $owner, $admin, $member] = $this->createOrganizationWithRoles();
    
    $this->actingAs($admin)
        ->get(route('billing.success', $organization))
        ->assertForbidden();
    
    $this->actingAs($admin)
        ->get(route('billing.cancel', $organization))
        ->assertForbidden();
});

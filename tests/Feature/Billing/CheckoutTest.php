<?php

use App\Domain\Billing\Enums\PaymentProvider;
use App\Domain\Billing\Enums\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Billing\BillingTestCase;


test('checkout creates payment transaction with correct data', function () {
    $this->createBillingPlans();
    $admin = User::factory()->create();
    $organization = $this->createOrganizationWithAdmin($admin);
    $plan = SubscriptionPlan::where('slug', 'team-monthly')->first();
    
    $this->actingAs($admin);
    
    Livewire::test('billing.checkout', ['organization' => $organization])
        ->set('selectedPlan', $plan->id)
        ->call('confirmPlanChange')
        ->assertHasNoErrors();
    
    $transaction = PaymentTransaction::where('organization_id', $organization->id)->first();
    
    expect($transaction)->not->toBeNull()
        ->and($transaction->subscription_plan_id)->toBe($plan->id)
        ->and($transaction->provider)->toBe(PaymentProvider::STRIPE->value)
        ->and($transaction->amount)->toBe($plan->price)
        ->and($transaction->currency)->toBe($plan->currency)
        ->and($transaction->status)->toBe(PaymentStatus::PROCESSING->value);
});

test('checkout rejects free plan', function () {
    $this->createBillingPlans();
    $admin = User::factory()->create();
    $organization = $this->createOrganizationWithAdmin($admin);
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    
    $this->actingAs($admin);
    
    Livewire::test('billing.checkout', ['organization' => $organization])
        ->set('selectedPlan', $freePlan->id)
        ->call('confirmPlanChange')
        ->assertHasErrors(['selectedPlan']);
});

test('checkout rejects inactive plan', function () {
    $this->createBillingPlans();
    $admin = User::factory()->create();
    $organization = $this->createOrganizationWithAdmin($admin);
    $inactivePlan = SubscriptionPlan::where('slug', 'inactive')->first();
    
    $this->actingAs($admin);
    
    Livewire::test('billing.checkout', ['organization' => $organization])
        ->set('selectedPlan', $inactivePlan->id)
        ->call('confirmPlanChange')
        ->assertHasErrors(['selectedPlan']);
});

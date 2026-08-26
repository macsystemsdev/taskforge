<?php

use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('successful transaction cannot be processed again', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::SUCCESSFUL,
        'paid_at' => now(),
    ]);
    
    // processing() should return null for already successful
    expect(PaymentTransaction::processing($transaction->id))->toBeNull();
});

test('failed transaction cannot become successful', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::FAILED,
        'failure_reason' => 'Card declined',
        'failed_at' => now(),
    ]);
    
    // processing() should return null for failed
    expect(PaymentTransaction::processing($transaction->id))->toBeNull();
    
    // CompletePaymentService should not process a failed transaction
    $service = app(\App\Domain\Billing\Services\CompletePaymentService::class);
    $service->handle($transaction->id);
    
    $transaction->refresh();
    expect($transaction->status)->toBe(PaymentStatus::FAILED);
});

test('cancelled transaction cannot become successful', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::CANCELLED,
    ]);
    
    expect(PaymentTransaction::processing($transaction->id))->toBeNull();
});

test('failed transaction stores failure reason', function () {
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
    
    $transaction->markFailed('Your card was declined');
    $transaction->refresh();
    
    expect($transaction->status)->toBe(PaymentStatus::FAILED)
        ->and($transaction->failure_reason)->toBe('Your card was declined')
        ->and($transaction->failed_at)->not->toBeNull();
});

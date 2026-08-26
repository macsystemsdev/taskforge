<?php

use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\WebhookEvent;

test('duplicate webhook event is stored only once', function () {
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
    
    $eventId = 'evt_test_123';
    
    WebhookEvent::create([
        'provider' => 'stripe',
        'event_id' => $eventId,
        'event_type' => 'checkout.session.completed',
        'processed_at' => now(),
    ]);
    
    $alreadyProcessed = WebhookEvent::where('event_id', $eventId)->exists();
    
    expect($alreadyProcessed)->toBeTrue()
        ->and(WebhookEvent::where('event_id', $eventId)->count())->toBe(1);
});

test('processing only returns transactions in processing state', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $processingTransaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::PROCESSING,
    ]);
    
    $successfulTransaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::SUCCESSFUL,
    ]);
    
    expect(PaymentTransaction::processing($processingTransaction->id))->not->toBeNull()
        ->and(PaymentTransaction::processing($successfulTransaction->id))->toBeNull()
        ->and(PaymentTransaction::processing(999999))->toBeNull();
});

test('failed transaction is marked with reason', function () {
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
    
    $transaction->markFailed('Card declined');
    $transaction->refresh();
    
    expect($transaction->status)->toBe(PaymentStatus::FAILED);
});

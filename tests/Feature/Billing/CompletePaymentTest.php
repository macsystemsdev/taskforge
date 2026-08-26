<?php

use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CompletePaymentService;
use App\Jobs\LogActivityJob;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Queue;

test('payment activates immediately for free to paid upgrade', function () {
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
    
    app(CompletePaymentService::class)->handle($transaction->id);
    
    $transaction->refresh();
    $subscription = $organization->subscription->refresh();
    
    expect($transaction->status)->toBe(PaymentStatus::SUCCESSFUL)
        ->and($transaction->paid_at)->not->toBeNull()
        ->and($subscription->subscription_plan_id)->toBe($proPlan->id)
        ->and($subscription->pending_subscription_plan_id)->toBeNull();
});

test('payment schedules plan change for paid to paid upgrade', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'ends_at' => now()->addMonth(),
    ]);
    
    $yearlyPlan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $yearlyPlan->id,
        'provider' => 'stripe',
        'amount' => $yearlyPlan->price,
        'currency' => $yearlyPlan->currency,
        'status' => PaymentStatus::PROCESSING,
    ]);
    
    app(CompletePaymentService::class)->handle($transaction->id);
    
    $subscription->refresh();
    
    expect($subscription->subscription_plan_id)->toBe($proPlan->id)
        ->and($subscription->pending_subscription_plan_id)->toBe($yearlyPlan->id)
        ->and($subscription->pending_payment_transaction_id)->toBe($transaction->id)
        ->and($subscription->pending_effective_at)->not->toBeNull();
});

test('payment clears trial', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $subscription = $organization->subscription;
    
    $subscription->update([
        'has_used_trial' => true,
        'trial_starts_at' => now(),
        'trial_ends_at' => now()->addDays(14),
    ]);
    
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $transaction = PaymentTransaction::create([
        'organization_id' => $organization->id,
        'subscription_plan_id' => $proPlan->id,
        'provider' => 'stripe',
        'amount' => $proPlan->price,
        'currency' => $proPlan->currency,
        'status' => PaymentStatus::PROCESSING,
    ]);
    
    app(CompletePaymentService::class)->handle($transaction->id);
    
    $subscription->refresh();
    
    expect($subscription->trial_starts_at)->toBeNull()
        ->and($subscription->trial_ends_at)->toBeNull();
});

test('duplicate successful payment is ignored', function () {
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
    
    Queue::fake();
    
    $service = app(CompletePaymentService::class);
    $service->handle($transaction->id);
    $service->handle($transaction->id);
    
    // Assert only one activity job was dispatched
    Queue::assertPushed(LogActivityJob::class, 1);
});

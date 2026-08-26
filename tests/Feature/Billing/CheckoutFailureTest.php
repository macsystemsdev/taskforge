<?php

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Exceptions\Billing\CannotPurchaseFreePlanException;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionChangeAlreadyScheduledException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;
use App\Models\SubscriptionPlan;

test('checkout rejects free plan', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $freePlan = SubscriptionPlan::where('slug', 'free')->first();
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $freePlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    // The service checks isPurchasable() first, which excludes free plans
    // So it throws SubscriptionPlanInactiveException
    $this->expectException(SubscriptionPlanInactiveException::class);
    $service->handle($data);
});

test('checkout rejects already active plan', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    // Set current plan to pro
    $organization->subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'ends_at' => now()->addMonth(),
    ]);
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $proPlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    $this->expectException(SubscriptionAlreadyActiveException::class);
    $service->handle($data);
});

test('checkout rejects when pending plan exists', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    $yearlyPlan = SubscriptionPlan::where('slug', 'team-yearly')->first();
    
    // Set up pending plan
    $organization->subscription->update([
        'subscription_plan_id' => $proPlan->id,
        'ends_at' => now()->addMonth(),
        'pending_subscription_plan_id' => $yearlyPlan->id,
        'pending_effective_at' => now()->addMonth(),
    ]);
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $yearlyPlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    $this->expectException(SubscriptionChangeAlreadyScheduledException::class);
    $service->handle($data);
});

test('checkout rejects retired plan', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    
    // Create a retired plan
    $retiredPlan = SubscriptionPlan::create([
        'name' => 'Old Plan',
        'slug' => 'old-plan',
        'price' => 9.99,
        'currency' => 'USD',
        'billing_interval' => \App\Domain\Billing\BillingInterval::MONTHLY,
        'status' => \App\Domain\Billing\SubscriptionPlanStatus::RETIRED,
        'max_workspaces' => 2,
        'max_projects' => 10,
        'max_members' => 5,
    ]);
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $retiredPlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    $this->expectException(SubscriptionPlanInactiveException::class);
    $service->handle($data);
});

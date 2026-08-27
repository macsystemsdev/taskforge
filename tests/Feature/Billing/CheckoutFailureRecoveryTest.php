<?php

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\DataTransferObjects\PaymentResponse;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('checkout failure marks transaction as failed', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $failingGateway = new class implements PaymentGateway {
        public function createCheckout(CheckoutData $data, PaymentTransaction $transaction): CheckoutResponse
        {
            throw new \Exception('Stripe API unavailable');
        }
        
        public function chargeCustomer(PaymentTransaction $transaction): PaymentResponse
        {
            throw new \Exception('Stripe API unavailable');
        }
    };
    
    $this->app->instance(PaymentGateway::class, $failingGateway);
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $proPlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    try {
        $service->handle($data);
        $this->fail('Expected exception');
    } catch (\Exception $e) {
        // Expected
    }
    
    $transaction = PaymentTransaction::where('organization_id', $organization->id)->first();
    
    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe(PaymentStatus::FAILED)
        ->and($transaction->failure_reason)->toBe('Stripe API unavailable');
});

test('stripe customer creation failure is handled', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $failingGateway = new class implements PaymentGateway {
        public function createCheckout(CheckoutData $data, PaymentTransaction $transaction): CheckoutResponse
        {
            throw new \App\Exceptions\Billing\BillingCustomerException('Cannot create Stripe customer');
        }
        
        public function chargeCustomer(PaymentTransaction $transaction): PaymentResponse
        {
            throw new \App\Exceptions\Billing\BillingCustomerException('Cannot create Stripe customer');
        }
    };
    
    $this->app->instance(PaymentGateway::class, $failingGateway);
    
    $data = new CheckoutData(
        organization: $organization,
        plan: $proPlan,
        provider: PaymentProvider::STRIPE,
    );
    
    $service = app(CreateCheckoutService::class);
    
    try {
        $service->handle($data);
        $this->fail('Expected BillingCustomerException');
    } catch (\App\Exceptions\Billing\BillingCustomerException $e) {
        expect($e->getMessage())->toBe('Cannot create Stripe customer');
    }
    
    $subscription = $organization->subscription->fresh();
    expect($subscription->subscription_plan_id)->not->toBe($proPlan->id);
});

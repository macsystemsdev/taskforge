<?php

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\DataTransferObjects\PaymentResponse;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Domain\Billing\Services\PaymentGatewayResolver;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('checkout failure does not leave orphaned transaction', function () {
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
    
    $resolver = new class($failingGateway) extends PaymentGatewayResolver {
        public function __construct(private PaymentGateway $gateway) {}
        
        public function resolve(PaymentProvider $provider): PaymentGateway
        {
            return $this->gateway;
        }
    };
    
    $this->app->instance(PaymentGatewayResolver::class, $resolver);
    
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
        expect($e->getMessage())->toBe('Stripe API unavailable');
    }
    
    // Transaction should not exist (rolled back)
    $transactionCount = PaymentTransaction::where('organization_id', $organization->id)->count();
    expect($transactionCount)->toBe(0);
    
    // Subscription should remain unchanged
    $subscription = $organization->subscription->fresh();
    expect($subscription->subscription_plan_id)->not->toBe($proPlan->id);
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
    
    $resolver = new class($failingGateway) extends PaymentGatewayResolver {
        public function __construct(private PaymentGateway $gateway) {}
        
        public function resolve(PaymentProvider $provider): PaymentGateway
        {
            return $this->gateway;
        }
    };
    
    $this->app->instance(PaymentGatewayResolver::class, $resolver);
    
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

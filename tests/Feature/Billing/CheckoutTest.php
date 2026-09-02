<?php

use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('checkout creates payment transaction with correct data', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();
    $plan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
    
    $this->actingAs($owner);
    
    // Call the checkout service directly
    $service = app(\App\Domain\Billing\Services\CreateCheckoutService::class);
    
    // Use a fake gateway resolver
    $resolver = new class extends \App\Domain\Billing\Services\PaymentGatewayResolver {
        public function resolve(PaymentProvider $provider): \App\Contracts\Billing\PaymentGateway
        {
            return new class implements \App\Contracts\Billing\PaymentGateway {
                public function createCheckout(\App\Domain\Billing\DataTransferObjects\CheckoutData $data, PaymentTransaction $transaction): \App\Domain\Billing\DataTransferObjects\CheckoutResponse
                {
                    return new \App\Domain\Billing\DataTransferObjects\CheckoutResponse(
                        url: 'https://fake-checkout.test',
                        reference: 'cs_fake',
                        metadata: [],
                    );
                }
                
                public function chargeCustomer(PaymentTransaction $transaction): \App\Domain\Billing\DataTransferObjects\PaymentResponse
                {
                    return \App\Domain\Billing\DataTransferObjects\PaymentResponse::successful('pi_fake', []);
                }
            };
        }
    };
    
    $this->app->instance(\App\Domain\Billing\Services\PaymentGatewayResolver::class, $resolver);
    
    $data = new \App\Domain\Billing\DataTransferObjects\CheckoutData(
        organization: $organization,
        plan: $plan,
        provider: PaymentProvider::STRIPE,
    );
    
    $response = $service->handle($data);
    
    $transaction = PaymentTransaction::where('organization_id', $organization->id)->first();
    
    expect($transaction)->not->toBeNull()
        ->and($transaction->subscription_plan_id)->toBe($plan->id)
        ->and($transaction->provider)->toBe(PaymentProvider::STRIPE)
        ->and((float) $transaction->amount)->toBe((float) $plan->price)
        ->and($transaction->currency)->toBe($plan->currency)
        ->and($transaction->status)->toBe(PaymentStatus::PROCESSING);
});

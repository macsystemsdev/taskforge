<?php

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\DataTransferObjects\PaymentResponse;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Services\CreateCheckoutService;
use App\Infrastructure\Billing\StripePaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;

test('checkout creates payment transaction with correct data', function () {
    $this->createBillingPlans();
    [$organization, $owner] = $this->createOrganizationWithOwner();

    $plan = SubscriptionPlan::where('slug', 'pro-monthly')->first();

    // Fake the Stripe gateway to avoid real API calls
    $fakeGateway = new class implements PaymentGateway {
        public function createCheckout($data, $transaction): CheckoutResponse
        {
            return new CheckoutResponse(
                url: 'https://fake-checkout.test',
                reference: 'cs_test_fake',
                metadata: [],
            );
        }

        public function chargeCustomer($transaction): PaymentResponse
        {
            return PaymentResponse::successful('pi_test_fake');
        }
    };

    $this->app->instance(StripePaymentGateway::class, $fakeGateway);

    $service = app(CreateCheckoutService::class);

    $checkoutData = new CheckoutData(
        organization: $organization,
        plan: $plan,
        provider: PaymentProvider::STRIPE,
    );

    $service->handle($checkoutData);

    $transaction = PaymentTransaction::where('organization_id', $organization->id)->first();

    expect($transaction)
        ->not->toBeNull()
        ->and($transaction->subscription_plan_id)->toBe($plan->id)
        ->and($transaction->provider->value)->toBe(PaymentProvider::STRIPE->value)
        ->and((float) $transaction->amount)->toEqual((float) $plan->price)
        ->and($transaction->currency)->toBe($plan->currency)
        ->and($transaction->status->value)->toBe('processing');
});

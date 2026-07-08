<?php

namespace App\Infrastructure\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\DataTransferObjects\PaymentResponse;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{

    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            config('services.stripe.secret')
        );
    }

    protected function successUrl(Organization $organization): string
    {
        return route('billing.success', $organization);
    }

    protected function cancelUrl(Organization $organization): string
    {
        return route('billing.cancel', $organization);
    }

    protected function getOrCreateCustomer(
        Organization $organization,
    ): string {
        if ($organization->hasStripeCustomer()) {
            return $organization->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'name' => $organization->name,
            'metadata' => [
                'organization_id' => $organization->id,
            ],
        ]);

        $organization->update([
            'stripe_customer_id' => $customer->id,
        ]);

        return $customer->id;
    }


    public function createCheckout(CheckoutData $data, PaymentTransaction $transaction): CheckoutResponse
    {


        $customerId = $this->getOrCreateCustomer(
            $data->organization,
        );

        $session = $this->stripe->checkout->sessions->create([

            'mode' => 'payment',

            'customer' => $customerId,

            'success_url' => $this->successUrl($data->organization),

            'cancel_url' => $this->cancelUrl($data->organization),

            'line_items' => [[

                'price_data' => [

                    'currency' => strtolower($data->plan->currency),

                    'product_data' => [

                        'name' => "{$data->plan->name} Plan",
                        'description' => sprintf(
                            '%s billing • %s workspaces • %s projects • %s members',
                            $data->plan->billingIntervalLabel(),
                            $data->plan->workspaceLimitLabel(),
                            $data->plan->projectLimitLabel(),
                            $data->plan->memberLimitLabel(),
                        ),
                    ],

                    'unit_amount' => (int) ($data->plan->price * 100),

                ],

                'quantity' => 1,

            ]],

            'payment_intent_data' => [

                'setup_future_usage' => 'off_session',

            ],

            'metadata' => [
                'payment_transaction_id' => $transaction->id,
                'organization_id' => $transaction->organization_id,
                'subscription_plan_id' => $transaction->subscription_plan_id,
            ]

        ]);

        $transaction->update([
            'status' => PaymentStatus::PROCESSING,
            'provider_reference' => $session->id,
        ]);

        return new CheckoutResponse(

            url: $session->url,
            reference: $session->id,
            metadata: [
                'customer' => $customerId,
            ],

        );
    }

    public function chargeCustomer(
        CheckoutData $data,
        PaymentTransaction $transaction,
    ): PaymentResponse {
        throw new \LogicException(
            'Stripe renewal payments are not implemented yet.'
        );
    }
}

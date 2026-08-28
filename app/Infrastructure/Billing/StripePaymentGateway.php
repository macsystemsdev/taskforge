<?php

namespace App\Infrastructure\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\DataTransferObjects\PaymentResponse;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Exceptions\Billing\BillingCustomerException;
use App\Exceptions\Billing\BillingException;
use App\Exceptions\Billing\MissingPaymentMethodException;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
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

        // Use database transaction with lock to prevent concurrent customer creation
        return DB::transaction(function () use ($organization) {
            // Re-check after lock
            $lockedOrg = Organization::whereKey($organization->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrg->hasStripeCustomer()) {
                return $lockedOrg->stripe_customer_id;
            }

            $customer = $this->stripe->customers->create([
                'name' => $lockedOrg->name,
                'metadata' => [
                    'organization_id' => $lockedOrg->id,
                ],
            ]);

            $lockedOrg->update([
                'stripe_customer_id' => $customer->id,
            ]);

            return $customer->id;
        });
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
        PaymentTransaction $transaction,
    ): PaymentResponse {

        $organization = $transaction->organization;

        if (
            ! $organization->stripe_customer_id
        ) {
            throw new BillingCustomerException(
                'Missing Stripe billing information.'
            );
        }

        if (
            ! $organization
                ->stripe_payment_method_id
        ) {
            throw new MissingPaymentMethodException();
        }

        try {
            $intent = $this->stripe
                ->paymentIntents
                ->create([

                    'customer' =>
                    $organization
                        ->stripe_customer_id,

                    'payment_method' =>
                    $organization
                        ->stripe_payment_method_id,

                    'amount' =>
                    $transaction->amount,

                    'currency' =>
                    strtolower(
                        $transaction->currency
                    ),

                    'off_session' => true,

                    'confirm' => true,

                    'metadata' => [

                        'payment_transaction_id' =>
                        $transaction->id,

                    ],
                ]);
        } catch (ApiErrorException $e) {

            report($e);

            $transaction->markFailed(
                reason: $e->getMessage()
            );

            return PaymentResponse::failed(
                $e->getMessage()
            );
        }

        return PaymentResponse::successful(
            reference: $intent->id,
            metadata: [
                'customer' => $organization->stripe_customer_id,
                'payment_method' => $organization->stripe_payment_method_id,
            ],
        );
    }
}

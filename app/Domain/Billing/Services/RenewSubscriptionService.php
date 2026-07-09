<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Models\Subscription;

class RenewSubscriptionService
{
    public function __construct(
        protected PaymentGateway $gateway,
        protected CreatePaymentTransactionAction $createPayment
    ) {}

    public function handle(
        Subscription $subscription,
    ): void {

        $plan = $subscription->plan;

        $data =  new CheckoutData(
            organization: $subscription->organization,
            plan: $plan,
            provider: $subscription->renewalProvider(),
        );

        $transaction = $this->createPayment->handle($data);

        $this->gateway->chargeCustomer(
            transaction: $transaction,
        );
    }
}
<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CreatePaymentTransactionData;
use App\Domain\Billing\Enum\PaymentProvider;;
use App\Models\Subscription;


class RenewSubscriptionsService
{
    public function __construct(
        protected PaymentGateway $gateway,
        protected CreatePaymentTransactionAction $createPayment
    ) {}
    public function handle(): void
    {
        Subscription::query()
            ->active()
            ->with([
                'plan',
                'organization',
                'pendingPlan',
            ])
            ->lazy()
            ->each(function (Subscription $subscription) {

                if (! $subscription->shouldRenew()) {
                    return;
                }

                $plan = $subscription->renewalPlan();
                $provider = $subscription->renewalProvider();
                $data =  new CheckoutData(
                        organization: $subscription->organization,
                        plan: $plan,
                        provider: $provider,
                    );
                $transaction = $this->createPayment->handle($data);

                $this->gateway->chargeCustomer(
                    data: $data,
                    transaction: $transaction,
                );
            });
    }
}

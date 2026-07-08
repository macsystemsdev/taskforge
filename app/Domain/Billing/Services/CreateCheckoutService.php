<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;
use App\Models\PaymentTransaction;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Exceptions\Billing\CannotPurchaseFreePlanException;
use App\Exceptions\Billing\SubscriptionChangeAlreadyScheduledException;

class CreateCheckoutService
{
    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected CreatePaymentTransactionAction $createPaymentTransaction,
    ) {}

    public function handle(
        CheckoutData $data,
    ): CheckoutResponse {

        $this->ensurePlanCanBeChanged($data);

        $transaction = $this->createPaymentTransaction->handle(
            data: $data,
        );

        $response = $this->paymentGateway->createCheckout($data, $transaction);

        $transaction->update([
            'provider_reference' => $response->reference,
            'metadata' => $response->metadata,
        ]);

        return $response;
    }

    protected function ensurePlanCanBeChanged(
        CheckoutData $data,
    ): void {

        if (! $data->plan->is_active) {
            throw new SubscriptionPlanInactiveException();
        }

        if ($data->organization->isSubscribedTo($data->plan)) {
            throw new SubscriptionAlreadyActiveException();
        }

        if ($data->plan->isFree()) {
            throw new CannotPurchaseFreePlanException();
        }

        $subscription = $data->organization->subscription;

        if ($subscription->hasPendingPlan()) {
            throw new SubscriptionChangeAlreadyScheduledException();
        }
    }
}

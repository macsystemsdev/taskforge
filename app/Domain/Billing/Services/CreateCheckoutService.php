<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Exceptions\Billing\CannotPurchaseFreePlanException;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionChangeAlreadyScheduledException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;
use Illuminate\Support\Facades\Log;

class CreateCheckoutService
{
    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected CreatePaymentTransactionAction $createPaymentTransaction,
    ) {}

    public function handle(CheckoutData $data): CheckoutResponse
    {
        $this->ensurePlanCanBeChanged($data);

        $transaction = $this->createPaymentTransaction->handle(data: $data);

        try {
            $response = $this->paymentGateway->createCheckout($data, $transaction);

            $transaction->update([
                'provider_reference' => $response->reference,
                'metadata' => $response->metadata,
            ]);

            return $response;
        } catch (\Exception $e) {
            // Mark the transaction as failed so it doesn't remain PROCESSING forever
            $transaction->markFailed(
                reason: $e->getMessage()
            );

            Log::error('Checkout creation failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function ensurePlanCanBeChanged(CheckoutData $data): void
    {
        // Check free plan FIRST (more specific exception)
        if ($data->plan->isFree()) {
            throw new CannotPurchaseFreePlanException();
        }

        // Then check if purchasable
        if (!$data->plan->isPurchasable()) {
            throw new SubscriptionPlanInactiveException();
        }

        // Then check if already subscribed
        if ($data->organization->isSubscribedTo($data->plan)) {
            throw new SubscriptionAlreadyActiveException();
        }

        // Then check pending plan
        $subscription = $data->organization->subscription;

        if ($subscription->hasPendingPlan()) {
            throw new SubscriptionChangeAlreadyScheduledException();
        }
    }
}

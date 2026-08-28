<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Actions\CreatePaymentTransactionAction;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Exceptions\Billing\CannotPurchaseFreePlanException;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionChangeAlreadyScheduledException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateCheckoutService
{
    public function __construct(
        protected PaymentGatewayResolver $gatewayResolver,
        protected CreatePaymentTransactionAction $createPaymentTransaction,
    ) {}

    public function handle(CheckoutData $data): CheckoutResponse
    {
        $this->ensurePlanCanBeChanged($data);

        return DB::transaction(function () use ($data) {
            // Lock the subscription row to prevent concurrent checkouts
            $subscription = $data->organization->subscription()->lockForUpdate()->firstOrFail();

            if ($subscription->hasPendingPlan()) {
                throw new SubscriptionChangeAlreadyScheduledException();
            }

            // Generate a UUID as the idempotency key for this checkout attempt
            $idempotencyKey = (string) Str::uuid();

            $transaction = $this->createPaymentTransaction->handle(
                data: $data,
                idempotencyKey: $idempotencyKey,
            );

            try {
                $gateway = $this->gatewayResolver->resolve($data->provider);
                $response = $gateway->createCheckout($data, $transaction);

                $transaction->update([
                    'provider_reference' => $response->reference,
                    'metadata' => $response->metadata,
                ]);

                return $response;
            } catch (\Exception $e) {
                $transaction->markFailed(reason: $e->getMessage());
                Log::error('Checkout creation failed', [
                    'transaction_id' => $transaction->id,
                    'provider' => $data->provider->value,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    protected function ensurePlanCanBeChanged(CheckoutData $data): void
    {
        if ($data->plan->isFree()) {
            throw new CannotPurchaseFreePlanException();
        }

        if (!$data->plan->isPurchasable()) {
            throw new SubscriptionPlanInactiveException();
        }

        if ($data->organization->isSubscribedTo($data->plan)) {
            throw new SubscriptionAlreadyActiveException();
        }
    }
}

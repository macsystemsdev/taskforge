<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Exceptions\Billing\SubscriptionAlreadyActiveException;
use App\Exceptions\Billing\SubscriptionPlanInactiveException;
use App\Models\PaymentTransaction;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;

class CreateCheckoutService
{
    public function __construct(
        protected PaymentGateway $paymentGateway,
    ) {}

    public function handle(
        CheckoutData $data,
    ): CheckoutResponse {
        
        $this->ensurePlanCanBeChanged($data);

        $transaction = PaymentTransaction::create([
            'organization_id' => $data->organization->id,
            'subscription_plan_id' => $data->plan->id,
            'provider' => $data->provider,
            'amount' => $data->plan->price,
            'currency' => $data->plan->currency,
            'status' => PaymentStatus::PENDING,
        ]);

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
    }
}

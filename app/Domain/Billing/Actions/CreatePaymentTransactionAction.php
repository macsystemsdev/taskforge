<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CreatePaymentTransactionData;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\PaymentTransaction;

class CreatePaymentTransactionAction
{
    public function handle(
        CheckoutData $data,
    ): PaymentTransaction {

        return PaymentTransaction::create([

            'organization_id' => $data->organization->id,

            'subscription_plan_id' => $data->plan->id,

            'provider' => $data->provider,

            'amount' => $data->plan->price,

            'currency' => $data->plan->currency,

            'status' => PaymentStatus::PROCESSING,

        ]);
    }
}

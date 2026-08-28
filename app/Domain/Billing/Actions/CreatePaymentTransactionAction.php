<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

class CreatePaymentTransactionAction
{
    public function handle(CheckoutData $data, ?string $idempotencyKey = null): PaymentTransaction
    {
        // Client-generated idempotency key or fallback to a UUID
        $idempotencyKey = $idempotencyKey ?? (string) Str::uuid();

        // Check if a transaction with this key already exists
        $existing = PaymentTransaction::where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            return $existing;
        }

        return PaymentTransaction::create([
            'idempotency_key' => $idempotencyKey,
            'organization_id' => $data->organization->id,
            'subscription_plan_id' => $data->plan->id,
            'provider' => $data->provider,
            'amount' => $data->plan->price,
            'currency' => $data->plan->currency,
            'status' => PaymentStatus::PROCESSING,
        ]);
    }
}

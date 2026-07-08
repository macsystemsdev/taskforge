<?php

namespace App\Domain\Billing\DataTransferObjects;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use Spatie\LaravelData\Data;

class CreatePaymentTransactionData extends Data
{
    public function __construct(
        public Organization $organization,
        public SubscriptionPlan $plan,
        public PaymentProvider $provider,
    ) {}
}

<?php

namespace App\Domain\Billing\DataTransferObjects;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Models\Organization;
use App\Models\SubscriptionPlan;

readonly class CheckoutData
{
    public function __construct(
        public Organization $organization,
        public SubscriptionPlan $plan,
        public PaymentProvider $provider,
    ) {}
}

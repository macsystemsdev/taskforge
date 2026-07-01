<?php

namespace App\Contracts\Billing;

use App\Models\Organization;
use App\Models\SubscriptionPlan;

interface PaymentGateway
{
    public function createCheckout(
        Organization $organization,
        SubscriptionPlan $plan,
    ): string;
}

<?php

namespace App\Infrastructure\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Models\Organization;
use App\Models\SubscriptionPlan;

class StripePaymentGateway implements PaymentGateway
{
    public function createCheckout(
        Organization $organization,
        SubscriptionPlan $plan,
    ): string {
        // Stripe implementation tomorrow.
        return '#';
    }
}

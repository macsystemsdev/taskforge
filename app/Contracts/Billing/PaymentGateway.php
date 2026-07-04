<?php

namespace App\Contracts\Billing;

use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Models\PaymentTransaction;

interface PaymentGateway
{
    /**
     * Creates a checkout session with the payment provider.
     *
     * Returns a URL the customer should be redirected to.
     */
    public function createCheckout(CheckoutData $data, PaymentTransaction $transaction): CheckoutResponse;
}

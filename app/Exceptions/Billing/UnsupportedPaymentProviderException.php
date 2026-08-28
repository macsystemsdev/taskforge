<?php

namespace App\Exceptions\Billing;

class UnsupportedPaymentProviderException extends SubscriptionException
{
    protected $message = 'This payment provider is not available yet.';
}

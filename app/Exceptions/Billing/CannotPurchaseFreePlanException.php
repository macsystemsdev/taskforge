<?php

namespace App\Exceptions\Billing;

class CannotPurchaseFreePlanException extends SubscriptionException
{
    protected $message = 'The free plan cannot be purchased. Please select a paid plan.';
}

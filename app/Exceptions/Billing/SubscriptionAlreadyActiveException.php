<?php

namespace App\Exceptions\Billing;

class SubscriptionAlreadyActiveException extends SubscriptionException
{
    protected $message = 'Your organization is already subscribed to this plan.';
}

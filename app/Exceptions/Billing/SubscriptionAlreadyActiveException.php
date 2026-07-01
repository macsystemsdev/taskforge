<?php

namespace App\Exceptions\Billing;


class SubscriptionAlreadyActiveException extends SubscriptionException
{
    protected $message = 'Organization is already subscribed to this plan.';
}

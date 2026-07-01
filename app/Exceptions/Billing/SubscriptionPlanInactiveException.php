<?php

namespace App\Exceptions\Billing;


class SubscriptionPlanInactiveException extends SubscriptionException
{
    protected $message = 'This subscription plan is no longer available.';
}

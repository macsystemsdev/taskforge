<?php

namespace App\Exceptions\Billing;

class SubscriptionChangeAlreadyScheduledException extends SubscriptionException
{
    protected $message = 'A subscription change is already scheduled. Please wait for it to complete before making another change.';
}

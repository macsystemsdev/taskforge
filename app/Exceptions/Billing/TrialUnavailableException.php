<?php

namespace App\Exceptions\Billing;

class TrialUnavailableException extends SubscriptionException
{
    protected $message = 'Free trial is no longer available for your organization.';
}

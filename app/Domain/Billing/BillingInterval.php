<?php

namespace App\Domain\Billing;

enum BillingInterval: string
{
    case NONE = 'none';

    case MONTHLY = 'monthly';

    case YEARLY = 'yearly';
}


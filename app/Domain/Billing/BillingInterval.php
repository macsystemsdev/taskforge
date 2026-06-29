<?php

namespace App\Domain\Billing;

enum BillingInterval: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}


<?php

namespace App\Domain\Billing;

enum BillingInterval: string
{
    case NONE = 'none';

    case MONTHLY = 'monthly';

    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => 'None',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }
}

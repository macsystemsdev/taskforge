<?php

namespace App\Domain\Billing\Enums;

enum SupportedCurrency: string
{
    case USD = 'USD';
    case XAF = 'XAF';
    case EUR = 'EUR';
    case GBP = 'GBP';

    public function label(): string
    {
        return $this->value;
    }
}

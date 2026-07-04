<?php

namespace App\Domain\Billing\Enum;

enum PaymentProvider: string
{
    case STRIPE = 'stripe';
    case MTN = 'mtn';
    case ORANGE = 'orange';

    public function label(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::MTN => 'MTN Mobile Money',
            self::ORANGE => 'Orange Money',
        };
    }
}

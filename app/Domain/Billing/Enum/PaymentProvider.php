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

    public function description(): string
    {
        return match ($this) {
            self::STRIPE => 'Card / international payment',
            self::MTN => 'Mobile money payment',
            self::ORANGE => 'Mobile money payment',
        };
    }

    public function isSupported(): bool
    {
        return $this === self::STRIPE;
    }
}

<?php

namespace App\Domain\Billing\Services;

use App\Contracts\Billing\PaymentGateway;
use App\Domain\Billing\Enum\PaymentProvider;
use App\Exceptions\Billing\UnsupportedPaymentProviderException;
use App\Infrastructure\Billing\StripePaymentGateway;

class PaymentGatewayResolver
{
    public function resolve(PaymentProvider $provider): PaymentGateway
    {
        return match ($provider) {
            PaymentProvider::STRIPE => app(StripePaymentGateway::class),
            default => throw new UnsupportedPaymentProviderException(
                "Payment provider '{$provider->value}' is not available."
            ),
        };
    }
}

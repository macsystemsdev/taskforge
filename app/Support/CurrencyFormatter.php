<?php

namespace App\Support;

use App\Domain\Billing\Enums\SupportedCurrency;
use Illuminate\Support\Number;

class CurrencyFormatter
{
    /**
     * Format a plan price using its configured currency.
     *
     * Some currencies like XAF are not supported by Number::currency,
     * so we fall back to a simple symbol + number format.
     */
    public static function format(float|int|string $price, string $currency): string
    {
        $price = (float) $price;

        if ($price === 0.0) {
            return 'Free';
        }

        $currency = strtoupper($currency);

        return match ($currency) {
            SupportedCurrency::USD->value => Number::currency($price, 'USD'),
            SupportedCurrency::EUR->value => Number::currency($price, 'EUR'),
            SupportedCurrency::GBP->value => Number::currency($price, 'GBP'),
            default => self::formatWithSymbol($price, $currency),
        };
    }

    /**
     * Fallback formatter for currencies not handled by Number::currency.
     */
    protected static function formatWithSymbol(float $price, string $currency): string
    {
        $symbol = match ($currency) {
            'XAF' => 'XAF',
            default => $currency . ' ',
        };

        return $symbol . number_format($price, 2);
    }
}

<?php

namespace App\Support;

class CurrencyConverter
{
    /**
     * Static rates relative to USD.
     *
     * These are development placeholders. Production should use a
     * trusted FX provider or database-managed rates.
     */
    protected static array $rates = [
        'USD' => ['USD' => 1, 'XAF' => 600, 'EUR' => 0.92, 'GBP' => 0.80],
        'XAF' => ['USD' => 1 / 600, 'XAF' => 1, 'EUR' => 0.92 / 600, 'GBP' => 0.80 / 600],
        'EUR' => ['USD' => 1 / 0.92, 'XAF' => 600 / 0.92, 'EUR' => 1, 'GBP' => 0.80 / 0.92],
        'GBP' => ['USD' => 1 / 0.80, 'XAF' => 600 / 0.80, 'EUR' => 0.92 / 0.80, 'GBP' => 1],
    ];

    public static function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return $amount;
        }

        if (!isset(self::$rates[$from][$to])) {
            return $amount;
        }

        return $amount * self::$rates[$from][$to];
    }
}

<?php

namespace App\Data\Reporting;

use Spatie\LaravelData\Data;

class ChartSeriesData extends Data
{
    /**
     * @param array<string> $labels
     * @param array<int|float> $values
     */
    public function __construct(
        public string $name,

        public array $labels,

        public array $values,
    ) {
    }
}

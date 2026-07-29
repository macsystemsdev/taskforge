<?php

namespace App\Data\Reporting;

use Spatie\LaravelData\Data;

class ReportMetricData extends Data
{
    public function __construct(
        public string $label,

        public string|int|float $value,

        public ?string $description = null,

        public ?string $icon = null,

        public ?string $color = null,
    ) {
    }
}

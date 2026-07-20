<?php

namespace App\Services\Owner\DTO;

use Spatie\LaravelData\Data;

class MetricData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $label,
        public int $value,
        public string $description,
        public string $icon,
        public string $color
    )
    {
        //
    }
}

<?php

namespace App\Services\Owner\DTO;

class MetricData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $label,
        public string|int|float $value,
        public string $description,
        public string $icon,
        public string $color = 'primary',
        public ?string $trend = null,
        //public ?string $url = null
    )
    {
        //
    }
}

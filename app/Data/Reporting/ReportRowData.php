<?php

namespace App\Data\Reporting;

use Spatie\LaravelData\Data;

class ReportRowData extends Data
{
    /**
     * @param array<string, mixed> $columns
     */
    public function __construct(
        public string $title,

        public array $columns,
    ) {
    }
}

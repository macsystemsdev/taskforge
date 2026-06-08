<?php

namespace App\Data\Organizations;

use Spatie\LaravelData\Data;

class UpdateOrganizationData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,

    ) {
        //
    }
}

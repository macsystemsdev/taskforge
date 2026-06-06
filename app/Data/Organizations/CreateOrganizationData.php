<?php

namespace App\Data\Organizations;

use Spatie\LaravelData\Data;

class CreateOrganizationData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public int $owner_id,
        public string $workspace_name
    ) {}
}

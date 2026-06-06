<?php

namespace App\Data\Worksapces;

use Spatie\LaravelData\Data;

class CreateWorkspaceData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}

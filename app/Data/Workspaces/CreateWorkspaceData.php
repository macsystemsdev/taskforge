<?php

namespace App\Data\Workspaces;

use Spatie\LaravelData\Data;

class CreateWorkspaceData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}

<?php

namespace App\Data\Projects;

use Spatie\LaravelData\Data;

class CreateProjectData extends Data
{
    public function __construct(
        public int $owner_id,
        public string $name,
        public ?string $description,
        public ?string $due_date,
    ) {}
}

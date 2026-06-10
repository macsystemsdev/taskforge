<?php

namespace App\Data\Projects;

use Spatie\LaravelData\Data;

class UpdateProjectData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $dueDate,
    ) {}
}